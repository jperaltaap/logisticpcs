<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoCalibracion extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos_calibraciones';

    protected $fillable = [
        'activo_id',
        'proyecto_id',
        'tipo',
        'proveedor_taller',
        'fecha_ingreso',
        'fecha_salida',
        'proxima_calibracion_sugerida',
        'costo',
        'certificado_calibracion_pdf',
        'descripcion_falla_o_trabajo',
        'resultado',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_salida' => 'date',
            'proxima_calibracion_sugerida' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
