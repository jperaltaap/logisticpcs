<?php

namespace App\Http\Controllers;

use App\Models\EmpresaConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmpresaConfigController extends Controller
{
    /**
     * Vista de configuración de empresa (única instancia).
     */
    public function edit(): View
    {
        $empresa = EmpresaConfig::instancia();

        return view('configuracion.empresa', compact('empresa'));
    }

    /**
     * Actualizar configuración de empresa.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['nullable', 'string', 'max:200'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:300'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'pais' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'sitio_web' => ['nullable', 'string', 'max:200'],
            'representante_legal' => ['nullable', 'string', 'max:150'],
            'cargo_representante' => ['nullable', 'string', 'max:100'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'zona_horaria' => ['nullable', 'string', 'max:60'],
            'sistema_nombre' => ['nullable', 'string', 'max:100'],
            'sistema_subtitulo' => ['nullable', 'string', 'max:200'],
            'logotipo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'icono' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:2048'],
        ]);

        $empresa = EmpresaConfig::instancia();

        // Procesar logotipo si se subió uno nuevo
        if ($request->hasFile('logotipo')) {
            // Eliminar logotipo anterior si existe
            if ($empresa->logotipo_path && Storage::disk('public')->exists($empresa->logotipo_path)) {
                Storage::disk('public')->delete($empresa->logotipo_path);
            }
            $path = $request->file('logotipo')->store('empresa', 'public');
            $validated['logotipo_path'] = $path;
        }

        // Procesar ícono si se subió uno nuevo
        if ($request->hasFile('icono')) {
            if ($empresa->icono_path && Storage::disk('public')->exists($empresa->icono_path)) {
                Storage::disk('public')->delete($empresa->icono_path);
            }
            $path = $request->file('icono')->store('empresa', 'public');
            $validated['icono_path'] = $path;
        }

        unset($validated['logotipo'], $validated['icono']);
        $empresa->update($validated);

        return redirect()->route('configuracion.empresa')
            ->with('success', 'Configuración de empresa actualizada correctamente.');
    }

    /**
     * Eliminar logotipo actual.
     */
    public function eliminarLogotipo(): RedirectResponse
    {
        $empresa = EmpresaConfig::instancia();

        if ($empresa->logotipo_path && Storage::disk('public')->exists($empresa->logotipo_path)) {
            Storage::disk('public')->delete($empresa->logotipo_path);
        }

        $empresa->update(['logotipo_path' => null]);

        return redirect()->route('configuracion.empresa')
            ->with('success', 'Logotipo eliminado correctamente.');
    }

    /**
     * Eliminar ícono personalizado actual y restaurar el predeterminado.
     */
    public function eliminarIcono(): RedirectResponse
    {
        $empresa = EmpresaConfig::instancia();

        if ($empresa->icono_path && Storage::disk('public')->exists($empresa->icono_path)) {
            Storage::disk('public')->delete($empresa->icono_path);
        }

        $empresa->update(['icono_path' => null]);

        return redirect()->route('configuracion.empresa')
            ->with('success', 'Ícono personalizado eliminado. Se restauró el ícono original del sistema.');
    }
}
