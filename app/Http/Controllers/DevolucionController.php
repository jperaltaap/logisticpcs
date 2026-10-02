<?php

namespace App\Http\Controllers;

use App\Http\Requests\Despacho\StoreDevolucionRequest;
use App\Models\Activo;
use App\Models\DespachoDetalle;
use App\Models\DespachoPrestamo;
use App\Models\Ubicacion;
use App\Services\KardexService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DevolucionController extends Controller
{
    public function __construct(
        protected KardexService $kardexService
    ) {}

    /**
     * Show the form for returning items from a dispatch.
     */
    public function create(DespachoPrestamo $despacho): View
    {
        $despacho->load([
            'proyecto',
            'personal',
            'cuadrilla',
            'ubicacionOrigen',
            'detalles' => function ($q) {
                $q->where('estado_item', 'ENTREGADO')
                    ->with(['articulo', 'activo', 'kit']);
            },
        ]);

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($despacho->proyecto_id, function ($q) use ($despacho) {
                $q->where(function ($sub) use ($despacho) {
                    $sub->where('proyecto_id', $despacho->proyecto_id)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
            ->orderBy('nombre')
            ->get();

        return view('despachos.devolucion', compact('despacho', 'ubicaciones'));
    }

    /**
     * Store returned items in warehouse and update kardex/assets.
     */
    public function store(StoreDevolucionRequest $request, DespachoPrestamo $despacho): RedirectResponse
    {
        $data = $request->validated();
        $ubicacionDestinoId = (int) $data['ubicacion_destino_id'];
        $items = $data['items'];

        try {
            DB::transaction(function () use ($despacho, $ubicacionDestinoId, $items) {
                foreach ($items as $itemData) {
                    $detalle = DespachoDetalle::where('despacho_id', $despacho->id)
                        ->where('id', $itemData['detalle_id'])
                        ->firstOrFail();

                    $estadoItem = $itemData['estado_item'];
                    $cantidadDevuelta = (float) $itemData['cantidad_devuelta'];
                    $observacion = $itemData['observacion_retorno'] ?? null;

                    // Actualizar el detalle
                    $detalle->update([
                        'estado_item' => $estadoItem,
                        'fecha_devolucion' => now(),
                        'usuario_recepcion_retorno_id' => auth()->id(),
                        'observacion_retorno' => $observacion,
                    ]);

                    // Si tenía un activo serializado asociado
                    if ($detalle->activo_id) {
                        $activo = Activo::findOrFail($detalle->activo_id);

                        $esInstalado = ($estadoItem === 'CONSUMIDO');

                        $condicionPrestamo = match ($estadoItem) {
                            'CONSUMIDO' => 'INSTALADO_PROYECTO',
                            'EXTRAVIADO' => 'EXTRAVIADO',
                            default => 'DISPONIBLE',
                        };

                        $estadoOperativo = match ($estadoItem) {
                            'DEVUELTO_DANADO' => 'DANADO',
                            'EXTRAVIADO' => $activo->estado_operativo,
                            default => 'OPERATIVO',
                        };

                        $activo->update([
                            'condicion_prestamo' => $condicionPrestamo,
                            'estado_operativo' => $estadoOperativo,
                            'ubicacion_actual_id' => $esInstalado ? $activo->ubicacion_actual_id : $ubicacionDestinoId,
                            'responsable_personal_id' => null,
                            'cuadrilla_actual_id' => null,
                            'proyecto_actual_id' => $esInstalado ? ($despacho->proyecto_id ?? $activo->proyecto_actual_id) : null,
                            'fecha_ultimo_retorno' => now(),
                            'observaciones' => $observacion ? ($activo->observaciones.' | '.($esInstalado ? 'Instalado en obra: ' : 'Retorno: ').$observacion) : $activo->observaciones,
                        ]);
                    }

                    // Si reingresa a stock de almacén (no serializado o material fungible no consumido)
                    if ($cantidadDevuelta > 0 && in_array($estadoItem, ['DEVUELTO_OPERATIVO', 'DEVUELTO_DANADO'], true)) {
                        $this->kardexService->registrarMovimiento(
                            articuloId: $detalle->articulo_id,
                            ubicacionId: $ubicacionDestinoId,
                            tipoMovimiento: 'RETORNO_PRESTAMO',
                            cantidad: $cantidadDevuelta,
                            usuarioId: auth()->id(),
                            despachoId: $despacho->id,
                            motivo: "Retorno de campo por Guía {$despacho->numero_guia} ({$estadoItem})"
                        );
                    }
                }

                // Evaluar si quedan ítems pendientes de retorno en este despacho
                $pendientes = $despacho->detalles()->where('estado_item', 'ENTREGADO')->count();
                $nuevoEstado = ($pendientes === 0) ? 'DEVUELTO_TOTAL' : 'PARCIALMENTE_DEVUELTO';

                $despacho->update([
                    'estado' => $nuevoEstado,
                    'ubicacion_destino_id' => $ubicacionDestinoId,
                ]);
            });

            return redirect()->route('despachos.show', $despacho)
                ->with('success', 'Devolución registrada exitosamente. Se actualizaron los saldos de inventario y condición de los activos.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar la devolución: '.$e->getMessage());
        }
    }
}
