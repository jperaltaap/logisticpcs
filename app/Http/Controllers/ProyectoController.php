<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proyecto\StoreProyectoRequest;
use App\Http\Requests\Proyecto\UpdateProyectoRequest;
use App\Models\Personal;
use App\Models\Proyecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProyectoController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $estado = $request->input('estado');

        $query = Proyecto::with(['responsable', 'responsables'])
            ->withCount(['personal', 'cuadrillas', 'activos', 'ubicaciones'])
            ->latest('id');

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null) {
            $query->whereIn('id', $permitidos);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('cliente', 'like', "%{$search}%")
                    ->orWhere('ubicacion_direccion', 'like', "%{$search}%");
            });
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $proyectos = $query->paginate(10)->withQueryString();

        $statsQuery = Proyecto::query();
        if ($permitidos !== null) {
            $statsQuery->whereIn('id', $permitidos);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'activos' => (clone $statsQuery)->where('estado', 'ACTIVO')->count(),
            'suspendidos' => (clone $statsQuery)->where('estado', 'SUSPENDIDO')->count(),
            'finalizados' => (clone $statsQuery)->where('estado', 'FINALIZADO')->count(),
        ];

        return view('proyectos.index', compact('proyectos', 'stats', 'search', 'estado'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $personalDisponibles = Personal::where('estado', 'ACTIVO')
            ->orderBy('nombres')
            ->get();

        return view('proyectos.create', compact('personalDisponibles'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProyectoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $responsablesIds = collect($data['responsables_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (! empty($data['responsable_personal_id']) && ! $responsablesIds->contains((int) $data['responsable_personal_id'])) {
            $responsablesIds->prepend((int) $data['responsable_personal_id']);
        }

        if ($responsablesIds->isNotEmpty() && empty($data['responsable_personal_id'])) {
            $data['responsable_personal_id'] = $responsablesIds->first();
        }

        unset($data['responsables_ids']);

        $proyecto = Proyecto::create($data);
        $this->syncResponsables($proyecto, $responsablesIds->all());

        $user = auth()->user();
        if ($user && $user->rol === 'SUPERVISOR' && ! $user->proyecto_id) {
            $user->update(['proyecto_id' => $proyecto->id]);
            $user->proyectosAsignados()->syncWithoutDetaching([$proyecto->id]);
            session(['proyecto_activo_id' => $proyecto->id]);
        }

        return redirect()
            ->route('proyectos.index')
            ->with('status', "El proyecto {$proyecto->codigo} - {$proyecto->nombre} se ha registrado exitosamente.");
    }

    /**
     * Establecer el proyecto como proyecto activo en la sesión.
     */
    public function activar(Proyecto $proyecto): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyecto->id, $permitidos, true)) {
            return back()->with('error', 'Solo tienes autorización para gestionar tus proyectos asignados.');
        }

        session(['proyecto_activo_id' => $proyecto->id]);

        return back()->with('status', "El proyecto {$proyecto->codigo} - {$proyecto->nombre} se ha establecido como el PROYECTO ACTIVO del sistema.");
    }

    /**
     * Display the specified project.
     */
    public function show(Proyecto $proyecto): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyecto->id, $permitidos, true)) {
            abort(403, 'Solo tienes autorización para gestionar la información de tus proyectos asignados.');
        }

        $proyecto->load([
            'responsable',
            'responsables',
            'usuariosAsignados',
            'ubicaciones',
            'personal' => fn ($q) => $q->orderBy('nombres')->limit(15),
            'cuadrillas.lider',
            'activos.articulo',
        ])->loadCount(['personal', 'cuadrillas', 'activos', 'despachos', 'ubicaciones']);

        return view('proyectos.show', compact('proyecto'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Proyecto $proyecto): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyecto->id, $permitidos, true)) {
            abort(403, 'Solo tienes autorización para gestionar la información de tus proyectos asignados.');
        }

        $proyecto->load('responsables');

        $personalDisponibles = Personal::where('estado', 'ACTIVO')
            ->orderBy('nombres')
            ->get();

        return view('proyectos.edit', compact('proyecto', 'personalDisponibles'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProyectoRequest $request, Proyecto $proyecto): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyecto->id, $permitidos, true)) {
            abort(403, 'Solo tienes autorización para gestionar la información de tus proyectos asignados.');
        }

        $data = $request->validated();
        $responsablesIds = collect($data['responsables_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (! empty($data['responsable_personal_id']) && ! $responsablesIds->contains((int) $data['responsable_personal_id'])) {
            $responsablesIds->prepend((int) $data['responsable_personal_id']);
        }

        if ($responsablesIds->isNotEmpty() && empty($data['responsable_personal_id'])) {
            $data['responsable_personal_id'] = $responsablesIds->first();
        } elseif ($request->has('responsables_ids') && $responsablesIds->isEmpty() && empty($data['responsable_personal_id'])) {
            $data['responsable_personal_id'] = null;
        }

        unset($data['responsables_ids']);

        $proyecto->update($data);

        if ($request->has('responsables_ids') || array_key_exists('responsable_personal_id', $data)) {
            $this->syncResponsables($proyecto, $responsablesIds->all());
        }

        return redirect()
            ->route('proyectos.index')
            ->with('status', "Proyecto {$proyecto->codigo} actualizado correctamente.");
    }

    /**
     * Sincroniza los responsables en la tabla pivote personal_proyecto.
     *
     * @param  array<int>  $responsablesIds
     */
    protected function syncResponsables(Proyecto $proyecto, array $responsablesIds): void
    {
        $antiguosResponsables = DB::table('personal_proyecto')
            ->where('proyecto_id', $proyecto->id)
            ->where('es_responsable', true)
            ->whereNotIn('personal_id', $responsablesIds)
            ->pluck('personal_id');

        foreach ($antiguosResponsables as $oldPid) {
            $esBase = Personal::where('id', $oldPid)->where('proyecto_id', $proyecto->id)->exists();
            if ($esBase) {
                DB::table('personal_proyecto')
                    ->where('proyecto_id', $proyecto->id)
                    ->where('personal_id', $oldPid)
                    ->update(['es_responsable' => false, 'updated_at' => now()]);
            } else {
                DB::table('personal_proyecto')
                    ->where('proyecto_id', $proyecto->id)
                    ->where('personal_id', $oldPid)
                    ->delete();
            }
        }

        foreach ($responsablesIds as $pid) {
            $proyecto->personalAsignado()->syncWithoutDetaching([
                $pid => ['es_responsable' => true],
            ]);
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Proyecto $proyecto): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyecto->id, $permitidos, true)) {
            abort(403, 'Solo tienes autorización para gestionar la información de tus proyectos asignados.');
        }

        // Regla de integridad referencial: Si tiene cuadrillas, personal o activos imputados, prevenir eliminación abrupta
        if ($proyecto->personal()->exists() || $proyecto->activos()->exists() || $proyecto->cuadrillas()->exists()) {
            return redirect()
                ->route('proyectos.index')
                ->with('error', "No es posible eliminar el proyecto {$proyecto->codigo} porque tiene personal, activos o cuadrillas asociadas. Puedes cambiar su estado a FINALIZADO o SUSPENDIDO.");
        }

        $codigo = $proyecto->codigo;
        $proyecto->delete();

        return redirect()
            ->route('proyectos.index')
            ->with('status', "Proyecto {$codigo} eliminado correctamente.");
    }
}
