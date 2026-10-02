<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Articulo>
 */
class ArticuloFactory extends Factory
{
    protected $model = Articulo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'codigo_sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'descripcion' => fake()->words(3, true),
            'marca' => fake()->randomElement(['3M', 'Stanley', 'Fujikura', 'Fluke', 'Bosch', 'DeWalt', 'Furukawa']),
            'modelo' => 'MOD-'.fake()->numerify('###'),
            'unidad_medida' => 'UND',
            'tipo_articulo' => fake()->randomElement(['EPP', 'HERRAMIENTA', 'EQUIPO', 'MATERIAL', 'CONSUMIBLE']),
            'control_serie' => false,
            'stock_minimo' => 5.00,
            'vida_util_meses' => 24,
            'foto_referencia' => null,
            'estado' => 'ACTIVO',
            'observaciones' => fake()->sentence(),
        ];
    }

    public function serializado(): static
    {
        return $this->state(fn (array $attributes) => [
            'control_serie' => true,
            'tipo_articulo' => 'EQUIPO',
        ]);
    }
}
