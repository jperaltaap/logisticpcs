<?php

namespace Tests\Feature;

use App\Models\Cuadrilla;
use App\Models\DespachoPrestamo;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuadrillaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    private Personal $lider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create();
        $this->lider = Personal::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_guests_cannot_access_cuadrillas(): void
    {
        $response = $this->get('/cuadrillas');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_cuadrillas_list(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-TEST-01',
            'nombre' => 'Cuadrilla Empalme Test',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/cuadrillas');

        $response->assertStatus(200);
        $response->assertSee('CD-TEST-01');
        $response->assertSee('Cuadrilla Empalme Test');
    }

    public function test_can_filter_cuadrillas_by_search(): void
    {
        Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-ALPHA',
            'nombre' => 'Cuadrilla Alfa Norte',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $lider2 = Personal::factory()->create([
            'nombres' => 'Carlos',
            'apellidos' => 'Santana',
            'proyecto_id' => $this->proyecto->id,
        ]);

        Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-BETA',
            'nombre' => 'Cuadrilla Beta Sur',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $lider2->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/cuadrillas?search=Alfa');

        $response->assertStatus(200);
        $response->assertSee('CD-ALPHA');
        $response->assertDontSee('CD-BETA');
    }

    public function test_can_create_cuadrilla_and_leader_is_automatically_added_as_member(): void
    {
        $data = [
            'codigo_cuadrilla' => 'CD-109',
            'nombre' => 'Cuadrilla Fusión Nueva',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
            'regimen_laboral' => '14x7',
            'estado' => 'ACTIVA',
            'observaciones' => 'Test de creación automática',
        ];

        $response = $this->actingAs($this->admin)->post('/cuadrillas', $data);

        $this->assertDatabaseHas('cuadrillas', [
            'codigo_cuadrilla' => 'CD-109',
            'nombre' => 'Cuadrilla Fusión Nueva',
        ]);

        $cuadrilla = Cuadrilla::where('codigo_cuadrilla', 'CD-109')->first();
        $response->assertRedirect("/cuadrillas/{$cuadrilla->id}");

        // El líder debe estar como miembro activo
        $this->assertDatabaseHas('cuadrilla_personal', [
            'cuadrilla_id' => $cuadrilla->id,
            'personal_id' => $this->lider->id,
            'rol_en_cuadrilla' => 'LIDER DE CUADRILLA',
            'fecha_retiro' => null,
        ]);
    }

    public function test_can_add_member_to_cuadrilla(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $tecnico = Personal::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->admin)->post("/cuadrillas/{$cuad->id}/miembros", [
            'personal_id' => $tecnico->id,
            'rol_en_cuadrilla' => 'TECNICO EMPALMADOR',
            'fecha_incorporacion' => now()->toDateString(),
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('cuadrilla_personal', [
            'cuadrilla_id' => $cuad->id,
            'personal_id' => $tecnico->id,
            'rol_en_cuadrilla' => 'TECNICO EMPALMADOR',
            'fecha_retiro' => null,
        ]);
    }

    public function test_can_retire_member_from_cuadrilla(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $tecnico = Personal::factory()->create();

        $miembro = $cuad->miembrosPivot()->create([
            'personal_id' => $tecnico->id,
            'rol_en_cuadrilla' => 'AYUDANTE',
            'fecha_incorporacion' => now()->subMonth()->toDateString(),
            'fecha_retiro' => null,
        ]);

        $response = $this->actingAs($this->admin)->delete("/cuadrillas/{$cuad->id}/miembros/{$tecnico->id}");

        $response->assertSessionHas('status');

        $this->assertNotNull($miembro->fresh()->fecha_retiro);
    }

    public function test_can_update_cuadrilla(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-ORIG',
            'nombre' => 'Nombre Original',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
            'estado' => 'ACTIVA',
        ]);

        $response = $this->actingAs($this->admin)->put("/cuadrillas/{$cuad->id}", [
            'codigo_cuadrilla' => 'CD-ORIG',
            'nombre' => 'Nombre Modificado',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
            'regimen_laboral' => '21x7',
            'estado' => 'EN_DESCANSO',
            'observaciones' => 'Turno cambiado a descanso',
        ]);

        $response->assertRedirect("/cuadrillas/{$cuad->id}");

        $this->assertDatabaseHas('cuadrillas', [
            'id' => $cuad->id,
            'nombre' => 'Nombre Modificado',
            'regimen_laboral' => '21x7',
            'estado' => 'EN_DESCANSO',
        ]);
    }

    public function test_cannot_delete_cuadrilla_with_dispatches(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $almacen = Ubicacion::factory()->create();

        DespachoPrestamo::create([
            'numero_guia' => 'DSP-TEST-CD-001',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'cuadrilla_id' => $cuad->id,
            'personal_id' => $this->lider->id,
            'ubicacion_origen_id' => $almacen->id,
            'usuario_registro_id' => $this->admin->id,
            'fecha_despacho' => now(),
            'estado' => 'ENTREGADO_EN_CAMPO',
        ]);

        $response = $this->actingAs($this->admin)->delete("/cuadrillas/{$cuad->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cuadrillas', ['id' => $cuad->id]);
    }

    public function test_can_delete_cuadrilla_without_dependencies(): void
    {
        $cuad = Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-DEL',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $this->lider->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/cuadrillas/{$cuad->id}");

        $response->assertRedirect('/cuadrillas');
        $this->assertDatabaseMissing('cuadrillas', ['codigo_cuadrilla' => 'CD-DEL']);
    }
}
