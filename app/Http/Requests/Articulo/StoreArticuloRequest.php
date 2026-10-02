<?php

namespace App\Http\Requests\Articulo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticuloRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $controlSerie = $this->boolean('control_serie');
        $esConsumibleSeriado = $controlSerie && ($this->boolean('es_instalable') || $this->input('tipo_articulo') === 'CONSUMIBLE');
        $esActivoSeriado = $controlSerie && ! $esConsumibleSeriado;

        return [
            'categoria_id' => ['required', 'exists:categorias,id'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'codigo_sku' => ['required', 'string', 'max:50', 'unique:articulos,codigo_sku'],
            'descripcion' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'unidad_medida' => ['required', 'string', 'max:20'],
            'tipo_articulo' => ['required', 'in:EPP,HERRAMIENTA,EQUIPO,MATERIAL,CONSUMIBLE'],
            'control_serie' => ['boolean'],
            'es_instalable' => ['boolean'],
            'stock_minimo' => $esActivoSeriado
                ? ['nullable', 'numeric', 'min:0']
                : ['required', 'numeric', 'min:0'],
            'vida_util_meses' => $controlSerie
                ? ['required', 'integer', 'min:1']
                : ['nullable', 'integer', 'min:1'],
            'foto_referencia' => ['nullable', 'image', 'max:2048'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vida_util_meses.required' => 'El tiempo de vida útil (en meses) es obligatorio para artículos serializados.',
            'stock_minimo.required' => 'El límite de stock mínimo es obligatorio para artículos consumibles o a granel.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $controlSerie = $this->boolean('control_serie');
        $esInstalable = $this->boolean('es_instalable') || ($controlSerie && $this->input('tipo_articulo') === 'CONSUMIBLE');
        $esActivoSeriado = $controlSerie && ! $esInstalable;

        $merge = [
            'control_serie' => $controlSerie,
            'es_instalable' => $controlSerie ? $esInstalable : false,
        ];

        if ($esActivoSeriado) {
            $merge['stock_minimo'] = 0;
        }

        $this->merge($merge);
    }
}
