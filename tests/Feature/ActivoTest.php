<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Articulo $articulo;

    private Ubicacion $ubicacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $categoria = Categoria::factory()->create([
            'codigo' => 'CAT-TEL',
            'nombre' => 'Telecomunicaciones',
        ]);

        $this->articulo = Articulo::factory()->serializado()->create([
            'categoria_id' => $categoria->id,
            'codigo_sku' => 'SKU-FUS-TEST',
            'descripcion' => 'Fusionadora Fujikura 90S Test',
        ]);

        $this->ubicacion = Ubicacion::factory()->create([
            'codigo' => 'ALM-CEN',
            'nombre' => 'Almacén Central',
        ]);
    }

    public function test_guests_cannot_access_activos(): void
    {
        $response = $this->get('/activos');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_activos_list(): void
    {
        $activo = Activo::factory()->create([
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-00001',
            'numero_serie' => 'SN-FUS-999',
            'ubicacion_actual_id' => $this->ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/activos');

        $response->assertStatus(200);
        $response->assertSee('ACT-00001');
        $response->assertSee('SN-FUS-999');
    }

    public function test_can_create_new_activo(): void
    {
        $personal = Personal::factory()->create();
        $proyecto = Proyecto::factory()->create();

        $payload = [
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-99999',
            'numero_serie' => 'SN-FUS-123456',
            'ubicacion_actual_id' => $this->ubicacion->id,
            'responsable_personal_id' => $personal->id,
            'proyecto_actual_id' => $proyecto->id,
            'estado_operativo' => 'OPERATIVO',
            'condicion_prestamo' => 'PRESTADO_CAMPO',
            'fecha_ingreso' => '2026-03-01',
            'observaciones' => 'Unidad entregada con maletín y accesorios completos.',
        ];

        $response = $this->actingAs($this->admin)->post('/activos', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('activos', [
            'codigo_interno' => 'ACT-99999',
            'numero_serie' => 'SN-FUS-123456',
            'responsable_personal_id' => null,
            'condicion_prestamo' => 'DISPONIBLE',
        ]);
    }

    public function test_activo_detail_renders_qr_code(): void
    {
        $activo = Activo::factory()->create([
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-QR-001',
            'ubicacion_actual_id' => $this->ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/activos/{$activo->id}");

        $response->assertStatus(200);
        $response->assertSee('ACT-QR-001');
        $response->assertSee('<svg', false); // SVG QR tag rendered
    }

    public function test_can_view_printable_qr_sticker_label(): void
    {
        $activo = Activo::factory()->create([
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-STICKER',
            'numero_serie' => 'SN-LABEL-123',
            'ubicacion_actual_id' => $this->ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/activos/{$activo->id}/etiqueta");

        $response->assertStatus(200);
        $response->assertSee('ACT-STICKER');
        $response->assertSee('LOGISTIC PCS');
        $response->assertSee('<svg', false);
    }

    public function test_can_update_activo(): void
    {
        $activo = Activo::factory()->create([
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-EDIT-01',
            'ubicacion_actual_id' => $this->ubicacion->id,
            'estado_operativo' => 'OPERATIVO',
        ]);

        $payload = [
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-EDIT-01',
            'ubicacion_actual_id' => $this->ubicacion->id,
            'estado_operativo' => 'EN_MANTENIMIENTO',
            'condicion_prestamo' => 'DISPONIBLE',
            'fecha_ingreso' => '2026-01-15',
            'observaciones' => 'Enviado a calibración semestral.',
        ];

        $response = $this->actingAs($this->admin)->put("/activos/{$activo->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('activos', [
            'id' => $activo->id,
            'estado_operativo' => 'EN_MANTENIMIENTO',
        ]);
    }

    public function test_can_delete_activo_without_history(): void
    {
        $activo = Activo::factory()->create([
            'articulo_id' => $this->articulo->id,
            'codigo_interno' => 'ACT-DELETE-01',
            'ubicacion_actual_id' => $this->ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/activos/{$activo->id}");

        $response->assertRedirect('/activos');
        $this->assertDatabaseMissing('activos', ['id' => $activo->id]);
    }
}
