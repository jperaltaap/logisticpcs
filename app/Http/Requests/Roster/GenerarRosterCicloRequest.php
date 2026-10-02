<?php

namespace App\Http\Requests\Roster;

use App\Models\Personal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerarRosterCicloRequest extends FormRequest
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
        $personalIds = $this->input('personal_ids');
        if (empty($personalIds) && $this->filled('personal_id')) {
            $personalIds = [(int) $this->input('personal_id')];
            $this->merge([
                'personal_ids' => $personalIds,
            ]);
        }

        if (! $this->filled('proyecto_id')) {
            $proyActivo = session('proyecto_activo_id');
            if (! $proyActivo && ! empty($personalIds)) {
                $primerPersonal = Personal::find((int) (is_array($personalIds) ? reset($personalIds) : $personalIds));
                $proyActivo = $primerPersonal?->proyecto_id;
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
            'personal_id' => ['nullable', 'exists:personal,id'],
            'personal_ids' => ['required', 'array', 'min:1'],
            'personal_ids.*' => ['required', 'exists:personal,id'],
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'grupo_guardia' => ['required', 'string', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'dias_trabajo' => ['required', 'integer', 'min:1', 'max:30'],
            'dias_descanso' => ['required', 'integer', 'min:1', 'max:15'],
            'ciclos' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $proyectoId = (int) $this->input('proyecto_id');
            $personalIds = (array) $this->input('personal_ids', []);

            if ($proyectoId && ! empty($personalIds)) {
                $trabajadores = Personal::with('proyectos')->whereIn('id', $personalIds)->get();
                foreach ($trabajadores as $trabajador) {
                    if (! $trabajador->perteneceAlProyecto($proyectoId)) {
                        $validator->errors()->add(
                            'personal_id',
                            "El trabajador {$trabajador->nombre_completo} no se encuentra asignado al proyecto seleccionado."
                        );
                        break;
                    }
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
            'personal_id' => 'trabajador a programar',
            'personal_ids' => 'personal a programar',
            'proyecto_id' => 'proyecto asignado',
            'grupo_guardia' => 'grupo de guardia',
            'fecha_inicio' => 'fecha de inicio del ciclo',
            'dias_trabajo' => 'días de trabajo en campo',
            'dias_descanso' => 'días de descanso / bajada',
            'ciclos' => 'número de ciclos a proyectar',
        ];
    }
}
