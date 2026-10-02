<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmpresaConfig extends Model
{
    protected $table = 'empresa_config';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'ruc',
        'direccion',
        'ciudad',
        'pais',
        'telefono',
        'email',
        'sitio_web',
        'representante_legal',
        'cargo_representante',
        'logotipo_path',
        'icono_path',
        'moneda',
        'zona_horaria',
        'sistema_nombre',
        'sistema_subtitulo',
    ];

    /**
     * Obtiene la configuración de empresa (singleton).
     * Crea un registro vacío si no existe.
     */
    public static function instancia(): self
    {
        return static::firstOrCreate([], [
            'razon_social' => 'Pendiente de Configuración',
            'sistema_nombre' => 'LogisticPCS',
            'zona_horaria' => 'America/Lima',
            'moneda' => 'PEN (S/)',
        ]);
    }

    /**
     * URL pública del logotipo para la web
     */
    public function getLogotipoUrlAttribute(): ?string
    {
        if (! $this->logotipo_path) {
            return null;
        }

        return asset('storage/'.$this->logotipo_path);
    }

    /**
     * URL pública del ícono principal del sistema (configurable, fallback a storage/app/public/sistema/icono.webp)
     */
    public function getIconoUrlAttribute(): string
    {
        if ($this->icono_path && Storage::disk('public')->exists($this->icono_path)) {
            return asset('storage/'.$this->icono_path);
        }

        return asset('storage/sistema/icono.webp');
    }

    /**
     * Data URI Base64 del logotipo para reportes PDF de DomPDF
     */
    public function getLogotipoBase64(): ?string
    {
        if (! $this->logotipo_path) {
            return null;
        }

        $fullPath = storage_path('app/public/'.$this->logotipo_path);
        if (! file_exists($fullPath)) {
            $fullPath = public_path('storage/'.$this->logotipo_path);
            if (! file_exists($fullPath)) {
                return null;
            }
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        $data = base64_encode(file_get_contents($fullPath));

        return 'data:'.$mime.';base64,'.$data;
    }
}
