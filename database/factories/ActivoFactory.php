<?php

namespace Database\Factories;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activo>
 */
class ActivoFactory extends Factory
{
    protected $model = Activo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'articulo_id' => Articulo::factory()->serializado(),
            'codigo_interno' => 'ACT-'.fake()->unique()->numerify('#####'),
            'numero_serie' => 'SN-'.fake()->unique()->bothify('??-####-####'),
            'ubicacion_actual_id' => Ubicacion::factory(),
            'responsable_personal_id' => null,
            'cuadrilla_actual_id' => null,
            'proyecto_actual_id' => null,
            'estado_operativo' => 'OPERATIVO',
            'condicion_prestamo' => 'DISPONIBLE',
            'fecha_ingreso' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'fecha_ultima_asignacion' => null,
            'fecha_ultimo_retorno' => null,
            'observaciones' => fake()->optional()->sentence(),
        ];
    }

    public function enMantenimiento(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_operativo' => 'EN_MANTENIMIENTO',
        ]);
    }

    public function prestado(): static
    {
        return $this->state(fn (array $attributes) => [
            'condicion_prestamo' => 'PRESTADO_CAMPO',
            'fecha_ultima_asignacion' => now(),
        ]);
    }
}
