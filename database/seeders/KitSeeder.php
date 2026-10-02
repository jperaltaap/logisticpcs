<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\Kit;
use Illuminate\Database\Seeder;

class KitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kitEmpalme = Kit::firstOrCreate(
            ['codigo_kit' => 'KIT-EMP-001'],
            [
                'nombre_kit' => 'Kit Estándar de Empalme de Fibra Óptica',
                'descripcion' => 'Conjunto completo para cuadrillas de empalme: fusionadora, cortadora cleaver, peladora, consumibles de limpieza y manguitos.',
                'tipo_kit' => 'KIT_EMPALME',
                'estado' => 'ACTIVO',
            ]
        );

        $artFusionadora = Articulo::where('codigo_sku', 'SKU-FUS-001')->first();
        $artCortadora = Articulo::where('codigo_sku', 'SKU-CORT-001')->first();
        $artPeladora = Articulo::where('codigo_sku', 'SKU-PEL-001')->first();
        $artAlcohol = Articulo::where('codigo_sku', 'SKU-ALC-001')->first();
        $artToallitas = Articulo::where('codigo_sku', 'SKU-TOA-001')->first();
        $artManguitos = Articulo::where('codigo_sku', 'SKU-MANG-001')->first();

        $componentesEmpalme = [
            $artFusionadora?->id => 1.00,
            $artCortadora?->id => 1.00,
            $artPeladora?->id => 2.00,
            $artAlcohol?->id => 1.00,
            $artToallitas?->id => 1.00,
            $artManguitos?->id => 2.00,
        ];

        foreach ($componentesEmpalme as $artId => $qty) {
            if ($artId) {
                $kitEmpalme->articulos()->syncWithoutDetaching([
                    $artId => ['cantidad' => $qty],
                ]);
            }
        }

        $kitAltura = Kit::firstOrCreate(
            ['codigo_kit' => 'KIT-EPP-001'],
            [
                'nombre_kit' => 'Kit de Protección contra Caídas / Trabajo en Altura',
                'descripcion' => 'Equipamiento homologado de seguridad para linieros y técnicos en postes o torres.',
                'tipo_kit' => 'KIT_EPP',
                'estado' => 'ACTIVO',
            ]
        );

        $artArnes = Articulo::where('codigo_sku', 'SKU-ARN-001')->first();
        $artCasco = Articulo::where('codigo_sku', 'SKU-CAS-001')->first();
        $artGuantes = Articulo::where('codigo_sku', 'SKU-GUA-001')->first();

        $componentesAltura = [
            $artArnes?->id => 1.00,
            $artCasco?->id => 1.00,
            $artGuantes?->id => 2.00,
        ];

        foreach ($componentesAltura as $artId => $qty) {
            if ($artId) {
                $kitAltura->articulos()->syncWithoutDetaching([
                    $artId => ['cantidad' => $qty],
                ]);
            }
        }
    }
}
