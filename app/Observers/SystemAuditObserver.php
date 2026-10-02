<?php

namespace App\Observers;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;

class SystemAuditObserver
{
    /**
     * Map model classes to friendly module names.
     *
     * @var array<string, string>
     */
    protected array $modulos = [
        'App\Models\User' => 'USUARIOS',
        'App\Models\Proyecto' => 'PROYECTOS',
        'App\Models\Personal' => 'PERSONAL',
        'App\Models\Cuadrilla' => 'CUADRILLAS',
        'App\Models\RosterTurno' => 'ROSTER',
        'App\Models\Ubicacion' => 'ALMACENES',
        'App\Models\Categoria' => 'CATEGORIAS',
        'App\Models\Articulo' => 'ARTICULOS',
        'App\Models\Activo' => 'ACTIVOS_SERIALIZADOS',
        'App\Models\Kit' => 'KITS',
        'App\Models\Ingreso' => 'INGRESOS_ALMACEN',
        'App\Models\DespachoPrestamo' => 'DESPACHOS_SALIDAS',
        'App\Models\MantenimientoCalibracion' => 'MANTENIMIENTO',
    ];

    public function created(Model $model): void
    {
        if ($model instanceof SystemLog) {
            return;
        }

        $modulo = $this->resolveModulo($model);
        $etiqueta = $this->resolveEtiqueta($model);
        $nuevos = $this->sanitizeAttributes($model->getAttributes());

        SystemLog::registrar(
            accion: 'CREACION',
            modulo: $modulo,
            descripcion: "Nuevo registro en {$modulo}: {$etiqueta}",
            modelo: $model,
            datosAnteriores: null,
            datosNuevos: $nuevos
        );
    }

    public function updated(Model $model): void
    {
        if ($model instanceof SystemLog) {
            return;
        }

        $changes = $this->sanitizeAttributes($model->getChanges());
        unset($changes['updated_at'], $changes['remember_token']);

        if (empty($changes)) {
            return;
        }

        $original = [];
        foreach (array_keys($changes) as $key) {
            $original[$key] = $model->getOriginal($key);
        }
        $original = $this->sanitizeAttributes($original);

        $modulo = $this->resolveModulo($model);
        $etiqueta = $this->resolveEtiqueta($model);
        $campos = implode(', ', array_keys($changes));

        SystemLog::registrar(
            accion: 'MODIFICACION',
            modulo: $modulo,
            descripcion: "Actualización en {$modulo} ({$etiqueta}) — Campos modificados: {$campos}",
            modelo: $model,
            datosAnteriores: $original,
            datosNuevos: $changes
        );
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof SystemLog) {
            return;
        }

        $modulo = $this->resolveModulo($model);
        $etiqueta = $this->resolveEtiqueta($model);
        $anteriores = $this->sanitizeAttributes($model->getOriginal());

        SystemLog::registrar(
            accion: 'ELIMINACION',
            modulo: $modulo,
            descripcion: "Eliminación / Baja en {$modulo}: {$etiqueta}",
            modelo: $model,
            datosAnteriores: $anteriores,
            datosNuevos: null
        );
    }

    protected function resolveModulo(Model $model): string
    {
        $class = get_class($model);

        return $this->modulos[$class] ?? strtoupper(class_basename($model));
    }

    protected function resolveEtiqueta(Model $model): string
    {
        foreach (['codigo_despacho', 'codigo_ingreso', 'codigo_interno', 'codigo_sku', 'codigo_cuadrilla', 'codigo_kit', 'codigo', 'dni', 'nombre', 'name', 'descripcion'] as $attr) {
            $val = $model->getAttribute($attr);
            if (! empty($val)) {
                return (string) $val.' (ID #'.$model->getKey().')';
            }
        }

        return 'ID #'.$model->getKey();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function sanitizeAttributes(array $attributes): array
    {
        unset($attributes['password'], $attributes['remember_token']);

        return $attributes;
    }
}
