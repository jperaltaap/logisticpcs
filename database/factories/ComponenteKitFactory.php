<?php

namespace Database\Factories;

use App\Models\Articulo;
use App\Models\ComponenteKit;
use App\Models\Kit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComponenteKit>
 */
class ComponenteKitFactory extends Factory
{
    protected $model = ComponenteKit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kit_id' => Kit::factory(),
            'articulo_id' => Articulo::factory(),
            'cantidad' => fake()->randomFloat(2, 1, 5),
        ];
    }
}
