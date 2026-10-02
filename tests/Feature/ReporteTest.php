<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Cuadrilla;
use App\Models\EmpresaConfig;
use App\Models\InventarioStock;
use App\Models\KardexMovimiento;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    private Ubicacion $almacen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create();
        $this->almacen = Ubicacion::factory()->create();
        Categoria::factory()->create();
        EmpresaConfig::instancia()->update([
            'razon_social' => 'Empresa Test S.A.C.',
            'ruc' => '20601234567',
        ]);
    }

    public function test_guests_cannot_access_reports(): void
    {
        $response = $this->get('/reportes');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_reports_hub(): void
    {
        $response = $this->actingAs($this->admin)->get('/reportes');

        $response->assertStatus(200);
        $response->assertSee('Centro de Reportes');
        $response->assertSee('Inventario');
        $response->assertSee('Kardex');
        $response->assertSee('Roster');
        $response->assertSee('Padrón de Activos');
    }

    public function test_can_export_inventario_stock_excel(): void
    {
        $cat = Categoria::factory()->create();
        $art = Articulo::factory()->create(['categoria_id' => $cat->id]);

        InventarioStock::create([
            'articulo_id' => $art->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 50,
        ]);

        $response = $this->actingAs($this->admin)->get('/reportes/export/inventario?ubicacion_id='.$this->almacen->id);

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Inventario_Stock_LogisticPCS_', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    public function test_can_export_kardex_excel(): void
    {
        $art = Articulo::factory()->create();

        KardexMovimiento::create([
            'articulo_id' => $art->id,
            'ubicacion_id' => $this->almacen->id,
            'tipo_movimiento' => 'INGRESO_COMPRA',
            'cantidad' => 10,
            'stock_anterior' => 0,
            'stock_posterior' => 10,
            'usuario_id' => $this->admin->id,
            'fecha_movimiento' => now(),
            'motivo' => 'Compra inicial de prueba',
        ]);

        $response = $this->actingAs($this->admin)->get('/reportes/export/kardex');

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Kardex_Movimientos_LogisticPCS_', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    public function test_can_export_roster_excel(): void
    {
        $pers = Personal::factory()->create(['proyecto_id' => $this->proyecto->id]);

        RosterTurno::factory()->create([
            'personal_id' => $pers->id,
            'proyecto_id' => $this->proyecto->id,
            'fecha' => now()->toDateString(),
            'condicion_laboral' => 'TRABAJO_CAMPO',
        ]);

        $response = $this->actingAs($this->admin)->get('/reportes/export/roster?proyecto_id='.$this->proyecto->id);

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Roster_14x7_LogisticPCS_', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    public function test_can_export_activos_excel(): void
    {
        $art = Articulo::factory()->serializado()->create();

        Activo::factory()->create([
            'articulo_id' => $art->id,
            'codigo_interno' => 'ACT-TEST-EXP',
            'ubicacion_actual_id' => $this->almacen->id,
            'estado_operativo' => 'OPERATIVO',
            'condicion_prestamo' => 'DISPONIBLE',
        ]);

        $response = $this->actingAs($this->admin)->get('/reportes/export/activos');

        $response->assertStatus(200);
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Padron_Activos_LogisticPCS_', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    public function test_can_generate_pdf_inventario(): void
    {
        $art = Articulo::factory()->create();

        InventarioStock::create([
            'articulo_id' => $art->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 25,
        ]);

        $response = $this->actingAs($this->admin)->get('/reportes/pdf/inventario?stream=1');

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_generate_pdf_cuadrilla_dotacion(): void
    {
        $lider = Personal::factory()->create(['proyecto_id' => $this->proyecto->id]);

        $cuad = Cuadrilla::factory()->create([
            'codigo_cuadrilla' => 'CD-PDF-01',
            'nombre' => 'Cuadrilla PDF Test',
            'proyecto_id' => $this->proyecto->id,
            'lider_personal_id' => $lider->id,
        ]);

        $tecnico = Personal::factory()->create(['proyecto_id' => $this->proyecto->id]);
        $cuad->miembrosPivot()->create([
            'personal_id' => $tecnico->id,
            'rol_en_cuadrilla' => 'EMPALMADOR',
            'fecha_incorporacion' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get("/reportes/cuadrilla/{$cuad->id}/pdf?stream=1");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_dashboard_loads_with_executive_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Centro de Reportes');
        $response->assertSee('Despachos Recientes en Campo');
        $response->assertSee('Transacciones de Kardex');
    }
}
