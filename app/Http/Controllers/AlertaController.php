<?php

namespace App\Http\Controllers;

use App\Models\DespachoPrestamo;
use App\Models\InventarioStock;
use App\Models\MantenimientoCalibracion;
use App\Models\NotificacionAlerta;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        $query = NotificacionAlerta::latest('fecha_alerta');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('leida') && $request->leida !== '') {
            $query->where('leida', (bool) $request->leida);
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        if ($proyectoActivoId) {
            $query->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        $alertas = $query->paginate(15)->withQueryString();

        $statsBase = NotificacionAlerta::query();
        if ($proyectoActivoId) {
            $statsBase->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $statsBase->whereIn('proyecto_id', $permitidos);
        }

        $metricas = [
            'total' => (clone $statsBase)->count(),
            'no_leidas' => (clone $statsBase)->where('leida', false)->count(),
            'stock_minimo' => (clone $statsBase)->where('tipo', 'STOCK_MINIMO')->count(),
            'prestamos_vencidos' => (clone $statsBase)->where('tipo', 'PRESTAMO_VENCIDO')->count(),
            'calibraciones' => (clone $statsBase)->where('tipo', 'CALIBRACION_POR_VENCER')->count(),
        ];

        return view('alertas.index', compact('alertas', 'metricas'));
    }

    public function marcarLeida(NotificacionAlerta $alerta): RedirectResponse
    {
        $alerta->update(['leida' => true]);

        return back()->with('success', 'Alerta marcada como leída.');
    }

    public function marcarTodasLeidas(): RedirectResponse
    {
        NotificacionAlerta::where('leida', false)->update(['leida' => true]);

        return back()->with('success', 'Todas las alertas fueron marcadas como leídas.');
    }

    public function escanear(): RedirectResponse
    {
        $nuevas = 0;
        $proyectoActivoId = session('proyecto_activo_id');

        // 1. Escanear artículos bajo stock mínimo
        $stocksBajos = InventarioStock::with(['articulo', 'ubicacion'])
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('inventario_stock.proyecto_id', $proyectoActivoId)
                        ->orWhereHas('articulo', fn ($aq) => $aq->where('proyecto_id', $proyectoActivoId))
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->where('proyecto_id', $proyectoActivoId));
                });
            })
            ->whereHas('articulo', function ($q) {
                $q->whereColumn('inventario_stock.cantidad_actual', '<=', 'articulos.stock_minimo');
            })
            ->get();

        foreach ($stocksBajos as $stock) {
            $articulo = $stock->articulo;
            if (! $articulo) {
                continue;
            }

            $existe = NotificacionAlerta::where('tipo', 'STOCK_MINIMO')
                ->where('referencia_id', $stock->articulo_id)
                ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->where('created_at', '>=', Carbon::now()->subDays(3))
                ->exists();

            if (! $existe) {
                $ubicacionNombre = $stock->ubicacion?->nombre ?? 'Almacén';
                NotificacionAlerta::create([
                    'proyecto_id' => $stock->proyecto_id ?? $articulo->proyecto_id ?? $proyectoActivoId,
                    'tipo' => 'STOCK_MINIMO',
                    'titulo' => "Stock Bajo: {$articulo->descripcion}",
                    'mensaje' => "El artículo {$articulo->codigo_sku} registra solo {$stock->cantidad_actual} {$articulo->unidad_medida} (mínimo: {$articulo->stock_minimo}) en {$ubicacionNombre}.",
                    'referencia_id' => $stock->articulo_id,
                    'leida' => false,
                    'fecha_alerta' => Carbon::now(),
                ]);
                $nuevas++;
            }
        }

        // 2. Escanear préstamos o salidas a campo con fecha compromiso de retorno vencida
        $prestamosVencidos = DespachoPrestamo::with(['personal', 'cuadrilla', 'proyecto'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->where('tipo_movimiento', 'PRESTAMO_RETORNABLE')
            ->whereIn('estado', ['DESPACHADO', 'DEVUELTO_PARCIAL'])
            ->whereNotNull('fecha_compromiso_retorno')
            ->where('fecha_compromiso_retorno', '<', Carbon::now())
            ->get();

        foreach ($prestamosVencidos as $prestamo) {
            $existe = NotificacionAlerta::where('tipo', 'PRESTAMO_VENCIDO')
                ->where('referencia_id', $prestamo->id)
                ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->exists();

            if (! $existe) {
                $responsable = $prestamo->personal?->nombre_completo
                    ?? ($prestamo->cuadrilla?->nombre ?? 'Personal de Campo');
                $fechaLimite = $prestamo->fecha_compromiso_retorno->format('d/m/Y');

                NotificacionAlerta::create([
                    'proyecto_id' => $prestamo->proyecto_id ?? $proyectoActivoId,
                    'tipo' => 'PRESTAMO_VENCIDO',
                    'titulo' => "Préstamo Vencido: Guía {$prestamo->numero_guia}",
                    'mensaje' => "La salida retornable {$prestamo->numero_guia} asignada a {$responsable} superó su fecha límite de devolución ({$fechaLimite}).",
                    'referencia_id' => $prestamo->id,
                    'leida' => false,
                    'fecha_alerta' => Carbon::now(),
                ]);
                $nuevas++;
            }
        }

        // 3. Escanear calibraciones próximas a vencer (en los próximos 15 días)
        $calibraciones = MantenimientoCalibracion::with(['activo.articulo'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->whereNotNull('proxima_calibracion_sugerida')
            ->where('proxima_calibracion_sugerida', '<=', Carbon::now()->addDays(15))
            ->get();

        foreach ($calibraciones as $cal) {
            if (! $cal->activo) {
                continue;
            }

            $existe = NotificacionAlerta::where('tipo', 'CALIBRACION_POR_VENCER')
                ->where('referencia_id', $cal->activo_id)
                ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->where('created_at', '>=', Carbon::now()->subDays(5))
                ->exists();

            if (! $existe) {
                $dias = Carbon::now()->diffInDays($cal->proxima_calibracion_sugerida, false);
                $diasMsg = $dias < 0 ? 'venció hace '.abs($dias).' días' : "vence en {$dias} días";
                $artDesc = $cal->activo->articulo?->descripcion ?? 'Equipo';

                NotificacionAlerta::create([
                    'proyecto_id' => $cal->proyecto_id ?? $proyectoActivoId,
                    'tipo' => 'CALIBRACION_POR_VENCER',
                    'titulo' => "Calibración por vencer: {$cal->activo->codigo_interno}",
                    'mensaje' => "El equipo {$cal->activo->codigo_interno} ({$artDesc}) requiere calibración periódica ({$diasMsg}).",
                    'referencia_id' => $cal->activo_id,
                    'leida' => false,
                    'fecha_alerta' => Carbon::now(),
                ]);
                $nuevas++;
            }
        }

        return back()->with('success', "Escaneo completado. Se generaron {$nuevas} nuevas alertas.");
    }

    public function destroy(NotificacionAlerta $alerta): RedirectResponse
    {
        $alerta->delete();

        return back()->with('success', 'Alerta eliminada.');
    }
}
