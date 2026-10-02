<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingreso extends Model
{
    use HasFactory;

    protected $table = 'ingresos';

    protected $fillable = [
        'codigo_ingreso',
        'tipo_ingreso',
        'ubicacion_id',
        'proyecto_id',
        'proveedor',
        'numero_comprobante',
        'fecha_ingreso',
        'usuario_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(IngresoDetalle::class, 'ingreso_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'ingreso_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'ingreso_id');
    }
}
