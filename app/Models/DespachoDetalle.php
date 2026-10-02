<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoDetalle extends Model
{
    use HasFactory;

    protected $table = 'despacho_detalles';

    protected $fillable = [
        'despacho_id',
        'articulo_id',
        'activo_id',
        'kit_id',
        'cantidad',
        'estado_item',
        'fecha_devolucion',
        'usuario_recepcion_retorno_id',
        'observacion_retorno',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_devolucion' => 'datetime',
        ];
    }

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(DespachoPrestamo::class, 'despacho_id');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class, 'kit_id');
    }

    public function usuarioRecepcionRetorno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_recepcion_retorno_id');
    }
}
