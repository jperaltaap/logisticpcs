<?php

namespace App\Http\Controllers;

use App\Models\DespachoPrestamo;
use App\Models\Ingreso;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class MovimientoController extends Controller
{
    /**
     * Display a unified mixed history of warehouse entries (Ingresos) and exits/loans (Despachos).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $request->input('proyecto_id') ?: $proyectoActivoId;

        $tipoFlujo = strtoupper((string) $request->input('tipo_flujo', 'TODOS'));
        $ubicacionId = $request->input('ubicacion_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $search = trim((string) $request->input('search', ''));

        $movimientos = collect();
        $totalIngresosCount = 0;
        $totalSalidasCount = 0;
        $totalPrestamosPendientes = 0;

        // 1. Consultar Entradas / Ingresos
        if (in_array($tipoFlujo, ['TODOS', 'INGRESO'], true)) {
            $ingresosQuery = Ingreso::with(['ubicacion', 'proyecto', 'usuario', 'detalles.articulo'])
                ->withCount('detalles');

            if ($search !== '') {
                $ingresosQuery->where(function ($q) use ($search) {
                    $q->where('codigo_ingreso', 'like', "%{$search}%")
                        ->orWhere('proveedor', 'like', "%{$search}%")
                        ->orWhere('numero_comprobante', 'like', "%{$search}%")
                        ->orWhereHas('detalles.articulo', function ($qArt) use ($search) {
                            $qArt->where('descripcion', 'like', "%{$search}%")
                                ->orWhere('codigo_sku', 'like', "%{$search}%");
                        });
                });
            }

            if ($ubicacionId) {
                $ingresosQuery->where('ubicacion_id', $ubicacionId);
            }

            if ($fechaDesde) {
                $ingresosQuery->whereDate('fecha_ingreso', '>=', $fechaDesde);
            }

            if ($fechaHasta) {
                $ingresosQuery->whereDate('fecha_ingreso', '<=', $fechaHasta);
            }

            if ($effectiveProyectoId) {
                $ingresosQuery->where(function ($q) use ($effectiveProyectoId) {
                    $q->where('proyecto_id', $effectiveProyectoId)
                        ->orWhereNull('proyecto_id');
                });
            } elseif ($permitidos !== null) {
                $ingresosQuery->where(function ($q) use ($permitidos) {
                    $q->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            }

            $ingresos = $ingresosQuery->orderByDesc('fecha_ingreso')->orderByDesc('id')->limit(300)->get();
            $totalIngresosCount = $ingresos->count();

            foreach ($ingresos as $ingreso) {
                $resumenItems = $ingreso->detalles->take(3)->map(function ($det) {
                    $desc = $det->articulo?->descripcion ?? 'Artículo';
                    $cant = number_format((float) $det->cantidad, 0);

                    return "{$desc} (x{$cant})";
                })->implode(', ');

                if ($ingreso->detalles_count > 3) {
                    $resumenItems .= ' y '.($ingreso->detalles_count - 3).' más...';
                }

                $movimientos->push((object) [
                    'uid' => 'ING-'.$ingreso->id,
                    'id' => $ingreso->id,
                    'flujo' => 'INGRESO',
                    'subtipo' => $ingreso->tipo_ingreso,
                    'codigo' => $ingreso->codigo_ingreso,
                    'documento_ref' => $ingreso->tipo_comprobante ? "{$ingreso->tipo_comprobante}: {$ingreso->numero_comprobante}" : ($ingreso->numero_comprobante ?: 'S/D'),
                    'fecha' => $ingreso->fecha_ingreso ?? $ingreso->created_at,
                    'proyecto' => $ingreso->proyecto,
                    'ubicacion' => $ingreso->ubicacion,
                    'contraparte' => $ingreso->proveedor ?: 'Ingreso Interno / Sin Proveedor',
                    'contraparte_sub' => $ingreso->ruc_proveedor ? "RUC: {$ingreso->ruc_proveedor}" : $ingreso->tipo_ingreso,
                    'usuario' => $ingreso->usuario?->name ?? 'Sistema',
                    'items_count' => $ingreso->detalles_count,
                    'resumen_items' => $resumenItems ?: 'Sin ítems detallados',
                    'estado' => $ingreso->estado ?? 'PROCESADO',
                    'url_detalle' => route('ingresos.show', $ingreso),
                    'url_impresion' => null,
                ]);
            }
        }

        // 2. Consultar Salidas / Préstamos (Despachos)
        if (in_array($tipoFlujo, ['TODOS', 'SALIDA', 'PRESTAMO', 'CONSUMO'], true)) {
            $despachosQuery = DespachoPrestamo::with([
                'proyecto',
                'personal',
                'cuadrilla',
                'ubicacionOrigen',
                'usuarioRegistro',
                'detalles.articulo',
                'detalles.activo',
            ])->withCount('detalles');

            if ($tipoFlujo === 'PRESTAMO') {
                $despachosQuery->where('tipo_movimiento', 'PRESTAMO_RETORNABLE');
            } elseif ($tipoFlujo === 'CONSUMO') {
                $despachosQuery->where('tipo_movimiento', 'CONSUMO_DEFINITIVO');
            }

            if ($search !== '') {
                $despachosQuery->where(function ($q) use ($search) {
                    $q->where('numero_guia', 'like', "%{$search}%")
                        ->orWhereHas('personal', function ($qPer) use ($search) {
                            $qPer->where('nombres', 'like', "%{$search}%")
                                ->orWhere('apellidos', 'like', "%{$search}%")
                                ->orWhere('dni', 'like', "%{$search}%");
                        })
                        ->orWhereHas('cuadrilla', function ($qCuad) use ($search) {
                            $qCuad->where('nombre', 'like', "%{$search}%")
                                ->orWhere('codigo', 'like', "%{$search}%");
                        })
                        ->orWhereHas('detalles.articulo', function ($qArt) use ($search) {
                            $qArt->where('descripcion', 'like', "%{$search}%")
                                ->orWhere('codigo_sku', 'like', "%{$search}%");
                        });
                });
            }

            if ($ubicacionId) {
                $despachosQuery->where('ubicacion_origen_id', $ubicacionId);
            }

            if ($fechaDesde) {
                $despachosQuery->whereDate('fecha_despacho', '>=', $fechaDesde);
            }

            if ($fechaHasta) {
                $despachosQuery->whereDate('fecha_despacho', '<=', $fechaHasta);
            }

            if ($effectiveProyectoId) {
                $despachosQuery->where('proyecto_id', $effectiveProyectoId);
            } elseif ($permitidos !== null) {
                $despachosQuery->whereIn('proyecto_id', $permitidos);
            }

            $despachos = $despachosQuery->orderByDesc('fecha_despacho')->orderByDesc('id')->limit(300)->get();
            $totalSalidasCount = $despachos->count();
            $totalPrestamosPendientes = $despachos->where('tipo_movimiento', 'PRESTAMO_RETORNABLE')
                ->whereIn('estado', ['DESPACHADO', 'DEVUELTO_PARCIAL'])
                ->count();

            foreach ($despachos as $despacho) {
                $resumenItems = $despacho->detalles->take(3)->map(function ($det) {
                    $desc = $det->articulo?->descripcion ?? 'Artículo';
                    $serie = $det->activo ? " [S/N: {$det->activo->numero_serie}]" : '';
                    $cant = number_format((float) $det->cantidad_despachada, 0);

                    return "{$desc}{$serie} (x{$cant})";
                })->implode(', ');

                if ($despacho->detalles_count > 3) {
                    $resumenItems .= ' y '.($despacho->detalles_count - 3).' más...';
                }

                $receptorNombre = $despacho->personal?->nombre_completo
                    ?? ($despacho->cuadrilla ? "Cuadrilla: {$despacho->cuadrilla->nombre}" : 'Receptor en Obra');
                $receptorSub = $despacho->personal
                    ? "DNI: {$despacho->personal->dni}".($despacho->cuadrilla ? " · {$despacho->cuadrilla->codigo}" : '')
                    : ($despacho->cuadrilla?->codigo ?? 'Asignación directa');

                $movimientos->push((object) [
                    'uid' => 'SAL-'.$despacho->id,
                    'id' => $despacho->id,
                    'flujo' => 'SALIDA',
                    'subtipo' => $despacho->tipo_movimiento === 'PRESTAMO_RETORNABLE' ? 'PRÉSTAMO (CON RETORNO)' : 'SALIDA DEFINITIVA / CONSUMO',
                    'codigo' => $despacho->numero_guia,
                    'documento_ref' => $despacho->fecha_compromiso_retorno
                        ? 'Retorno: '.$despacho->fecha_compromiso_retorno->format('d/m/Y')
                        : 'Sin retorno programado',
                    'fecha' => $despacho->fecha_despacho ?? $despacho->created_at,
                    'proyecto' => $despacho->proyecto,
                    'ubicacion' => $despacho->ubicacionOrigen,
                    'contraparte' => $receptorNombre,
                    'contraparte_sub' => $receptorSub,
                    'usuario' => $despacho->usuarioRegistro?->name ?? 'Sistema',
                    'items_count' => $despacho->detalles_count,
                    'resumen_items' => $resumenItems ?: 'Sin ítems detallados',
                    'estado' => $despacho->estado,
                    'url_detalle' => route('despachos.show', $despacho),
                    'url_impresion' => route('despachos.acta', $despacho),
                ]);
            }
        }

        $sorted = $movimientos->sortByDesc(function ($item) {
            return $item->fecha ? $item->fecha->timestamp : 0;
        })->values();

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedMovimientos = new LengthAwarePaginator(
            $currentItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $proyectos = $user
            ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get()
            : Proyecto::orderBy('nombre')->get();

        $ubicacionesQuery = Ubicacion::where('es_almacen_central', true)->orderBy('nombre');
        if ($effectiveProyectoId) {
            $ubicacionesQuery->where(function ($q) use ($effectiveProyectoId) {
                $q->where('proyecto_id', $effectiveProyectoId)->orWhereNull('proyecto_id');
            });
        } elseif ($permitidos !== null) {
            $ubicacionesQuery->where(function ($q) use ($permitidos) {
                $q->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            });
        }
        $ubicaciones = $ubicacionesQuery->get();

        $stats = [
            'total_operaciones' => $sorted->count(),
            'total_ingresos' => $totalIngresosCount,
            'total_salidas' => $totalSalidasCount,
            'prestamos_pendientes' => $totalPrestamosPendientes,
        ];

        return view('movimientos.index', compact(
            'paginatedMovimientos',
            'proyectos',
            'ubicaciones',
            'stats',
            'tipoFlujo'
        ));
    }
}
