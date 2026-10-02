<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class InventarioStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $almacenCentral = Ubicacion::where('codigo', 'ALM-CEN-01')->first() ?? Ubicacion::first();
        $almacenNorte = Ubicacion::where('codigo', 'ALM-NORTE')->first();

        $articulosNoSerializados = Articulo::where('control_serie', false)->get();

        foreach ($articulosNoSerializados as $articulo) {
            $stockCentral = match ($articulo->codigo_sku) {
                'SKU-CAB-001' => 8000.00,
                'SKU-CAS-001' => 45.00,
                'SKU-GUA-001' => 120.00,
                'SKU-PEL-001' => 25.00,
                'SKU-ALC-001' => 30.00,
                'SKU-TOA-001' => 40.00,
                'SKU-MANG-001' => 50.00,
                default => 20.00,
            };

            if ($almacenCentral) {
                InventarioStock::firstOrCreate(
                    [
                        'articulo_id' => $articulo->id,
                        'ubicacion_id' => $almacenCentral->id,
                    ],
                    [
                        'cantidad_actual' => $stockCentral,
                    ]
                );
            }

            if ($almacenNorte) {
                InventarioStock::firstOrCreate(
                    [
                        'articulo_id' => $articulo->id,
                        'ubicacion_id' => $almacenNorte->id,
                    ],
                    [
                        'cantidad_actual' => round($stockCentral * 0.35, 2),
                    ]
                );
            }
        }
    }
}
