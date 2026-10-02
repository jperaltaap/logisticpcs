<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermissions
{
    /**
     * Valida permisos y restricciones según el rol del usuario autenticado:
     * - ADMINISTRADOR: Acceso total y libre gestión a todos los módulos.
     * - LOGISTICO y SUPERVISOR: Acceso a operaciones de proyectos; bloqueados de datos de inicialización (empresa, categorías, usuarios y roles).
     * - AUDITOR: Solo lectura (GET), visualización y exportación de reportes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $rol = $user->rol;

        // 1. El rol ADMINISTRADOR tiene autorización total a todos los módulos y acciones
        if ($rol === 'ADMINISTRADOR') {
            return $next($request);
        }

        // 2. Reglas estrictas para AUDITOR (Solo lectura, reportes y visualización)
        if ($rol === 'AUDITOR') {
            $allowedMethods = ['GET', 'HEAD', 'OPTIONS'];
            $isLogout = $request->is('logout') && $request->isMethod('POST');

            if (! in_array($request->method(), $allowedMethods, true) && ! $isLogout) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Acceso denegado: El rol AUDITOR tiene únicamente permisos de consulta, visualización y exportación.',
                    ], 403);
                }

                return back()->with('error', 'Acceso denegado: El rol AUDITOR cuenta exclusivamente con permisos de visualización y exportación de reportes.');
            }
        }

        // 3. Resolución dinámica del permiso requerido para la ruta y acción actual
        $requiredPermission = $this->resolveRequiredPermission($request);

        if ($requiredPermission && ! $user->tienePermiso($requiredPermission)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => "Acceso denegado: Su rol ({$rol}) no tiene activado el permiso de acceso [{$requiredPermission}].",
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', "Acceso denegado: Su rol ({$rol}) no cuenta con el permiso activo [{$requiredPermission}] para acceder a este módulo o funcionalidad.");
        }

        // 4. Restricción adicional para módulo de cuadrillas/roster si no tiene permisos de proyecto
        if ($request->is('personal*', 'cuadrillas*', 'roster*') && $rol === 'TECNICO' && ! $user->tienePermiso('proyectos.gestionar')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Acceso restringido para el rol TÉCNICO.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Acceso denegado: El rol TÉCNICO no tiene acceso al control de frentes y cuadrillas.');
        }

        return $next($request);
    }

    /**
     * Resuelve el permiso Spatie requerido según el módulo y verbo HTTP.
     */
    protected function resolveRequiredPermission(Request $request): ?string
    {
        $method = $request->method();

        // Mantenimientos & Taller
        if ($request->is('mantenimientos*')) {
            if ($request->is('mantenimientos/*/dar-alta')) {
                return 'mantenimientos.alta_tecnica';
            }
            if ($request->is('mantenimientos/create') || ($request->is('mantenimientos') && $method === 'POST')) {
                return 'mantenimientos.registrar';
            }

            return 'mantenimientos.ver';
        }

        // Centro de Reportes
        if ($request->is('reportes*')) {
            if ($request->is('reportes/export*')) {
                return 'reportes.exportar_excel';
            }
            if ($request->is('reportes/pdf*') || $request->is('reportes/cuadrilla*')) {
                return 'reportes.exportar_pdf';
            }

            return 'reportes.ver';
        }

        // Ingresos de Almacén
        if ($request->is('ingresos*')) {
            if ($request->is('ingresos/create') || $request->is('ingresos/crear-articulo-rapido') || ($request->is('ingresos') && $method === 'POST')) {
                return 'ingresos.gestionar';
            }

            return 'ingresos.ver';
        }

        // Despachos y Devoluciones
        if ($request->is('despachos*')) {
            if ($request->is('despachos/*/devolucion*') || $request->is('despachos/create') || in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return 'despachos.gestionar';
            }

            return 'despachos.ver';
        }

        // Catálogo de Artículos
        if ($request->is('articulos*')) {
            if ($request->is('articulos/create') || in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return 'articulos.gestionar';
            }

            return 'articulos.ver';
        }

        // Activos Serializados (QR)
        if ($request->is('activos*')) {
            if ($request->is('activos/create') || in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return 'activos.gestionar';
            }

            return 'activos.ver';
        }

        // Kits de Almacén
        if ($request->is('kits*')) {
            if ($request->is('kits/create') || in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return 'kits.gestionar';
            }

            return 'kits.ver';
        }

        // Stock disponible
        if ($request->is('inventario/stock*')) {
            return 'stock.ver';
        }

        // Kardex y Movimientos
        if ($request->is('kardex*') || $request->is('movimientos*')) {
            return 'kardex.ver';
        }

        // Centro de Alertas
        if ($request->is('alertas*')) {
            return 'alertas.ver';
        }

        // Configuración y Datos Maestros
        if ($request->is('configuracion/empresa*')) {
            return 'empresa.gestionar';
        }
        if ($request->is('proyectos*')) {
            if (! in_array($method, ['GET', 'HEAD'], true) || $request->is('proyectos/create') || $request->is('proyectos/*/edit')) {
                return 'proyectos.gestionar';
            }

            if ($request->user() && $request->user()->rol === 'TECNICO') {
                return 'proyectos.gestionar';
            }

            return null;
        }
        if ($request->is('ubicaciones*')) {
            if (! in_array($method, ['GET', 'HEAD'], true) || $request->is('ubicaciones/create') || $request->is('ubicaciones/*/edit')) {
                return 'almacenes.gestionar';
            }

            if ($request->user() && $request->user()->rol === 'TECNICO') {
                return 'almacenes.gestionar';
            }

            return null;
        }
        if ($request->is('categorias*')) {
            return 'categorias.gestionar';
        }
        if ($request->is('users*')) {
            return 'usuarios.gestionar';
        }
        if ($request->is('auditoria*')) {
            return 'auditoria.ver';
        }
        if ($request->is('backups*')) {
            return 'backups.gestionar';
        }

        return null;
    }
}
