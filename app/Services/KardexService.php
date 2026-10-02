<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\KardexMovimiento;
use App\Models\Ubicacion;
use Exception;
use Illuminate\Support\Facades\DB;

class KardexService
{
    /**
     * Registrar un movimiento de salida o entrada en el kardex y actualizar stock de almacén.
     *
     * @throws Exception
     */
    public function registrarMovimiento(
        int $articuloId,
        int $ubicacionId,
        string $tipoMovimiento,
        float $cantidad,
        int $usuarioId,
        ?int $despachoId = null,
        ?string $motivo = null,
        ?int $ingresoId = null,
        ?int $proyectoId = null
    ): KardexMovimiento {
        return DB::transaction(function () use (
            $articuloId,
            $ubicacionId,
            $tipoMovimiento,
            $cantidad,
            $usuarioId,
            $despachoId,
            $motivo,
            $ingresoId,
            $proyectoId
        ) {
            $articulo = Articulo::findOrFail($articuloId);
            $ubicacion = Ubicacion::findOrFail($ubicacionId);
            $effectiveProyectoId = $proyectoId ?? $ubicacion->proyecto_id ?? session('proyecto_activo_id');

            // Obtener o inicializar stock en la ubicación
            $stockRegistro = InventarioStock::firstOrCreate(
                [
                    'articulo_id' => $articuloId,
                    'ubicacion_id' => $ubicacionId,
                ],
                [
                    'proyecto_id' => $effectiveProyectoId,
                    'cantidad_actual' => 0.00,
                ]
            );

            if ($effectiveProyectoId && ! $stockRegistro->proyecto_id) {
                $stockRegistro->update(['proyecto_id' => $effectiveProyectoId]);
            }

            $stockAnterior = (float) $stockRegistro->cantidad_actual;

            // Determinar si suma o resta
            $esSalida = in_array($tipoMovimiento, [
                'SALIDA_CONSUMO',
                'SALIDA_PRESTAMO',
                'TRANSFERENCIA_SALIDA',
                'AJUSTE_FALTANTE',
            ], true);

            if ($esSalida) {
                // Para artículos fungibles validar saldo suficiente
                if (! $articulo->control_serie && $stockAnterior < $cantidad) {
                    throw new Exception("Stock insuficiente para el artículo {$articulo->codigo_sku} en {$ubicacion->nombre}. Disponible: {$stockAnterior}, Solicitado: {$cantidad}");
                }
                $stockPosterior = max(0.00, $stockAnterior - $cantidad);
            } else {
                $stockPosterior = $stockAnterior + $cantidad;
            }

            // Actualizar stock en almacén físico
            $stockRegistro->update([
                'cantidad_actual' => $stockPosterior,
            ]);

            // Registrar movimiento inmutable en kardex
            return KardexMovimiento::create([
                'articulo_id' => $articuloId,
                'ubicacion_id' => $ubicacionId,
                'despacho_id' => $despachoId,
                'ingreso_id' => $ingresoId,
                'proyecto_id' => $effectiveProyectoId,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockPosterior,
                'usuario_id' => $usuarioId,
                'fecha_movimiento' => now(),
                'motivo' => $motivo ?? "Movimiento {$tipoMovimiento} registrado.",
            ]);
        });
    }
}
