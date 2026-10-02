<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mantenimiento\StoreMantenimientoRequest;
use App\Http\Requests\Mantenimiento\UpdateMantenimientoRequest;
use App\Models\Activo;
use App\Models\MantenimientoCalibracion;
use App\Services\InventarioStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MantenimientoCalibracionController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'en_taller');

        $query = MantenimientoCalibracion::with(['activo.articulo', 'activo.ubicacion', 'user'])
            ->latest('fecha_ingreso');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        } else {
            if ($tab === 'en_taller') {
                $query->where('resultado', 'EN_PROCESO');
            } elseif ($tab === 'concluidos') {
                $query->whereIn('resultado', ['CONFORME_OPERATIVO', 'NO_CONFORME_BAJA']);
            }
        }

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($sub) use ($searchTerm) {
                $sub->whereHas('activo', function ($q) use ($searchTerm) {
                    $q->where('codigo_interno', 'like', "%{$searchTerm}%")
                        ->orWhere('numero_serie', 'like', "%{$searchTerm}%");
                })->orWhere('proveedor_taller', 'like', "%{$searchTerm}%")
                    ->orWhere('descripcion_falla_o_trabajo', 'like', "%{$searchTerm}%");
            });
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        if ($proyectoActivoId) {
            $query->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        $mantenimientos = $query->paginate(15)->withQueryString();

        $statsBase = MantenimientoCalibracion::query();
        if ($proyectoActivoId) {
            $statsBase->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $statsBase->whereIn('proyecto_id', $permitidos);
        }

        $metricas = [
            'total' => (clone $statsBase)->count(),
            'en_taller' => (clone $statsBase)->where('resultado', 'EN_PROCESO')->count(),
            'calibraciones' => (clone $statsBase)->where('tipo', 'CALIBRACION_LAB')->count(),
            'operativos' => (clone $statsBase)->where('resultado', 'CONFORME_OPERATIVO')->count(),
            'concluidos' => (clone $statsBase)->whereIn('resultado', ['CONFORME_OPERATIVO', 'NO_CONFORME_BAJA'])->count(),
        ];

        return view('mantenimientos.index', compact('mantenimientos', 'metricas', 'tab'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');

        $selectedActivoId = $request->get('activo_id');

        $activos = Activo::with('articulo')
            ->where(function ($q) use ($selectedActivoId) {
                $q->whereIn('estado_operativo', ['OPERATIVO', 'EN_MANTENIMIENTO', 'DANADO']);
                if ($selectedActivoId) {
                    $q->orWhere('id', $selectedActivoId);
                }
            })
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))
            ->orderBy('codigo_interno')
            ->get();

        return view('mantenimientos.create', compact('activos', 'selectedActivoId'));
    }

    public function store(StoreMantenimientoRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['user_id'] = Auth::id() ?? 1;

            if (empty($data['proyecto_id'])) {
                $activo = Activo::find($request->activo_id);
                $data['proyecto_id'] = session('proyecto_activo_id') ?: $activo?->proyecto_actual_id;
            }

            MantenimientoCalibracion::create($data);

            $activo = Activo::findOrFail($request->activo_id);
            if ($request->resultado === 'EN_PROCESO') {
                $activo->update(['estado_operativo' => 'EN_MANTENIMIENTO']);
            } elseif ($request->resultado === 'CONFORME_OPERATIVO') {
                $activo->update([
                    'estado_operativo' => 'OPERATIVO',
                    'condicion_prestamo' => 'DISPONIBLE',
                    'fecha_ultimo_retorno' => now(),
                ]);
            } elseif ($request->resultado === 'NO_CONFORME_BAJA') {
                $activo->update([
                    'estado_operativo' => 'DE_BAJA',
                    'condicion_prestamo' => 'BAJA',
                    'fecha_ultimo_retorno' => now(),
                ]);
            }

            app(InventarioStockService::class)->syncStockForArticuloUbicacion(
                (int) $activo->articulo_id,
                $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null
            );
        });

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Registro de mantenimiento o calibración creado exitosamente. El estado del activo ha sido actualizado.');
    }

    public function show(MantenimientoCalibracion $mantenimiento): View
    {
        $mantenimiento->load(['activo.articulo', 'activo.ubicacion', 'user']);

        return view('mantenimientos.show', compact('mantenimiento'));
    }

    public function edit(MantenimientoCalibracion $mantenimiento): View
    {
        $activos = Activo::with('articulo')->orderBy('codigo_interno')->get();

        return view('mantenimientos.edit', compact('mantenimiento', 'activos'));
    }

    public function update(UpdateMantenimientoRequest $request, MantenimientoCalibracion $mantenimiento): RedirectResponse
    {
        DB::transaction(function () use ($request, $mantenimiento) {
            $mantenimiento->update($request->validated());

            $activo = Activo::findOrFail($request->activo_id);
            if ($request->resultado === 'EN_PROCESO') {
                $activo->update(['estado_operativo' => 'EN_MANTENIMIENTO']);
            } elseif ($request->resultado === 'CONFORME_OPERATIVO') {
                $activo->update([
                    'estado_operativo' => 'OPERATIVO',
                    'condicion_prestamo' => 'DISPONIBLE',
                    'fecha_ultimo_retorno' => now(),
                ]);
            } elseif ($request->resultado === 'NO_CONFORME_BAJA') {
                $activo->update([
                    'estado_operativo' => 'DE_BAJA',
                    'condicion_prestamo' => 'BAJA',
                    'fecha_ultimo_retorno' => now(),
                ]);
            }

            app(InventarioStockService::class)->syncStockForArticuloUbicacion(
                (int) $activo->articulo_id,
                $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null
            );
        });

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Mantenimiento o calibración actualizado correctamente.');
    }

    /**
     * Da de alta a un activo desde el módulo de Taller y Calibraciones.
     */
    public function darAlta(Request $request, MantenimientoCalibracion $mantenimiento): RedirectResponse
    {
        $validated = $request->validate([
            'fecha_salida' => ['required', 'date'],
            'resultado' => ['required', 'in:CONFORME_OPERATIVO,NO_CONFORME_BAJA'],
            'proxima_calibracion_sugerida' => ['nullable', 'date', 'after_or_equal:fecha_salida'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'descripcion_falla_o_trabajo' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($mantenimiento, $validated) {
            $datosUpdate = [
                'fecha_salida' => $validated['fecha_salida'],
                'resultado' => $validated['resultado'],
            ];
            if (! empty($validated['proxima_calibracion_sugerida'])) {
                $datosUpdate['proxima_calibracion_sugerida'] = $validated['proxima_calibracion_sugerida'];
            }
            if ($validated['costo'] !== null) {
                $datosUpdate['costo'] = $validated['costo'];
            }
            if (! empty($validated['descripcion_falla_o_trabajo'])) {
                $datosUpdate['descripcion_falla_o_trabajo'] = $mantenimiento->descripcion_falla_o_trabajo
                    ."\n[ALTA OPERATIVA - ".now()->format('d/m/Y H:i').']: '.$validated['descripcion_falla_o_trabajo'];
            }

            $mantenimiento->update($datosUpdate);

            $activo = $mantenimiento->activo;
            if ($validated['resultado'] === 'CONFORME_OPERATIVO') {
                $updateAttrs = [
                    'estado_operativo' => 'OPERATIVO',
                    'condicion_prestamo' => 'DISPONIBLE',
                    'fecha_ultimo_retorno' => now(),
                ];
                $activo->update($updateAttrs);
            } elseif ($validated['resultado'] === 'NO_CONFORME_BAJA') {
                $activo->update([
                    'estado_operativo' => 'DE_BAJA',
                    'condicion_prestamo' => 'BAJA',
                    'fecha_ultimo_retorno' => now(),
                ]);
            }

            app(InventarioStockService::class)->syncStockForArticuloUbicacion(
                (int) $activo->articulo_id,
                $activo->ubicacion_actual_id ? (int) $activo->ubicacion_actual_id : null
            );
        });

        return redirect()->route('mantenimientos.index')
            ->with('success', "El activo {$mantenimiento->activo->codigo_interno} ha sido dado de alta exitosamente, retornó a almacén y su estado cambió a OPERATIVO.");
    }

    public function destroy(MantenimientoCalibracion $mantenimiento): RedirectResponse
    {
        $mantenimiento->delete();

        return redirect()->route('mantenimientos.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}
