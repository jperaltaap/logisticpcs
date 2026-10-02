<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Mapeo oficial de permisos agrupados por módulo.
     *
     * @return array<string, array<string, string>>
     */
    public static function getPermissionsGrouped(): array
    {
        return [
            'Dashboard General & Métricas' => [
                'dashboard.ver' => 'Visualizar Dashboard General',
                'dashboard.metricas_completas' => 'Ver métricas financieras y valorización global',
            ],
            'Catálogo de Artículos & Activos' => [
                'articulos.ver' => 'Consultar catálogo general de artículos',
                'articulos.gestionar' => 'Crear, editar y eliminar artículos',
                'activos.ver' => 'Consultar activos serializados y códigos QR',
                'activos.gestionar' => 'Crear, editar y dar de baja activos serializados',
                'kits.ver' => 'Consultar kits de herramientas',
                'kits.gestionar' => 'Crear, editar y desvincular componentes de kits',
            ],
            'Ingresos, Despachos & Kardex' => [
                'ingresos.ver' => 'Consultar ingresos y recepciones de almacén',
                'ingresos.gestionar' => 'Registrar ingresos de mercadería al almacén',
                'despachos.ver' => 'Consultar despachos, préstamos y actas',
                'despachos.gestionar' => 'Emitir despachos y recepcionar devoluciones',
                'despachos.firmar_receptor' => 'Firmar actas de recepción de herramientas en campo',
                'stock.ver' => 'Consultar existencias y stock disponible',
                'kardex.ver' => 'Consultar historial de movimientos Kardex',
                'alertas.ver' => 'Visualizar y escanear Centro de Alertas',
            ],
            'Calibraciones & Taller (Alta Técnica)' => [
                'mantenimientos.ver' => 'Consultar órdenes de servicio y calibración',
                'mantenimientos.registrar' => 'Enviar activos a taller y registrar diagnóstico',
                'mantenimientos.alta_tecnica' => 'Dar de alta técnica y retorno a almacén',
            ],
            'Centro de Reportes (Excel / PDF)' => [
                'reportes.ver' => 'Acceso al Centro de Reportes',
                'reportes.exportar_excel' => 'Exportar hojas de cálculo en Excel (.xlsx)',
                'reportes.exportar_pdf' => 'Emitir actas e informes ejecutivos en PDF',
            ],
            'Configuración Empresa, Usuarios & Backups' => [
                'empresa.gestionar' => 'Configurar razón social, RUC y logotipo',
                'proyectos.gestionar' => 'Administrar proyectos y frentes de obra',
                'almacenes.gestionar' => 'Administrar centros de almacenamiento',
                'categorias.gestionar' => 'Administrar categorías de bienes',
                'usuarios.gestionar' => 'Gestionar usuarios y permisos por rol',
                'auditoria.ver' => 'Consultar log de auditoría del sistema',
                'backups.gestionar' => 'Generar y descargar copias de seguridad de BD',
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsByModule = self::getPermissionsGrouped();

        foreach ($permissionsByModule as $perms) {
            foreach ($perms as $permName => $desc) {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web']
                );
            }
        }

        $roles = [
            'ADMINISTRADOR',
            'LOGISTICO',
            'SUPERVISOR',
            'TECNICO',
            'AUDITOR',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            if ($roleName === 'ADMINISTRADOR') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions(self::getDefaultPermissionsForRole($roleName));
            }
        }
    }

    /**
     * Matriz de permisos por defecto asignados a cada rol del sistema.
     *
     * @return array<string>
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'ADMINISTRADOR' => array_keys(array_merge(...array_values(self::getPermissionsGrouped()))),
            'LOGISTICO' => [
                'dashboard.ver', 'dashboard.metricas_completas',
                'articulos.ver', 'articulos.gestionar', 'activos.ver', 'activos.gestionar', 'kits.ver', 'kits.gestionar',
                'ingresos.ver', 'ingresos.gestionar', 'despachos.ver', 'despachos.gestionar', 'stock.ver', 'kardex.ver', 'alertas.ver',
                'mantenimientos.ver', 'mantenimientos.registrar', 'mantenimientos.alta_tecnica',
                'reportes.ver', 'reportes.exportar_excel', 'reportes.exportar_pdf',
                'almacenes.gestionar', 'categorias.gestionar',
            ],
            'SUPERVISOR' => [
                'dashboard.ver',
                'articulos.ver', 'activos.ver', 'kits.ver',
                'despachos.ver', 'despachos.gestionar', 'stock.ver', 'alertas.ver',
                'mantenimientos.ver', 'mantenimientos.registrar',
                'reportes.ver', 'reportes.exportar_pdf',
            ],
            'TECNICO' => [
                'dashboard.ver',
                'articulos.ver', 'activos.ver', 'kits.ver',
                'despachos.ver', 'despachos.firmar_receptor', 'stock.ver',
            ],
            'AUDITOR' => [
                'dashboard.ver', 'dashboard.metricas_completas',
                'articulos.ver', 'activos.ver', 'kits.ver',
                'ingresos.ver', 'despachos.ver', 'stock.ver', 'kardex.ver', 'alertas.ver',
                'mantenimientos.ver',
                'reportes.ver', 'reportes.exportar_excel', 'reportes.exportar_pdf',
                'auditoria.ver',
            ],
            default => [],
        };
    }
}
