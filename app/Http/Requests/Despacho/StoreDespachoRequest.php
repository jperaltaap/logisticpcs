<?php

namespace App\Http\Requests\Despacho;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDespachoRequest extends FormRequest
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
            'numero_guia' => ['required', 'string', 'max:30', 'unique:despachos_prestamos,numero_guia'],
            'tipo_movimiento' => ['required', 'in:SALIDA_PRESTAMO_CAMPO,DEVOLUCION_CAMPO,CONSUMO_DIRECTO,TRANSFERENCIA_UBICACION,AJUSTE_INVENTARIO'],
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'personal_id' => ['nullable', 'exists:personal,id'],
            'ubicacion_origen_id' => ['required', 'exists:ubicaciones,id'],
            'ubicacion_destino_id' => ['nullable', 'exists:ubicaciones,id'],
            'fecha_despacho' => ['nullable', 'date'],
            'fecha_compromiso_retorno' => ['nullable', 'date'],
            'firma_digital_base64' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.articulo_id' => ['required', 'exists:articulos,id'],
            'detalles.*.activo_id' => ['nullable', 'exists:activos,id'],
            'detalles.*.kit_id' => ['nullable', 'exists:kits,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:1'],
        ];
    }
}
