<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexMovimiento extends Model
{
    use HasFactory;

    protected $table = 'kardex_movimientos';

    protected $fillable = [
        'articulo_id',
        'ubicacion_id',
        'despacho_id',
        'ingreso_id',
        'proyecto_id',
        'tipo_movimiento',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'usuario_id',
        'fecha_movimiento',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'stock_anterior' => 'decimal:2',
            'stock_posterior' => 'decimal:2',
            'fecha_movimiento' => 'datetime',
        ];
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

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(DespachoPrestamo::class, 'despacho_id');
    }

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
