<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /**
     * Muestra el registro de auditoría y log del sistema con filtros avanzados.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless(
            $user && in_array($user->rol, ['ADMINISTRADOR', 'AUDITOR'], true),
            403,
            'Acceso denegado: Solo Administradores y Auditores pueden consultar el Log del Sistema.'
        );

        $search = $request->input('search');
        $accion = $request->input('accion');
        $modulo = $request->input('modulo');
        $usuarioId = $request->input('user_id');
        $proyectoId = $request->input('proyecto_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        if ($proyectoId === null && session('proyecto_activo_id') && ! $request->has('proyecto_id')) {
            $proyectoId = (string) session('proyecto_activo_id');
        }

        $query = SystemLog::with(['usuario', 'proyecto'])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('modulo', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($accion, fn ($q, $accion) => $q->where('accion', $accion))
            ->when($modulo, fn ($q, $modulo) => $q->where('modulo', $modulo))
            ->when($usuarioId, fn ($q, $usuarioId) => $q->where('user_id', $usuarioId))
            ->when($proyectoId, fn ($q, $proyectoId) => $q->where('proyecto_id', $proyectoId))
            ->when($fechaDesde, fn ($q, $fechaDesde) => $q->whereDate('created_at', '>=', $fechaDesde))
            ->when($fechaHasta, fn ($q, $fechaHasta) => $q->whereDate('created_at', '<=', $fechaHasta));

        $stats = [
            'total' => (clone $query)->count(),
            'creaciones' => (clone $query)->where('accion', 'CREACION')->count(),
            'modificaciones' => (clone $query)->where('accion', 'MODIFICACION')->count(),
            'eliminaciones' => (clone $query)->where('accion', 'ELIMINACION')->count(),
            'backups' => (clone $query)->where('accion', 'BACKUP')->count(),
        ];

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        $usuarios = User::withTrashed()->orderBy('name')->get(['id', 'name', 'rol']);
        $proyectos = Proyecto::orderBy('nombre')->get(['id', 'codigo', 'nombre']);
        $modulosDisponibles = SystemLog::select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');

        return view('auditoria.index', compact(
            'logs',
            'stats',
            'usuarios',
            'proyectos',
            'modulosDisponibles',
            'search',
            'accion',
            'modulo',
            'usuarioId',
            'proyectoId',
            'fechaDesde',
            'fechaHasta'
        ));
    }
}
