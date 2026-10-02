<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Categoria;
use App\Models\InventarioStock;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioStockController extends Controller
{
    /**
     * Vista Consolidada de Stock por Almacén
     */
    public function index(Request $request): View
    {
        $ubicacionId = $request->input('ubicacion_id');
        $categoriaId = $request->input('categoria_id');
        $tipoControl = $request->input('tipo_control'); // 'todos', 'serializado', 'consumible' (o 'fungible')
        $search = $request->input('search');
        $soloBajos = $request->boolean('solo_bajos');

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('proyecto_id', $proyectoActivoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
            ->orderBy('nombre')
            ->get();

        $categorias = Categoria::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
            $q->whereHas('articulos', function ($sq) use ($proyectoActivoId) {
                $sq->where('proyecto_id', $proyectoActivoId)->orWhereNull('proyecto_id');
            });
        })
            ->orderBy('nombre')
            ->get();

        $stocksQuery = InventarioStock::with(['articulo.categoria', 'ubicacion.proyecto', 'proyecto'])
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('inventario_stock.proyecto_id', $proyectoActivoId)
                        ->orWhereHas('articulo', fn ($aq) => $aq->where('proyecto_id', $proyectoActivoId))
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->where('proyecto_id', $proyectoActivoId));
                });
            })
            ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('inventario_stock.proyecto_id', $permitidos)
                        ->orWhereNull('inventario_stock.proyecto_id')
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id'));
                });
            })
            ->when($ubicacionId, fn ($q) => $q->where('ubicacion_id', $ubicacionId))
            ->when($categoriaId, fn ($q) => $q->whereHas('articulo', fn ($qa) => $qa->where('categoria_id', $categoriaId)))
            ->when($tipoControl === 'serializado', fn ($q) => $q->whereHas('articulo', fn ($qa) => $qa->where('control_serie', true)))
            ->when($tipoControl === 'consumible' || $tipoControl === 'fungible', fn ($q) => $q->whereHas('articulo', fn ($qa) => $qa->where('control_serie', false)))
            ->when($soloBajos, function ($q) {
                $q->whereHas('articulo', function ($qa) {
                    $qa->whereColumn('inventario_stock.cantidad_actual', '<=', 'articulos.stock_minimo');
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('articulo', function ($qa) use ($search) {
                    $qa->where('codigo_sku', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%")
                        ->orWhere('marca', 'like', "%{$search}%");
                });
            });

        $stocks = (clone $stocksQuery)
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id')
            ->paginate(15)
            ->withQueryString();

        // Para artículos con control_serie, calcular el desglose de activos en la ubicación
        $stocks->getCollection()->each(function ($st) use ($proyectoActivoId) {
            if ($st->articulo && $st->articulo->control_serie) {
                $activosQuery = Activo::where('articulo_id', $st->articulo_id)
                    ->where('ubicacion_actual_id', $st->ubicacion_id);

                $st->activos_ubicacion_count = (clone $activosQuery)
                    ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                    ->count();

                $st->activos_disponibles_count = (clone $activosQuery)
                    ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                    ->where('condicion_prestamo', 'DISPONIBLE')
                    ->where('estado_operativo', 'OPERATIVO')
                    ->count();

                if ($proyectoActivoId && $st->activos_ubicacion_count > 0) {
                    $st->cantidad_actual = $st->activos_ubicacion_count;
                }
            }
        });

        $baseMetricsQuery = InventarioStock::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
            $q->where(function ($sub) use ($proyectoActivoId) {
                $sub->where('inventario_stock.proyecto_id', $proyectoActivoId)
                    ->orWhereHas('articulo', fn ($aq) => $aq->where('proyecto_id', $proyectoActivoId))
                    ->orWhereHas('ubicacion', fn ($uq) => $uq->where('proyecto_id', $proyectoActivoId));
            });
        })
            ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('inventario_stock.proyecto_id', $permitidos)
                        ->orWhereNull('inventario_stock.proyecto_id')
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id'));
                });
            });

        $metrics = [
            'total_registros' => (clone $baseMetricsQuery)->count(),
            'total_unidades' => (clone $baseMetricsQuery)->whereHas('articulo', fn ($q) => $q->where('control_serie', false))->sum('cantidad_actual'),
            'total_activos' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))->count(),
            'total_bajo_minimo' => (clone $baseMetricsQuery)->whereHas('articulo', function ($q) {
                $q->whereColumn('inventario_stock.cantidad_actual', '<=', 'articulos.stock_minimo');
            })->count(),
            'total_almacenes' => $ubicaciones->count(),
        ];

        return view('inventario.index', compact(
            'stocks',
            'ubicaciones',
            'categorias',
            'ubicacionId',
            'categoriaId',
            'tipoControl',
            'search',
            'soloBajos',
            'metrics'
        ));
    }
}
