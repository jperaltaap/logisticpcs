<?php

namespace App\Http\Requests\Ingreso;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIngresoRequest extends FormRequest
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
            'tipo_ingreso' => ['required', 'in:COMPRA_NUEVA,AJUSTE_SOBRANTE,TRANSFERENCIA_INGRESO,DONACION_TRASPASO'],
            'ubicacion_id' => ['required', 'exists:ubicaciones,id'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'proveedor' => ['nullable', 'string', 'max:150'],
            'numero_comprobante' => ['nullable', 'string', 'max:100'],
            'fecha_ingreso' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.articulo_id' => ['required', 'exists:articulos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:1'],
            'items.*.observaciones' => ['nullable', 'string', 'max:255'],
            'items.*.series' => ['nullable', 'array'],
            'items.*.series.*' => ['nullable', 'string', 'max:100'],
        ];
    }
}
