<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\EmpresaConfig;
use App\Models\Proyecto;
use App\Models\Ubicacion;

class SystemInitializationService
{
    /**
     * Evalúa si el sistema cuenta con los 4 datos básicos obligatorios:
     * 1. Datos de empresa configurados (RUC y Razón Social).
     * 2. Al menos un proyecto registrado.
     * 3. Al menos un almacén o ubicación registrada.
     * 4. Al menos una categoría de artículos registrada.
     */
    public function isInitialized(): bool
    {
        $status = $this->getStatus();

        return $status['is_complete'];
    }

    /**
     * Retorna el detalle del estado de inicialización y avance porcentual.
     *
     * @return array{
     *     is_complete: bool,
     *     completed_count: int,
     *     total_steps: int,
     *     percentage: int,
     *     steps: array<string, array{
     *         key: string,
     *         step_number: int,
     *         title: string,
     *         description: string,
     *         completed: bool,
     *         current: int,
     *         required: int,
     *         route: string,
     *         action_label: string,
     *         icon: string
     *     }>
     * }
     */
    public function getStatus(): array
    {
        $empresa = EmpresaConfig::instancia();
        $empresaCompleted = ! empty($empresa->ruc)
            && ! empty($empresa->razon_social)
            && ! in_array($empresa->razon_social, ['Pendiente de Configuración', 'Mi Empresa S.A.C.'], true);

        $proyectosCount = Proyecto::count();
        $proyectoCompleted = $proyectosCount >= 1;

        $almacenesCount = Ubicacion::count();
        $almacenCompleted = $almacenesCount >= 1;

        $categoriasCount = Categoria::count();
        $categoriaCompleted = $categoriasCount >= 1;

        $steps = [
            'empresa' => [
                'key' => 'empresa',
                'step_number' => 1,
                'title' => 'Datos de Empresa',
                'description' => 'Configuración de Razón Social, RUC, dirección y datos de contacto de la entidad.',
                'completed' => $empresaCompleted,
                'current' => $empresaCompleted ? 1 : 0,
                'required' => 1,
                'route' => route('configuracion.empresa'),
                'action_label' => $empresaCompleted ? 'Editar Empresa' : 'Configurar Empresa',
                'icon' => 'bi-building-gear',
            ],
            'proyecto' => [
                'key' => 'proyecto',
                'step_number' => 2,
                'title' => 'Proyecto Operativo',
                'description' => 'Registro de al menos un proyecto o frente de trabajo para vincular almacenes y operaciones.',
                'completed' => $proyectoCompleted,
                'current' => $proyectosCount,
                'required' => 1,
                'route' => route('proyectos.create'),
                'action_label' => $proyectoCompleted ? 'Gestionar Proyectos' : 'Crear Proyecto',
                'icon' => 'bi-folder2-open',
            ],
            'almacen' => [
                'key' => 'almacen',
                'step_number' => 3,
                'title' => 'Almacén o Ubicación',
                'description' => 'Registro de al menos un almacén principal, centro de acopio o punto de stock físico.',
                'completed' => $almacenCompleted,
                'current' => $almacenesCount,
                'required' => 1,
                'route' => route('ubicaciones.create'),
                'action_label' => $almacenCompleted ? 'Gestionar Almacenes' : 'Crear Almacén',
                'icon' => 'bi-geo-alt-fill',
            ],
            'categoria' => [
                'key' => 'categoria',
                'step_number' => 4,
                'title' => 'Categoría de Artículos',
                'description' => 'Registro de al menos una categoría o familia de materiales para organizar el catálogo maestro.',
                'completed' => $categoriaCompleted,
                'current' => $categoriasCount,
                'required' => 1,
                'route' => route('categorias.create'),
                'action_label' => $categoriaCompleted ? 'Gestionar Categorías' : 'Crear Categoría',
                'icon' => 'bi-tags-fill',
            ],
        ];

        $completedCount = 0;
        foreach ($steps as $step) {
            if ($step['completed']) {
                $completedCount++;
            }
        }

        $totalSteps = count($steps);
        $percentage = (int) round(($completedCount / $totalSteps) * 100);

        return [
            'is_complete' => $completedCount === $totalSteps,
            'completed_count' => $completedCount,
            'total_steps' => $totalSteps,
            'percentage' => $percentage,
            'steps' => $steps,
        ];
    }
}
