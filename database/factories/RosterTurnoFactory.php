<?php

namespace Database\Factories;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RosterTurno>
 */
class RosterTurnoFactory extends Factory
{
    protected $model = RosterTurno::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personal_id' => Personal::factory(),
            'proyecto_id' => Proyecto::factory(),
            'grupo_guardia' => 'GUARDIA A',
            'fecha' => fake()->date(),
            'condicion_laboral' => 'TRABAJO_CAMPO',
            'observaciones' => null,
        ];
    }
}
