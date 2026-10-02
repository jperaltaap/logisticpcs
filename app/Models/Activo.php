<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activo extends Model
{
    use HasFactory;

    protected $table = 'activos';

    protected $fillable = [
        'articulo_id',
        'ingreso_id',
        'codigo_interno',
        'numero_serie',
        'ubicacion_actual_id',
        'responsable_personal_id',
        'cuadrilla_actual_id',
        'proyecto_actual_id',
        'estado_operativo',
        'condicion_prestamo',
        'fecha_ingreso',
        'fecha_ultima_asignacion',
        'fecha_ultimo_retorno',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_ultima_asignacion' => 'datetime',
            'fecha_ultimo_retorno' => 'datetime',
        ];
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_actual_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'responsable_personal_id');
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class, 'cuadrilla_actual_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_actual_id');
    }

    public function despachoDetalles(): HasMany
    {
        return $this->hasMany(DespachoDetalle::class, 'activo_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(MantenimientoCalibracion::class, 'activo_id');
    }

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id');
    }
}
