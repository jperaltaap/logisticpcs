<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal';

    protected $fillable = [
        'codigo_trabajador',
        'codigo_fotocheck',
        'dni',
        'nombres',
        'apellidos',
        'cargo',
        'area',
        'telefono',
        'correo',
        'proyecto_id',
        'user_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombres} {$this->apellidos}")
        );
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'personal_proyecto', 'personal_id', 'proyecto_id')
            ->withPivot('es_responsable', 'rol_en_proyecto')
            ->withTimestamps();
    }

    public function proyectosResponsable(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'personal_proyecto', 'personal_id', 'proyecto_id')
            ->wherePivot('es_responsable', true)
            ->withPivot('es_responsable', 'rol_en_proyecto')
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyectosACargo(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'responsable_personal_id');
    }

    public function cuadrillasLideradas(): HasMany
    {
        return $this->hasMany(Cuadrilla::class, 'lider_personal_id');
    }

    public function cuadrillaMembresias(): HasMany
    {
        return $this->hasMany(CuadrillaPersonal::class, 'personal_id');
    }

    public function cuadrillas(): BelongsToMany
    {
        return $this->belongsToMany(Cuadrilla::class, 'cuadrilla_personal', 'personal_id', 'cuadrilla_id')
            ->withPivot('rol_en_cuadrilla', 'fecha_incorporacion', 'fecha_retiro')
            ->wherePivotNull('fecha_retiro')
            ->withTimestamps();
    }

    public function cuadrillasHistorico(): BelongsToMany
    {
        return $this->belongsToMany(Cuadrilla::class, 'cuadrilla_personal', 'personal_id', 'cuadrilla_id')
            ->withPivot('rol_en_cuadrilla', 'fecha_incorporacion', 'fecha_retiro')
            ->withTimestamps();
    }

    public function rosterTurnos(): HasMany
    {
        return $this->hasMany(RosterTurno::class, 'personal_id');
    }

    public function activosAsignados(): HasMany
    {
        return $this->hasMany(Activo::class, 'responsable_personal_id');
    }

    public function despachos(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'personal_id');
    }

    public function inspeccionesEpp(): HasMany
    {
        return $this->hasMany(InspeccionEpp::class, 'personal_id');
    }

    /**
     * Scope para filtrar personal que pertenece a un proyecto (como principal o en la tabla pivote).
     */
    public function scopeDelProyecto($query, int $proyectoId)
    {
        return $query->where(function ($q) use ($proyectoId) {
            $q->where('personal.proyecto_id', $proyectoId)
                ->orWhereHas('proyectos', fn ($sq) => $sq->where('proyectos.id', $proyectoId));
        });
    }

    /**
     * Scope para filtrar personal dentro de un conjunto de proyectos permitidos.
     *
     * @param  array<int>  $proyectosIds
     */
    public function scopeDeProyectos($query, array $proyectosIds)
    {
        return $query->where(function ($q) use ($proyectosIds) {
            $q->whereIn('personal.proyecto_id', $proyectosIds)
                ->orWhereHas('proyectos', fn ($sq) => $sq->whereIn('proyectos.id', $proyectosIds));
        });
    }

    /**
     * Verifica si el trabajador está asignado al proyecto indicado.
     */
    public function perteneceAlProyecto(int $proyectoId): bool
    {
        if ((int) $this->proyecto_id === $proyectoId) {
            return true;
        }

        if ($this->relationLoaded('proyectos')) {
            return $this->proyectos->contains('id', $proyectoId);
        }

        return $this->proyectos()->where('proyectos.id', $proyectoId)->exists();
    }

    /**
     * Retorna todos los IDs de proyectos a los que pertenece este personal.
     *
     * @return array<int>
     */
    public function getProyectosIdsAttribute(): array
    {
        $ids = $this->relationLoaded('proyectos')
            ? $this->proyectos->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $this->proyectos()->pluck('proyectos.id')->map(fn ($id) => (int) $id)->all();

        if ($this->proyecto_id && ! in_array((int) $this->proyecto_id, $ids, true)) {
            array_unshift($ids, (int) $this->proyecto_id);
        }

        return array_values(array_unique($ids));
    }
}
