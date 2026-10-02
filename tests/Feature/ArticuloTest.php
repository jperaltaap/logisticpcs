<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\InventarioStock;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticuloTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $this->categoria = Categoria::factory()->create([
            'codigo' => 'CAT-TEST',
            'nombre' => 'Categoría Test',
        ]);
    }

    public function test_guests_cannot_access_articulos(): void
    {
        $response = $this->get('/articulos');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_articulos_list(): void
    {
        Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-TEST-001',
            'descripcion' => 'Fusionadora Fujikura 90S',
        ]);

        $response = $this->actingAs($this->admin)->get('/articulos');

        $response->assertStatus(200);
        $response->assertSee('SKU-TEST-001');
        $response->assertSee('Fusionadora Fujikura 90S');
    }

    public function test_can_filter_articulos_by_search_term(): void
    {
        Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-ALPHA-01',
            'descripcion' => 'Reflectómetro OTDR',
        ]);

        Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-BETA-02',
            'descripcion' => 'Cinta Aislante Negra',
        ]);

        $response = $this->actingAs($this->admin)->get('/articulos?search=Reflectómetro');

        $response->assertStatus(200);
        $response->assertSee('SKU-ALPHA-01');
        $response->assertDontSee('SKU-BETA-02');
    }

    public function test_can_create_new_articulo_with_series_control(): void
    {
        $payload = [
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-NEW-FUS',
            'descripcion' => 'Fusionadora Núcleo 90S+',
            'marca' => 'Fujikura',
            'modelo' => '90S+',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'EQUIPO',
            'control_serie' => '1',
            'stock_minimo' => 2,
            'vida_util_meses' => 60,
            'estado' => 'ACTIVO',
            'observaciones' => 'Equipo nuevo con calibración de fábrica.',
        ];

        $response = $this->actingAs($this->admin)->post('/articulos', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('articulos', [
            'codigo_sku' => 'SKU-NEW-FUS',
            'control_serie' => true,
        ]);
    }

    public function test_articulo_sku_must_be_unique(): void
    {
        Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-DUPLICATE',
        ]);

        $payload = [
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-DUPLICATE',
            'descripcion' => 'Otro artículo',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'HERRAMIENTA',
            'stock_minimo' => 1,
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->admin)->post('/articulos', $payload);

        $response->assertSessionHasErrors('codigo_sku');
    }

    public function test_can_update_existing_articulo(): void
    {
        $art = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-EDIT-01',
            'descripcion' => 'Descripción Antigua',
        ]);

        $payload = [
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-EDIT-01',
            'descripcion' => 'Descripción Actualizada',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'MATERIAL',
            'control_serie' => '0',
            'stock_minimo' => 10,
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->admin)->put("/articulos/{$art->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('articulos', [
            'id' => $art->id,
            'descripcion' => 'Descripción Actualizada',
        ]);
    }

    public function test_cannot_delete_articulo_with_registered_activos(): void
    {
        $art = Articulo::factory()->serializado()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $ubicacion = Ubicacion::factory()->create();

        Activo::factory()->create([
            'articulo_id' => $art->id,
            'ubicacion_actual_id' => $ubicacion->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/articulos/{$art->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('articulos', ['id' => $art->id]);
    }

    public function test_cannot_delete_articulo_with_existing_warehouse_stock(): void
    {
        $art = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'control_serie' => false,
        ]);

        $ubicacion = Ubicacion::factory()->create();

        InventarioStock::create([
            'articulo_id' => $art->id,
            'ubicacion_id' => $ubicacion->id,
            'cantidad_actual' => 50,
        ]);

        $response = $this->actingAs($this->admin)->delete("/articulos/{$art->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('articulos', ['id' => $art->id]);
    }

    public function test_can_delete_articulo_without_dependencies(): void
    {
        $art = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/articulos/{$art->id}");

        $response->assertRedirect('/articulos');
        $this->assertSoftDeleted('articulos', ['id' => $art->id]);
    }

    public function test_can_filter_articulos_using_q_param_and_serial_numbers(): void
    {
        $catFibra = Categoria::factory()->create(['nombre' => 'Fibra Óptica Avanzada']);
        $art1 = Articulo::factory()->create([
            'categoria_id' => $catFibra->id,
            'codigo_sku' => 'SKU-FIBRA-99',
            'descripcion' => 'Cortadora de Precisión',
            'control_serie' => true,
        ]);
        $art2 = Articulo::factory()->create([
            'categoria_id' => $this->categoria->id,
            'codigo_sku' => 'SKU-OTHER-00',
            'descripcion' => 'Destornillador Plano',
            'control_serie' => false,
        ]);

        $ubicacion = Ubicacion::factory()->create();
        Activo::factory()->create([
            'articulo_id' => $art1->id,
            'ubicacion_actual_id' => $ubicacion->id,
            'numero_serie' => 'SN-FIBRA-888',
        ]);

        // Search by category name using q
        $responseCat = $this->actingAs($this->admin)->get('/articulos?q=Avanzada');
        $responseCat->assertStatus(200);
        $responseCat->assertSee('SKU-FIBRA-99');
        $responseCat->assertDontSee('SKU-OTHER-00');

        // Search by asset serial number using search
        $responseSerial = $this->actingAs($this->admin)->get('/articulos?search=SN-FIBRA-888');
        $responseSerial->assertStatus(200);
        $responseSerial->assertSee('SKU-FIBRA-99');
        $responseSerial->assertDontSee('SKU-OTHER-00');
    }
}
