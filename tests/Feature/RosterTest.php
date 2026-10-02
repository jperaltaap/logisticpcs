<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    private Personal $personal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create();
        $this->personal = Personal::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_guests_cannot_access_roster(): void
    {
        $response = $this->get('/roster');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_roster_matrix(): void
    {
        RosterTurno::factory()->create([
            'personal_id' => $this->personal->id,
            'proyecto_id' => $this->proyecto->id,
            'fecha' => now()->format('Y-m-d'),
            'condicion_laboral' => 'TRABAJO_CAMPO',
        ]);

        $response = $this->actingAs($this->admin)->get('/roster');

        $response->assertStatus(200);
        $response->assertSee($this->personal->apellidos);
        $response->assertSee('Roster de Turnos &amp; Relevos 14x7', false);
    }

    public function test_can_record_manual_roster_shift(): void
    {
        $fecha = now()->addDays(2)->format('Y-m-d');
        $data = [
            'personal_id' => $this->personal->id,
            'proyecto_id' => $this->proyecto->id,
            'grupo_guardia' => 'GUARDIA A',
            'fecha' => $fecha,
            'condicion_laboral' => 'BAJADA_DESCANSO',
            'observaciones' => 'Descanso programado 14x7',
        ];

        $response = $this->actingAs($this->admin)->post('/roster', $data);

        $response->assertSessionHas('status');

        $turno = RosterTurno::where('personal_id', $this->personal->id)
            ->whereDate('fecha', $fecha)
            ->first();

        $this->assertNotNull($turno);
        $this->assertEquals('BAJADA_DESCANSO', $turno->condicion_laboral);
    }

    public function test_can_generate_automated_14x7_cycle(): void
    {
        $p2 = Personal::factory()->create(['proyecto_id' => $this->proyecto->id]);

        $fechaInicio = now()->startOfMonth()->format('Y-m-d');

        $data = [
            'personal_ids' => [$this->personal->id, $p2->id],
            'proyecto_id' => $this->proyecto->id,
            'grupo_guardia' => 'GUARDIA A',
            'fecha_inicio' => $fechaInicio,
            'dias_trabajo' => 14,
            'dias_descanso' => 7,
            'ciclos' => 1,
        ];

        $response = $this->actingAs($this->admin)->post('/roster/generar-ciclo', $data);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Cada persona debe tener 14 días de TRABAJO_CAMPO y 7 días de BAJADA_DESCANSO = 21 turnos
        $this->assertEquals(14, RosterTurno::where('personal_id', $this->personal->id)->where('condicion_laboral', 'TRABAJO_CAMPO')->count());
        $this->assertEquals(7, RosterTurno::where('personal_id', $this->personal->id)->where('condicion_laboral', 'BAJADA_DESCANSO')->count());
        $this->assertEquals(21, RosterTurno::where('personal_id', $this->personal->id)->count());
        $this->assertEquals(21, RosterTurno::where('personal_id', $p2->id)->count());
    }

    public function test_can_check_labor_condition_via_ajax(): void
    {
        $fecha = now()->format('Y-m-d');

        RosterTurno::factory()->create([
            'personal_id' => $this->personal->id,
            'proyecto_id' => $this->proyecto->id,
            'fecha' => $fecha,
            'condicion_laboral' => 'BAJADA_DESCANSO',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/roster/check-condicion?personal_id={$this->personal->id}&fecha={$fecha}");

        $response->assertStatus(200);
        $response->assertJson([
            'registrado' => true,
            'condicion_laboral' => 'BAJADA_DESCANSO',
            'es_operativo' => false,
        ]);
    }
}
