<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\MantenimientoCalibracion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MantenimientoCalibracionSeeder extends Seeder
{
    public function run(): void
    {
        $activos = Activo::take(3)->get();
        $user = User::first();

        if ($activos->count() > 0 && $user) {
            MantenimientoCalibracion::firstOrCreate(
                [
                    'activo_id' => $activos[0]->id,
                    'tipo' => 'CALIBRACION_LAB',
                    'certificado_calibracion_pdf' => 'CERT-LAB-2026-0041.pdf',
                ],
                [
                    'fecha_ingreso' => Carbon::now()->subMonths(2),
                    'fecha_salida' => Carbon::now()->subMonths(2)->addDays(2),
                    'proveedor_taller' => 'Laboratorio Metrológico Certificado del Perú',
                    'costo' => 380.00,
                    'resultado' => 'CONFORME_OPERATIVO',
                    'descripcion_falla_o_trabajo' => 'Calibración anual trazable INACAL con ajuste de curvas y tolerancias.',
                    'proxima_calibracion_sugerida' => Carbon::now()->addDays(10),
                    'user_id' => $user->id,
                ]
            );
        }

        if ($activos->count() > 1 && $user) {
            MantenimientoCalibracion::firstOrCreate(
                [
                    'activo_id' => $activos[1]->id,
                    'tipo' => 'PREVENTIVO',
                ],
                [
                    'fecha_ingreso' => Carbon::now()->subDays(5),
                    'fecha_salida' => null,
                    'proveedor_taller' => 'Taller Especializado Central',
                    'costo' => 150.00,
                    'resultado' => 'EN_PROCESO',
                    'descripcion_falla_o_trabajo' => 'Revisión periódica de componentes mecánicos, cambio de grasa y verificación de torque.',
                    'proxima_calibracion_sugerida' => null,
                    'user_id' => $user->id,
                ]
            );
        }
    }
}
