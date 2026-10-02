<?php

namespace Database\Seeders;

use App\Models\Cuadrilla;
use App\Models\CuadrillaPersonal;
use App\Models\Personal;
use App\Models\Proyecto;
use Illuminate\Database\Seeder;

class CuadrillaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pry1 = Proyecto::where('codigo', 'PRY-001')->first() ?? Proyecto::first();
        $pry2 = Proyecto::where('codigo', 'PRY-002')->first() ?? $pry1;

        $trab2 = Personal::where('codigo_trabajador', 'TRAB-002')->first() ?? Personal::first();
        $trab3 = Personal::where('codigo_trabajador', 'TRAB-003')->first();
        $trab4 = Personal::where('codigo_trabajador', 'TRAB-004')->first();
        $trab5 = Personal::where('codigo_trabajador', 'TRAB-005')->first();
        $trab6 = Personal::where('codigo_trabajador', 'TRAB-006')->first();

        // 1. Cuadrilla Fusión & Empalme Norte
        $c1 = Cuadrilla::firstOrCreate(
            ['codigo_cuadrilla' => 'CD-101'],
            [
                'nombre' => 'Cuadrilla Fusión & Empalme Troncal Norte',
                'proyecto_id' => $pry1->id,
                'lider_personal_id' => $trab2->id,
                'regimen_laboral' => '14x7',
                'estado' => 'ACTIVA',
                'observaciones' => 'Especializada en empalmes de fibra óptica monomodo y pruebas OTDR.',
            ]
        );

        CuadrillaPersonal::firstOrCreate(
            ['cuadrilla_id' => $c1->id, 'personal_id' => $trab2->id],
            [
                'rol_en_cuadrilla' => 'LIDER DE CUADRILLA',
                'fecha_incorporacion' => now()->subMonths(2)->toDateString(),
            ]
        );

        if ($trab3) {
            CuadrillaPersonal::firstOrCreate(
                ['cuadrilla_id' => $c1->id, 'personal_id' => $trab3->id],
                [
                    'rol_en_cuadrilla' => 'TECNICO EMPALMADOR',
                    'fecha_incorporacion' => now()->subMonths(2)->toDateString(),
                ]
            );
        }

        if ($trab4) {
            CuadrillaPersonal::firstOrCreate(
                ['cuadrilla_id' => $c1->id, 'personal_id' => $trab4->id],
                [
                    'rol_en_cuadrilla' => 'FUSIONADOR / LINIERTO',
                    'fecha_incorporacion' => now()->subMonths(1)->toDateString(),
                ]
            );
        }

        // 2. Cuadrilla Tendido Aéreo & Postes
        $lider2 = $trab5 ?? $trab2;
        $c2 = Cuadrilla::firstOrCreate(
            ['codigo_cuadrilla' => 'CD-102'],
            [
                'nombre' => 'Cuadrilla Tendido Aéreo & Postes',
                'proyecto_id' => $pry1->id,
                'lider_personal_id' => $lider2->id,
                'regimen_laboral' => '14x7',
                'estado' => 'ACTIVA',
                'observaciones' => 'Trabajos de altura, ferretería de soporte y tendido de cable ADSS.',
            ]
        );

        CuadrillaPersonal::firstOrCreate(
            ['cuadrilla_id' => $c2->id, 'personal_id' => $lider2->id],
            [
                'rol_en_cuadrilla' => 'CAPATAZ DE CUADRILLA',
                'fecha_incorporacion' => now()->subMonths(1)->toDateString(),
            ]
        );

        // 3. Cuadrilla Canalización & Obras Civiles Sur
        $lider3 = $trab6 ?? $trab2;
        Cuadrilla::firstOrCreate(
            ['codigo_cuadrilla' => 'CD-103'],
            [
                'nombre' => 'Cuadrilla Canalización & Obras Civiles Sur',
                'proyecto_id' => $pry2->id,
                'lider_personal_id' => $lider3->id,
                'regimen_laboral' => '14x7',
                'estado' => 'EN_DESCANSO',
                'observaciones' => 'Zanjeo, buzones y ductería subterránea. En período de relevo de guardia.',
            ]
        );
    }
}
