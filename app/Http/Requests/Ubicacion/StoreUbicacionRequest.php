<?php

namespace App\Http\Requests\Ubicacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('tipo')) {
            $this->merge([
                'tipo' => 'ALMACEN_CENTRAL',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30', 'unique:ubicaciones,codigo'],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['nullable', 'in:ALMACEN_CENTRAL,ALMACEN_OBRA,CENTRO_ACOPIO,EN_CAMPO,TALLER_REPARACION,BAJA'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }
}
