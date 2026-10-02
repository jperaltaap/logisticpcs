<?php

namespace App\Http\Requests\Ubicacion;

use App\Models\Ubicacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('tipo')) {
            $param = $this->route('ubicacion') ?? $this->route('ubicacione');
            $existingTipo = $param instanceof Ubicacion ? $param->tipo : null;

            $this->merge([
                'tipo' => $existingTipo ?: 'ALMACEN_CENTRAL',
            ]);
        }
    }

    public function rules(): array
    {
        $param = $this->route('ubicacion') ?? $this->route('ubicacione');
        $ubicacionId = $param instanceof Ubicacion ? $param->id : $param;

        return [
            'codigo' => ['required', 'string', 'max:30', Rule::unique('ubicaciones', 'codigo')->ignore($ubicacionId)],
            'proyecto_id' => ['nullable', 'exists:proyectos,id'],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['nullable', 'in:ALMACEN_CENTRAL,ALMACEN_OBRA,CENTRO_ACOPIO,EN_CAMPO,TALLER_REPARACION,BAJA'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }
}
