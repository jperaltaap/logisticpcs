<?php

namespace App\Http\Middleware;

use App\Services\SystemInitializationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemInitialized
{
    /**
     * Permite controlar en tests unitarios si el middleware se ejecuta o se desactiva.
     */
    public static bool $enabledInTests = false;

    public function __construct(
        protected SystemInitializationService $initializationService
    ) {}

    /**
     * Maneja la verificación de estado del sistema:
     * Si no se han configurado los 4 datos obligatorios (Empresa, Proyecto >= 1, Almacén >= 1, Categoría >= 1):
     * - Restringe el acceso a todos los módulos operativos.
     * - Para ADMINISTRADOR: permite únicamente rutas de inicialización y configuración básica
     *   (inicializacion.*, configuracion.empresa*, proyectos.*, ubicaciones.*, categorias.*, logout, profile.*).
     *   Cualquier otra ruta redirige a inicializacion.index.
     * - Para otros roles: no permite acceso a módulos y redirige a inicializacion.espera (o 403 en AJAX).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // En entorno de tests, omitir si no fue activado explícitamente para el test
        if (app()->runningUnitTests() && ! static::$enabledInTests) {
            return $next($request);
        }

        $user = $request->user();

        // Si no está autenticado, dejar que continúe (auth middleware lo manejará)
        if (! $user) {
            return $next($request);
        }

        // Si el sistema ya está inicializado, permitir acceso normal según permisos
        if ($this->initializationService->isInitialized()) {
            return $next($request);
        }

        // Si el usuario es ADMINISTRADOR:
        if ($user->rol === 'ADMINISTRADOR') {
            if ($this->isAllowedForAdmin($request)) {
                return $next($request);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'El sistema requiere completar los datos básicos obligatorios (Empresa, Proyecto, Almacén y Categorías) antes de utilizar este módulo.',
                ], 403);
            }

            return redirect()->route('inicializacion.index')
                ->with('warning', 'Debe completar la configuración básica del sistema (Empresa, Proyecto, Almacén y Categoría) para desbloquear los módulos operativos.');
        }

        // Para usuarios no administradores (Almacén, Supervisor, Técnico, Auditor):
        if ($this->isAllowedForNonAdmin($request)) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'El sistema se encuentra en proceso de configuración inicial por el Administrador.',
            ], 403);
        }

        return redirect()->route('inicializacion.espera');
    }

    /**
     * Rutas permitidas para el Administrador durante la configuración inicial.
     */
    protected function isAllowedForAdmin(Request $request): bool
    {
        return $request->routeIs(
            'inicializacion.*',
            'configuracion.empresa*',
            'proyectos.*',
            'ubicaciones.*',
            'categorias.*',
            'profile.*',
            'logout',
            'storage.file'
        );
    }

    /**
     * Rutas permitidas para otros usuarios durante la configuración inicial.
     */
    protected function isAllowedForNonAdmin(Request $request): bool
    {
        return $request->routeIs(
            'inicializacion.espera',
            'logout',
            'storage.file'
        );
    }
}
