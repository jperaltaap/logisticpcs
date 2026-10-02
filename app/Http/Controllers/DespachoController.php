<?php

namespace App\Http\Controllers;

use App\Http\Requests\Despacho\StoreDespachoRequest;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\DespachoDetalle;
use App\Models\DespachoPrestamo;
use App\Models\EmpresaConfig;
use App\Models\Kit;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Services\KardexService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DespachoController extends Controller
{
    public function __construct(
        protected KardexService $kardexService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = DespachoPrestamo::with(['proyecto', 'personal', 'cuadrilla', 'ubicacionOrigen', 'usuarioRegistro', 'detalles.articulo', 'detalles.activo'])
            ->withCount('detalles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('numero_guia', 'like', "%{$search}%")
                    ->orWhereHas('personal', function ($qPer) use ($search) {
                        $qPer->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('dni', 'like', "%{$search}%");
                    })
                    ->orWhereHas('proyecto', function ($qPry) use ($search) {
                        $qPry->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->input('tipo_movimiento'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $request->input('proyecto_id') ?: $proyectoActivoId;

        if ($effectiveProyectoId) {
            $query->where('proyecto_id', $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        $despachos = $query->orderByDesc('id')->paginate(12)->withQueryString();

        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();

        return view('despachos.index', compact('despachos', 'proyectos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        $proyectoActivoId = session('proyecto_activo_id');

        $proyectos = $user ? $user->proyectosPermitidosQuery()->whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get() : Proyecto::whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get();
        $personal = Personal::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->delProyecto((int) $proyectoActivoId))
            ->orderBy('apellidos')
            ->get();
        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('proyecto_id', $proyectoActivoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
            ->orderBy('nombre')
            ->get();

        $articulos = Articulo::with('categoria')
            ->where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->orderBy('descripcion')
            ->get();

        $activosDisponibles = Activo::with(['articulo', 'ubicacion'])
            ->where('estado_operativo', 'OPERATIVO')
            ->where('condicion_prestamo', 'DISPONIBLE')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
            ->orderBy('codigo_interno')
            ->get();

        $kits = Kit::with(['ubicacion', 'componentes.articulo'])
            ->where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->orderBy('nombre_kit')
            ->get();

        // Número de guía sugerido
        $ultimoDespacho = DespachoPrestamo::orderByDesc('id')->first();
        $nextId = $ultimoDespacho ? ($ultimoDespacho->id + 1) : 1;
        $numeroGuiaSugerido = sprintf('DSP-%s-%04d', date('Y'), $nextId);

        return view('despachos.create', compact(
            'proyectos',
            'personal',
            'ubicaciones',
            'articulos',
            'activosDisponibles',
            'kits',
            'numeroGuiaSugerido'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDespachoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['proyecto_id']) && session('proyecto_activo_id')) {
            $data['proyecto_id'] = session('proyecto_activo_id');
        }
        $detalles = $data['detalles'];
        unset($data['detalles']);

        $data['cuadrilla_id'] = null;
        $data['usuario_registro_id'] = auth()->id();
        $data['fecha_despacho'] = $data['fecha_despacho'] ?? now();
        $data['estado'] = 'ENTREGADO_EN_CAMPO';

        try {
            DB::transaction(function () use ($data, $detalles, &$despacho) {
                $despacho = DespachoPrestamo::create($data);

                $kardexTipo = match ($despacho->tipo_movimiento) {
                    'SALIDA_PRESTAMO_CAMPO' => 'SALIDA_PRESTAMO',
                    'CONSUMO_DIRECTO' => 'SALIDA_CONSUMO',
                    'TRANSFERENCIA_UBICACION' => 'TRANSFERENCIA_SALIDA',
                    default => 'SALIDA_PRESTAMO',
                };

                foreach ($detalles as $det) {
                    $articulo = Articulo::findOrFail($det['articulo_id']);
                    $cantidad = (int) round((float) $det['cantidad']);

                    // Crear detalle del despacho
                    $despachoDetalle = DespachoDetalle::create([
                        'despacho_id' => $despacho->id,
                        'articulo_id' => $articulo->id,
                        'activo_id' => $det['activo_id'] ?? null,
                        'kit_id' => $det['kit_id'] ?? null,
                        'cantidad' => $cantidad,
                        'estado_item' => 'ENTREGADO',
                    ]);

                    // Si es un activo serializado individual
                    if (! empty($det['activo_id'])) {
                        $activo = Activo::findOrFail($det['activo_id']);
                        $activo->update([
                            'condicion_prestamo' => 'PRESTADO_CAMPO',
                            'responsable_personal_id' => $despacho->personal_id,
                            'cuadrilla_actual_id' => null,
                            'proyecto_actual_id' => $despacho->proyecto_id,
                            'fecha_ultima_asignacion' => now(),
                        ]);
                    }

                    // Afectar kardex de almacén
                    $this->kardexService->registrarMovimiento(
                        articuloId: $articulo->id,
                        ubicacionId: $despacho->ubicacion_origen_id,
                        tipoMovimiento: $kardexTipo,
                        cantidad: $cantidad,
                        usuarioId: auth()->id(),
                        despachoId: $despacho->id,
                        motivo: "Despacho con Guía {$despacho->numero_guia} hacia Proyecto {$despacho->proyecto->nombre}"
                    );
                }
            });

            return redirect()->route('despachos.show', $despacho)
                ->with('success', "Guía de despacho {$despacho->numero_guia} emitida exitosamente con firma y registro en Kardex.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar el despacho: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DespachoPrestamo $despacho): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($despacho->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este despacho.');
        }

        $despacho->load([
            'proyecto',
            'personal',
            'cuadrilla',
            'ubicacionOrigen',
            'ubicacionDestino',
            'usuarioRegistro',
            'detalles.articulo.categoria',
            'detalles.activo',
            'detalles.kit',
            'detalles.usuarioRecepcionRetorno',
        ]);

        return view('despachos.show', compact('despacho'));
    }

    /**
     * Show printable delivery voucher / acta de entrega con firma.
     */
    public function acta(DespachoPrestamo $despacho): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($despacho->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este despacho.');
        }

        $despacho->load([
            'proyecto',
            'personal',
            'cuadrilla',
            'ubicacionOrigen',
            'usuarioRegistro',
            'detalles.articulo.categoria',
            'detalles.activo',
            'detalles.kit',
        ]);

        $empresa = EmpresaConfig::instancia();

        return view('despachos.acta', compact('despacho', 'empresa'));
    }
}
