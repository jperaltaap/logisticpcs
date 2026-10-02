<?php

namespace App\Providers;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Cuadrilla;
use App\Models\DespachoPrestamo;
use App\Models\Ingreso;
use App\Models\Kit;
use App\Models\MantenimientoCalibracion;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\Ubicacion;
use App\Models\User;
use App\Observers\ActivoObserver;
use App\Observers\SystemAuditObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'America/Lima'));

        Paginator::useBootstrapFive();
        Activo::observe(ActivoObserver::class);

        Gate::before(function ($user, $ability) {
            return $user->rol === 'ADMINISTRADOR' ? true : null;
        });

        $modelosAuditables = [
            User::class,
            Proyecto::class,
            Personal::class,
            Cuadrilla::class,
            RosterTurno::class,
            Ubicacion::class,
            Categoria::class,
            Articulo::class,
            Activo::class,
            Kit::class,
            Ingreso::class,
            DespachoPrestamo::class,
            MantenimientoCalibracion::class,
        ];

        foreach ($modelosAuditables as $modeloClass) {
            $modeloClass::observe(SystemAuditObserver::class);
        }
    }
}
