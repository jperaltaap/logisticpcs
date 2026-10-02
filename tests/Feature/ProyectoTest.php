<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_guests_cannot_access_proyectos(): void
    {
        $response = $this->get('/proyectos');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_proyectos_list(): void
    {
        $pry = Proyecto::factory()->create([
            'codigo' => 'PRY-TEST-01',
            'nombre' => 'Fibra Óptica Lima',
            'cliente' => 'Telefónica',
        ]);

        $response = $this->actingAs($this->admin)->get('/proyectos');

        $response->assertStatus(200);
        $response->assertSee('PRY-TEST-01');
        $response->assertSee('Fibra Óptica Lima');
        $response->assertSee('Telefónica');
    }

    public function test_can_filter_proyectos_by_search(): void
    {
        Proyecto::factory()->create(['nombre' => 'Proyecto Trujillo']);
        Proyecto::factory()->create(['nombre' => 'Proyecto Cusco']);

        $response = $this->actingAs($this->admin)->get('/proyectos?search=Trujillo');

        $response->assertStatus(200);
        $response->assertSee('Proyecto Trujillo');
        $this->assertTrue($response->viewData('proyectos')->contains('nombre', 'Proyecto Trujillo'));
        $this->assertFalse($response->viewData('proyectos')->contains('nombre', 'Proyecto Cusco'));
    }

    public function test_can_create_a_new_proyecto(): void
    {
        $personal = Personal::factory()->create(['estado' => 'ACTIVO']);

        $data = [
            'codigo' => 'PRY-NEW-01',
            'nombre' => 'Construcción Nodo Sur',
            'cliente' => 'Entel Perú',
            'ubicacion_direccion' => 'Av. Arequipa 1234, Lima',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin_estimada' => '2026-10-30',
            'responsable_personal_id' => $personal->id,
            'estado' => 'ACTIVO',
            'observaciones' => 'Nodo de distribución para enlaces de transporte.',
        ];

        $response = $this->actingAs($this->admin)->post('/proyectos', $data);

        $response->assertRedirect('/proyectos');
        $this->assertDatabaseHas('proyectos', [
            'codigo' => 'PRY-NEW-01',
            'cliente' => 'Entel Perú',
            'responsable_personal_id' => $personal->id,
        ]);
    }

    public function test_validation_fails_for_duplicate_proyecto_codigo(): void
    {
        Proyecto::factory()->create(['codigo' => 'PRY-DUP-01']);

        $data = [
            'codigo' => 'PRY-DUP-01',
            'nombre' => 'Proyecto Duplicado',
            'cliente' => 'Cliente X',
            'ubicacion_direccion' => 'Calle 1',
            'fecha_inicio' => '2026-01-01',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->admin)->post('/proyectos', $data);

        $response->assertSessionHasErrors(['codigo']);
    }

    public function test_can_update_an_existing_proyecto(): void
    {
        $pry = Proyecto::factory()->create(['nombre' => 'Nombre Antiguo']);

        $response = $this->actingAs($this->admin)->put("/proyectos/{$pry->id}", [
            'codigo' => $pry->codigo,
            'nombre' => 'Nombre Actualizado',
            'cliente' => $pry->cliente,
            'ubicacion_direccion' => $pry->ubicacion_direccion,
            'fecha_inicio' => $pry->fecha_inicio->format('Y-m-d'),
            'estado' => 'SUSPENDIDO',
        ]);

        $response->assertRedirect('/proyectos');
        $this->assertDatabaseHas('proyectos', [
            'id' => $pry->id,
            'nombre' => 'Nombre Actualizado',
            'estado' => 'SUSPENDIDO',
        ]);
    }

    public function test_cannot_delete_proyecto_if_it_has_assigned_personal(): void
    {
        $pry = Proyecto::factory()->create();
        Personal::factory()->create(['proyecto_id' => $pry->id]);

        $response = $this->actingAs($this->admin)->delete("/proyectos/{$pry->id}");

        $response->assertRedirect('/proyectos');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('proyectos', ['id' => $pry->id]);
    }

    public function test_can_delete_empty_proyecto(): void
    {
        $pry = Proyecto::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/proyectos/{$pry->id}");

        $response->assertRedirect('/proyectos');
        $this->assertDatabaseMissing('proyectos', ['id' => $pry->id]);
    }
}
