<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\DespachoDetalle;
use App\Models\DespachoPrestamo;
use App\Models\InventarioStock;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DespachoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    private Personal $personal;

    private Ubicacion $almacen;

    private Articulo $artFungible;

    private Articulo $artSerializado;

    private Activo $activo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create([
            'codigo' => 'PRY-TEST-01',
            'nombre' => 'Proyecto Test Obra',
        ]);

        $this->personal = Personal::factory()->create([
            'dni' => '12345678',
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
        ]);

        $this->almacen = Ubicacion::factory()->create([
            'codigo' => 'ALM-CENTRAL',
            'nombre' => 'Almacén Central',
            'tipo' => 'ALMACEN_CENTRAL',
        ]);

        $categoria = Categoria::factory()->create();

        // Fungible con stock 100
        $this->artFungible = Articulo::factory()->create([
            'categoria_id' => $categoria->id,
            'codigo_sku' => 'SKU-FUNGIBLE',
            'descripcion' => 'Cinta Aislante 3M',
            'control_serie' => false,
        ]);

        InventarioStock::create([
            'articulo_id' => $this->artFungible->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 100.00,
        ]);

        // Serializado con 1 unidad física
        $this->artSerializado = Articulo::factory()->serializado()->create([
            'categoria_id' => $categoria->id,
            'codigo_sku' => 'SKU-FUS-TEST',
            'descripcion' => 'Fusionadora Fujikura 90S',
        ]);

        $this->activo = Activo::factory()->create([
            'articulo_id' => $this->artSerializado->id,
            'codigo_interno' => 'ACT-00001',
            'ubicacion_actual_id' => $this->almacen->id,
            'estado_operativo' => 'OPERATIVO',
            'condicion_prestamo' => 'DISPONIBLE',
        ]);
    }

    public function test_guests_cannot_access_despachos(): void
    {
        $response = $this->get('/despachos');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_despachos_list(): void
    {
        DespachoPrestamo::create([
            'numero_guia' => 'DSP-2026-0001',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $this->personal->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'usuario_registro_id' => $this->admin->id,
            'fecha_despacho' => now(),
            'estado' => 'ENTREGADO_EN_CAMPO',
        ]);

        $response = $this->actingAs($this->admin)->get('/despachos');

        $response->assertStatus(200);
        $response->assertSee('DSP-2026-0001');
    }

    public function test_can_create_despacho_with_material_and_serialized_activo(): void
    {
        $payload = [
            'numero_guia' => 'DSP-TEST-NEW',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $this->personal->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'fecha_compromiso_retorno' => date('Y-m-d', strtotime('+7 days')),
            'firma_digital_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'observaciones' => 'Despacho de prueba para cuadrilla.',
            'detalles' => [
                [
                    'articulo_id' => $this->artFungible->id,
                    'activo_id' => null,
                    'cantidad' => 15.00,
                ],
                [
                    'articulo_id' => $this->artSerializado->id,
                    'activo_id' => $this->activo->id,
                    'cantidad' => 1.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/despachos', $payload);

        $response->assertRedirect();

        // Verificar despacho creado
        $this->assertDatabaseHas('despachos_prestamos', [
            'numero_guia' => 'DSP-TEST-NEW',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $this->personal->id,
        ]);

        // Verificar que el activo cambió de condición a PRESTADO_CAMPO
        $this->activo->refresh();
        $this->assertEquals('PRESTADO_CAMPO', $this->activo->condicion_prestamo);
        $this->assertEquals($this->personal->id, $this->activo->responsable_personal_id);

        // Verificar que el inventario del material fungible bajó de 100 a 85
        $stock = InventarioStock::where('articulo_id', $this->artFungible->id)
            ->where('ubicacion_id', $this->almacen->id)
            ->first();
        $this->assertEquals(85.00, (float) $stock->cantidad_actual);

        // Verificar movimientos en Kardex
        $this->assertDatabaseHas('kardex_movimientos', [
            'articulo_id' => $this->artFungible->id,
            'tipo_movimiento' => 'SALIDA_PRESTAMO',
            'stock_anterior' => 100.00,
            'stock_posterior' => 85.00,
        ]);
    }

    public function test_cannot_dispatch_more_fungible_stock_than_available(): void
    {
        $payload = [
            'numero_guia' => 'DSP-TEST-FAIL',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'detalles' => [
                [
                    'articulo_id' => $this->artFungible->id,
                    'activo_id' => null,
                    'cantidad' => 500.00, // Hay solo 100 en stock
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/despachos', $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('despachos_prestamos', ['numero_guia' => 'DSP-TEST-FAIL']);
    }

    public function test_can_view_printable_acta(): void
    {
        $despacho = DespachoPrestamo::create([
            'numero_guia' => 'DSP-ACTA-01',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $this->personal->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'usuario_registro_id' => $this->admin->id,
            'fecha_despacho' => now(),
            'estado' => 'ENTREGADO_EN_CAMPO',
        ]);

        $response = $this->actingAs($this->admin)->get("/despachos/{$despacho->id}/acta");

        $response->assertStatus(200);
        $response->assertSee('DSP-ACTA-01');
        $response->assertSee('Acta de Entrega / Vale de Despacho');
    }

    public function test_can_process_devolucion_and_reenter_stock_and_assets(): void
    {
        // 1. Crear despacho previo
        $despacho = DespachoPrestamo::create([
            'numero_guia' => 'DSP-DEV-01',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $this->personal->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'usuario_registro_id' => $this->admin->id,
            'fecha_despacho' => now(),
            'estado' => 'ENTREGADO_EN_CAMPO',
        ]);

        $detActivo = DespachoDetalle::create([
            'despacho_id' => $despacho->id,
            'articulo_id' => $this->artSerializado->id,
            'activo_id' => $this->activo->id,
            'cantidad' => 1.00,
            'estado_item' => 'ENTREGADO',
        ]);

        $detFungible = DespachoDetalle::create([
            'despacho_id' => $despacho->id,
            'articulo_id' => $this->artFungible->id,
            'activo_id' => null,
            'cantidad' => 10.00,
            'estado_item' => 'ENTREGADO',
        ]);

        $this->activo->update(['condicion_prestamo' => 'PRESTADO_CAMPO']);

        // 2. Procesar Devolución
        $payload = [
            'ubicacion_destino_id' => $this->almacen->id,
            'items' => [
                [
                    'detalle_id' => $detActivo->id,
                    'estado_item' => 'DEVUELTO_OPERATIVO',
                    'cantidad_devuelta' => 1.00,
                    'observacion_retorno' => 'Equipo retornado conforme y probado.',
                ],
                [
                    'detalle_id' => $detFungible->id,
                    'estado_item' => 'DEVUELTO_OPERATIVO',
                    'cantidad_devuelta' => 4.00, // 4 retornaron, 6 se consumieron
                    'observacion_retorno' => 'Sobrante de instalación.',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post("/despachos/{$despacho->id}/devolucion", $payload);

        $response->assertRedirect();

        // 3. Verificaciones
        $despacho->refresh();
        $this->assertEquals('DEVUELTO_TOTAL', $despacho->estado);

        // Activo vuelve a DISPONIBLE y OPERATIVO
        $this->activo->refresh();
        $this->assertEquals('DISPONIBLE', $this->activo->condicion_prestamo);
        $this->assertEquals('OPERATIVO', $this->activo->estado_operativo);
        $this->assertNull($this->activo->responsable_personal_id);

        // Kardex registra RETORNO_PRESTAMO de 4 unidades
        $this->assertDatabaseHas('kardex_movimientos', [
            'articulo_id' => $this->artFungible->id,
            'tipo_movimiento' => 'RETORNO_PRESTAMO',
            'cantidad' => 4.00,
        ]);
    }
}
