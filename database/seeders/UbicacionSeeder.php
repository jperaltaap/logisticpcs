<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ubicaciones = [
            [
                'codigo' => 'ALM-CEN',
                'nombre' => 'Almacén Central',
                'descripcion' => 'Base central de almacenamiento, recepción de proveedores y control principal de inventario.',
                'tipo' => 'ALMACEN_CENTRAL',
                'estado' => 'ACTIVO',
            ],
            [
                'codigo' => 'CAMPO',
                'nombre' => 'En Campo / Frente de Trabajo',
                'descripcion' => 'Ubicación operativa en frentes de obra y proyectos activos donde operan las cuadrillas.',
                'tipo' => 'EN_CAMPO',
                'estado' => 'ACTIVO',
            ],
            [
                'codigo' => 'TALLER-REP',
                'nombre' => 'Taller de Reparación',
                'descripcion' => 'Área técnica destinada a mantenimientos preventivos, correctivos y diagnósticos de equipos.',
                'tipo' => 'TALLER_REPARACION',
                'estado' => 'ACTIVO',
            ],
        ];

        foreach ($ubicaciones as $ubi) {
            Ubicacion::firstOrCreate(
                ['codigo' => $ubi['codigo']],
                $ubi
            );
        }
    }
}
