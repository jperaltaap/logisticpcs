<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'codigo' => 'CAT-EPP',
                'nombre' => 'Equipos de Protección Personal (EPP)',
                'descripcion' => 'Cascos, guantes, arneses, botas dieléctricas y lentes de seguridad.',
            ],
            [
                'codigo' => 'CAT-HERR',
                'nombre' => 'Herramientas Manuales y Eléctricas',
                'descripcion' => 'Taladros, amoladoras, crimpadoras, llaves y herramientas de torque.',
            ],
            [
                'codigo' => 'CAT-MAT',
                'nombre' => 'Materiales de Construcción e Instalación',
                'descripcion' => 'Cables, tuberías, bandejas portacables, fijaciones y ferretería pesada.',
            ],
            [
                'codigo' => 'CAT-CONS',
                'nombre' => 'Consumibles de Taller y Obra',
                'descripcion' => 'Cintas aislantes, siliconas, solventes, brocas y discos de corte.',
            ],
            [
                'codigo' => 'CAT-TEL',
                'nombre' => 'Equipos de Telecomunicaciones',
                'descripcion' => 'Fusionadoras de fibra óptica, OTDR, routers, switches y antenas.',
            ],
            [
                'codigo' => 'CAT-INST',
                'nombre' => 'Instrumentos de Medición y Precisión',
                'descripcion' => 'Multímetros calibrados, telurómetros, medidores de potencia óptica y niveles láser.',
            ],
            [
                'codigo' => 'CAT-OFIC',
                'nombre' => 'Artículos de Oficina y Administración',
                'descripcion' => 'Papelería, suministros de impresión, archivadores y mobiliario de campo.',
            ],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['codigo' => $cat['codigo']],
                $cat
            );
        }
    }
}
