<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\EmpresaConfig;
use App\Models\InventarioStock;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    private function initializeBasicSystemData(): void
    {
        EmpresaConfig::instancia()->update([
            'razon_social' => 'LogisticPCS S.A.C.',
            'ruc' => '20601234567',
        ]);

        if (Proyecto::count() === 0) {
            Proyecto::factory()->create();
        }

        if (Ubicacion::count() === 0) {
            Ubicacion::factory()->create();
        }

        if (Categoria::count() === 0) {
            Categoria::factory()->create();
        }
    }

    public function test_authenticated_user_can_access_dashboard_when_system_is_initialized(): void
    {
        $this->initializeBasicSystemData();

        $user = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Consola de Operaciones');
        $response->assertSee('Personalizar Apariencia');
        $response->assertSee('sidebar-user-name');
        $response->assertSee('sidebar-user-role-badge');
        $response->assertSee('user-profile-btn');
    }

    public function test_dashboard_displays_accurate_alertas_stock_and_links_to_solo_bajos(): void
    {
        $this->initializeBasicSystemData();

        $user = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $proyecto = Proyecto::factory()->create(['nombre' => 'Proyecto A']);
        $ubicacion = Ubicacion::factory()->create([
            'proyecto_id' => $proyecto->id,
            'tipo' => 'CENTRO_ACOPIO',
        ]);

        // Artículo con stock crítico (actual 1 <= minimo 5)
        $articuloBajo = Articulo::factory()->create([
            'stock_minimo' => 5.00,
            'control_serie' => false,
            'proyecto_id' => $proyecto->id,
        ]);
        InventarioStock::create([
            'articulo_id' => $articuloBajo->id,
            'ubicacion_id' => $ubicacion->id,
            'proyecto_id' => $proyecto->id,
            'cantidad_actual' => 1.00,
        ]);

        // Artículo con stock óptimo (actual 20 > minimo 5)
        $articuloOptimo = Articulo::factory()->create([
            'stock_minimo' => 5.00,
            'control_serie' => false,
            'proyecto_id' => $proyecto->id,
        ]);
        InventarioStock::create([
            'articulo_id' => $articuloOptimo->id,
            'ubicacion_id' => $ubicacion->id,
            'proyecto_id' => $proyecto->id,
            'cantidad_actual' => 20.00,
        ]);

        // 1. Acceso en modo proyecto activo
        session(['proyecto_activo_id' => $proyecto->id]);
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['alertas_stock'] === 1;
        });
        $response->assertSee(route('inventario.stock', ['solo_bajos' => 1]));

        // 2. Acceso en modo global (todos los proyectos)
        session()->forget('proyecto_activo_id');
        $responseGlobal = $this->actingAs($user)->get('/dashboard');
        $responseGlobal->assertOk();
        $responseGlobal->assertViewHas('stats', function ($stats) {
            return $stats['alertas_stock'] === 1;
        });
    }

    public function test_alerta_escanear_runs_successfully(): void
    {
        $this->initializeBasicSystemData();

        $user = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $proyecto = Proyecto::factory()->create();
        $ubicacion = Ubicacion::factory()->create(['proyecto_id' => $proyecto->id]);
        $articulo = Articulo::factory()->create([
            'stock_minimo' => 10.00,
            'proyecto_id' => $proyecto->id,
        ]);

        InventarioStock::create([
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $ubicacion->id,
            'proyecto_id' => $proyecto->id,
            'cantidad_actual' => 2.00,
        ]);

        $response = $this->actingAs($user)->post(route('alertas.escanear'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('notificaciones_alertas', [
            'tipo' => 'STOCK_MINIMO',
            'referencia_id' => $articulo->id,
        ]);
    }

    public function test_dashboard_redirects_to_inicializacion_window_when_system_is_empty(): void
    {
        $user = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('inicializacion.index'));

        $initResponse = $this->actingAs($user)->get(route('inicializacion.index'));
        $initResponse->assertOk();
        $initResponse->assertSee('Asistente de Puesta en Marcha del Sistema');
        $initResponse->assertSee('Datos de Empresa');
        $initResponse->assertSee('Proyecto Operativo');
        $initResponse->assertSee('Almacén o Ubicación');
        $initResponse->assertSee('Categoría de Artículos');
        $initResponse->assertSee(route('configuracion.empresa'));
        $initResponse->assertSee(route('proyectos.create'));
        $initResponse->assertSee(route('ubicaciones.create'));
        $initResponse->assertSee(route('categorias.create'));
    }
}
