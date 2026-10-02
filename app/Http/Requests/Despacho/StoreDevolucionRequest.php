<?php

namespace App\Http\Requests\Despacho;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDevolucionRequest extends FormRequest
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
            'ubicacion_destino_id' => ['required', 'exists:ubicaciones,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.detalle_id' => ['required', 'exists:despacho_detalles,id'],
            'items.*.estado_item' => ['required', 'in:DEVUELTO_OPERATIVO,DEVUELTO_DANADO,EXTRAVIADO,CONSUMIDO'],
            'items.*.cantidad_devuelta' => ['required', 'numeric', 'min:0'],
            'items.*.observacion_retorno' => ['nullable', 'string'],
        ];
    }
}
