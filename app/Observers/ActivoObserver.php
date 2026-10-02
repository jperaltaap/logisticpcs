<?php

namespace App\Observers;

use App\Models\Activo;
use App\Services\InventarioStockService;

class ActivoObserver
{
    public function __construct(
        protected InventarioStockService $stockService
    ) {}

    /**
     * Handle the Activo "created" event.
     */
    public function created(Activo $activo): void
    {
        if ($activo->ubicacion_actual_id) {
            $this->stockService->syncStockForArticuloUbicacion(
                $activo->articulo_id,
                $activo->ubicacion_actual_id
            );
        }
    }

    /**
     * Handle the Activo "updated" event.
     */
    public function updated(Activo $activo): void
    {
        // Si cambió la ubicación física, sincronizar ambas ubicaciones (la anterior y la nueva)
        if ($activo->wasChanged('ubicacion_actual_id')) {
            $oldUbicacionId = $activo->getOriginal('ubicacion_actual_id');
            if ($oldUbicacionId) {
                $this->stockService->syncStockForArticuloUbicacion(
                    $activo->articulo_id,
                    (int) $oldUbicacionId
                );
            }
        }

        // Sincronizar ubicación actual
        if ($activo->ubicacion_actual_id) {
            $this->stockService->syncStockForArticuloUbicacion(
                $activo->articulo_id,
                $activo->ubicacion_actual_id
            );
        }
    }

    /**
     * Handle the Activo "deleted" event.
     */
    public function deleted(Activo $activo): void
    {
        if ($activo->ubicacion_actual_id) {
            $this->stockService->syncStockForArticuloUbicacion(
                $activo->articulo_id,
                $activo->ubicacion_actual_id
            );
        }
    }
}
