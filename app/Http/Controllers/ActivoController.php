<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activo\StoreActivoRequest;
use App\Http\Requests\Activo\UpdateActivoRequest;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\MantenimientoCalibracion;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Services\InventarioStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ActivoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Activo::with(['articulo.categoria', 'ubicacion', 'responsable', 'proyecto', 'cuadrilla']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo_interno', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%")
                    ->orWhereHas('articulo', function ($qArt) use ($search) {
                        $qArt->where('descripcion', 'like', "%{$search}%")
                            ->orWhere('codigo_sku', 'like', "%{$search}%")
                            ->orWhere('marca', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('articulo_id')) {
            $query->where('articulo_id', $request->input('articulo_id'));
        }

        if ($request->filled('estado_operativo')) {
            $query->where('estado_operativo', $request->input('estado_operativo'));
        }

        if ($request->filled('condicion_prestamo')) {
            $query->where('condicion_prestamo', $request->input('condicion_prestamo'));
        }

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_actual_id', $request->input('ubicacion_id'));
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $proyectoActivoId ?: $request->input('proyecto_id');

        if ($effectiveProyectoId) {
            $query->where('proyecto_actual_id', $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_actual_id', $permitidos);
        }

        $activos = $query->orderBy('codigo_interno')->paginate(15)->withQueryString();

        $baseStatsQuery = Activo::query()
            ->when($effectiveProyectoId, fn ($q) => $q->where('proyecto_actual_id', $effectiveProyectoId))
            ->when(! $effectiveProyectoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos));

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'disponibles' => (clone $baseStatsQuery)->where('condicion_prestamo', 'DISPONIBLE')->count(),
            'prestados' => (clone $baseStatsQuery)->whereIn('condicion_prestamo', ['PRESTADO_CAMPO', 'INSTALADO_PROYECTO'])->count(),
            'mantenimiento' => (clone $baseStatsQuery)->where('estado_operativo', 'EN_MANTENIMIENTO')->count(),
        ];

        $articulos = Articulo::where('control_serie', true)
            ->when($effectiveProyectoId, fn ($q) => $q->where(function ($sub) use ($effectiveProyectoId) {
                $sub->where('proyecto_id', $effectiveProyectoId)
                    ->orWhereHas('activos', fn ($aq) => $aq->where('proyecto_actual_id', $effectiveProyectoId));
            }))
            ->when(! $effectiveProyectoId && $permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)
                    ->orWhereHas('activos', fn ($aq) => $aq->whereIn('proyecto_actual_id', $permitidos))
                    ->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->get();
        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($effectiveProyectoId, function ($q) use ($effectiveProyectoId) {
                $q->where(function ($sub) use ($effectiveProyectoId) {
                    $sub->where('proyecto_id', $effectiveProyectoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->when(! $effectiveProyectoId && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN proyecto_id IS NOT NULL THEN 1 ELSE 2 END')
            ->orderBy('nombre')
            ->get();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();

        return view('activos.index', compact('activos', 'articulos', 'ubicaciones', 'proyectos', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');

        $articulos = Articulo::where('control_serie', true)
            ->where('estado', 'ACTIVO')
            ->when($permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->get();

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN proyecto_id IS NOT NULL THEN 1 ELSE 2 END')
            ->orderBy('nombre')
            ->get();

        $proyectos = $user
            ? $user->proyectosPermitidosQuery()->whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get()
            : Proyecto::whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get();

        // Generate next automatic internal code
        $ultimoActivo = Activo::orderByDesc('id')->first();
        $nextId = $ultimoActivo ? ($ultimoActivo->id + 1) : 1;
        $codigoSugerido = sprintf('ACT-%05d', $nextId);

        return view('activos.create', compact('articulos', 'ubicaciones', 'proyectos', 'codigoSugerido', 'proyectoActivoId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['proyecto_actual_id']) && session('proyecto_activo_id')) {
            $data['proyecto_actual_id'] = session('proyecto_activo_id');
        }
        if (empty($data['proyecto_actual_id']) && ! empty($data['ubicacion_actual_id'])) {
            $ubicacion = Ubicacion::find($data['ubicacion_actual_id']);
            $data['proyecto_actual_id'] = $ubicacion?->proyecto_id;
        }

        $data['responsable_personal_id'] = null;
        $data['cuadrilla_actual_id'] = null;
        $data['condicion_prestamo'] = 'DISPONIBLE';

        $activo = Activo::create($data);

        app(InventarioStockService::class)->syncStockForArticuloUbicacion(
            (int) $activo->articulo_id,
            $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null
        );

        return redirect()->route('activos.show', $activo)
            ->with('success', "Activo {$activo->codigo_interno} registrado en almacén sin asignación inicial.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Activo $activo): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $activo->proyecto_actual_id && ! in_array($activo->proyecto_actual_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este activo.');
        }

        $activo->load(['articulo.categoria', 'ubicacion', 'responsable', 'proyecto', 'cuadrilla', 'mantenimientos', 'despachoDetalles.despacho']);

        // Generate QR code SVG string
        $qrData = route('activos.show', $activo);
        $qrSvg = QrCode::size(160)->margin(1)->generate($qrData);

        return view('activos.show', compact('activo', 'qrSvg'));
    }

    /**
     * Show printable sticker label with QR code.
     */
    public function etiqueta(Activo $activo): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $activo->proyecto_actual_id && ! in_array($activo->proyecto_actual_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este activo.');
        }

        $activo->load(['articulo.categoria', 'ubicacion']);
        $qrData = route('activos.show', $activo);
        $qrSvg = QrCode::size(140)->margin(0)->generate($qrData);

        return view('activos.etiqueta', compact('activo', 'qrSvg'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activo $activo): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $activo->proyecto_actual_id && ! in_array($activo->proyecto_actual_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para editar este activo.');
        }

        $activo->load(['responsable', 'cuadrilla', 'proyecto', 'ubicacion']);

        $articulos = Articulo::where('control_serie', true)
            ->when($permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->get();

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN proyecto_id IS NOT NULL THEN 1 ELSE 2 END')
            ->orderBy('nombre')
            ->get();

        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();

        return view('activos.edit', compact('activo', 'articulos', 'ubicaciones', 'proyectos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivoRequest $request, Activo $activo): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $activo->proyecto_actual_id && ! in_array($activo->proyecto_actual_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar este activo.');
        }

        $data = $request->validated();
        if (empty($data['proyecto_actual_id'])) {
            $data['proyecto_actual_id'] = $activo->proyecto_actual_id;
        }

        // Restricción de negocio: Si el activo está actualmente en taller/calibración con un servicio abierto,
        // no se puede dar de alta arbitrariamente como OPERATIVO desde la edición del activo;
        // el alta debe realizarse de forma controlada desde el módulo de Calibraciones & Taller.
        if ($activo->estado_operativo === 'EN_MANTENIMIENTO' && ($data['estado_operativo'] ?? '') === 'OPERATIVO') {
            $tieneMantenimientoAbierto = $activo->mantenimientos()->where('resultado', 'EN_PROCESO')->exists();
            if ($tieneMantenimientoAbierto) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'estado_operativo' => 'Este equipo tiene un servicio activo en Taller o Calibración. La forma de darle de alta como OPERATIVO es exclusivamente registrando la conformidad y retorno en el módulo de Calibraciones & Taller.',
                    ]);
            }
        }

        $oldUbicacionId = $activo->ubicacion_actual_id;
        $oldArticuloId = $activo->articulo_id;
        $estabaEnMantenimiento = $activo->estado_operativo === 'EN_MANTENIMIENTO';

        $activo->update($data);

        // Si el equipo pasa a estar en mantenimiento, se enlista automáticamente en Calibraciones & Taller
        if (($data['estado_operativo'] ?? '') === 'EN_MANTENIMIENTO' && ! $estabaEnMantenimiento) {
            $tieneMantenimientoAbierto = $activo->mantenimientos()->where('resultado', 'EN_PROCESO')->exists();
            if (! $tieneMantenimientoAbierto) {
                MantenimientoCalibracion::create([
                    'activo_id' => $activo->id,
                    'proyecto_id' => $data['proyecto_actual_id'] ?: $activo->proyecto_actual_id,
                    'tipo' => 'CORRECTIVO',
                    'proveedor_taller' => 'Taller / Laboratorio Especializado',
                    'fecha_ingreso' => now()->toDateString(),
                    'resultado' => 'EN_PROCESO',
                    'descripcion_falla_o_trabajo' => ! empty($data['observaciones']) ? $data['observaciones'] : 'Equipo derivado a taller desde la ficha del activo.',
                    'user_id' => auth()->id() ?? 1,
                ]);
            }
        }

        $stockService = app(InventarioStockService::class);
        $stockService->syncStockForArticuloUbicacion((int) $activo->articulo_id, $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null);
        if ($oldUbicacionId !== $activo->ubicacion_actual_id || $oldArticuloId !== $activo->articulo_id) {
            $stockService->syncStockForArticuloUbicacion((int) $oldArticuloId, $oldUbicacionId ? (int) $oldUbicacionId : null);
        }

        $mensajeExtra = (($data['estado_operativo'] ?? '') === 'EN_MANTENIMIENTO' && ! $estabaEnMantenimiento)
            ? ' y fue enlistado automáticamente en Calibraciones & Taller para su seguimiento.'
            : '.';

        return redirect()->route('activos.show', $activo)
            ->with('success', "Activo {$activo->codigo_interno} actualizado exitosamente{$mensajeExtra}");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activo $activo): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $activo->proyecto_actual_id && ! in_array($activo->proyecto_actual_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para eliminar este activo.');
        }

        if ($activo->despachoDetalles()->exists()) {
            return back()->with('error', 'No se puede eliminar el activo porque registra despachos o préstamos en historial.');
        }

        if ($activo->mantenimientos()->exists()) {
            return back()->with('error', 'No se puede eliminar el activo porque registra calibraciones o mantenimientos.');
        }

        $codigo = $activo->codigo_interno;
        $articuloId = (int) $activo->articulo_id;
        $ubicacionId = $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null;
        $activo->delete();

        app(InventarioStockService::class)->syncStockForArticuloUbicacion($articuloId, $ubicacionId);

        return redirect()->route('activos.index')
            ->with('success', "Activo {$codigo} eliminado satisfactoriamente.");
    }
}
