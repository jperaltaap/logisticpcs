<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DespachoPrestamo extends Model
{
    use HasFactory;

    protected $table = 'despachos_prestamos';

    protected $fillable = [
        'numero_guia',
        'tipo_movimiento',
        'proyecto_id',
        'personal_id',
        'cuadrilla_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'usuario_registro_id',
        'fecha_despacho',
        'fecha_compromiso_retorno',
        'estado',
        'firma_digital_base64',
        'foto_acta_respaldo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_despacho' => 'datetime',
            'fecha_compromiso_retorno' => 'date',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class, 'cuadrilla_id');
    }

    public function ubicacionOrigen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }

    public function ubicacionDestino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DespachoDetalle::class, 'despacho_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'despacho_id');
    }
}
