<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /**
     * Catálogo de Categorías de Bienes
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $categorias = Categoria::withCount('articulos')
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'search'));
    }

    /**
     * Formulario de Creación
     */
    public function create(): View
    {
        return view('categorias.create');
    }

    /**
     * Guardar Categoría
     */
    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        $categoria = Categoria::create($request->validated());

        return redirect()->route('categorias.index')
            ->with('status', "Categoría {$categoria->nombre} ({$categoria->codigo}) creada exitosamente.");
    }

    /**
     * Ver Detalle y Artículos de la Categoría
     */
    public function show(Categoria $categoria): View
    {
        $categoria->loadCount('articulos');

        $articulos = $categoria->articulos()
            ->with(['activos'])
            ->paginate(15);

        return view('categorias.show', compact('categoria', 'articulos'));
    }

    /**
     * Formulario de Edición
     */
    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualizar Categoría
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')
            ->with('status', "Categoría {$categoria->nombre} actualizada correctamente.");
    }

    /**
     * Eliminar Categoría
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->articulos()->exists()) {
            return redirect()->route('categorias.index')
                ->with('error', "No se puede eliminar la categoría '{$categoria->nombre}' porque tiene artículos asociados en el catálogo.");
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría eliminada satisfactoriamente.');
    }
}
