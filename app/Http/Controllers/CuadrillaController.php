<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cuadrilla\AddMiembroCuadrillaRequest;
use App\Http\Requests\Cuadrilla\StoreCuadrillaRequest;
use App\Http\Requests\Cuadrilla\UpdateCuadrillaRequest;
use App\Models\Cuadrilla;
use App\Models\CuadrillaPersonal;
use App\Models\Personal;
use App\Models\Proyecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuadrillaController extends Controller
{
    /**
     * Display a listing of cuadrillas.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $proyectoId = $request->input('proyecto_id');
        $estado = $request->input('estado');

        $query = Cuadrilla::with(['proyecto', 'lider'])
            ->withCount(['miembros', 'activosAsignados', 'despachos'])
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_cuadrilla', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhereHas('lider', function ($ql) use ($search) {
                        $ql->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('dni', 'like', "%{$search}%");
                    });
            });
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $proyectoId ?: $proyectoActivoId;
        $proyectoId = $effectiveProyectoId;

        if ($effectiveProyectoId) {
            $query->where('proyecto_id', $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $cuadrillas = $query->paginate(12)->withQueryString();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        $statsBase = Cuadrilla::query();
        if ($effectiveProyectoId) {
            $statsBase->where('proyecto_id', $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $statsBase->whereIn('proyecto_id', $permitidos);
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'activas' => (clone $statsBase)->where('estado', 'ACTIVA')->count(),
            'en_descanso' => (clone $statsBase)->where('estado', 'EN_DESCANSO')->count(),
            'disueltas' => (clone $statsBase)->where('estado', 'DISUELTA')->count(),
        ];

        return view('cuadrillas.index', compact('cuadrillas', 'proyectos', 'stats', 'search', 'proyectoId', 'estado'));
    }

    /**
     * Show the form for creating a new cuadrilla.
     */
    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden crear cuadrillas.');
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        $personal = Personal::with(['proyecto', 'proyectos'])
            ->where('estado', 'ACTIVO')
            ->when($permitidos !== null, fn ($q) => $q->deProyectos($permitidos))
            ->orderBy('apellidos')
            ->get();

        return view('cuadrillas.create', compact('proyectos', 'personal'));
    }

    /**
     * Store a newly created cuadrilla in storage.
     */
    public function store(StoreCuadrillaRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden crear cuadrillas.');
        $data = $request->validated();
        if (empty($data['proyecto_id']) && session('proyecto_activo_id')) {
            $data['proyecto_id'] = (int) session('proyecto_activo_id');
        }

        $lider = Personal::with('proyectos')->find($data['lider_personal_id']);
        if (! $lider || ! $lider->perteneceAlProyecto((int) $data['proyecto_id'])) {
            return back()
                ->withInput()
                ->withErrors(['lider_personal_id' => 'El líder seleccionado no se encuentra asignado al mismo proyecto de la cuadrilla.']);
        }

        $cuadrilla = Cuadrilla::create($data);

        // Incorporar automáticamente al líder como miembro activo con rol de LIDER
        CuadrillaPersonal::create([
            'cuadrilla_id' => $cuadrilla->id,
            'personal_id' => $cuadrilla->lider_personal_id,
            'rol_en_cuadrilla' => 'LIDER DE CUADRILLA',
            'fecha_incorporacion' => now()->toDateString(),
        ]);

        return redirect()
            ->route('cuadrillas.show', $cuadrilla)
            ->with('status', "Cuadrilla {$cuadrilla->nombre} ({$cuadrilla->codigo_cuadrilla}) creada exitosamente.");
    }

    /**
     * Display the specified cuadrilla.
     */
    public function show(Cuadrilla $cuadrilla): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar esta cuadrilla.');
        }

        $cuadrilla->load([
            'proyecto',
            'lider',
            'miembrosPivot.personal',
            'activosAsignados.articulo',
            'despachos' => fn ($q) => $q->with('ubicacionOrigen')->latest('id')->limit(10),
        ]);

        // Personal disponible asignado al mismo proyecto que no esté activo en esta cuadrilla
        $miembrosActivosIds = $cuadrilla->miembrosPivot()
            ->whereNull('fecha_retiro')
            ->pluck('personal_id')
            ->toArray();

        $personalDisponible = Personal::with(['proyecto', 'proyectos'])
            ->where('estado', 'ACTIVO')
            ->when($cuadrilla->proyecto_id, fn ($q) => $q->delProyecto((int) $cuadrilla->proyecto_id))
            ->whereNotIn('id', $miembrosActivosIds)
            ->orderBy('apellidos')
            ->get();

        return view('cuadrillas.show', compact('cuadrilla', 'personalDisponible'));
    }

    /**
     * Show the form for editing the specified cuadrilla.
     */
    public function edit(Cuadrilla $cuadrilla): View
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden editar cuadrillas.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para editar esta cuadrilla.');
        }

        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $personal = Personal::with(['proyecto', 'proyectos'])
            ->where('estado', 'ACTIVO')
            ->when($permitidos !== null, fn ($q) => $q->deProyectos($permitidos))
            ->orderBy('apellidos')
            ->get();

        return view('cuadrillas.edit', compact('cuadrilla', 'proyectos', 'personal'));
    }

    /**
     * Update the specified cuadrilla in storage.
     */
    public function update(UpdateCuadrillaRequest $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden modificar cuadrillas.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar esta cuadrilla.');
        }

        $data = $request->validated();
        $cuadrilla->update($data);

        // Asegurar que el líder figure como miembro activo en la cuadrilla
        if ($cuadrilla->lider_personal_id) {
            $liderYaActivo = CuadrillaPersonal::where('cuadrilla_id', $cuadrilla->id)
                ->where('personal_id', $cuadrilla->lider_personal_id)
                ->whereNull('fecha_retiro')
                ->exists();

            if (! $liderYaActivo) {
                CuadrillaPersonal::create([
                    'cuadrilla_id' => $cuadrilla->id,
                    'personal_id' => $cuadrilla->lider_personal_id,
                    'rol_en_cuadrilla' => 'LIDER DE CUADRILLA',
                    'fecha_incorporacion' => now()->toDateString(),
                ]);
            }
        }

        return redirect()
            ->route('cuadrillas.show', $cuadrilla)
            ->with('status', "Cuadrilla {$cuadrilla->nombre} actualizada correctamente.");
    }

    /**
     * Remove the specified cuadrilla from storage.
     */
    public function destroy(Cuadrilla $cuadrilla): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden eliminar cuadrillas.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para eliminar esta cuadrilla.');
        }

        if ($cuadrilla->activosAsignados()->count() > 0) {
            return back()->with('error', "No se puede eliminar la cuadrilla {$cuadrilla->codigo_cuadrilla} porque mantiene activos asignados en campo.");
        }

        if ($cuadrilla->despachos()->count() > 0) {
            return back()->with('error', "No se puede eliminar la cuadrilla {$cuadrilla->codigo_cuadrilla} porque posee historial de vales de despacho asociados.");
        }

        $codigo = $cuadrilla->codigo_cuadrilla;
        $cuadrilla->miembrosPivot()->delete();
        $cuadrilla->delete();

        return redirect()
            ->route('cuadrillas.index')
            ->with('status', "Cuadrilla {$codigo} eliminada correctamente.");
    }

    /**
     * Add a member to the cuadrilla.
     */
    public function addMiembro(AddMiembroCuadrillaRequest $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden gestionar miembros de cuadrilla.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar los miembros de esta cuadrilla.');
        }

        $personalId = (int) $request->input('personal_id');
        $personal = Personal::with('proyectos')->findOrFail($personalId);

        if ($cuadrilla->proyecto_id && ! $personal->perteneceAlProyecto((int) $cuadrilla->proyecto_id)) {
            return back()->with('error', "No se puede incorporar a {$personal->nombre_completo} porque no pertenece al proyecto de esta cuadrilla.");
        }

        // Verificar si ya está activo
        $yaActivo = CuadrillaPersonal::where('cuadrilla_id', $cuadrilla->id)
            ->where('personal_id', $personalId)
            ->whereNull('fecha_retiro')
            ->exists();

        if ($yaActivo) {
            return back()->with('error', 'El trabajador ya es miembro activo de esta cuadrilla.');
        }

        CuadrillaPersonal::create([
            'cuadrilla_id' => $cuadrilla->id,
            'personal_id' => $personalId,
            'rol_en_cuadrilla' => $request->input('rol_en_cuadrilla'),
            'fecha_incorporacion' => $request->input('fecha_incorporacion'),
        ]);

        return back()->with('status', "Trabajador {$personal->nombre_completo} incorporado exitosamente a la cuadrilla.");
    }

    /**
     * Retire a member from the cuadrilla.
     */
    public function retirarMiembro(Request $request, Cuadrilla $cuadrilla, Personal $personal): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden gestionar miembros de cuadrilla.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($cuadrilla->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar los miembros de esta cuadrilla.');
        }

        $miembro = CuadrillaPersonal::where('cuadrilla_id', $cuadrilla->id)
            ->where('personal_id', $personal->id)
            ->whereNull('fecha_retiro')
            ->first();

        if (! $miembro) {
            return back()->with('error', 'El trabajador no se encuentra activo en esta cuadrilla.');
        }

        $miembro->update([
            'fecha_retiro' => now()->toDateString(),
        ]);

        return back()->with('status', "Se registró el retiro de {$personal->nombre_completo} de la cuadrilla.");
    }
}
