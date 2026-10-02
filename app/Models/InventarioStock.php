<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioStock extends Model
{
    use HasFactory;

    protected $table = 'inventario_stock';

    protected $fillable = [
        'articulo_id',
        'ubicacion_id',
        'proyecto_id',
        'cantidad_actual',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_actual' => 'decimal:2',
        ];
    }

    public function getCantidadAttribute(): float
    {
        return (float) ($this->cantidad_actual ?? 0);
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }
}
