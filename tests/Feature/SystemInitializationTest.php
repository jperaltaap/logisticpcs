<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSystemInitialized;
use App\Models\Categoria;
use App\Models\EmpresaConfig;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemInitializationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        EnsureSystemInitialized::$enabledInTests = true;
    }

    protected function tearDown(): void
    {
        EnsureSystemInitialized::$enabledInTests = false;
        parent::tearDown();
    }

    public function test_uninitialized_system_blocks_operational_modules_for_admin_and_redirects_to_inicializacion(): void
    {
        $admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        // Intentar acceder a módulos bloqueados
        $responseDashboard = $this->actingAs($admin)->get('/dashboard');
        $responseDashboard->assertRedirect(route('inicializacion.index'));

        $responseArticulos = $this->actingAs($admin)->get('/articulos');
        $responseArticulos->assertRedirect(route('inicializacion.index'));
        $responseArticulos->assertSessionHas('warning');

        $responseIngresos = $this->actingAs($admin)->get('/ingresos');
        $responseIngresos->assertRedirect(route('inicializacion.index'));

        $responseReportes = $this->actingAs($admin)->get('/reportes');
        $responseReportes->assertRedirect(route('inicializacion.index'));
    }

    public function test_admin_can_access_setup_routes_during_initialization(): void
    {
        $admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($admin)->get(route('inicializacion.index'))->assertOk();
        $this->actingAs($admin)->get(route('configuracion.empresa'))->assertOk();
        $this->actingAs($admin)->get(route('proyectos.create'))->assertOk();
        $this->actingAs($admin)->get(route('ubicaciones.create'))->assertOk();
        $this->actingAs($admin)->get(route('categorias.create'))->assertOk();
    }

    public function test_non_admin_users_are_redirected_to_espera_window_during_initialization(): void
    {
        $userAlmacen = User::factory()->create([
            'rol' => 'ALMACEN',
            'estado' => 'ACTIVO',
        ]);

        // Redirección al intentar acceder al dashboard o módulos
        $responseDashboard = $this->actingAs($userAlmacen)->get('/dashboard');
        $responseDashboard->assertRedirect(route('inicializacion.espera'));

        $responseArticulos = $this->actingAs($userAlmacen)->get('/articulos');
        $responseArticulos->assertRedirect(route('inicializacion.espera'));

        // La ventana de espera carga correctamente informando del proceso
        $responseEspera = $this->actingAs($userAlmacen)->get(route('inicializacion.espera'));
        $responseEspera->assertOk();
        $responseEspera->assertSee('Configuración Inicial en Proceso');
        $responseEspera->assertSee('ALMACEN');
    }

    public function test_system_unlocks_all_modules_and_dashboard_once_the_4_basic_requirements_are_fulfilled(): void
    {
        // 1. Datos de empresa
        EmpresaConfig::instancia()->update([
            'razon_social' => 'Constructora Logistic PCS S.A.C.',
            'ruc' => '20601234567',
        ]);

        // 2. Al menos un proyecto
        $proyecto = Proyecto::factory()->create(['nombre' => 'Proyecto Central 01']);

        // 3. Al menos un almacén
        Ubicacion::factory()->create([
            'proyecto_id' => $proyecto->id,
            'nombre' => 'Almacén Central Lima',
        ]);

        // 4. Al menos una categoría
        Categoria::factory()->create(['nombre' => 'Materiales de Fibra']);

        $admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $almacen = User::factory()->create([
            'rol' => 'ALMACEN',
            'estado' => 'ACTIVO',
        ]);

        // Con los 4 requisitos listos, el dashboard carga sin restricciones
        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Consola de Operaciones');
        $this->actingAs($almacen)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/articulos')->assertOk();
    }

    public function test_system_icon_defaults_to_storage_sistema_icono_and_can_be_customized(): void
    {
        Storage::fake('public');

        $empresa = EmpresaConfig::instancia();
        $empresa->update(['icono_path' => null]);

        // Por defecto apunta a storage/sistema/icono.webp
        $this->assertStringContainsString('storage/sistema/icono.webp', $empresa->fresh()->icono_url);

        $admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $customIcon = UploadedFile::fake()->image('nuevo_icono.webp', 100, 100);

        $response = $this->actingAs($admin)->put(route('configuracion.empresa.update'), [
            'razon_social' => 'Empresa Test',
            'icono' => $customIcon,
        ]);

        $response->assertSessionHas('success');
        $this->assertNotNull($empresa->fresh()->icono_path);
        $this->assertStringContainsString('storage/empresa/', $empresa->fresh()->icono_url);

        // Eliminar ícono personalizado restaura el ícono base
        $deleteResponse = $this->actingAs($admin)->delete(route('configuracion.empresa.icono.destroy'));
        $deleteResponse->assertSessionHas('success');
        $this->assertNull($empresa->fresh()->icono_path);
        $this->assertStringContainsString('storage/sistema/icono.webp', $empresa->fresh()->icono_url);
    }
}
