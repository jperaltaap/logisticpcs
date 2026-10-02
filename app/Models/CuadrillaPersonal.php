<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuadrillaPersonal extends Model
{
    use HasFactory;

    protected $table = 'cuadrilla_personal';

    protected $fillable = [
        'cuadrilla_id',
        'personal_id',
        'rol_en_cuadrilla',
        'fecha_incorporacion',
        'fecha_retiro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_incorporacion' => 'date',
            'fecha_retiro' => 'date',
        ];
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class, 'cuadrilla_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }
}
