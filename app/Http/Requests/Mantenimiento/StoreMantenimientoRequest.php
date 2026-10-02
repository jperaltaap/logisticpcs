<?php

namespace App\Http\Requests\Mantenimiento;

use Illuminate\Foundation\Http\FormRequest;

class StoreMantenimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activo_id' => ['required', 'exists:activos,id'],
            'tipo' => ['required', 'in:PREVENTIVO,CORRECTIVO,CALIBRACION_LAB,CERTIFICACION'],
            'fecha_ingreso' => ['required', 'date'],
            'fecha_salida' => ['nullable', 'date', 'after_or_equal:fecha_ingreso'],
            'proxima_calibracion_sugerida' => ['nullable', 'date'],
            'proveedor_taller' => ['nullable', 'string', 'max:150'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'resultado' => ['required', 'in:CONFORME_OPERATIVO,NO_CONFORME_BAJA,EN_PROCESO'],
            'descripcion_falla_o_trabajo' => ['required', 'string'],
            'certificado_calibracion_pdf' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'activo_id.required' => 'Debe seleccionar un activo válido.',
            'tipo.required' => 'El tipo de servicio o calibración es obligatorio.',
            'fecha_ingreso.required' => 'La fecha de ingreso a taller/laboratorio es requerida.',
            'descripcion_falla_o_trabajo.required' => 'La descripción del motivo o falla es obligatoria.',
        ];
    }
}
