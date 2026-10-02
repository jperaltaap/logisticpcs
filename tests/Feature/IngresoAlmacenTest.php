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

class IngresoAlmacenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Ubicacion $almacen;

    private Proyecto $proyecto;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create([
            'codigo' => 'PRY-FIBRA',
            'nombre' => 'Proyecto Fibra Óptica Norte',
            'estado' => 'ACTIVO',
        ]);

        $this->almacen = Ubicacion::factory()->create([
            'nombre' => 'Almacén Principal Central',
            'tipo' => 'ALMACEN_CENTRAL',
            'estado' => 'ACTIVO',
            'proyecto_id' => $this->proyecto->id,
        ]);

        $this->categoria = Categoria::factory()->create([
            'codigo' => 'CAT-REDES',
            'nombre' => 'Equipos de Red y Telecom',
        ]);
    }

    public function test_user_can_view_ingresos_list_and_create_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ingresos.index'));
        $response->assertStatus(200);
        $response->assertSee('Operaciones: Entradas / Ingresos de Almacén');

        $responseCreate = $this->actingAs($this->admin)->get(route('ingresos.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Buscador de Artículos en Tiempo Real');
    }

    public function test_real_time_search_articles(): void
    {
        $art1 = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SW-CISCO-24',
            'descripcion' => 'Switch Cisco Catalyst 24 Puertos',
            'tipo_articulo' => 'EQUIPO',
            'control_serie' => true,
            'es_instalable' => true,
            'estado' => 'ACTIVO',
        ]);

        $art2 = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'CAB-UTP-CAT6',
            'descripcion' => 'Bobina Cable UTP Cat6 305m',
            'tipo_articulo' => 'MATERIAL',
            'control_serie' => false,
            'estado' => 'ACTIVO',
        ]);

        InventarioStock::create([
            'articulo_id' => $art2->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 12.00,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('ingresos.buscar-articulo', [
            'q' => 'Cisco',
            'ubicacion_id' => $this->almacen->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'codigo_sku' => 'SW-CISCO-24',
            'control_serie' => true,
            'es_instalable' => true,
        ]);

        $response2 = $this->actingAs($this->admin)->getJson(route('ingresos.buscar-articulo', [
            'q' => 'Cable',
            'ubicacion_id' => $this->almacen->id,
        ]));

        $response2->assertStatus(200);
        $response2->assertJsonFragment([
            'codigo_sku' => 'CAB-UTP-CAT6',
            'stock_actual' => 12.00,
            'control_serie' => false,
        ]);
    }

    public function test_quick_create_article_via_ajax(): void
    {
        $payload = [
            'descripcion' => 'Router Mikrotik RB4011',
            'categoria_id' => $this->categoria->id,
            'tipo_articulo' => 'EQUIPO',
            'unidad_medida' => 'UND',
            'marca' => 'Mikrotik',
            'modelo' => 'RB4011iGS+',
            'control_serie' => 1,
            'es_instalable' => 1,
            'stock_minimo' => 1.00,
            'ubicacion_id' => $this->almacen->id,
        ];

        $response = $this->actingAs($this->admin)->postJson(route('ingresos.crear-articulo-rapido'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'articulo' => [
                'descripcion' => 'Router Mikrotik RB4011',
                'control_serie' => true,
                'es_instalable' => true,
            ],
        ]);

        $this->assertDatabaseHas('articulos', [
            'descripcion' => 'Router Mikrotik RB4011',
            'control_serie' => 1,
            'es_instalable' => 1,
        ]);
    }

    public function test_store_non_serialized_ingreso_increments_inventory_stock_and_creates_kardex(): void
    {
        $articulo = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'CON-RJ45-CAT6',
            'descripcion' => 'Conector RJ45 Cat6 blindado',
            'tipo_articulo' => 'CONSUMIBLE',
            'control_serie' => false,
            'unidad_medida' => 'BOLSA',
            'estado' => 'ACTIVO',
        ]);

        InventarioStock::create([
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 5.00,
        ]);

        $payload = [
            'tipo_ingreso' => 'COMPRA_NUEVA',
            'ubicacion_id' => $this->almacen->id,
            'proyecto_id' => $this->proyecto->id,
            'proveedor' => 'Telecom Soluciones SAC',
            'numero_comprobante' => 'F001-998822',
            'fecha_ingreso' => '2026-09-29',
            'items' => [
                [
                    'articulo_id' => $articulo->id,
                    'cantidad' => 15.00,
                    'costo_unitario' => 25.50,
                    'observaciones' => 'Lote nuevo',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('ingresos.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('ingresos', [
            'proveedor' => 'Telecom Soluciones SAC',
            'numero_comprobante' => 'F001-998822',
        ]);

        $stock = InventarioStock::where('articulo_id', $articulo->id)
            ->where('ubicacion_id', $this->almacen->id)
            ->first();

        // 5 + 15 = 20
        $this->assertEquals(20.00, (float) $stock->cantidad_actual);

        $this->assertDatabaseHas('kardex_movimientos', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'tipo_movimiento' => 'INGRESO_COMPRA',
            'cantidad' => 15.00,
            'stock_anterior' => 5.00,
            'stock_posterior' => 20.00,
        ]);
    }

    public function test_store_serialized_ingreso_creates_activos_with_series_and_available_condition(): void
    {
        $articulo = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SW-HUAWEI-S5700',
            'descripcion' => 'Switch Huawei S5700 Gigabit',
            'tipo_articulo' => 'MATERIAL',
            'control_serie' => true,
            'es_instalable' => true,
            'unidad_medida' => 'UND',
            'estado' => 'ACTIVO',
        ]);

        $payload = [
            'tipo_ingreso' => 'COMPRA_NUEVA',
            'ubicacion_id' => $this->almacen->id,
            'proyecto_id' => $this->proyecto->id,
            'proveedor' => 'Huawei del Perú SAC',
            'numero_comprobante' => 'GR-002-3344',
            'fecha_ingreso' => '2026-09-29',
            'items' => [
                [
                    'articulo_id' => $articulo->id,
                    'cantidad' => 2,
                    'costo_unitario' => 1200.00,
                    'series' => ['HW-SN-001A', 'HW-SN-002B'],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('ingresos.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('activos', [
            'articulo_id' => $articulo->id,
            'numero_serie' => 'HW-SN-001A',
            'ubicacion_actual_id' => $this->almacen->id,
            'condicion_prestamo' => 'DISPONIBLE',
            'estado_operativo' => 'OPERATIVO',
        ]);

        $this->assertDatabaseHas('activos', [
            'articulo_id' => $articulo->id,
            'numero_serie' => 'HW-SN-002B',
            'ubicacion_actual_id' => $this->almacen->id,
            'condicion_prestamo' => 'DISPONIBLE',
        ]);

        $stock = InventarioStock::where('articulo_id', $articulo->id)
            ->where('ubicacion_id', $this->almacen->id)
            ->first();

        $this->assertEquals(2.00, (float) $stock->cantidad_actual);
    }

    public function test_serialized_installable_material_can_be_marked_as_consumed_installed_on_devolucion(): void
    {
        $articulo = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'ONT-FIBERHOME',
            'descripcion' => 'ONT GPON WiFi Dual Band',
            'tipo_articulo' => 'MATERIAL',
            'control_serie' => true,
            'es_instalable' => true,
            'estado' => 'ACTIVO',
        ]);

        $personal = Personal::factory()->create([
            'proyecto_id' => $this->proyecto->id,
            'estado' => 'ACTIVO',
        ]);

        $activo = Activo::factory()->create([
            'articulo_id' => $articulo->id,
            'codigo_interno' => 'ACT-ONT-001',
            'numero_serie' => 'FH-ONT-9988',
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
            'condicion_prestamo' => 'PRESTADO_CAMPO',
            'responsable_personal_id' => $personal->id,
        ]);

        $despacho = DespachoPrestamo::create([
            'numero_guia' => 'GUIA-OBRA-001',
            'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
            'proyecto_id' => $this->proyecto->id,
            'personal_id' => $personal->id,
            'ubicacion_origen_id' => $this->almacen->id,
            'usuario_registro_id' => $this->admin->id,
            'fecha_despacho' => now(),
            'estado' => 'ENTREGADO_EN_CAMPO',
        ]);

        $detalle = DespachoDetalle::create([
            'despacho_id' => $despacho->id,
            'articulo_id' => $articulo->id,
            'activo_id' => $activo->id,
            'cantidad' => 1.00,
            'estado_item' => 'ENTREGADO',
        ]);

        // Registrar devolución donde se declara que la ONT quedó instalada en obra (CONSUMIDO)
        $payloadDevolucion = [
            'ubicacion_destino_id' => $this->almacen->id,
            'items' => [
                [
                    'detalle_id' => $detalle->id,
                    'estado_item' => 'CONSUMIDO',
                    'cantidad_devuelta' => 0.00,
                    'observacion_retorno' => 'Instalado en rack de abonado cliente',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('despachos.devolucion.store', $despacho), $payloadDevolucion);
        $response->assertRedirect();

        $activo->refresh();
        $this->assertEquals('INSTALADO_PROYECTO', $activo->condicion_prestamo);
        $this->assertEquals($this->proyecto->id, $activo->proyecto_actual_id);
        $this->assertNull($activo->responsable_personal_id);
    }
}
