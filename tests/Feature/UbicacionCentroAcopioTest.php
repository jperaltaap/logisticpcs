<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionCentroAcopioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create([
            'codigo' => 'PRY-2026-TEST',
            'nombre' => 'Proyecto Fibra Óptica Test',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_can_list_ubicaciones_with_active_project_and_global_warehouses(): void
    {
        $global = Ubicacion::factory()->create([
            'codigo' => 'ALM-GLOBAL',
            'nombre' => 'Almacén Base Global',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => null,
        ]);

        $centroAcopio = Ubicacion::factory()->create([
            'codigo' => 'ACOPIO-PRY1',
            'nombre' => 'Centro de Acopio Proyecto 1',
            'tipo' => 'CENTRO_ACOPIO',
            'proyecto_id' => $this->proyecto->id,
        ]);

        $otroProyecto = Proyecto::factory()->create();
        $otroAcopio = Ubicacion::factory()->create([
            'codigo' => 'ACOPIO-PRY2',
            'nombre' => 'Centro de Acopio Proyecto 2',
            'tipo' => 'CENTRO_ACOPIO',
            'proyecto_id' => $otroProyecto->id,
        ]);

        // When active project is set, shows project warehouses + global warehouses
        $response = $this->actingAs($this->admin)
            ->withSession(['proyecto_activo_id' => $this->proyecto->id])
            ->get(route('ubicaciones.index'));

        $response->assertOk();
        $response->assertSee('Centro de Acopio Proyecto 1');
        $response->assertSee('Almacén Base Global');
        $response->assertDontSee('Centro de Acopio Proyecto 2');
    }

    public function test_can_create_centro_de_acopio_assigned_to_project(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['proyecto_activo_id' => $this->proyecto->id])
            ->post(route('ubicaciones.store'), [
                'codigo' => 'ACOPIO-VIRU',
                'nombre' => 'Centro de Acopio Virú Norte',
                'descripcion' => 'Centro de acopio para despliegue de postes y fibra',
                'tipo' => 'CENTRO_ACOPIO',
                'proyecto_id' => $this->proyecto->id,
                'estado' => 'ACTIVO',
            ]);

        $response->assertRedirect(route('ubicaciones.index'));

        $this->assertDatabaseHas('ubicaciones', [
            'codigo' => 'ACOPIO-VIRU',
            'nombre' => 'Centro de Acopio Virú Norte',
            'tipo' => 'CENTRO_ACOPIO',
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_can_create_global_almacen_central_without_project(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('ubicaciones.store'), [
                'codigo' => 'ALM-CENTRAL-BASE',
                'nombre' => 'Almacén Central Principal Lima',
                'descripcion' => 'Base central general',
                'tipo' => 'ALMACEN_CENTRAL',
                'proyecto_id' => null,
                'estado' => 'ACTIVO',
            ]);

        $response->assertRedirect(route('ubicaciones.index'));

        $this->assertDatabaseHas('ubicaciones', [
            'codigo' => 'ALM-CENTRAL-BASE',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => null,
        ]);
    }

    public function test_can_update_ubicacion_type_and_project(): void
    {
        $ubicacion = Ubicacion::factory()->create([
            'codigo' => 'ALM-MODIF',
            'nombre' => 'Almacén Temporal',
            'tipo' => 'ALMACEN_OBRA',
            'proyecto_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('ubicaciones.update', $ubicacion), [
                'codigo' => 'ALM-MODIF',
                'nombre' => 'Centro de Acopio Oficial',
                'tipo' => 'CENTRO_ACOPIO',
                'proyecto_id' => $this->proyecto->id,
                'estado' => 'ACTIVO',
            ]);

        $response->assertRedirect(route('ubicaciones.index'));

        $this->assertDatabaseHas('ubicaciones', [
            'id' => $ubicacion->id,
            'nombre' => 'Centro de Acopio Oficial',
            'tipo' => 'CENTRO_ACOPIO',
            'proyecto_id' => $this->proyecto->id,
        ]);
    }

    public function test_cannot_delete_ubicacion_with_physical_assets(): void
    {
        $ubicacion = Ubicacion::factory()->create([
            'codigo' => 'ALM-CON-ACTIVOS',
            'tipo' => 'ALMACEN_CENTRAL',
        ]);

        $articulo = Articulo::factory()->serializado()->create();
        Activo::factory()->create([
            'articulo_id' => $articulo->id,
            'ubicacion_actual_id' => $ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('ubicaciones.destroy', $ubicacion));

        $response->assertRedirect(route('ubicaciones.index'));
        $this->assertDatabaseHas('ubicaciones', ['id' => $ubicacion->id]);
    }
}
