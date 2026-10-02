<?php

namespace Database\Seeders;

use App\Models\Proyecto;
use Illuminate\Database\Seeder;

class ProyectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proyectos = [
            [
                'codigo' => 'PRY-001',
                'nombre' => 'Expansión Red Troncal Fibra Óptica Lima Sur',
                'cliente' => 'Telefónica del Perú / PangeaCo',
                'ubicacion_direccion' => 'Av. Separadora Industrial Km 38, Villa El Salvador / Lurín, Lima',
                'fecha_inicio' => '2026-01-15',
                'fecha_fin_estimada' => '2026-12-31',
                'estado' => 'ACTIVO',
                'observaciones' => 'Despliegue de 120 km de cable ADSS de 48 hilos.',
            ],
            [
                'codigo' => 'PRY-002',
                'nombre' => 'Mantenimiento Preventivo Enlaces Microondas Centro',
                'cliente' => 'Torrecom Perú S.A.',
                'ubicacion_direccion' => 'Carretera Central Km 118, Casapalca / Morococha / Huancayo, Junín',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin_estimada' => '2026-08-30',
                'estado' => 'ACTIVO',
                'observaciones' => 'Alineamiento de antenas parabólicas y cambio de guías de onda.',
            ],
            [
                'codigo' => 'PRY-003',
                'nombre' => 'Despliegue FTTH Conectividad Rural Norte',
                'cliente' => 'PRONATEL',
                'ubicacion_direccion' => 'Panamericana Norte Km 520, Virú / Chao / Trujillo, La Libertad',
                'fecha_inicio' => '2026-03-01',
                'fecha_fin_estimada' => '2026-11-30',
                'estado' => 'ACTIVO',
                'observaciones' => 'Tendido de drop óptico e instalación de cajas NAP para 3,500 abonados.',
            ],
        ];

        foreach ($proyectos as $pry) {
            Proyecto::updateOrCreate(
                ['codigo' => $pry['codigo']],
                $pry
            );
        }
    }
}
