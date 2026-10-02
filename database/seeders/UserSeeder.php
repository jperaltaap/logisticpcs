<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@logisticpcs.pe',
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('Password123!'),
                'rol' => 'ADMINISTRADOR',
                'estado' => 'ACTIVO',
                'role_spatie' => 'ADMINISTRADOR',
            ],
            [
                'email' => 'almacen@logisticpcs.pe',
                'name' => 'Encargado de Logística y Almacén',
                'password' => Hash::make('Password123!'),
                'rol' => 'LOGISTICO',
                'estado' => 'ACTIVO',
                'role_spatie' => 'LOGISTICO',
            ],
            [
                'email' => 'supervisor@logisticpcs.pe',
                'name' => 'Supervisor de Operaciones',
                'password' => Hash::make('Password123!'),
                'rol' => 'SUPERVISOR',
                'estado' => 'ACTIVO',
                'role_spatie' => 'SUPERVISOR',
            ],
            [
                'email' => 'tecnico@logisticpcs.pe',
                'name' => 'Técnico de Campo',
                'password' => Hash::make('Password123!'),
                'rol' => 'TECNICO',
                'estado' => 'ACTIVO',
                'role_spatie' => 'TECNICO',
            ],
            [
                'email' => 'auditor@logisticpcs.pe',
                'name' => 'Auditor de Control Interno',
                'password' => Hash::make('Password123!'),
                'rol' => 'AUDITOR',
                'estado' => 'ACTIVO',
                'role_spatie' => 'AUDITOR',
            ],
        ];

        foreach ($users as $data) {
            $roleSpatie = $data['role_spatie'];
            unset($data['role_spatie']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            $user->syncRoles([$roleSpatie]);
        }
    }
}
