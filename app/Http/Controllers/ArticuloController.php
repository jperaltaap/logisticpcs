<?php

namespace App\Http\Controllers;

use App\Http\Requests\Articulo\StoreArticuloRequest;
use App\Http\Requests\Articulo\UpdateArticuloRequest;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Proyecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');

        $query = Articulo::with(['categoria', 'proyecto']);

        // Contabilizar activos y existencias estrictamente según proyecto activo (o global si modo general)
        if ($proyectoActivoId) {
            $query->withCount(['activos' => function ($q) use ($proyectoActivoId) {
                $q->where('proyecto_actual_id', $proyectoActivoId);
            }])
                ->withSum(['inventarioStocks as stock_total' => function ($q) use ($proyectoActivoId) {
                    $q->where('proyecto_id', $proyectoActivoId);
                }], 'cantidad_actual');
        } elseif ($permitidos !== null) {
            $query->withCount(['activos' => function ($q) use ($permitidos) {
                $q->whereIn('proyecto_actual_id', $permitidos);
            }])
                ->withSum(['inventarioStocks as stock_total' => function ($q) use ($permitidos) {
                    $q->whereIn('proyecto_id', $permitidos);
                }], 'cantidad_actual');
        } else {
            // Modo general (todos los proyectos): cálculo global
            $query->withCount('activos')
                ->withSum('inventarioStocks as stock_total', 'cantidad_actual');
        }

        // Filtrar artículos que pertenecen u operan en el proyecto activo
        if ($proyectoActivoId) {
            $query->where(function ($q) use ($proyectoActivoId) {
                $q->where('proyecto_id', $proyectoActivoId)
                    ->orWhereHas('activos', fn ($aq) => $aq->where('proyecto_actual_id', $proyectoActivoId))
                    ->orWhereHas('inventarioStocks', fn ($sq) => $sq->where('proyecto_id', $proyectoActivoId));
            });
        } elseif ($permitidos !== null) {
            $query->where(function ($q) use ($permitidos) {
                $q->whereIn('proyecto_id', $permitidos)
                    ->orWhereHas('activos', fn ($aq) => $aq->whereIn('proyecto_actual_id', $permitidos))
                    ->orWhereHas('inventarioStocks', fn ($sq) => $sq->whereIn('proyecto_id', $permitidos));
            });
        }

        $search = $request->input('search', $request->input('q'));
        if (! empty($search)) {
            $query->where(function ($q) use ($search, $proyectoActivoId) {
                $q->where('codigo_sku', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhereHas('categoria', function ($cq) use ($search) {
                        $cq->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('activos', function ($aq) use ($search, $proyectoActivoId) {
                        $aq->where(function ($s) use ($search) {
                            $s->where('numero_serie', 'like', "%{$search}%")
                                ->orWhere('codigo_interno', 'like', "%{$search}%");
                        });
                        if ($proyectoActivoId) {
                            $aq->where('proyecto_actual_id', $proyectoActivoId);
                        }
                    });
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('tipo_articulo')) {
            $query->where('tipo_articulo', $request->input('tipo_articulo'));
        }

        if ($request->filled('control_serie')) {
            $query->where('control_serie', $request->boolean('control_serie'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $articulos = $query->orderBy('descripcion')->paginate(12)->withQueryString();

        // Categorías que obedecen al proyecto activo (o todas en modo general)
        $categorias = Categoria::when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
            $q->whereHas('articulos', function ($aq) use ($proyectoActivoId) {
                $aq->where('proyecto_id', $proyectoActivoId)
                    ->orWhereHas('activos', fn ($actQ) => $actQ->where('proyecto_actual_id', $proyectoActivoId))
                    ->orWhereHas('inventarioStocks', fn ($stkQ) => $stkQ->where('proyecto_id', $proyectoActivoId));
            });
        })->when(! $proyectoActivoId && $permitidos !== null, function ($q) use ($permitidos) {
            $q->whereHas('articulos', function ($aq) use ($permitidos) {
                $aq->whereIn('proyecto_id', $permitidos)
                    ->orWhereHas('activos', fn ($actQ) => $actQ->whereIn('proyecto_actual_id', $permitidos))
                    ->orWhereHas('inventarioStocks', fn ($stkQ) => $stkQ->whereIn('proyecto_id', $permitidos));
            });
        })->orderBy('nombre')->get();

        return view('articulos.index', compact('articulos', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        $categorias = Categoria::orderBy('nombre')->get();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('articulos.create', compact('categorias', 'proyectos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticuloRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['proyecto_id']) && session('proyecto_activo_id')) {
            $data['proyecto_id'] = session('proyecto_activo_id');
        }

        if (! empty($data['control_serie']) && empty($data['es_instalable']) && ($data['tipo_articulo'] ?? '') !== 'CONSUMIBLE') {
            $data['stock_minimo'] = 0;
        }

        if ($request->hasFile('foto_referencia')) {
            $path = $request->file('foto_referencia')->store('articulos', 'public');
            $data['foto_referencia'] = $path;
        }

        $articulo = Articulo::create($data);

        return redirect()->route('articulos.show', $articulo)
            ->with('success', "Artículo {$articulo->codigo_sku} creado exitosamente.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Articulo $articulo): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $articulo->proyecto_id && ! in_array($articulo->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este artículo.');
        }

        $proyectoActivoId = session('proyecto_activo_id');

        $articulo->load([
            'categoria',
            'proyecto',
            'activos' => function ($q) use ($proyectoActivoId, $permitidos) {
                $q->with(['ubicacion', 'responsable', 'proyecto']);
                if ($proyectoActivoId) {
                    $q->where('proyecto_actual_id', $proyectoActivoId);
                } elseif ($permitidos !== null) {
                    $q->whereIn('proyecto_actual_id', $permitidos);
                }
            },
            'inventarioStocks' => function ($q) use ($proyectoActivoId, $permitidos) {
                $q->with('ubicacion');
                if ($proyectoActivoId) {
                    $q->where('proyecto_id', $proyectoActivoId);
                } elseif ($permitidos !== null) {
                    $q->whereIn('proyecto_id', $permitidos);
                }
            },
            'kits',
        ]);

        return view('articulos.show', compact('articulo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Articulo $articulo): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $articulo->proyecto_id && ! in_array($articulo->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para editar este artículo.');
        }

        $categorias = Categoria::orderBy('nombre')->get();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('articulos.edit', compact('articulo', 'categorias', 'proyectos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticuloRequest $request, Articulo $articulo): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $articulo->proyecto_id && ! in_array($articulo->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar este artículo.');
        }

        $data = $request->validated();

        if (empty($data['proyecto_id']) && session('proyecto_activo_id') && empty($articulo->proyecto_id)) {
            $data['proyecto_id'] = session('proyecto_activo_id');
        }

        if (! empty($data['control_serie']) && empty($data['es_instalable']) && ($data['tipo_articulo'] ?? '') !== 'CONSUMIBLE') {
            $data['stock_minimo'] = 0;
        }

        if ($request->hasFile('foto_referencia')) {
            if ($articulo->foto_referencia && Storage::disk('public')->exists($articulo->foto_referencia)) {
                Storage::disk('public')->delete($articulo->foto_referencia);
            }
            $path = $request->file('foto_referencia')->store('articulos', 'public');
            $data['foto_referencia'] = $path;
        }

        $articulo->update($data);

        return redirect()->route('articulos.show', $articulo)
            ->with('success', "Artículo {$articulo->codigo_sku} actualizado exitosamente.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Articulo $articulo): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $articulo->proyecto_id && ! in_array($articulo->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para eliminar este artículo.');
        }

        if ($articulo->activos()->exists()) {
            return back()->with('error', 'No se puede eliminar el artículo porque posee unidades serializadas registradas en inventario.');
        }

        if ($articulo->componentesKit()->exists()) {
            return back()->with('error', 'No se puede eliminar el artículo porque forma parte de kits técnicos configurados.');
        }

        if ($articulo->inventarioStocks()->where('cantidad_actual', '>', 0)->exists()) {
            return back()->with('error', 'No se puede eliminar el artículo porque cuenta con stock existente en almacenes.');
        }

        $sku = $articulo->codigo_sku;
        $articulo->delete();

        return redirect()->route('articulos.index')
            ->with('success', "Artículo {$sku} eliminado correctamente.");
    }
}
