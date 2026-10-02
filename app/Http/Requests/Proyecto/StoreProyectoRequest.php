<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProyectoRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:30', 'unique:proyectos,codigo'],
            'nombre' => ['required', 'string', 'max:150'],
            'cliente' => ['required', 'string', 'max:150'],
            'ubicacion_direccion' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin_estimada' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'responsable_personal_id' => ['nullable', 'exists:personal,id'],
            'responsables_ids' => ['nullable', 'array'],
            'responsables_ids.*' => ['integer', 'exists:personal,id'],
            'estado' => ['required', 'in:ACTIVO,SUSPENDIDO,FINALIZADO'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
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
            'codigo' => 'código único de proyecto',
            'nombre' => 'nombre del proyecto',
            'cliente' => 'cliente contratante',
            'ubicacion_direccion' => 'ubicación / dirección de obra',
            'fecha_inicio' => 'fecha de inicio programada',
            'fecha_fin_estimada' => 'fecha estimada de finalización',
            'responsable_personal_id' => 'responsable / residente de obra',
            'estado' => 'estado operativo',
            'observaciones' => 'observaciones',
        ];
    }
}
