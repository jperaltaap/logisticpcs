<?php

namespace App\Http\Controllers;

use App\Exports\ActivosExport;
use App\Exports\InventarioStockExport;
use App\Exports\KardexExport;
use App\Exports\RosterExport;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Cuadrilla;
use App\Models\EmpresaConfig;
use App\Models\InventarioStock;
use App\Models\KardexMovimiento;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\Ubicacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    /**
     * Centro de Reportes y Descarga de Archivos
     */
    public function index(Request $request): View
    {
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
        $categorias = Categoria::orderBy('nombre')->get();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();
        $articulos = Articulo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->take(100)
            ->get();
        $cuadrillas = Cuadrilla::with('proyecto')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('codigo_cuadrilla')
            ->get();

        $metrics = [
            'total_articulos' => Articulo::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('proyecto_id', $proyectoActivoId)
                        ->orWhereHas('activos', fn ($aq) => $aq->where('proyecto_actual_id', $proyectoActivoId))
                        ->orWhereHas('inventarioStocks', fn ($sq) => $sq->where('proyecto_id', $proyectoActivoId));
                });
            })
                ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                    $q->where(function ($sub) use ($permitidos) {
                        $sub->whereIn('proyecto_id', $permitidos)
                            ->orWhereHas('activos', fn ($aq) => $aq->whereIn('proyecto_actual_id', $permitidos))
                            ->orWhereHas('inventarioStocks', fn ($sq) => $sq->whereIn('proyecto_id', $permitidos));
                    });
                })->count(),
            'total_activos' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))->count(),
            'movimientos_kardex' => KardexMovimiento::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))->count(),
            'turnos_roster' => RosterTurno::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))->count(),
            'cuadrillas_activas' => Cuadrilla::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->where('estado', 'ACTIVA')->count(),
            'items_bajo_stock' => InventarioStock::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where('inventario_stock.proyecto_id', $proyectoActivoId);
            })
                ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                    $q->whereIn('inventario_stock.proyecto_id', $permitidos);
                })
                ->whereHas('articulo', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('control_serie', false)
                            ->orWhere('es_instalable', true)
                            ->orWhere('tipo_articulo', 'CONSUMIBLE');
                    })->whereColumn('inventario_stock.cantidad_actual', '<=', 'articulos.stock_minimo');
                })->count(),
        ];

        return view('reportes.index', compact(
            'ubicaciones',
            'categorias',
            'proyectos',
            'articulos',
            'cuadrillas',
            'metrics'
        ));
    }

    /**
     * Exportar Inventario & Stock a Excel (.xlsx)
     */
    public function exportInventario(Request $request): BinaryFileResponse
    {
        $filename = 'Inventario_Stock_LogisticPCS_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new InventarioStockExport(
                $request->filled('ubicacion_id') ? (int) $request->ubicacion_id : null,
                $request->filled('categoria_id') ? (int) $request->categoria_id : null,
                $request->search
            ),
            $filename
        );
    }

    /**
     * Exportar Movimientos de Kardex a Excel (.xlsx)
     */
    public function exportKardex(Request $request): BinaryFileResponse
    {
        $filename = 'Kardex_Movimientos_LogisticPCS_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new KardexExport(
                $request->filled('articulo_id') ? (int) $request->articulo_id : null,
                $request->filled('ubicacion_id') ? (int) $request->ubicacion_id : null,
                $request->tipo_movimiento,
                $request->fecha_inicio,
                $request->fecha_fin
            ),
            $filename
        );
    }

    /**
     * Exportar Programación de Roster 14x7 a Excel (.xlsx)
     */
    public function exportRoster(Request $request): BinaryFileResponse
    {
        $filename = 'Roster_14x7_LogisticPCS_'.now()->format('Ymd_His').'.xlsx';

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $request->filled('proyecto_id') ? (int) $request->proyecto_id : $proyectoActivoId;

        if ($permitidos !== null && $effectiveProyectoId && ! in_array($effectiveProyectoId, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para exportar datos de este proyecto.');
        }

        return Excel::download(
            new RosterExport(
                $effectiveProyectoId,
                $request->filled('mes') ? (int) $request->mes : null,
                $request->filled('anio') ? (int) $request->anio : null,
                $request->grupo_guardia,
                $request->condicion_laboral
            ),
            $filename
        );
    }

    /**
     * Exportar Padrón de Activos Serializados a Excel (.xlsx)
     */
    public function exportActivos(Request $request): BinaryFileResponse
    {
        $filename = 'Padron_Activos_LogisticPCS_'.now()->format('Ymd_His').'.xlsx';

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $request->filled('proyecto_id') ? (int) $request->proyecto_id : $proyectoActivoId;

        if ($permitidos !== null && $effectiveProyectoId && ! in_array($effectiveProyectoId, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para exportar datos de este proyecto.');
        }

        return Excel::download(
            new ActivosExport(
                $request->estado_operativo,
                $request->condicion_prestamo,
                $request->filled('ubicacion_id') ? (int) $request->ubicacion_id : null,
                $effectiveProyectoId,
                $request->search
            ),
            $filename
        );
    }

    /**
     * Generar Reporte de Inventario Consolidado en PDF
     */
    public function pdfInventario(Request $request): Response
    {
        $proyectoActivoId = session('proyecto_activo_id');

        $stocks = InventarioStock::with(['articulo.categoria', 'ubicacion'])
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('inventario_stock.proyecto_id', $proyectoActivoId)
                        ->orWhereHas('articulo', fn ($aq) => $aq->where('proyecto_id', $proyectoActivoId))
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->where('proyecto_id', $proyectoActivoId));
                });
            })
            ->when($request->filled('ubicacion_id'), fn ($q) => $q->where('ubicacion_id', $request->ubicacion_id))
            ->when($request->filled('categoria_id'), fn ($q) => $q->whereHas('articulo', fn ($qa) => $qa->where('categoria_id', $request->categoria_id)))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('articulo', function ($qa) use ($request) {
                    $qa->where('codigo_sku', 'like', "%{$request->search}%")
                        ->orWhere('descripcion', 'like', "%{$request->search}%");
                });
            })
            ->get();

        $ubicacionNombre = $request->filled('ubicacion_id')
            ? Ubicacion::find($request->ubicacion_id)?->nombre
            : 'Todos los Almacenes';

        $categoriaNombre = $request->filled('categoria_id')
            ? Categoria::find($request->categoria_id)?->nombre
            : 'Todas las Categorías';

        $empresa = EmpresaConfig::instancia();
        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;

        $pdf = Pdf::loadView('reportes.pdf.inventario', compact('stocks', 'ubicacionNombre', 'categoriaNombre', 'empresa', 'proyectoActivo'))
            ->setPaper('a4', 'landscape');

        if ($request->boolean('stream')) {
            return $pdf->stream('Reporte_Inventario_LogisticPCS_'.now()->format('Ymd').'.pdf');
        }

        return $pdf->download('Reporte_Inventario_LogisticPCS_'.now()->format('Ymd').'.pdf');
    }

    /**
     * Generar Hoja de Cargo y Asignación Oficial de Cuadrilla en PDF
     */
    public function pdfCuadrillaDotacion(Cuadrilla $cuadrilla, Request $request): Response
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para generar reportes de esta cuadrilla.');
        }

        $cuadrilla->load(['proyecto', 'lider']);

        $miembrosActivos = $cuadrilla->miembrosPivot()
            ->whereNull('fecha_retiro')
            ->with('personal')
            ->get();

        $activosCustodia = Activo::with('articulo')
            ->where('cuadrilla_actual_id', $cuadrilla->id)
            ->get();

        $empresa = EmpresaConfig::instancia();

        $pdf = Pdf::loadView('reportes.pdf.cuadrilla_dotacion', compact('cuadrilla', 'miembrosActivos', 'activosCustodia', 'empresa'))
            ->setPaper('a4', 'portrait');

        if ($request->boolean('stream')) {
            return $pdf->stream('Hoja_Cargo_'.$cuadrilla->codigo_cuadrilla.'_'.now()->format('Ymd').'.pdf');
        }

        return $pdf->download('Hoja_Cargo_'.$cuadrilla->codigo_cuadrilla.'_'.now()->format('Ymd').'.pdf');
    }
}
