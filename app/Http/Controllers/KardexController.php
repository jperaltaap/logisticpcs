<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\KardexMovimiento;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KardexController extends Controller
{
    /**
     * Display a listing of the Kardex transactional ledger.
     */
    public function index(Request $request): View
    {
        $query = KardexMovimiento::with(['articulo.categoria', 'ubicacion', 'despacho', 'ingreso', 'usuario']);

        if ($request->filled('articulo_id')) {
            $query->where('articulo_id', $request->input('articulo_id'));
        }

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->input('ubicacion_id'));
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->input('tipo_movimiento'));
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        if ($proyectoActivoId) {
            $query->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'like', "%{$search}%")
                    ->orWhereHas('articulo', function ($qArt) use ($search) {
                        $qArt->where('descripcion', 'like', "%{$search}%")
                            ->orWhere('codigo_sku', 'like', "%{$search}%");
                    })
                    ->orWhereHas('despacho', function ($qDsp) use ($search) {
                        $qDsp->where('numero_guia', 'like', "%{$search}%");
                    })
                    ->orWhereHas('ingreso', function ($qIng) use ($search) {
                        $qIng->where('codigo_ingreso', 'like', "%{$search}%");
                    });
            });
        }

        $tiposIngreso = ['INGRESO_COMPRA', 'RETORNO_PRESTAMO', 'TRANSFERENCIA_INGRESO', 'AJUSTE_SOBRANTE'];
        $tiposSalida = ['SALIDA_PRESTAMO', 'SALIDA_CONSUMO', 'TRANSFERENCIA_SALIDA', 'AJUSTE_FALTANTE'];

        $resumen = [
            'total_movimientos' => (clone $query)->count(),
            'total_entradas' => (clone $query)->whereIn('tipo_movimiento', $tiposIngreso)->count(),
            'total_salidas' => (clone $query)->whereIn('tipo_movimiento', $tiposSalida)->count(),
            'articulos_involucrados' => (clone $query)->distinct('articulo_id')->count('articulo_id'),
        ];

        $movimientos = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $articulos = Articulo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->get();
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

        return view('kardex.index', compact('movimientos', 'articulos', 'ubicaciones', 'resumen'));
    }
}
