<?php

namespace Database\Factories;

use App\Models\Cuadrilla;
use App\Models\Personal;
use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuadrilla>
 */
class CuadrillaFactory extends Factory
{
    protected $model = Cuadrilla::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_cuadrilla' => 'CD-'.fake()->unique()->numerify('###'),
            'nombre' => 'Cuadrilla '.fake()->words(2, true),
            'proyecto_id' => Proyecto::factory(),
            'lider_personal_id' => Personal::factory(),
            'regimen_laboral' => '14x7',
            'estado' => 'ACTIVA',
            'observaciones' => fake()->sentence(),
        ];
    }
}
