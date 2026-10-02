<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\KardexMovimiento;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KardexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Articulo $articulo;

    private Ubicacion $almacen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $cat = Categoria::factory()->create();

        $this->articulo = Articulo::factory()->create([
            'categoria_id' => $cat->id,
            'codigo_sku' => 'SKU-KDX-01',
            'descripcion' => 'Fibra Óptica ADSS',
        ]);

        $this->almacen = Ubicacion::factory()->create([
            'codigo' => 'ALM-01',
            'nombre' => 'Almacén Central',
        ]);
    }

    public function test_guests_cannot_access_kardex(): void
    {
        $response = $this->get('/kardex');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_kardex_list(): void
    {
        KardexMovimiento::create([
            'articulo_id' => $this->articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'tipo_movimiento' => 'INGRESO_COMPRA',
            'cantidad' => 500.00,
            'stock_anterior' => 0.00,
            'stock_posterior' => 500.00,
            'usuario_id' => $this->admin->id,
            'fecha_movimiento' => now(),
            'motivo' => 'Compra inicial de bobinas',
        ]);

        $response = $this->actingAs($this->admin)->get('/kardex');

        $response->assertStatus(200);
        $response->assertSee('SKU-KDX-01');
        $response->assertSee('INGRESO COMPRA');
        $response->assertSee('500.00');
    }

    public function test_can_filter_kardex_by_movement_type(): void
    {
        KardexMovimiento::create([
            'articulo_id' => $this->articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'tipo_movimiento' => 'INGRESO_COMPRA',
            'cantidad' => 100.00,
            'stock_anterior' => 0.00,
            'stock_posterior' => 100.00,
            'usuario_id' => $this->admin->id,
            'motivo' => 'Ingreso de bobinas',
        ]);

        KardexMovimiento::create([
            'articulo_id' => $this->articulo->id,
            'ubicacion_id' => $this->almacen->id,
            'tipo_movimiento' => 'SALIDA_CONSUMO',
            'cantidad' => 20.00,
            'stock_anterior' => 100.00,
            'stock_posterior' => 80.00,
            'usuario_id' => $this->admin->id,
            'motivo' => 'Consumo en tendido',
        ]);

        $response = $this->actingAs($this->admin)->get('/kardex?tipo_movimiento=SALIDA_CONSUMO');

        $response->assertStatus(200);
        $response->assertSee('Consumo en tendido');
        $response->assertDontSee('Ingreso de bobinas');
    }
}
