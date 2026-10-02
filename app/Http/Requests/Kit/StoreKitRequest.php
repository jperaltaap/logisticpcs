<?php

namespace App\Http\Requests\Kit;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\Ubicacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKitRequest extends FormRequest
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
        return [
            'codigo_kit' => ['required', 'string', 'max:30', 'unique:kits,codigo_kit'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'ubicacion_id' => ['nullable', 'exists:ubicaciones,id'],
            'nombre_kit' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo_kit' => ['required', 'in:KIT_HERRAMIENTAS,KIT_EPP,KIT_EMPALME,KIT_MATERIALES'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'componentes' => ['nullable', 'array'],
            'componentes.*.articulo_id' => ['required', 'exists:articulos,id'],
            'componentes.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $ubicacionId = (int) $this->input('ubicacion_id');
            $componentes = $this->input('componentes', []);

            if (! $ubicacionId || empty($componentes) || ! is_array($componentes)) {
                return;
            }

            $ubicacion = Ubicacion::find($ubicacionId);
            if (! $ubicacion) {
                return;
            }

            foreach ($componentes as $idx => $comp) {
                $articuloId = (int) ($comp['articulo_id'] ?? 0);
                if (! $articuloId) {
                    continue;
                }

                $tieneStock = InventarioStock::where('ubicacion_id', $ubicacionId)
                    ->where('articulo_id', $articuloId)
                    ->where('cantidad_actual', '>', 0)
                    ->exists();

                $tieneActivo = Activo::where('ubicacion_actual_id', $ubicacionId)
                    ->where('articulo_id', $articuloId)
                    ->where('estado_operativo', 'OPERATIVO')
                    ->where('condicion_prestamo', 'DISPONIBLE')
                    ->exists();

                if (! $tieneStock && ! $tieneActivo) {
                    $articulo = Articulo::find($articuloId);
                    $nombreArt = $articulo ? "{$articulo->codigo_sku} - {$articulo->descripcion}" : "ID #{$articuloId}";
                    $msg = "El ítem «{$nombreArt}» no cuenta con unidades disponibles en el almacén «{$ubicacion->nombre}». Los kits solo pueden crearse con ítems que se encuentren en el mismo almacén.";
                    $validator->errors()->add("componentes.{$idx}.articulo_id", $msg);
                    $validator->errors()->add('componentes', $msg);
                }
            }
        });
    }
}
