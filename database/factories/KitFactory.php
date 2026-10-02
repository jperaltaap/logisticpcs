<?php

namespace Database\Factories;

use App\Models\Kit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kit>
 */
class KitFactory extends Factory
{
    protected $model = Kit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_kit' => 'KIT-'.fake()->unique()->numerify('####'),
            'nombre_kit' => 'Kit de '.fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'tipo_kit' => fake()->randomElement(['KIT_HERRAMIENTAS', 'KIT_EPP', 'KIT_EMPALME', 'KIT_MATERIALES']),
            'estado' => 'ACTIVO',
        ];
    }
}
