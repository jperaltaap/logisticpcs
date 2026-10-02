<?php

namespace App\Services;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\Ubicacion;

class InventarioStockService
{
    /**
     * Sincronizar el stock registrado de un artículo serializado en una ubicación.
     */
    public function syncStockForArticuloUbicacion(int $articuloId, ?int $ubicacionId): void
    {
        if (! $ubicacionId) {
            return;
        }

        $articulo = Articulo::find($articuloId);
        if (! $articulo || ! $articulo->control_serie) {
            return;
        }

        $ubicacion = Ubicacion::find($ubicacionId);
        if (! $ubicacion) {
            return;
        }

        // Conteo de activos físicos ubicados actualmente en esta ubicación
        $conteoActivos = Activo::where('articulo_id', $articuloId)
            ->where('ubicacion_actual_id', $ubicacionId)
            ->count();

        $proyectoId = $ubicacion->proyecto_id ?? $articulo->proyecto_id;

        InventarioStock::updateOrCreate(
            [
                'articulo_id' => $articuloId,
                'ubicacion_id' => $ubicacionId,
            ],
            [
                'cantidad_actual' => $conteoActivos,
                'proyecto_id' => $proyectoId,
            ]
        );
    }

    /**
     * Sincronizar existencias de todos los artículos serializados en todas las ubicaciones.
     */
    public function syncAllSerializedStock(): int
    {
        $articulosSerializados = Articulo::where('control_serie', true)->get();
        $totalSincronizados = 0;

        foreach ($articulosSerializados as $articulo) {
            // Ubicaciones con activos de este artículo
            $ubicacionesConActivos = Activo::where('articulo_id', $articulo->id)
                ->whereNotNull('ubicacion_actual_id')
                ->selectRaw('ubicacion_actual_id, count(*) as total')
                ->groupBy('ubicacion_actual_id')
                ->get();

            $ubicacionesProcesadas = [];

            foreach ($ubicacionesConActivos as $item) {
                $ubicacionId = (int) $item->ubicacion_actual_id;
                $ubicacion = Ubicacion::find($ubicacionId);
                $proyectoId = $ubicacion?->proyecto_id ?? $articulo->proyecto_id;

                InventarioStock::updateOrCreate(
                    [
                        'articulo_id' => $articulo->id,
                        'ubicacion_id' => $ubicacionId,
                    ],
                    [
                        'cantidad_actual' => (float) $item->total,
                        'proyecto_id' => $proyectoId,
                    ]
                );

                $ubicacionesProcesadas[] = $ubicacionId;
                $totalSincronizados++;
            }

            // Ubicaciones con registro de stock previo pero sin activos actuales -> actualizar a 0
            InventarioStock::where('articulo_id', $articulo->id)
                ->whereNotIn('ubicacion_id', $ubicacionesProcesadas)
                ->update(['cantidad_actual' => 0.00]);
        }

        return $totalSincronizados;
    }
}
