<?php

namespace App\Http\Requests\Roster;

use App\Models\Personal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRosterTurnoRequest extends FormRequest
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
        if (! $this->filled('proyecto_id')) {
            $proyActivo = session('proyecto_activo_id');
            if (! $proyActivo && $this->filled('personal_id')) {
                $personal = Personal::find((int) $this->input('personal_id'));
                $proyActivo = $personal?->proyecto_id;
            }
            if ($proyActivo) {
                $this->merge([
                    'proyecto_id' => (int) $proyActivo,
                ]);
            }
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
            'personal_id' => ['required', 'exists:personal,id'],
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'grupo_guardia' => ['required', 'string', 'max:30'],
            'fecha' => ['required', 'date'],
            'condicion_laboral' => ['required', 'in:TRABAJO_CAMPO,DESCANSO_CAMPAMENTO,BAJADA_DESCANSO,PERMISO,LICENCIA_MEDICA'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $proyectoId = (int) $this->input('proyecto_id');
            $personalId = (int) $this->input('personal_id');

            if ($proyectoId && $personalId) {
                $personal = Personal::with('proyectos')->find($personalId);
                if ($personal && ! $personal->perteneceAlProyecto($proyectoId)) {
                    $validator->errors()->add(
                        'personal_id',
                        'El trabajador seleccionado no pertenece al proyecto indicado.'
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
            'personal_id' => 'trabajador',
            'proyecto_id' => 'proyecto',
            'grupo_guardia' => 'grupo de guardia',
            'fecha' => 'fecha de turno',
            'condicion_laboral' => 'condición laboral',
            'observaciones' => 'observaciones',
        ];
    }
}
