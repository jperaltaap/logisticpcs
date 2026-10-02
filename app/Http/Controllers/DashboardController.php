<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Cuadrilla;
use App\Models\DespachoPrestamo;
use App\Models\Ingreso;
use App\Models\InventarioStock;
use App\Models\KardexMovimiento;
use App\Models\Kit;
use App\Models\MantenimientoCalibracion;
use App\Models\NotificacionAlerta;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\SystemInitializationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct(
        protected SystemInitializationService $initializationService
    ) {}

    /**
     * Consola Ejecutiva y Dashboard General de Operaciones
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        // No mostrar el dashboard si no se tienen los datos básicos configurados
        if (! $this->initializationService->isInitialized()) {
            if ($user?->rol === 'ADMINISTRADOR') {
                return redirect()->route('inicializacion.index');
            }

            return redirect()->route('inicializacion.espera');
        }

        $hoy = now()->toDateString();
        $rol = $user?->rol ?? 'ADMINISTRADOR';
        $esAdminOLogistico = in_array($rol, ['ADMINISTRADOR', 'LOGISTICO'], true);
        $permitidos = $user?->getProyectosPermitidosIds();

        // Si es supervisor y no tiene proyectos permitidos, array vacío
        if ($rol === 'SUPERVISOR' && $permitidos === null) {
            $permitidos = [];
        }

        $proyectoActivoId = session('proyecto_activo_id');
        if ($proyectoActivoId && $permitidos !== null && ! in_array((int) $proyectoActivoId, $permitidos, true)) {
            session()->forget('proyecto_activo_id');
            $proyectoActivoId = null;
        }

        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;

        // Métricas de Kits
        $kitsQuery = Kit::with(['ubicacion', 'componentes.articulo'])
            ->where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos));

        $kitsList = $kitsQuery->get();
        $kitsTotal = $kitsList->count();
        $kitsCompletos = 0;
        $kitsIncompletos = 0;
        $kitsArmablesTotal = 0;
        $topKits = [];

        foreach ($kitsList as $k) {
            $eval = $k->evaluarDisponibilidadEnAlmacen($k->ubicacion_id);
            if ($eval['disponible']) {
                $kitsCompletos++;
            } else {
                $kitsIncompletos++;
            }
            $kitsArmablesTotal += ($eval['kits_armables'] ?? 0);
            if (count($topKits) < 4) {
                $topKits[] = [
                    'kit' => $k,
                    'eval' => $eval,
                ];
            }
        }

        $stats = [
            'categorias' => Categoria::count(),
            'ubicaciones' => Ubicacion::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('proyecto_id', $proyectoActivoId)->orWhereNull('proyecto_id');
                });
            })
                ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                    $q->where(function ($sub) use ($permitidos) {
                        $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
                    });
                })->count(),
            'roles' => Role::count(),
            'users' => User::count(),
            'articulos' => Articulo::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
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
            'activos' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))->count(),
            'kits' => $kitsTotal,
            'kits_completos' => $kitsCompletos,
            'kits_incompletos' => $kitsIncompletos,
            'kits_armables' => $kitsArmablesTotal,
            'proyectos' => $permitidos !== null ? count($permitidos) : Proyecto::where('estado', 'ACTIVO')->count(),
            'personal' => Personal::when($proyectoActivoId, fn ($q) => $q->delProyecto((int) $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->deProyectos($permitidos))->count(),
            'cuadrillas_activas' => Cuadrilla::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->where('estado', 'ACTIVA')->count(),
            'despachos_en_campo' => DespachoPrestamo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->where('estado', 'ENTREGADO_EN_CAMPO')->count(),
            'ingresos_mes' => Ingreso::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereMonth('fecha_ingreso', now()->month)
                ->whereYear('fecha_ingreso', now()->year)
                ->count(),
            'despachos_mes' => DespachoPrestamo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereMonth('fecha_despacho', now()->month)
                ->whereYear('fecha_despacho', now()->year)
                ->count(),
            'alertas_stock' => InventarioStock::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
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
            'alertas_no_leidas' => NotificacionAlerta::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where('proyecto_id', $proyectoActivoId)->orWhereNull('proyecto_id');
            })
                ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                    $q->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
                })
                ->where('leida', false)
                ->count(),
            'personal_campo_hoy' => RosterTurno::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereDate('fecha', $hoy)->where('condicion_laboral', 'TRABAJO_CAMPO')->count(),
            'personal_descanso_hoy' => RosterTurno::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereDate('fecha', $hoy)->where('condicion_laboral', 'BAJADA_DESCANSO')->count(),
            'personal_campamento_hoy' => RosterTurno::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereDate('fecha', $hoy)->where('condicion_laboral', 'DESCANSO_CAMPAMENTO')->count(),
            'personal_permiso_hoy' => RosterTurno::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereDate('fecha', $hoy)->whereIn('condicion_laboral', ['PERMISO', 'LICENCIA_MEDICA'])->count(),
            'activos_operativos' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))
                ->where('estado_operativo', 'OPERATIVO')->count(),
            'activos_prestados' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))
                ->where('condicion_prestamo', 'PRESTADO_CAMPO')->count(),
            'activos_mantenimiento' => Activo::when($proyectoActivoId, fn ($q) => $q->where('proyecto_actual_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_actual_id', $permitidos))
                ->where('estado_operativo', 'EN_MANTENIMIENTO')->count(),
            'movimientos_kardex' => KardexMovimiento::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))->count(),
            'movimientos_kardex_mes' => KardexMovimiento::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->whereMonth('fecha_movimiento', now()->month)
                ->whereYear('fecha_movimiento', now()->year)
                ->count(),
            'calibraciones_pendientes' => MantenimientoCalibracion::when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
                ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
                ->where('resultado', 'EN_PROCESO')->count(),
            'stock_total_unidades' => (int) InventarioStock::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where('inventario_stock.proyecto_id', $proyectoActivoId);
            })
                ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                    $q->whereIn('inventario_stock.proyecto_id', $permitidos);
                })->sum('cantidad_actual'),
        ];

        // 1. Tendencia de Actividad de los últimos 7 días
        $chartTrend = [
            'labels' => [],
            'campo' => [],
            'despachos' => [],
            'kardex' => [],
            'ingresos' => [],
        ];

        $diasMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $dt = now()->subDays($i);
            $fechaStr = $dt->toDateString();
            $diaSemanaEsp = match ($dt->dayOfWeek) {
                0 => 'Dom',
                1 => 'Lun',
                2 => 'Mar',
                3 => 'Mié',
                4 => 'Jue',
                5 => 'Vie',
                6 => 'Sáb',
            };
            $diasMap[$fechaStr] = $diaSemanaEsp.' '.$dt->format('d');
            $chartTrend['labels'][] = $diasMap[$fechaStr];
        }

        $despachosPorDia = DespachoPrestamo::selectRaw('DATE(fecha_despacho) as fecha, count(*) as total')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->where('fecha_despacho', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $ingresosPorDia = Ingreso::selectRaw('DATE(fecha_ingreso) as fecha, count(*) as total')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->where('fecha_ingreso', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $campoPorDia = RosterTurno::selectRaw('fecha, count(*) as total')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->where('fecha', '>=', now()->subDays(6)->toDateString())
            ->where('condicion_laboral', 'TRABAJO_CAMPO')
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $kardexPorDia = KardexMovimiento::selectRaw('DATE(fecha_movimiento) as fecha, count(*) as total')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->where('fecha_movimiento', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        foreach (array_keys($diasMap) as $f) {
            $chartTrend['despachos'][] = (int) ($despachosPorDia[$f] ?? 0);
            $chartTrend['ingresos'][] = (int) ($ingresosPorDia[$f] ?? 0);
            $chartTrend['campo'][] = (int) ($campoPorDia[$f] ?? 0);
            $chartTrend['kardex'][] = (int) ($kardexPorDia[$f] ?? 0);
        }

        // 2. Distribución de Estados (Para Donut Chart)
        $chartDonut = [
            'personal' => [
                'labels' => ['En Campo', 'En Bajada', 'Campamento', 'Permiso/Médico'],
                'data' => [
                    $stats['personal_campo_hoy'],
                    $stats['personal_descanso_hoy'],
                    $stats['personal_campamento_hoy'],
                    $stats['personal_permiso_hoy'],
                ],
                'colors' => ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6'],
                'total' => $stats['personal_campo_hoy'] + $stats['personal_descanso_hoy'] + $stats['personal_campamento_hoy'] + $stats['personal_permiso_hoy'],
            ],
            'activos' => [
                'labels' => ['Operativos', 'Prestados en Campo', 'En Calibración / Taller', 'Disponibles en Almacén'],
                'data' => [
                    $stats['activos_operativos'],
                    $stats['activos_prestados'],
                    $stats['activos_mantenimiento'],
                    max(0, $stats['activos'] - ($stats['activos_prestados'] + $stats['activos_mantenimiento'])),
                ],
                'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#06b6d4'],
                'total' => $stats['activos'],
            ],
            'kits' => [
                'labels' => ['Kits Completos', 'Kits Incompletos'],
                'data' => [
                    $kitsCompletos,
                    $kitsIncompletos,
                ],
                'colors' => ['#10b981', '#ef4444'],
                'total' => $kitsTotal,
            ],
        ];

        $categorias = Categoria::all();
        $ubicaciones = Ubicacion::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
            $q->where(function ($sub) use ($proyectoActivoId) {
                $sub->where('proyecto_id', $proyectoActivoId)->orWhereNull('proyecto_id');
            });
        })
            ->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
                });
            })
            ->get();

        $ultimosDespachos = DespachoPrestamo::with(['proyecto', 'personal', 'cuadrilla', 'usuarioRegistro'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->latest('fecha_despacho')
            ->take(5)
            ->get();

        $ultimosIngresos = Ingreso::with(['proyecto', 'ubicacion', 'usuario'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->latest('fecha_ingreso')
            ->take(5)
            ->get();

        $ultimosMovimientos = KardexMovimiento::with(['articulo', 'ubicacion', 'usuario'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->latest('fecha_movimiento')
            ->take(5)
            ->get();

        $cuadrillasActivas = Cuadrilla::with(['proyecto', 'lider'])
            ->withCount('miembros')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->where('estado', 'ACTIVA')
            ->take(5)
            ->get();

        $alertasStock = InventarioStock::with(['articulo', 'ubicacion'])
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
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
            })
            ->take(5)
            ->get();

        $ultimasCalibraciones = MantenimientoCalibracion::with(['activo', 'proyecto'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->latest('fecha_ingreso')
            ->take(5)
            ->get();

        $personal = $user?->personal;
        if (! $personal && $user?->email) {
            $personal = Personal::where('correo', $user->email)->first();
        }

        $misActivosCustodia = collect();
        $misDespachos = collect();
        $misPrestamosPendientesCount = 0;

        if ($rol === 'TECNICO' && $personal) {
            $misActivosCustodia = Activo::with(['articulo', 'ubicacion'])
                ->where('responsable_personal_id', $personal->id)
                ->where('estado_operativo', 'OPERATIVO')
                ->get();

            $misDespachos = DespachoPrestamo::with(['proyecto', 'ubicacionOrigen', 'detalles.articulo'])
                ->where('personal_id', $personal->id)
                ->latest('fecha_despacho')
                ->take(8)
                ->get();

            $misPrestamosPendientesCount = DespachoPrestamo::where('personal_id', $personal->id)
                ->where('tipo_movimiento', 'SALIDA_PRESTAMO_CAMPO')
                ->whereIn('estado', ['ENTREGADO_EN_CAMPO', 'PARCIALMENTE_DEVUELTO'])
                ->count();
        }

        return view('dashboard', compact(
            'stats',
            'chartTrend',
            'chartDonut',
            'categorias',
            'ubicaciones',
            'ultimosDespachos',
            'ultimosIngresos',
            'ultimosMovimientos',
            'cuadrillasActivas',
            'alertasStock',
            'ultimasCalibraciones',
            'topKits',
            'rol',
            'esAdminOLogistico',
            'proyectoActivoId',
            'proyectoActivo',
            'personal',
            'misActivosCustodia',
            'misDespachos',
            'misPrestamosPendientesCount'
        ));
    }
}
