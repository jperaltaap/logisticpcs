<?php

namespace Tests\Feature;

use App\Models\Cuadrilla;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupervisorScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        Role::firstOrCreate(['name' => 'LOGISTICO']);
        Role::firstOrCreate(['name' => 'SUPERVISOR']);
        Role::firstOrCreate(['name' => 'AUDITOR']);
    }

    public function test_supervisor_only_sees_assigned_or_in_charge_projects_in_proyectos_index(): void
    {
        $pry1 = Proyecto::factory()->create(['nombre' => 'Proyecto Asignado 1', 'codigo' => 'PRY-001']);
        $pry2 = Proyecto::factory()->create(['nombre' => 'Proyecto Ajeno', 'codigo' => 'PRY-002']);

        $supervisor = User::factory()->create([
            'rol' => 'SUPERVISOR',
            'proyecto_id' => $pry1->id,
            'estado' => 'ACTIVO',
        ]);
        $supervisor->assignRole('SUPERVISOR');

        $response = $this->actingAs($supervisor)->get(route('proyectos.index'));

        $response->assertStatus(200);
        $response->assertSee('Proyecto Asignado 1');
        $response->assertDontSee('Proyecto Ajeno');
    }

    public function test_supervisor_sees_projects_where_personal_is_responsible(): void
    {
        $personal = Personal::factory()->create(['estado' => 'ACTIVO']);
        $pryInCharge = Proyecto::factory()->create([
            'nombre' => 'Proyecto A Cargo',
            'codigo' => 'PRY-CARGO',
            'responsable_personal_id' => $personal->id,
        ]);
        $pryOther = Proyecto::factory()->create([
            'nombre' => 'Proyecto No A Cargo',
            'codigo' => 'PRY-OTRO',
        ]);

        $supervisor = User::factory()->create([
            'rol' => 'SUPERVISOR',
            'proyecto_id' => null,
            'estado' => 'ACTIVO',
            'email' => $personal->correo,
        ]);
        $supervisor->assignRole('SUPERVISOR');
        $personal->update(['user_id' => $supervisor->id]);

        $response = $this->actingAs($supervisor)->get(route('proyectos.index'));

        $response->assertStatus(200);
        $response->assertSee('Proyecto A Cargo');
        $response->assertDontSee('Proyecto No A Cargo');
    }

    public function test_supervisor_cannot_access_unassigned_project_details(): void
    {
        $pryAssigned = Proyecto::factory()->create();
        $pryOther = Proyecto::factory()->create();

        $supervisor = User::factory()->create([
            'rol' => 'SUPERVISOR',
            'proyecto_id' => $pryAssigned->id,
            'estado' => 'ACTIVO',
        ]);
        $supervisor->assignRole('SUPERVISOR');

        $response = $this->actingAs($supervisor)->get(route('proyectos.show', $pryOther));
        $response->assertStatus(403);
    }

    public function test_logistico_can_view_all_projects_and_resources(): void
    {
        $pry1 = Proyecto::factory()->create(['nombre' => 'Proyecto Alpha']);
        $pry2 = Proyecto::factory()->create(['nombre' => 'Proyecto Beta']);

        $logistico = User::factory()->create([
            'rol' => 'LOGISTICO',
            'proyecto_id' => $pry1->id,
            'estado' => 'ACTIVO',
        ]);
        $logistico->assignRole('LOGISTICO');

        $response = $this->actingAs($logistico)->get(route('proyectos.index'));

        $response->assertStatus(200);
        $response->assertSee('Proyecto Alpha');
        $response->assertSee('Proyecto Beta');
    }

    public function test_supervisor_filter_dropdowns_only_contain_permitted_projects(): void
    {
        $pryAssigned = Proyecto::factory()->create(['nombre' => 'Proyecto Supervisor', 'estado' => 'ACTIVO']);
        $pryOther = Proyecto::factory()->create(['nombre' => 'Proyecto Ajeno', 'estado' => 'ACTIVO']);

        $supervisor = User::factory()->create([
            'rol' => 'SUPERVISOR',
            'proyecto_id' => $pryAssigned->id,
            'estado' => 'ACTIVO',
        ]);
        $supervisor->assignRole('SUPERVISOR');

        // Cuadrillas index
        $responseCuadrillas = $this->actingAs($supervisor)->get(route('cuadrillas.index'));
        $responseCuadrillas->assertStatus(200);
        $proyectosView = $responseCuadrillas->viewData('proyectos');
        $this->assertTrue($proyectosView->contains('id', $pryAssigned->id));
        $this->assertFalse($proyectosView->contains('id', $pryOther->id));

        // Personal index
        $responsePersonal = $this->actingAs($supervisor)->get(route('personal.index'));
        $responsePersonal->assertStatus(200);
        $proyectosPersonal = $responsePersonal->viewData('proyectos');
        $this->assertTrue($proyectosPersonal->contains('id', $pryAssigned->id));
        $this->assertFalse($proyectosPersonal->contains('id', $pryOther->id));

        // Despachos index
        $responseDespachos = $this->actingAs($supervisor)->get(route('despachos.index'));
        $responseDespachos->assertStatus(200);
        $proyectosDespachos = $responseDespachos->viewData('proyectos');
        $this->assertTrue($proyectosDespachos->contains('id', $pryAssigned->id));
        $this->assertFalse($proyectosDespachos->contains('id', $pryOther->id));

        // Activos index
        $responseActivos = $this->actingAs($supervisor)->get(route('activos.index'));
        $responseActivos->assertStatus(200);
        $proyectosActivos = $responseActivos->viewData('proyectos');
        $this->assertTrue($proyectosActivos->contains('id', $pryAssigned->id));
        $this->assertFalse($proyectosActivos->contains('id', $pryOther->id));
    }

    public function test_supervisor_resource_listings_only_show_records_from_permitted_projects(): void
    {
        $pryAssigned = Proyecto::factory()->create(['estado' => 'ACTIVO']);
        $pryOther = Proyecto::factory()->create(['estado' => 'ACTIVO']);

        $supervisor = User::factory()->create([
            'rol' => 'SUPERVISOR',
            'proyecto_id' => $pryAssigned->id,
            'estado' => 'ACTIVO',
        ]);
        $supervisor->assignRole('SUPERVISOR');

        $cuadrillaAssigned = Cuadrilla::factory()->create(['proyecto_id' => $pryAssigned->id, 'nombre' => 'Cuadrilla Norte']);
        $cuadrillaOther = Cuadrilla::factory()->create(['proyecto_id' => $pryOther->id, 'nombre' => 'Cuadrilla Sur']);

        $response = $this->actingAs($supervisor)->get(route('cuadrillas.index'));
        $response->assertStatus(200);
        $response->assertSee('Cuadrilla Norte');
        $response->assertDontSee('Cuadrilla Sur');
    }
}
