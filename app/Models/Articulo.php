<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Articulo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'articulos';

    protected $fillable = [
        'categoria_id',
        'proyecto_id',
        'codigo_sku',
        'descripcion',
        'marca',
        'modelo',
        'unidad_medida',
        'tipo_articulo',
        'control_serie',
        'es_instalable',
        'stock_minimo',
        'vida_util_meses',
        'foto_referencia',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'control_serie' => 'boolean',
            'es_instalable' => 'boolean',
            'stock_minimo' => 'decimal:2',
            'vida_util_meses' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function getNombreAttribute(): string
    {
        return $this->descripcion ?? '';
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto_referencia)) {
            return null;
        }

        if (str_starts_with($this->foto_referencia, 'http://') || str_starts_with($this->foto_referencia, 'https://')) {
            return $this->foto_referencia;
        }

        return asset('storage/'.$this->foto_referencia);
    }

    public function getFotoBase64(): ?string
    {
        if (empty($this->foto_referencia)) {
            return null;
        }

        $fullPath = storage_path('app/public/'.$this->foto_referencia);
        if (! file_exists($fullPath)) {
            $fullPath = public_path('storage/'.$this->foto_referencia);
            if (! file_exists($fullPath)) {
                return null;
            }
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        $data = base64_encode(file_get_contents($fullPath));

        return 'data:'.$mime.';base64,'.$data;
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(Activo::class, 'articulo_id');
    }

    public function componentesKit(): HasMany
    {
        return $this->hasMany(ComponenteKit::class, 'articulo_id');
    }

    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(Kit::class, 'componentes_kit', 'articulo_id', 'kit_id')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function inventarioStocks(): HasMany
    {
        return $this->hasMany(InventarioStock::class, 'articulo_id');
    }

    public function despachoDetalles(): HasMany
    {
        return $this->hasMany(DespachoDetalle::class, 'articulo_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'articulo_id');
    }

    public function inspeccionesEpp(): HasMany
    {
        return $this->hasMany(InspeccionEpp::class, 'articulo_id');
    }

    public function ingresoDetalles(): HasMany
    {
        return $this->hasMany(IngresoDetalle::class, 'articulo_id');
    }

    /**
     * Determina si el artículo es serializado y se gestiona como Activo Fijo / Retornable (no consumible).
     */
    public function esSerializadoActivo(): bool
    {
        return (bool) $this->control_serie && ! $this->es_instalable && $this->tipo_articulo !== 'CONSUMIBLE';
    }

    /**
     * Determina si el artículo es serializado y se gestiona como Consumible / Instalable en obra.
     */
    public function esSerializadoConsumible(): bool
    {
        return (bool) $this->control_serie && ($this->es_instalable || $this->tipo_articulo === 'CONSUMIBLE');
    }

    /**
     * Determina si el artículo debe considerar límite mínimo de stock.
     * Si es serializado y como activo, NO considera límite mínimo de stock.
     */
    public function aplicaStockMinimo(): bool
    {
        return ! $this->esSerializadoActivo();
    }
}
