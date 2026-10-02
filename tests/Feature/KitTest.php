<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Kit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Articulo $art1;

    private Articulo $art2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);

        $cat = Categoria::factory()->create();

        $this->art1 = Articulo::factory()->create([
            'categoria_id' => $cat->id,
            'codigo_sku' => 'SKU-KIT-01',
            'descripcion' => 'Arnés 4 Anillos',
        ]);

        $this->art2 = Articulo::factory()->create([
            'categoria_id' => $cat->id,
            'codigo_sku' => 'SKU-KIT-02',
            'descripcion' => 'Casco Dieléctrico',
        ]);
    }

    public function test_guests_cannot_access_kits(): void
    {
        $response = $this->get('/kits');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_kits_list(): void
    {
        Kit::factory()->create([
            'codigo_kit' => 'KIT-TEST-01',
            'nombre_kit' => 'Kit de Altura Especial',
        ]);

        $response = $this->actingAs($this->admin)->get('/kits');

        $response->assertStatus(200);
        $response->assertSee('KIT-TEST-01');
        $response->assertSee('Kit de Altura Especial');
    }

    public function test_can_create_kit_with_components(): void
    {
        $payload = [
            'codigo_kit' => 'KIT-NEW-001',
            'nombre_kit' => 'Kit Trabajo en Altura',
            'descripcion' => 'Equipo para cuadrilla liniera en torres.',
            'tipo_kit' => 'KIT_EPP',
            'estado' => 'ACTIVO',
            'componentes' => [
                ['articulo_id' => $this->art1->id, 'cantidad' => 1.00],
                ['articulo_id' => $this->art2->id, 'cantidad' => 2.00],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/kits', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('kits', [
            'codigo_kit' => 'KIT-NEW-001',
            'nombre_kit' => 'Kit Trabajo en Altura',
        ]);

        $kit = Kit::where('codigo_kit', 'KIT-NEW-001')->first();
        $this->assertDatabaseHas('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $this->art1->id,
            'cantidad' => 1.00,
        ]);
        $this->assertDatabaseHas('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $this->art2->id,
            'cantidad' => 2.00,
        ]);
    }

    public function test_can_view_kit_detail_with_components(): void
    {
        $kit = Kit::factory()->create([
            'codigo_kit' => 'KIT-VIEW-01',
            'nombre_kit' => 'Kit Empalmador Pro',
        ]);

        $kit->articulos()->attach($this->art1->id, ['cantidad' => 3.00]);

        $response = $this->actingAs($this->admin)->get("/kits/{$kit->id}");

        $response->assertStatus(200);
        $response->assertSee('KIT-VIEW-01');
        $response->assertSee('Kit Empalmador Pro');
        $response->assertSee('SKU-KIT-01');
        $response->assertSee('3.00');
    }

    public function test_can_update_kit_and_sync_components(): void
    {
        $kit = Kit::factory()->create([
            'codigo_kit' => 'KIT-UPD-01',
            'nombre_kit' => 'Kit Original',
        ]);

        $kit->articulos()->attach($this->art1->id, ['cantidad' => 1.00]);

        $payload = [
            'codigo_kit' => 'KIT-UPD-01',
            'nombre_kit' => 'Kit Renombrado',
            'tipo_kit' => 'KIT_HERRAMIENTAS',
            'estado' => 'ACTIVO',
            'componentes' => [
                ['articulo_id' => $this->art2->id, 'cantidad' => 5.00],
            ],
        ];

        $response = $this->actingAs($this->admin)->put("/kits/{$kit->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('kits', [
            'id' => $kit->id,
            'nombre_kit' => 'Kit Renombrado',
        ]);

        // art1 should have been replaced by art2
        $this->assertDatabaseMissing('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $this->art1->id,
        ]);
        $this->assertDatabaseHas('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $this->art2->id,
            'cantidad' => 5.00,
        ]);
    }

    public function test_can_delete_kit(): void
    {
        $kit = Kit::factory()->create([
            'codigo_kit' => 'KIT-DEL-01',
        ]);

        $kit->articulos()->attach($this->art1->id, ['cantidad' => 1.00]);

        $response = $this->actingAs($this->admin)->delete("/kits/{$kit->id}");

        $response->assertRedirect('/kits');
        $this->assertDatabaseMissing('kits', ['id' => $kit->id]);
        $this->assertDatabaseMissing('componentes_kit', ['kit_id' => $kit->id]);
    }
}
