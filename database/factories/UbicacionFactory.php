<?php

namespace Database\Factories;

use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ubicacion>
 */
class UbicacionFactory extends Factory
{
    protected $model = Ubicacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => 'ALM-'.fake()->unique()->numerify('###'),
            'nombre' => 'Almacén '.fake()->city(),
            'descripcion' => fake()->sentence(),
            'tipo' => fake()->randomElement(['ALMACEN_CENTRAL', 'ALMACEN_OBRA', 'EN_CAMPO', 'TALLER_REPARACION']),
            'estado' => 'ACTIVO',
        ];
    }
}
