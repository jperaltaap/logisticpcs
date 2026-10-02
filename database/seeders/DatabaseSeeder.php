<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            UbicacionSeeder::class,
            ProyectoSeeder::class,
            PersonalSeeder::class,
            ArticuloSeeder::class,
            ActivoSeeder::class,
            KitSeeder::class,
            InventarioStockSeeder::class,
            DespachoSeeder::class,
            CuadrillaSeeder::class,
            RosterSeeder::class,
            MantenimientoCalibracionSeeder::class,
            NotificacionAlertaSeeder::class,
        ]);
    }
}
