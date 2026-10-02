<?php

namespace Database\Factories;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proyecto>
 */
class ProyectoFactory extends Factory
{
    protected $model = Proyecto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => 'PRY-'.fake()->unique()->numerify('###'),
            'nombre' => 'Proyecto '.fake()->city(),
            'cliente' => fake()->company(),
            'ubicacion_direccion' => fake()->address(),
            'fecha_inicio' => now()->subMonths(1)->format('Y-m-d'),
            'fecha_fin_estimada' => now()->addMonths(6)->format('Y-m-d'),
            'responsable_personal_id' => null,
            'estado' => 'ACTIVO',
            'observaciones' => fake()->sentence(),
        ];
    }
}
