<?php

namespace App\Http\Requests\Cuadrilla;

use App\Models\Personal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCuadrillaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManagePersonal();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('proyecto_id') && session('proyecto_activo_id')) {
            $this->merge([
                'proyecto_id' => session('proyecto_activo_id'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo_cuadrilla' => ['required', 'string', 'max:30', 'unique:cuadrillas,codigo_cuadrilla'],
            'nombre' => ['required', 'string', 'max:120'],
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'lider_personal_id' => ['required', 'exists:personal,id'],
            'regimen_laboral' => ['required', 'string', 'max:30'],
            'estado' => ['required', 'in:ACTIVA,DISUELTA,EN_DESCANSO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $proyectoId = (int) $this->input('proyecto_id');
            $liderId = (int) $this->input('lider_personal_id');

            if ($proyectoId && $liderId) {
                $lider = Personal::with('proyectos')->find($liderId);
                if ($lider && ! $lider->perteneceAlProyecto($proyectoId)) {
                    $validator->errors()->add(
                        'lider_personal_id',
                        'El líder seleccionado no se encuentra asignado al mismo proyecto de la cuadrilla.'
                    );
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'codigo_cuadrilla' => 'código de cuadrilla',
            'nombre' => 'nombre de la cuadrilla',
            'proyecto_id' => 'proyecto asignado',
            'lider_personal_id' => 'líder / responsable',
            'regimen_laboral' => 'régimen laboral',
            'estado' => 'estado operativo',
            'observaciones' => 'observaciones',
        ];
    }
}
