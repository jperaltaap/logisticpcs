<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\Categoria;
use Illuminate\Database\Seeder;

class ArticuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catEpp = Categoria::where('codigo', 'CAT-EPP')->first();
        $catHerr = Categoria::where('codigo', 'CAT-HERR')->first();
        $catMat = Categoria::where('codigo', 'CAT-MAT')->first();
        $catCons = Categoria::where('codigo', 'CAT-CONS')->first();
        $catTel = Categoria::where('codigo', 'CAT-TEL')->first();
        $catInst = Categoria::where('codigo', 'CAT-INST')->first();

        $articulos = [
            // Serialized Telecom equipment
            [
                'categoria_id' => $catTel?->id,
                'codigo_sku' => 'SKU-FUS-001',
                'descripcion' => 'Fusionadora de Fibra Óptica por Alineación de Núcleo',
                'marca' => 'Fujikura',
                'modelo' => '90S+',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EQUIPO',
                'control_serie' => true,
                'stock_minimo' => 2.00,
                'vida_util_meses' => 60,
                'estado' => 'ACTIVO',
                'observaciones' => 'Equipo de alta precisión. Requiere calibración periódica.',
            ],
            [
                'categoria_id' => $catTel?->id,
                'codigo_sku' => 'SKU-OTDR-001',
                'descripcion' => 'Reflectómetro Óptico en el Dominio del Tiempo (OTDR)',
                'marca' => 'EXFO',
                'modelo' => 'FTB-1v2 Pro',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EQUIPO',
                'control_serie' => true,
                'stock_minimo' => 2.00,
                'vida_util_meses' => 48,
                'estado' => 'ACTIVO',
                'observaciones' => 'Mide atenuación y eventos en enlaces de fibra monomodo y multimodo.',
            ],
            [
                'categoria_id' => $catTel?->id,
                'codigo_sku' => 'SKU-OPM-001',
                'descripcion' => 'Medidor de Potencia Óptica Portátil (Power Meter)',
                'marca' => 'Grandway',
                'modelo' => 'FHP2P01',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EQUIPO',
                'control_serie' => true,
                'stock_minimo' => 4.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'Para mediciones de potencia PON y fibras ópticas.',
            ],
            // Serialized Instruments & Power Tools
            [
                'categoria_id' => $catInst?->id,
                'codigo_sku' => 'SKU-MULT-001',
                'descripcion' => 'Multímetro Digital True-RMS con Certificado de Calibración',
                'marca' => 'Fluke',
                'modelo' => '179 True-RMS',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EQUIPO',
                'control_serie' => true,
                'stock_minimo' => 3.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'Uso en tableros eléctricos y pruebas de continuidad.',
            ],
            [
                'categoria_id' => $catInst?->id,
                'codigo_sku' => 'SKU-TELUR-001',
                'descripcion' => 'Telurómetro Digital para Medición de Puesta a Tierra',
                'marca' => 'Megger',
                'modelo' => 'DET4TD2',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EQUIPO',
                'control_serie' => true,
                'stock_minimo' => 2.00,
                'vida_util_meses' => 48,
                'estado' => 'ACTIVO',
                'observaciones' => 'Incluye picas y cables de prueba para pozos a tierra.',
            ],
            [
                'categoria_id' => $catHerr?->id,
                'codigo_sku' => 'SKU-TAL-001',
                'descripcion' => 'Rotomartillo Inalámbrico SDS-Plus 18V Brushless',
                'marca' => 'DeWalt',
                'modelo' => 'DCH273B',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'HERRAMIENTA',
                'control_serie' => true,
                'stock_minimo' => 4.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'Incluye 2 baterías de litio 5.0Ah y cargador rápido.',
            ],
            // EPP with series control (critical harnesses) and regular EPP
            [
                'categoria_id' => $catEpp?->id,
                'codigo_sku' => 'SKU-ARN-001',
                'descripcion' => 'Arnés de Seguridad de Cuerpo Entero 4 Anillos Dieléctrico',
                'marca' => '3M Protecta',
                'modelo' => 'PRO-D100',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EPP',
                'control_serie' => true,
                'stock_minimo' => 10.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'Inspección visual obligatoria cada 6 meses. Serie trazable.',
            ],
            [
                'categoria_id' => $catEpp?->id,
                'codigo_sku' => 'SKU-CAS-001',
                'descripcion' => 'Casco de Seguridad Dieléctrico Tipo II con Barbiquejo 4 Puntas',
                'marca' => '3M',
                'modelo' => 'SecureFit H-700',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'EPP',
                'control_serie' => false,
                'stock_minimo' => 20.00,
                'vida_util_meses' => 24,
                'estado' => 'ACTIVO',
                'observaciones' => 'Cumple ANSI Z89.1 Clase E.',
            ],
            [
                'categoria_id' => $catEpp?->id,
                'codigo_sku' => 'SKU-GUA-001',
                'descripcion' => 'Guantes de Nitrilo Antideslizantes para Trabajo de Precisión',
                'marca' => 'Ansell',
                'modelo' => 'HyFlex 11-840',
                'unidad_medida' => 'PAR',
                'tipo_articulo' => 'EPP',
                'control_serie' => false,
                'stock_minimo' => 50.00,
                'vida_util_meses' => 6,
                'estado' => 'ACTIVO',
                'observaciones' => 'Tallas M y L para empalmadores y técnicos.',
            ],
            // Non-serialized tools & materials
            [
                'categoria_id' => $catHerr?->id,
                'codigo_sku' => 'SKU-PEL-001',
                'descripcion' => 'Peladora de Fibra Óptica de 3 Posiciones (Stripper)',
                'marca' => 'Miller',
                'modelo' => 'FO 103-T-250-J',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'HERRAMIENTA',
                'control_serie' => false,
                'stock_minimo' => 15.00,
                'vida_util_meses' => 24,
                'estado' => 'ACTIVO',
                'observaciones' => 'Para recubrimiento de 250um, 900um y chaqueta exterior.',
            ],
            [
                'categoria_id' => $catHerr?->id,
                'codigo_sku' => 'SKU-CORT-001',
                'descripcion' => 'Cortadora de Precisión para Fibra Óptica (Cleaver)',
                'marca' => 'Fujikura',
                'modelo' => 'CT-50',
                'unidad_medida' => 'UND',
                'tipo_articulo' => 'HERRAMIENTA',
                'control_serie' => true,
                'stock_minimo' => 4.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'Cuchilla rotativa con conteo inteligente de cortes.',
            ],
            [
                'categoria_id' => $catMat?->id,
                'codigo_sku' => 'SKU-CAB-001',
                'descripcion' => 'Cable de Fibra Óptica ADSS 48 Hilos Span 200m Monomodo G.652D',
                'marca' => 'Furukawa',
                'modelo' => 'ADSS-48H-S200',
                'unidad_medida' => 'METRO',
                'tipo_articulo' => 'MATERIAL',
                'control_serie' => false,
                'stock_minimo' => 1000.00,
                'vida_util_meses' => 240,
                'estado' => 'ACTIVO',
                'observaciones' => 'Bobinas de 4000 metros para tendido aéreo.',
            ],
            // Consumables
            [
                'categoria_id' => $catCons?->id,
                'codigo_sku' => 'SKU-ALC-001',
                'descripcion' => 'Alcohol Isopropílico de Alta Pureza 99.8% Frasco Dispensador 1 Litro',
                'marca' => 'Electrolube',
                'modelo' => 'IPA-99.8',
                'unidad_medida' => 'LITRO',
                'tipo_articulo' => 'CONSUMIBLE',
                'control_serie' => false,
                'stock_minimo' => 10.00,
                'vida_util_meses' => 24,
                'estado' => 'ACTIVO',
                'observaciones' => 'Para limpieza de caras ópticas y empalmes.',
            ],
            [
                'categoria_id' => $catCons?->id,
                'codigo_sku' => 'SKU-TOA-001',
                'descripcion' => 'Toallitas Limpiadoras Secas Sin Pelusa para Fibra Óptica (Caja x 280)',
                'marca' => 'Kimwipes',
                'modelo' => 'EX-L 280',
                'unidad_medida' => 'CAJA',
                'tipo_articulo' => 'CONSUMIBLE',
                'control_serie' => false,
                'stock_minimo' => 12.00,
                'vida_util_meses' => 36,
                'estado' => 'ACTIVO',
                'observaciones' => 'No desprende residuos de pelusa.',
            ],
            [
                'categoria_id' => $catCons?->id,
                'codigo_sku' => 'SKU-MANG-001',
                'descripcion' => 'Manguitos Termocontraíbles de Empalme 60mm con Varilla de Acero (Paq x 100)',
                'marca' => 'OptiFit',
                'modelo' => 'SM-60',
                'unidad_medida' => 'PAQUETE',
                'tipo_articulo' => 'CONSUMIBLE',
                'control_serie' => false,
                'stock_minimo' => 20.00,
                'vida_util_meses' => 60,
                'estado' => 'ACTIVO',
                'observaciones' => 'Protección de fusiones en bandejas de empalme.',
            ],
        ];

        foreach ($articulos as $art) {
            Articulo::firstOrCreate(
                ['codigo_sku' => $art['codigo_sku']],
                $art
            );
        }
    }
}
