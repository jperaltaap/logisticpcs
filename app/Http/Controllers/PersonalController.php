<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personal\StorePersonalRequest;
use App\Http\Requests\Personal\UpdatePersonalRequest;
use App\Models\Cuadrilla;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalController extends Controller
{
    /**
     * Display a listing of personal.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $proyectoId = $request->input('proyecto_id');
        $cuadrillaId = $request->input('cuadrilla_id');
        $estado = $request->input('estado');

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $proyectoId ?: $proyectoActivoId;
        $proyectoId = $effectiveProyectoId;

        if ($cuadrillaId && $effectiveProyectoId) {
            $cuadrillaValida = Cuadrilla::where('id', $cuadrillaId)
                ->where('proyecto_id', $effectiveProyectoId)
                ->exists();
            if (! $cuadrillaValida) {
                $cuadrillaId = null;
            }
        }

        $query = Personal::with([
            'proyecto',
            'proyectos',
            'user',
            'cuadrillas' => fn ($q) => $q->when($effectiveProyectoId, fn ($sq) => $sq->where('cuadrillas.proyecto_id', $effectiveProyectoId)),
        ])
            ->withCount(['cuadrillas', 'activosAsignados', 'despachos'])
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('dni', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('codigo_trabajador', 'like', "%{$search}%")
                    ->orWhere('codigo_fotocheck', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($effectiveProyectoId) {
            $query->delProyecto((int) $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $query->deProyectos($permitidos);
        }

        if ($cuadrillaId) {
            $query->whereHas('cuadrillas', fn ($q) => $q->where('cuadrillas.id', $cuadrillaId));
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $personal = $query->paginate(10)->withQueryString();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();
        $cuadrillas = Cuadrilla::where('estado', 'ACTIVA')
            ->when($effectiveProyectoId, fn ($q) => $q->where('proyecto_id', $effectiveProyectoId))
            ->when(! $effectiveProyectoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('nombre')
            ->get();

        $statsBase = Personal::query();
        if ($effectiveProyectoId) {
            $statsBase->delProyecto((int) $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $statsBase->deProyectos($permitidos);
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'activos' => (clone $statsBase)->where('estado', 'ACTIVO')->count(),
            'vacaciones' => (clone $statsBase)->where('estado', 'VACACIONES')->count(),
            'descanso' => (clone $statsBase)->where('estado', 'DESCANSO_MEDICO')->count(),
            'cesados' => (clone $statsBase)->where('estado', 'CESADO')->count(),
        ];

        return view('personal.index', compact('personal', 'proyectos', 'cuadrillas', 'stats', 'search', 'proyectoId', 'cuadrillaId', 'estado'));
    }

    /**
     * Show the form for creating a new personal.
     */
    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden registrar personal.');
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        // Usuarios del sistema que aún no tienen una ficha de personal vinculada
        $usuariosDisponibles = User::whereDoesntHave('personal')
            ->where('estado', 'ACTIVO')
            ->orderBy('name')
            ->get();

        return view('personal.create', compact('proyectos', 'usuariosDisponibles'));
    }

    /**
     * Store a newly created personal in storage.
     */
    public function store(StorePersonalRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden registrar personal.');
        $data = $request->validated();
        $proyectosIds = collect($data['proyectos_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (empty($data['proyecto_id']) && $proyectosIds->isNotEmpty()) {
            $data['proyecto_id'] = $proyectosIds->first();
        }

        if (empty($data['proyecto_id']) && session('proyecto_activo_id')) {
            $data['proyecto_id'] = (int) session('proyecto_activo_id');
        }

        if (! empty($data['proyecto_id']) && ! $proyectosIds->contains((int) $data['proyecto_id'])) {
            $proyectosIds->prepend((int) $data['proyecto_id']);
        }

        unset($data['proyectos_ids']);

        $personal = Personal::create($data);

        if ($proyectosIds->isNotEmpty()) {
            $personal->proyectos()->syncWithoutDetaching($proyectosIds->all());
        }

        return redirect()
            ->route('personal.index')
            ->with('status', "Ficha de personal de {$personal->nombres} {$personal->apellidos} (DNI {$personal->dni}) registrada exitosamente.");
    }

    /**
     * Display the specified personal record.
     */
    public function show(Personal $personal): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $personal->proyecto_id && ! in_array($personal->proyecto_id, $permitidos, true)) {
            $tieneProyectoPermitido = $personal->proyectos()->whereIn('proyectos.id', $permitidos)->exists();
            if (! $tieneProyectoPermitido) {
                abort(403, 'Acceso denegado: No tienes autorización para visualizar este personal.');
            }
        }

        $personal->load([
            'proyecto',
            'proyectos',
            'proyectosResponsable',
            'user',
            'cuadrillas.lider',
            'activosAsignados.articulo',
            'despachos' => fn ($q) => $q->latest('id')->limit(10),
            'proyectosACargo',
        ])->loadCount(['cuadrillas', 'activosAsignados', 'despachos']);

        return view('personal.show', compact('personal'));
    }

    /**
     * Show the form for editing the specified personal record.
     */
    public function edit(Personal $personal): View
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden editar personal.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $personal->proyecto_id && ! in_array($personal->proyecto_id, $permitidos, true)) {
            $tieneProyectoPermitido = $personal->proyectos()->whereIn('proyectos.id', $permitidos)->exists();
            if (! $tieneProyectoPermitido) {
                abort(403, 'Acceso denegado: No tienes autorización para editar este personal.');
            }
        }

        $personal->load('proyectos');

        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();
        // Usuarios sin personal vinculado, o el propio usuario actual
        $usuariosDisponibles = User::where(function ($q) use ($personal) {
            $q->whereDoesntHave('personal')
                ->orWhere('id', $personal->user_id);
        })->where('estado', 'ACTIVO')->orderBy('name')->get();

        return view('personal.edit', compact('personal', 'proyectos', 'usuariosDisponibles'));
    }

    /**
     * Update the specified personal in storage.
     */
    public function update(UpdatePersonalRequest $request, Personal $personal): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden modificar personal.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $personal->proyecto_id && ! in_array($personal->proyecto_id, $permitidos, true)) {
            $tieneProyectoPermitido = $personal->proyectos()->whereIn('proyectos.id', $permitidos)->exists();
            if (! $tieneProyectoPermitido) {
                abort(403, 'Acceso denegado: No tienes autorización para modificar este personal.');
            }
        }

        $data = $request->validated();
        $proyectosIds = collect($data['proyectos_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (empty($data['proyecto_id']) && $proyectosIds->isNotEmpty()) {
            $data['proyecto_id'] = $proyectosIds->first();
        }

        if (! empty($data['proyecto_id']) && ! $proyectosIds->contains((int) $data['proyecto_id'])) {
            $proyectosIds->prepend((int) $data['proyecto_id']);
        }

        unset($data['proyectos_ids']);

        $personal->update($data);

        if ($request->has('proyectos_ids') || array_key_exists('proyecto_id', $data)) {
            // Conservar proyectos donde es responsable aunque no se hayan marcado como proyecto secundario
            $responsableEnIds = $personal->proyectosResponsable()->pluck('proyectos.id')->map(fn ($id) => (int) $id);
            $syncData = [];
            foreach ($proyectosIds->merge($responsableEnIds)->unique() as $pid) {
                $syncData[$pid] = [
                    'es_responsable' => $responsableEnIds->contains($pid),
                ];
            }
            $personal->proyectos()->sync($syncData);
        }

        return redirect()
            ->route('personal.index')
            ->with('status', "Ficha de {$personal->nombres} {$personal->apellidos} actualizada correctamente.");
    }

    /**
     * Remove the specified personal from storage.
     */
    public function destroy(Personal $personal): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden dar de baja a personal.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $personal->proyecto_id && ! in_array($personal->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para dar de baja a este personal.');
        }
        // Regla de integridad: no borrar si tiene activos asignados sin retornar
        if ($personal->activosAsignados()->where('estado_operativo', '!=', 'DE_BAJA')->exists()) {
            return redirect()
                ->route('personal.index')
                ->with('error', "No se puede dar de baja a {$personal->nombres} {$personal->apellidos} porque mantiene activos/herramientas asignadas a su cargo.");
        }

        $nombre = $personal->nombre_completo;
        $personal->delete();

        return redirect()
            ->route('personal.index')
            ->with('status', "Registro de {$nombre} dado de baja del sistema.");
    }
}
