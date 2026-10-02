<?php

namespace App\Http\Middleware;

use App\Models\NotificacionAlerta;
use App\Models\Proyecto;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetProyectoActivo
{
    /**
     * Detecta y propaga el proyecto activo en sesión.
     * Comparte las variables con todas las vistas del layout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Obtener proyectos permitidos (null = sin restricción para ADMINISTRADOR, LOGISTICO, AUDITOR; array = IDs para SUPERVISOR)
        $permitidosIds = $user ? $user->getProyectosPermitidosIds() : null;

        // 1. Si se solicita cambiar de proyecto activo desde el selector del navbar
        if ($request->has('set_proyecto_activo')) {
            $proyectoId = (int) $request->input('set_proyecto_activo');

            if ($proyectoId === 0) {
                // 0 = Ver todos (sin filtro)
                session()->forget('proyecto_activo_id');
            } else {
                // Validar que si tiene restricción de proyectos, el seleccionado esté permitido
                if ($permitidosIds === null || in_array($proyectoId, $permitidosIds, true)) {
                    $proyecto = Proyecto::find($proyectoId);
                    if ($proyecto) {
                        session(['proyecto_activo_id' => $proyecto->id]);
                    }
                }
            }

            // Redirigir a la misma URL sin el parámetro de cambio de proyecto
            return redirect($request->fullUrlWithQuery(['set_proyecto_activo' => null]));
        }

        // 2. Cargar el proyecto activo desde sesión
        $proyectoActivoId = session('proyecto_activo_id');

        // Si es SUPERVISOR y tiene un proyecto activo en sesión que ya no está permitido, limpiarlo
        if ($permitidosIds !== null && $proyectoActivoId && ! in_array((int) $proyectoActivoId, $permitidosIds, true)) {
            session()->forget('proyecto_activo_id');
            $proyectoActivoId = null;
        }

        // Si es SUPERVISOR y solo tiene 1 proyecto permitido y no tiene ninguno activo en sesión, fijarlo por defecto
        if ($permitidosIds !== null && ! $proyectoActivoId && count($permitidosIds) === 1) {
            $proyectoActivoId = $permitidosIds[0];
            session(['proyecto_activo_id' => $proyectoActivoId]);
        }

        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;

        // Si el proyecto activo ya no existe en BD, limpiar sesión
        if ($proyectoActivoId && ! $proyectoActivo) {
            session()->forget('proyecto_activo_id');
            $proyectoActivo = null;
        }

        // 3. Lista de proyectos disponibles para el selector del navbar y vistas
        $proyectosQuery = Proyecto::where('estado', 'ACTIVO')->orderBy('nombre');
        if ($permitidosIds !== null) {
            $proyectosQuery->whereIn('id', $permitidosIds);
        }

        $proyectosDisponibles = $proyectosQuery->get(['id', 'codigo', 'nombre']);

        // Compartir con todas las vistas
        View::share('proyectoActivo', $proyectoActivo);
        View::share('proyectosDisponibles', $proyectosDisponibles);

        // 4. Compartir alertas operativas y notificaciones en tiempo real para el navbar
        try {
            $alertasQuery = NotificacionAlerta::query();
            if ($proyectoActivoId) {
                $alertasQuery->where(function ($q) use ($proyectoActivoId) {
                    $q->where('proyecto_id', $proyectoActivoId)->orWhereNull('proyecto_id');
                });
            } elseif ($permitidosIds !== null) {
                $alertasQuery->where(function ($q) use ($permitidosIds) {
                    $q->whereIn('proyecto_id', $permitidosIds)->orWhereNull('proyecto_id');
                });
            }

            $conteoAlertasNoLeidas = (clone $alertasQuery)->where('leida', false)->count();
            $notificacionesNavbar = (clone $alertasQuery)->latest('fecha_alerta')->take(5)->get();
        } catch (\Throwable $e) {
            $conteoAlertasNoLeidas = 0;
            $notificacionesNavbar = collect();
        }

        View::share('conteoAlertasNoLeidas', $conteoAlertasNoLeidas);
        View::share('notificacionesNavbar', $notificacionesNavbar);

        return $next($request);
    }
}
