<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Personal>
 */
class PersonalFactory extends Factory
{
    protected $model = Personal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_trabajador' => 'TRAB-'.fake()->unique()->numerify('####'),
            'codigo_fotocheck' => 'FCH-'.fake()->unique()->numerify('####'),
            'dni' => fake()->unique()->numerify('########'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            'cargo' => fake()->randomElement(['Técnico Instalador', 'Supervisor de Red', 'Capataz', 'Almacenero Auxiliar']),
            'area' => fake()->randomElement(['Operaciones', 'Telecomunicaciones', 'Construcción', 'Logística']),
            'telefono' => fake()->numerify('9########'),
            'correo' => fake()->unique()->safeEmail(),
            'proyecto_id' => null,
            'user_id' => null,
            'estado' => 'ACTIVO',
        ];
    }
}
