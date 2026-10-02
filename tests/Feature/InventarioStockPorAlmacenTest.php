<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\InventarioStock;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioStockPorAlmacenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Proyecto $proyecto;

    private Ubicacion $almacen;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->proyecto = Proyecto::factory()->create([
            'codigo' => 'PRY-TEST-01',
            'nombre' => 'Proyecto Troncal Norte',
            'estado' => 'ACTIVO',
        ]);

        $this->almacen = Ubicacion::factory()->create([
            'codigo' => 'ALM-CEN-01',
            'nombre' => 'Almacén Central Base',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => null,
        ]);

        $this->categoria = Categoria::factory()->create([
            'nombre' => 'Herramientas y Equipos',
        ]);
    }

    public function test_stock_index_shows_both_serialized_and_fungible_articles_in_warehouse(): void
    {
        // 1. Artículo serializado con 2 activos en el almacén
        $artSerializado = Articulo::factory()->serializado()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-FUS-100',
            'descripcion' => 'Fusionadora Núcleo 100',
            'proyecto_id' => $this->proyecto->id,
        ]);

        Activo::factory()->create([
            'articulo_id' => $artSerializado->id,
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
            'condicion_prestamo' => 'DISPONIBLE',
            'estado_operativo' => 'OPERATIVO',
        ]);
        Activo::factory()->create([
            'articulo_id' => $artSerializado->id,
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
            'condicion_prestamo' => 'DISPONIBLE',
            'estado_operativo' => 'OPERATIVO',
        ]);

        // 2. Artículo fungible con 100 metros en el almacén
        $artFungible = Articulo::factory()->create([
            'control_serie' => false,
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-CAB-100',
            'descripcion' => 'Cable Fibra ADSS 100m',
            'proyecto_id' => $this->proyecto->id,
            'unidad_medida' => 'MTR',
        ]);

        InventarioStock::create([
            'articulo_id' => $artFungible->id,
            'ubicacion_id' => $this->almacen->id,
            'proyecto_id' => $this->proyecto->id,
            'cantidad_actual' => 150.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('inventario.stock', ['ubicacion_id' => $this->almacen->id]));

        $response->assertOk();
        $response->assertSee('SKU-FUS-100');
        $response->assertSee('Fusionadora Núcleo 100');
        $response->assertSee('Serializado');
        $response->assertSee('SKU-CAB-100');
        $response->assertSee('Cable Fibra ADSS 100m');
        $response->assertSee('Consumible');
        $response->assertSee('150.00');
    }

    public function test_creating_activo_automatically_syncs_inventario_stock_for_warehouse(): void
    {
        $articulo = Articulo::factory()->serializado()->create([
            'codigo_sku' => 'SKU-OTDR-50',
            'proyecto_id' => $this->proyecto->id,
        ]);

        // Antes de crear activos, no hay stock
        $this->assertDatabaseMissing('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 1.00,
        ]);

        // Crear activo
        $activo = Activo::factory()->create([
            'articulo_id' => $articulo->id,
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
        ]);

        // El observer debe haber creado o actualizado inventario_stock
        $this->assertDatabaseHas('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 1.00,
        ]);

        // Crear segundo activo
        Activo::factory()->create([
            'articulo_id' => $articulo->id,
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
        ]);

        $this->assertDatabaseHas('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 2.00,
        ]);
    }

    public function test_moving_activo_to_another_warehouse_updates_both_warehouse_stocks(): void
    {
        $almacenDestino = Ubicacion::factory()->create([
            'codigo' => 'ACOPIO-OBRA-01',
            'tipo' => 'CENTRO_ACOPIO',
            'proyecto_id' => $this->proyecto->id,
        ]);

        $articulo = Articulo::factory()->serializado()->create([
            'codigo_sku' => 'SKU-TAL-50',
            'proyecto_id' => $this->proyecto->id,
        ]);

        $activo = Activo::factory()->create([
            'articulo_id' => $articulo->id,
            'ubicacion_actual_id' => $this->almacen->id,
            'proyecto_actual_id' => $this->proyecto->id,
        ]);

        $this->assertDatabaseHas('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 1.00,
        ]);

        // Mover el activo al centro de acopio
        $activo->update(['ubicacion_actual_id' => $almacenDestino->id]);

        // Almacén origen debe tener 0 y destino debe tener 1
        $this->assertDatabaseHas('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 0.00,
        ]);

        $this->assertDatabaseHas('inventario_stock', [
            'articulo_id' => $articulo->id,
            'ubicacion_id' => $almacenDestino->id,
            'cantidad_actual' => 1.00,
        ]);
    }

    public function test_can_filter_stock_by_tipo_control_serializado_or_fungible(): void
    {
        $artSerializado = Articulo::factory()->serializado()->create([
            'codigo_sku' => 'SKU-SERIE-X',
            'descripcion' => 'Equipo Serializado X',
        ]);
        Activo::factory()->create([
            'articulo_id' => $artSerializado->id,
            'ubicacion_actual_id' => $this->almacen->id,
        ]);

        $artFungible = Articulo::factory()->create([
            'control_serie' => false,
            'codigo_sku' => 'SKU-FUNGIBLE-Y',
            'descripcion' => 'Material Fungible Y',
        ]);
        InventarioStock::create([
            'articulo_id' => $artFungible->id,
            'ubicacion_id' => $this->almacen->id,
            'cantidad_actual' => 50.00,
        ]);

        // Filtrar solo serializados
        $responseSerial = $this->actingAs($this->admin)
            ->get(route('inventario.stock', ['tipo_control' => 'serializado']));
        $responseSerial->assertOk();
        $responseSerial->assertSee('SKU-SERIE-X');
        $responseSerial->assertDontSee('SKU-FUNGIBLE-Y');

        // Filtrar consumibles (con 'consumible')
        $responseConsumible = $this->actingAs($this->admin)
            ->get(route('inventario.stock', ['tipo_control' => 'consumible']));
        $responseConsumible->assertOk();
        $responseConsumible->assertSee('SKU-FUNGIBLE-Y');
        $responseConsumible->assertSee('Consumible');
        $responseConsumible->assertDontSee('SKU-SERIE-X');

        // Filtrar fungibles (alias 'fungible')
        $responseFungible = $this->actingAs($this->admin)
            ->get(route('inventario.stock', ['tipo_control' => 'fungible']));
        $responseFungible->assertOk();
        $responseFungible->assertSee('SKU-FUNGIBLE-Y');
        $responseFungible->assertDontSee('SKU-SERIE-X');
    }
}
