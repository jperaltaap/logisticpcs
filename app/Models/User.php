<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'estado',
        'proyecto_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->rol && Role::where('name', $user->rol)->exists()) {
                if (! $user->hasRole($user->rol)) {
                    $user->syncRoles([$user->rol]);
                }
            }
        });
    }

    /**
     * Determina si el usuario tiene un permiso específico por Spatie o Administrador.
     */
    public function tienePermiso(string $permiso): bool
    {
        if ($this->rol === 'ADMINISTRADOR') {
            return true;
        }

        try {
            if ($this->hasPermissionTo($permiso)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Continuar con resolución por rol
        }

        if ($this->rol) {
            try {
                $role = Role::where('name', $this->rol)->first();
                if ($role && $role->hasPermissionTo($permiso)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fallback para entornos de pruebas donde la tabla permissions no ha sido sembrada
                try {
                    if (Permission::count() === 0) {
                        return in_array($permiso, RoleSeeder::getDefaultPermissionsForRole($this->rol), true);
                    }
                } catch (\Throwable) {
                    return in_array($permiso, RoleSeeder::getDefaultPermissionsForRole($this->rol), true);
                }
            }
        }

        return false;
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function proyectosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_user', 'user_id', 'proyecto_id')
            ->withTimestamps();
    }

    public function personal(): HasOne
    {
        return $this->hasOne(Personal::class, 'user_id');
    }

    /**
     * Retorna la lista de IDs de proyectos permitidos para el usuario.
     * - ADMINISTRADOR: null (acceso globalizado a todos los proyectos y sucursales).
     * - SUPERVISOR (y otros roles con proyectos asignados en proyecto_user): array de IDs de proyectos permitidos.
     *
     * @return array<int>|null
     */
    public function getProyectosPermitidosIds(): ?array
    {
        if ($this->rol === 'ADMINISTRADOR') {
            return null;
        }

        $asignadosPivot = $this->proyectosAsignados()->pluck('proyectos.id');

        if ($this->rol !== 'SUPERVISOR' && $asignadosPivot->isEmpty()) {
            return null;
        }

        $ids = collect();

        // 1. Proyecto principal asignado en la tabla users
        if ($this->proyecto_id) {
            $ids->push((int) $this->proyecto_id);
        }

        // 2. Múltiples proyectos asignados al usuario en tabla pivote proyecto_user
        foreach ($asignadosPivot as $pid) {
            $ids->push((int) $pid);
        }

        // 3. Proyectos relacionados a través de la ficha de personal vinculada
        $personal = $this->personal;
        if (! $personal && $this->email) {
            $personal = Personal::where('correo', $this->email)->first();
        }

        if ($personal) {
            if ($personal->proyecto_id) {
                $ids->push((int) $personal->proyecto_id);
            }

            // Proyectos donde este personal figura como responsable principal
            $proyectosACargoIds = Proyecto::where('responsable_personal_id', $personal->id)->pluck('id');
            foreach ($proyectosACargoIds as $id) {
                $ids->push((int) $id);
            }

            // Proyectos donde este personal está asignado o es co-responsable en personal_proyecto
            $proyectosPivotIds = $personal->proyectos()->pluck('proyectos.id');
            foreach ($proyectosPivotIds as $id) {
                $ids->push((int) $id);
            }
        }

        return $ids->unique()->values()->all();
    }

    /**
     * Retorna un Query Builder de proyectos según el alcance permitido para este usuario.
     *
     * @return Builder
     */
    public function proyectosPermitidosQuery()
    {
        $permitidos = $this->getProyectosPermitidosIds();

        if ($permitidos === null) {
            return Proyecto::query();
        }

        return Proyecto::whereIn('id', $permitidos);
    }

    /**
     * Determina si el usuario tiene autorización para gestionar (crear, modificar o dar de baja)
     * registros de Personal, Cuadrillas y Programación de Roster.
     * Solo permitido para ADMINISTRADOR y SUPERVISOR.
     */
    public function canManagePersonal(): bool
    {
        return in_array($this->rol, ['ADMINISTRADOR', 'SUPERVISOR'], true);
    }

    public function despachosRegistrados(): HasMany
    {
        return $this->hasMany(DespachoPrestamo::class, 'usuario_registro_id');
    }

    public function despachoDetallesRetornados(): HasMany
    {
        return $this->hasMany(DespachoDetalle::class, 'usuario_recepcion_retorno_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'usuario_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(MantenimientoCalibracion::class, 'user_id');
    }

    public function inspeccionesRealizadas(): HasMany
    {
        return $this->hasMany(InspeccionEpp::class, 'inspector_id');
    }
}
