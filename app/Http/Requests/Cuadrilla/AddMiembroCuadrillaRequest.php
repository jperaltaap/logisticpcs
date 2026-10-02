<?php

namespace App\Http\Requests\Cuadrilla;

use App\Models\Personal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddMiembroCuadrillaRequest extends FormRequest
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
        return [
            'personal_id' => ['required', 'exists:personal,id'],
            'rol_en_cuadrilla' => ['required', 'string', 'max:80'],
            'fecha_incorporacion' => ['required', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cuadrilla = $this->route('cuadrilla');
            $personalId = (int) $this->input('personal_id');

            if ($cuadrilla && $cuadrilla->proyecto_id && $personalId) {
                $personal = Personal::with('proyectos')->find($personalId);
                if ($personal && ! $personal->perteneceAlProyecto((int) $cuadrilla->proyecto_id)) {
                    $validator->errors()->add(
                        'personal_id',
                        'El trabajador seleccionado no se encuentra asignado al mismo proyecto de la cuadrilla.'
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
            'personal_id' => 'trabajador / técnico',
            'rol_en_cuadrilla' => 'rol dentro de la cuadrilla',
            'fecha_incorporacion' => 'fecha de incorporación',
        ];
    }
}
