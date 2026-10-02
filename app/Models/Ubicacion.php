<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'proyecto_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'estado',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'ubicacion_actual_id');
    }

    public function inventarioStocks(): HasMany
    {
        return $this->hasMany(InventarioStock::class, 'ubicacion_id');
    }

    public function despachosOrigen(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'ubicacion_origen_id');
    }

    public function despachosDestino(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'ubicacion_destino_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'ubicacion_id');
    }
}
