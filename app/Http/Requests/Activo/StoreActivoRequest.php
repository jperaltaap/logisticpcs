<?php

namespace App\Http\Requests\Activo;

use App\Models\Ubicacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivoRequest extends FormRequest
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
            'articulo_id' => ['required', 'exists:articulos,id'],
            'codigo_interno' => ['required', 'string', 'max:50', 'unique:activos,codigo_interno'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'proyecto_actual_id' => ['required', 'exists:proyectos,id'],
            'ubicacion_actual_id' => [
                'required',
                'exists:ubicaciones,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $proyectoId = (int) $this->input('proyecto_actual_id');
                    $ubicacion = Ubicacion::find($value);
                    if ($ubicacion && $ubicacion->proyecto_id && $proyectoId && (int) $ubicacion->proyecto_id !== $proyectoId) {
                        $fail("El centro de almacén '{$ubicacion->nombre}' pertenece a otro proyecto y no corresponde al proyecto seleccionado.");
                    }
                },
            ],
            'responsable_personal_id' => ['nullable'],
            'cuadrilla_actual_id' => ['nullable'],
            'estado_operativo' => ['required', 'in:OPERATIVO,EN_MANTENIMIENTO,DANADO,DE_BAJA'],
            'condicion_prestamo' => ['nullable', 'string', 'in:DISPONIBLE,PRESTADO_CAMPO,ASIGNADO_CUADRILLA,EN_REPARACION,BAJA'],
            'fecha_ingreso' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proyecto_actual_id.required' => 'Debe seleccionar el proyecto al que pertenece la unidad serializada.',
            'ubicacion_actual_id.required' => 'Debe seleccionar el centro de almacén al que pertenece la unidad serializada.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'responsable_personal_id' => null,
            'cuadrilla_actual_id' => null,
            'condicion_prestamo' => 'DISPONIBLE',
        ];

        if (! $this->filled('proyecto_actual_id') && session('proyecto_activo_id')) {
            $merge['proyecto_actual_id'] = session('proyecto_activo_id');
        }

        $this->merge($merge);
    }
}
