<?php

namespace Database\Seeders;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use Illuminate\Database\Seeder;

class RosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pry1 = Proyecto::where('codigo', 'PRY-001')->first() ?? Proyecto::first();
        $pry2 = Proyecto::where('codigo', 'PRY-002')->first() ?? $pry1;

        $personal = Personal::where('estado', 'ACTIVO')->get();

        if ($personal->isEmpty()) {
            return;
        }

        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        // Dividir personal en 2 grupos: Guardia A (activa) y Guardia B (en bajada/relevo)
        $guardiaA = $personal->slice(0, (int) ceil($personal->count() / 2));
        $guardiaB = $personal->slice((int) ceil($personal->count() / 2));

        // Guardia A: Días 1 al 14 en TRABAJO_CAMPO, Días 15 al 21 en BAJADA_DESCANSO, Días 22 en adelante en TRABAJO_CAMPO
        foreach ($guardiaA as $p) {
            $curr = $inicioMes->copy();
            while ($curr->lte($finMes)) {
                $diaMes = $curr->day;
                $condicion = ($diaMes >= 15 && $diaMes <= 21) ? 'BAJADA_DESCANSO' : 'TRABAJO_CAMPO';

                RosterTurno::updateOrCreate(
                    [
                        'personal_id' => $p->id,
                        'fecha' => $curr->toDateString(),
                    ],
                    [
                        'proyecto_id' => $p->proyecto_id ?? $pry1->id,
                        'grupo_guardia' => 'GUARDIA A',
                        'condicion_laboral' => $condicion,
                        'observaciones' => $condicion === 'BAJADA_DESCANSO' ? 'Descanso programado 14x7' : 'Obra activa',
                    ]
                );

                $curr->addDay();
            }
        }

        // Guardia B: Días 1 al 7 en BAJADA_DESCANSO, Días 8 al 21 en TRABAJO_CAMPO, Días 22 al 28 en BAJADA_DESCANSO
        foreach ($guardiaB as $p) {
            $curr = $inicioMes->copy();
            while ($curr->lte($finMes)) {
                $diaMes = $curr->day;
                $condicion = ($diaMes <= 7 || $diaMes >= 22) ? 'BAJADA_DESCANSO' : 'TRABAJO_CAMPO';

                RosterTurno::updateOrCreate(
                    [
                        'personal_id' => $p->id,
                        'fecha' => $curr->toDateString(),
                    ],
                    [
                        'proyecto_id' => $p->proyecto_id ?? $pry2->id,
                        'grupo_guardia' => 'GUARDIA B',
                        'condicion_laboral' => $condicion,
                        'observaciones' => $condicion === 'BAJADA_DESCANSO' ? 'Relevo 14x7' : 'Obra activa',
                    ]
                );

                $curr->addDay();
            }
        }
    }
}
