<?php

namespace App\Http\Requests\Cuadrilla;

use App\Models\Personal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCuadrillaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManagePersonal();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cuadrillaId = $this->route('cuadrilla')?->id ?? $this->input('id');

        return [
            'codigo_cuadrilla' => [
                'required',
                'string',
                'max:30',
                Rule::unique('cuadrillas', 'codigo_cuadrilla')->ignore($cuadrillaId),
            ],
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

            $cuadrilla = $this->route('cuadrilla');
            if ($cuadrilla && $proyectoId && (int) $cuadrilla->proyecto_id !== $proyectoId) {
                $miembrosActivos = $cuadrilla->miembros()->with('proyectos')->get();
                foreach ($miembrosActivos as $miembro) {
                    if (! $miembro->perteneceAlProyecto($proyectoId)) {
                        $validator->errors()->add(
                            'proyecto_id',
                            "No se puede cambiar el proyecto de la cuadrilla porque el miembro activo {$miembro->nombre_completo} no pertenece al proyecto seleccionado."
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
