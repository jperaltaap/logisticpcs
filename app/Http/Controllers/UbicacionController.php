<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ubicacion\StoreUbicacionRequest;
use App\Http\Requests\Ubicacion\UpdateUbicacionRequest;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    /**
     * Listado de Centros de Almacenamiento & Ubicaciones
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $proyectoFiltroId = $request->input('proyecto_id');

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;
        $effectiveProyectoId = ($proyectoFiltroId !== null && $proyectoFiltroId !== '')
            ? $proyectoFiltroId
            : ($proyectoActivoId ? (string) $proyectoActivoId : null);

        $ubicaciones = Ubicacion::with('proyecto')
            ->withCount(['activos', 'inventarioStocks'])
            ->when($effectiveProyectoId !== null, function ($q) use ($effectiveProyectoId, $proyectoFiltroId) {
                if ($effectiveProyectoId === 'global') {
                    $q->whereNull('proyecto_id');
                } elseif ($proyectoFiltroId !== null && $proyectoFiltroId !== '') {
                    $q->where('proyecto_id', (int) $effectiveProyectoId);
                } else {
                    $q->where(function ($sub) use ($effectiveProyectoId) {
                        $sub->where('proyecto_id', (int) $effectiveProyectoId)
                            ->orWhereNull('proyecto_id');
                    });
                }
            })
            ->when($effectiveProyectoId === null && $permitidos !== null, function ($q) use ($permitidos) {
                $q->where(function ($sub) use ($permitidos) {
                    $sub->whereIn('proyecto_id', $permitidos)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('CASE WHEN proyecto_id IS NOT NULL THEN 1 ELSE 2 END')
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $proyectoFiltroId = $effectiveProyectoId;

        return view('ubicaciones.index', compact('ubicaciones', 'search', 'proyectoFiltroId', 'proyectos', 'proyectoActivo'));
    }

    /**
     * Formulario de Creación
     */
    public function create(): View
    {
        $user = auth()->user();
        $proyectoActivoId = session('proyecto_activo_id');
        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('ubicaciones.create', compact('proyectos', 'proyectoActivo'));
    }

    /**
     * Guardar Almacén / Ubicación
     */
    public function store(StoreUbicacionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['proyecto_id']) && session('proyecto_activo_id')) {
            $data['proyecto_id'] = (int) session('proyecto_activo_id');
        }
        $data['tipo'] = $data['tipo'] ?? 'ALMACEN_CENTRAL';
        $ubicacion = Ubicacion::create($data);

        return redirect()->route('ubicaciones.index')
            ->with('status', "Centro de almacén {$ubicacion->nombre} ({$ubicacion->codigo}) registrado exitosamente.");
    }

    /**
     * Ver Detalle de Ubicación, Existencias y Activos
     */
    public function show(Ubicacion $ubicacion): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $ubicacion->proyecto_id && ! in_array($ubicacion->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar esta ubicación.');
        }

        $ubicacion->load('proyecto')->loadCount(['activos', 'inventarioStocks']);

        $stocks = $ubicacion->inventarioStocks()
            ->with('articulo.categoria')
            ->paginate(15, ['*'], 'stocks_page');

        $activos = $ubicacion->activos()
            ->with('articulo')
            ->paginate(15, ['*'], 'activos_page');

        return view('ubicaciones.show', compact('ubicacion', 'stocks', 'activos'));
    }

    /**
     * Formulario de Edición
     */
    public function edit(Ubicacion $ubicacion): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $ubicacion->proyecto_id && ! in_array($ubicacion->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para editar esta ubicación.');
        }

        $proyectoActivoId = session('proyecto_activo_id');
        $proyectoActivo = $proyectoActivoId ? Proyecto::find($proyectoActivoId) : null;
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('ubicaciones.edit', compact('ubicacion', 'proyectos', 'proyectoActivo'));
    }

    /**
     * Actualizar Ubicación
     */
    public function update(UpdateUbicacionRequest $request, Ubicacion $ubicacion): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $ubicacion->proyecto_id && ! in_array($ubicacion->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar esta ubicación.');
        }

        $data = $request->validated();
        $ubicacion->update($data);

        return redirect()->route('ubicaciones.index')
            ->with('status', "Ubicación {$ubicacion->nombre} actualizada correctamente.");
    }

    /**
     * Eliminar Ubicación con Protección Referencial
     */
    public function destroy(Ubicacion $ubicacion): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $ubicacion->proyecto_id && ! in_array($ubicacion->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para eliminar esta ubicación.');
        }
        if ($ubicacion->activos()->exists() || $ubicacion->inventarioStocks()->where('cantidad_actual', '>', 0)->exists()) {
            return redirect()->route('ubicaciones.index')
                ->with('error', "No se puede eliminar la ubicación '{$ubicacion->nombre}' porque cuenta con activos físicos o stock activo en inventario.");
        }

        if ($ubicacion->despachosOrigen()->exists() || $ubicacion->kardexMovimientos()->exists()) {
            return redirect()->route('ubicaciones.index')
                ->with('error', "No se puede eliminar la ubicación '{$ubicacion->nombre}' porque tiene historial de movimientos en kardex.");
        }

        $ubicacion->delete();

        return redirect()->route('ubicaciones.index')
            ->with('status', 'Ubicación eliminada satisfactoriamente.');
    }
}
