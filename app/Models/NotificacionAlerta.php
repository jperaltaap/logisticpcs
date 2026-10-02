<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionAlerta extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_alertas';

    protected $fillable = [
        'proyecto_id',
        'tipo',
        'titulo',
        'mensaje',
        'referencia_id',
        'leida',
        'fecha_alerta',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha_alerta' => 'datetime',
            'referencia_id' => 'integer',
        ];
    }
}
