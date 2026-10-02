<?php

namespace Database\Seeders;

use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pry1 = Proyecto::where('codigo', 'PRY-001')->first();
        $pry2 = Proyecto::where('codigo', 'PRY-002')->first();
        $pry3 = Proyecto::where('codigo', 'PRY-003')->first();
        $adminUser = User::where('email', 'admin@logisticpcs.test')->first();

        $personalList = [
            [
                'codigo_trabajador' => 'TRAB-001',
                'codigo_fotocheck' => 'FCH-1001',
                'dni' => '45871234',
                'nombres' => 'Carlos Alberto',
                'apellidos' => 'Mendoza Rivas',
                'cargo' => 'Ingeniero Residente',
                'area' => 'Operaciones',
                'telefono' => '987123456',
                'correo' => 'cmendoza@logisticpcs.test',
                'proyecto_id' => $pry1?->id,
                'user_id' => $adminUser?->id,
                'estado' => 'ACTIVO',
            ],
            [
                'codigo_trabajador' => 'TRAB-002',
                'codigo_fotocheck' => 'FCH-1002',
                'dni' => '42981122',
                'nombres' => 'José Luis',
                'apellidos' => 'Huamán Quispe',
                'cargo' => 'Capataz de Cuadrilla',
                'area' => 'Construcción',
                'telefono' => '976543210',
                'correo' => 'jhuaman@logisticpcs.test',
                'proyecto_id' => $pry1?->id,
                'user_id' => null,
                'estado' => 'ACTIVO',
            ],
            [
                'codigo_trabajador' => 'TRAB-003',
                'codigo_fotocheck' => 'FCH-1003',
                'dni' => '71029384',
                'nombres' => 'Walter Daniel',
                'apellidos' => 'Sánchez Torres',
                'cargo' => 'Técnico Empalmador Fibra',
                'area' => 'Telecomunicaciones',
                'telefono' => '954321876',
                'correo' => 'wsanchez@gmail.com',
                'proyecto_id' => $pry1?->id,
                'user_id' => null,
                'estado' => 'ACTIVO',
            ],
            [
                'codigo_trabajador' => 'TRAB-004',
                'codigo_fotocheck' => 'FCH-1004',
                'dni' => '40918273',
                'nombres' => 'Raúl Fernando',
                'apellidos' => 'Cáceres Vega',
                'cargo' => 'Supervisor de Seguridad & SSOMA',
                'area' => 'Seguridad',
                'telefono' => '998877665',
                'correo' => 'rcaceres@logisticpcs.test',
                'proyecto_id' => $pry2?->id,
                'user_id' => null,
                'estado' => 'ACTIVO',
            ],
            [
                'codigo_trabajador' => 'TRAB-005',
                'codigo_fotocheck' => 'FCH-1005',
                'dni' => '46718290',
                'nombres' => 'Marisol Elena',
                'apellidos' => 'Ramos Díaz',
                'cargo' => 'Almacenera de Obra',
                'area' => 'Logística',
                'telefono' => '912345678',
                'correo' => 'mramos@logisticpcs.test',
                'proyecto_id' => $pry3?->id,
                'user_id' => null,
                'estado' => 'ACTIVO',
            ],
        ];

        foreach ($personalList as $p) {
            Personal::updateOrCreate(
                ['dni' => $p['dni']],
                $p
            );
        }

        // Asignar responsables de proyecto
        $ingCarlos = Personal::where('dni', '45871234')->first();
        if ($ingCarlos && $pry1) {
            $pry1->update(['responsable_personal_id' => $ingCarlos->id]);
        }
        $supRaul = Personal::where('dni', '40918273')->first();
        if ($supRaul && $pry2) {
            $pry2->update(['responsable_personal_id' => $supRaul->id]);
        }
    }
}
