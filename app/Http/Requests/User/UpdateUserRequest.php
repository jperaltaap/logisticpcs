<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'rol' => ['required', 'in:ADMINISTRADOR,LOGISTICO,SUPERVISOR,AUDITOR,TECNICO'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'proyectos_ids' => ['nullable', 'array'],
            'proyectos_ids.*' => ['integer', 'exists:proyectos,id'],
            'personal_id' => ['nullable', 'exists:personal,id'],
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
            'name' => 'nombre completo',
            'email' => 'correo electrónico corporativo',
            'password' => 'contraseña',
            'rol' => 'rol del sistema',
            'estado' => 'estado de cuenta',
            'personal_id' => 'ficha de personal vinculada',
        ];
    }
}
