<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalTest extends TestCase
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

    public function test_guests_cannot_access_personal(): void
    {
        $response = $this->get('/personal');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_personal_list(): void
    {
        $pers = Personal::factory()->create([
            'nombres' => 'Manuel',
            'apellidos' => 'Salazar',
            'dni' => '12345678',
        ]);

        $response = $this->actingAs($this->admin)->get('/personal');

        $response->assertStatus(200);
        $response->assertSee('Manuel Salazar');
        $response->assertSee('12345678');
    }

    public function test_can_filter_personal_by_proyecto(): void
    {
        $pry1 = Proyecto::factory()->create(['nombre' => 'Obra Norte']);
        $pry2 = Proyecto::factory()->create(['nombre' => 'Obra Sur']);

        $p1 = Personal::factory()->create(['nombres' => 'Pedro', 'proyecto_id' => $pry1->id]);
        $p2 = Personal::factory()->create(['nombres' => 'Juan', 'proyecto_id' => $pry2->id]);

        $response = $this->actingAs($this->admin)->get("/personal?proyecto_id={$pry1->id}");

        $response->assertStatus(200);
        $response->assertSee('Pedro');
        $response->assertDontSee('Juan');
    }

    public function test_can_create_a_new_personal(): void
    {
        $pry = Proyecto::factory()->create();

        $data = [
            'codigo_trabajador' => 'TRAB-9999',
            'codigo_fotocheck' => 'FCH-9999',
            'dni' => '87654321',
            'nombres' => 'Ricardo',
            'apellidos' => 'Gómez',
            'cargo' => 'Técnico Empalmador',
            'area' => 'Telecomunicaciones',
            'telefono' => '911223344',
            'correo' => 'rgomez@test.com',
            'proyecto_id' => $pry->id,
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->admin)->post('/personal', $data);

        $response->assertRedirect('/personal');
        $this->assertDatabaseHas('personal', [
            'dni' => '87654321',
            'codigo_trabajador' => 'TRAB-9999',
            'proyecto_id' => $pry->id,
        ]);
    }

    public function test_validation_fails_for_duplicate_dni(): void
    {
        Personal::factory()->create(['dni' => '99887766']);

        $data = [
            'dni' => '99887766',
            'nombres' => 'Otro',
            'apellidos' => 'Personal',
            'cargo' => 'Auxiliar',
            'area' => 'Almacén',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->admin)->post('/personal', $data);

        $response->assertSessionHasErrors(['dni']);
    }

    public function test_can_update_personal_record(): void
    {
        $pers = Personal::factory()->create(['cargo' => 'Técnico Junior']);

        $response = $this->actingAs($this->admin)->put("/personal/{$pers->id}", [
            'dni' => $pers->dni,
            'nombres' => $pers->nombres,
            'apellidos' => $pers->apellidos,
            'cargo' => 'Técnico Senior / Residente',
            'area' => $pers->area,
            'estado' => 'VACACIONES',
        ]);

        $response->assertRedirect('/personal');
        $this->assertDatabaseHas('personal', [
            'id' => $pers->id,
            'cargo' => 'Técnico Senior / Residente',
            'estado' => 'VACACIONES',
        ]);
    }

    public function test_can_soft_delete_personal(): void
    {
        $pers = Personal::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/personal/{$pers->id}");

        $response->assertRedirect('/personal');
        $this->assertSoftDeleted('personal', ['id' => $pers->id]);
    }
}
