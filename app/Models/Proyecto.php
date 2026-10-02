<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    protected $fillable = [
        'codigo',
        'nombre',
        'cliente',
        'ubicacion_direccion',
        'fecha_inicio',
        'fecha_fin_estimada',
        'responsable_personal_id',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_estimada' => 'date',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'responsable_personal_id');
    }

    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'personal_proyecto', 'proyecto_id', 'personal_id')
            ->wherePivot('es_responsable', true)
            ->withPivot('es_responsable', 'rol_en_proyecto')
            ->withTimestamps();
    }

    public function personalAsignado(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'personal_proyecto', 'proyecto_id', 'personal_id')
            ->withPivot('es_responsable', 'rol_en_proyecto')
            ->withTimestamps();
    }

    public function usuariosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proyecto_user', 'proyecto_id', 'user_id')
            ->withTimestamps();
    }

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'proyecto_id');
    }

    public function cuadrillas(): HasMany
    {
        return $this->hasMany(Cuadrilla::class, 'proyecto_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'proyecto_actual_id');
    }

    public function rosterTurnos(): HasMany
    {
        return $this->hasMany(RosterTurno::class, 'proyecto_id');
    }

    public function despachos(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'proyecto_id');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'proyecto_id');
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'proyecto_id');
    }

    public function kits(): HasMany
    {
        return $this->hasMany(Kit::class, 'proyecto_id');
    }

    public function inventarioStocks(): HasMany
    {
        return $this->hasMany(InventarioStock::class, 'proyecto_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'proyecto_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(MantenimientoCalibracion::class, 'proyecto_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionAlerta::class, 'proyecto_id');
    }
}
