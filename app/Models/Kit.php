<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kit extends Model
{
    use HasFactory;

    protected $table = 'kits';

    protected $fillable = [
        'proyecto_id',
        'ubicacion_id',
        'codigo_kit',
        'nombre_kit',
        'descripcion',
        'tipo_kit',
        'estado',
    ];

    public function getNombreAttribute(): ?string
    {
        return $this->nombre_kit;
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(ComponenteKit::class, 'kit_id');
    }

    public function articulos(): BelongsToMany
    {
        return $this->belongsToMany(Articulo::class, 'componentes_kit', 'kit_id', 'articulo_id')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function despachoDetalles(): HasMany
    {
        return $this->hasMany(DespachoDetalle::class, 'kit_id');
    }

    /**
     * Evalúa la disponibilidad de este kit en su almacén asignado (ubicacion_id).
     *
     * @return array<string, mixed>
     */
    public function evaluarDisponibilidadEnAlmacen(?int $ubicacionId = null): array
    {
        $targetUbicacionId = $ubicacionId ?? $this->ubicacion_id;
        $componentes = $this->relationLoaded('componentes')
            ? $this->componentes
            : $this->componentes()->with('articulo')->get();

        $totalComponentes = $componentes->count();

        if ($componentes->isEmpty() || ! $targetUbicacionId) {
            return [
                'disponible' => false,
                'completo' => false,
                'kits_armables' => 0,
                'completos' => 0,
                'faltantes' => $totalComponentes,
                'total_componentes' => $totalComponentes,
                'componentes_disponibles' => 0,
                'componentes_faltantes' => $totalComponentes,
                'detalles' => [],
                'detalle_componentes' => [],
            ];
        }

        $articuloIds = $componentes->pluck('articulo_id')->all();

        $stocks = InventarioStock::where('ubicacion_id', $targetUbicacionId)
            ->whereIn('articulo_id', $articuloIds)
            ->pluck('cantidad_actual', 'articulo_id')
            ->all();

        $activosDisponibles = Activo::where('ubicacion_actual_id', $targetUbicacionId)
            ->whereIn('articulo_id', $articuloIds)
            ->where('estado_operativo', 'OPERATIVO')
            ->where('condicion_prestamo', 'DISPONIBLE')
            ->selectRaw('articulo_id, COUNT(*) as total')
            ->groupBy('articulo_id')
            ->pluck('total', 'articulo_id')
            ->all();

        $disponiblesCount = 0;
        $faltantesCount = 0;
        $minKitsArmables = null;
        $detalle = [];
        $detallesLista = [];

        foreach ($componentes as $comp) {
            $artId = (int) $comp->articulo_id;
            $requerido = max(1, (int) round((float) $comp->cantidad));
            $esSeriado = (bool) ($comp->articulo?->control_serie);

            $stockAlmacen = (int) round((float) ($stocks[$artId] ?? 0));
            if ($esSeriado) {
                $stockAlmacen = max($stockAlmacen, (int) ($activosDisponibles[$artId] ?? 0));
            }

            $suficiente = $stockAlmacen >= $requerido;
            if ($suficiente) {
                $disponiblesCount++;
            } else {
                $faltantesCount++;
            }

            $armablesConEsteItem = intdiv($stockAlmacen, $requerido);
            $minKitsArmables = $minKitsArmables === null
                ? $armablesConEsteItem
                : min($minKitsArmables, $armablesConEsteItem);

            $itemInfo = [
                'articulo_id' => $artId,
                'requerido' => $requerido,
                'disponible' => $stockAlmacen,
                'cumple' => $suficiente,
                'suficiente' => $suficiente,
            ];
            $detalle[$artId] = $itemInfo;
            $detallesLista[] = $itemInfo;
        }

        $esCompleto = $faltantesCount === 0 && $disponiblesCount > 0;

        return [
            'disponible' => $esCompleto,
            'completo' => $esCompleto,
            'kits_armables' => $minKitsArmables ?? 0,
            'completos' => $disponiblesCount,
            'faltantes' => $faltantesCount,
            'total_componentes' => $totalComponentes,
            'componentes_disponibles' => $disponiblesCount,
            'componentes_faltantes' => $faltantesCount,
            'detalles' => $detallesLista,
            'detalle_componentes' => $detalle,
        ];
    }
}
