<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuadrilla extends Model
{
    use HasFactory;

    protected $table = 'cuadrillas';

    protected $fillable = [
        'codigo_cuadrilla',
        'nombre',
        'proyecto_id',
        'lider_personal_id',
        'regimen_laboral',
        'estado',
        'observaciones',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'lider_personal_id');
    }

    public function miembrosPivot(): HasMany
    {
        return $this->hasMany(CuadrillaPersonal::class, 'cuadrilla_id');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'cuadrilla_personal', 'cuadrilla_id', 'personal_id')
            ->withPivot('rol_en_cuadrilla', 'fecha_incorporacion', 'fecha_retiro')
            ->wherePivotNull('fecha_retiro')
            ->withTimestamps();
    }

    public function activosAsignados(): HasMany
    {
        return $this->hasMany(Activo::class, 'cuadrilla_actual_id');
    }

    public function despachos(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'cuadrilla_id');
    }
}
