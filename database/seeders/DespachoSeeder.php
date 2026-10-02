<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\DespachoDetalle;
use App\Models\DespachoPrestamo;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\KardexService;
use Illuminate\Database\Seeder;

class DespachoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $almacenCentral = Ubicacion::where('codigo', 'ALM-CEN-01')->first() ?? Ubicacion::first();
        $proyectoMetro = Proyecto::where('codigo', 'PRY-2026-001')->first() ?? Proyecto::first();
        $tecnicoEmpalme = Personal::where('cargo', 'Técnico Empalmador de Fibra Óptica')->first() ?? Personal::first();

        $activoFusionadora = Activo::whereHas('articulo', fn ($q) => $q->where('codigo_sku', 'SKU-FUS-001'))->first();
        $artAlcohol = Articulo::where('codigo_sku', 'SKU-ALC-001')->first();
        $artManguitos = Articulo::where('codigo_sku', 'SKU-MANG-001')->first();

        if (! $admin || ! $almacenCentral || ! $proyectoMetro || ! $tecnicoEmpalme || ! $activoFusionadora || ! $artAlcohol) {
            return;
        }

        $kardexService = app(KardexService::class);

        // Guía 1: Despacho activo en campo con firma digital
        $despacho1 = DespachoPrestamo::firstOrCreate(
            ['numero_guia' => 'DSP-2026-0001'],
            [
                'tipo_movimiento' => 'SALIDA_PRESTAMO_CAMPO',
                'proyecto_id' => $proyectoMetro->id,
                'personal_id' => $tecnicoEmpalme->id,
                'cuadrilla_id' => null,
                'ubicacion_origen_id' => $almacenCentral->id,
                'usuario_registro_id' => $admin->id,
                'fecha_despacho' => now()->subDays(5),
                'fecha_compromiso_retorno' => now()->addDays(9),
                'estado' => 'ENTREGADO_EN_CAMPO',
                // Firma en base64 de ejemplo (trazo curvo simulado en PNG)
                'firma_digital_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
                'observaciones' => 'Despacho de cuadrilla de empalme para tramo Este.',
            ]
        );

        if ($despacho1->wasRecentlyCreated) {
            // Detalle 1: Activo serializado
            DespachoDetalle::create([
                'despacho_id' => $despacho1->id,
                'articulo_id' => $activoFusionadora->articulo_id,
                'activo_id' => $activoFusionadora->id,
                'cantidad' => 1.00,
                'estado_item' => 'ENTREGADO',
            ]);

            $activoFusionadora->update([
                'condicion_prestamo' => 'PRESTADO_CAMPO',
                'responsable_personal_id' => $tecnicoEmpalme->id,
                'proyecto_actual_id' => $proyectoMetro->id,
                'fecha_ultima_asignacion' => now()->subDays(5),
            ]);

            $kardexService->registrarMovimiento(
                articuloId: $activoFusionadora->articulo_id,
                ubicacionId: $almacenCentral->id,
                tipoMovimiento: 'SALIDA_PRESTAMO',
                cantidad: 1.00,
                usuarioId: $admin->id,
                despachoId: $despacho1->id,
                motivo: "Préstamo en campo de fusionadora {$activoFusionadora->codigo_interno}"
            );

            // Detalle 2: Consumible alcohol
            DespachoDetalle::create([
                'despacho_id' => $despacho1->id,
                'articulo_id' => $artAlcohol->id,
                'cantidad' => 2.00,
                'estado_item' => 'ENTREGADO',
            ]);

            $kardexService->registrarMovimiento(
                articuloId: $artAlcohol->id,
                ubicacionId: $almacenCentral->id,
                tipoMovimiento: 'SALIDA_PRESTAMO',
                cantidad: 2.00,
                usuarioId: $admin->id,
                despachoId: $despacho1->id,
                motivo: 'Salida de alcohol isopropílico para limpieza de empalmes'
            );
        }
    }
}
