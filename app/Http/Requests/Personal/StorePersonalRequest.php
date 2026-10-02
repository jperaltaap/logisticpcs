<?php

namespace App\Http\Requests\Personal;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonalRequest extends FormRequest
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
            'codigo_trabajador' => ['nullable', 'string', 'max:30', 'unique:personal,codigo_trabajador'],
            'codigo_fotocheck' => ['nullable', 'string', 'max:30', 'unique:personal,codigo_fotocheck'],
            'dni' => ['required', 'string', 'max:15', 'unique:personal,dni'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cargo' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'proyectos_ids' => ['nullable', 'array'],
            'proyectos_ids.*' => ['integer', 'exists:proyectos,id'],
            'user_id' => ['nullable', 'exists:users,id', 'unique:personal,user_id'],
            'estado' => ['required', 'in:ACTIVO,VACACIONES,DESCANSO_MEDICO,CESADO'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'codigo_trabajador' => 'código de trabajador',
            'codigo_fotocheck' => 'código de fotocheck',
            'dni' => 'documento de identidad (DNI/CE)',
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'cargo' => 'cargo laboral',
            'area' => 'área de trabajo',
            'telefono' => 'teléfono de contacto',
            'correo' => 'correo electrónico',
            'proyecto_id' => 'proyecto base',
            'user_id' => 'usuario del sistema asociado',
            'estado' => 'estado laboral',
        ];
    }
}
