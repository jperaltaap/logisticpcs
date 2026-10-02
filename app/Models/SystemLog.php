<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemLog extends Model
{
    protected $table = 'system_logs';

    protected $fillable = [
        'user_id',
        'proyecto_id',
        'accion',
        'modulo',
        'modelo_type',
        'modelo_id',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    /**
     * Helper to safely record a system audit log entry.
     *
     * @param  array<string, mixed>|null  $datosAnteriores
     * @param  array<string, mixed>|null  $datosNuevos
     */
    public static function registrar(
        string $accion,
        string $modulo,
        string $descripcion,
        ?Model $modelo = null,
        ?int $proyectoId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null
    ): ?self {
        try {
            if (! Schema::hasTable('system_logs')) {
                return null;
            }

            $resolvedProyectoId = $proyectoId
                ?? ($modelo?->getAttribute('proyecto_id') ?: $modelo?->getAttribute('proyecto_actual_id'))
                ?? (session()->has('proyecto_activo_id') ? (int) session('proyecto_activo_id') : null);

            return self::create([
                'user_id' => auth()->id(),
                'proyecto_id' => $resolvedProyectoId ?: null,
                'accion' => strtoupper($accion),
                'modulo' => $modulo,
                'modelo_type' => $modelo ? get_class($modelo) : null,
                'modelo_id' => $modelo?->getKey(),
                'descripcion' => mb_substr($descripcion, 0, 495),
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $datosNuevos,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent() ? mb_substr((string) request()->userAgent(), 0, 250) : null,
            ]);
        } catch (Throwable) {
            return null;
        }
    }
}
