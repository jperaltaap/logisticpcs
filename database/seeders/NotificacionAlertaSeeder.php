<?php

namespace Database\Seeders;

use App\Models\NotificacionAlerta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificacionAlertaSeeder extends Seeder
{
    public function run(): void
    {
        $alertas = [
            [
                'tipo' => 'STOCK_MINIMO',
                'titulo' => 'Alerta de Stock Crítico: Cintas Aislantes 3M',
                'mensaje' => 'El stock de cintas aislantes de alta tensión se encuentra en 3 unidades (mínimo permitido: 10).',
                'referencia_id' => 1,
                'leida' => false,
                'fecha_alerta' => Carbon::now()->subHours(4),
            ],
            [
                'tipo' => 'CALIBRACION_POR_VENCER',
                'titulo' => 'Calibración por Vencer: Multímetro Digital Fluke',
                'mensaje' => 'El equipo Fluke 87V con serie SN-FLK-001 requiere calibración de laboratorio en los próximos 10 días.',
                'referencia_id' => 1,
                'leida' => false,
                'fecha_alerta' => Carbon::now()->subHours(12),
            ],
            [
                'tipo' => 'PRESTAMO_VENCIDO',
                'titulo' => 'Herramienta de Cuadrilla pendiente de devolución',
                'mensaje' => 'Despacho #0012 registra kits de crimpado con fecha límite de retorno superada por más de 48 horas.',
                'referencia_id' => 1,
                'leida' => true,
                'fecha_alerta' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($alertas as $alerta) {
            NotificacionAlerta::firstOrCreate(
                ['titulo' => $alerta['titulo']],
                $alerta
            );
        }
    }
}
