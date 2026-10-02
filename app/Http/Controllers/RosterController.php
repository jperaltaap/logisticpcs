<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roster\GenerarRosterCicloRequest;
use App\Http\Requests\Roster\StoreRosterTurnoRequest;
use App\Models\Cuadrilla;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RosterController extends Controller
{
    /**
     * Display the monthly roster matrix.
     */
    public function index(Request $request): View
    {
        $mesInput = $request->input('mes', now()->format('Y-m'));
        $search = trim((string) $request->input('search', ''));
        $proyectoId = $request->input('proyecto_id');
        $cuadrillaId = $request->input('cuadrilla_id');
        $grupoGuardia = $request->input('grupo_guardia');

        $inicioMes = Carbon::parse($mesInput.'-01')->startOfMonth();
        $finMes = $inicioMes->copy()->endOfMonth();

        // Generar lista de días del mes
        $diasPeriodo = CarbonPeriod::create($inicioMes, $finMes);
        $dias = [];
        foreach ($diasPeriodo as $date) {
            $dias[] = $date->copy();
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $proyectoId ?: $proyectoActivoId;
        $proyectoId = $effectiveProyectoId;

        // Si se filtró por cuadrilla pero la cuadrilla no pertenece al proyecto activo, limpiar el filtro
        if ($cuadrillaId && $effectiveProyectoId) {
            $cuadrillaValida = Cuadrilla::where('id', $cuadrillaId)
                ->where('proyecto_id', $effectiveProyectoId)
                ->exists();
            if (! $cuadrillaValida) {
                $cuadrillaId = null;
            }
        }

        // Consultar personal filtrado (respetando proyecto principal y pivote personal_proyecto)
        $personalQuery = Personal::with([
            'proyecto',
            'proyectos',
            'cuadrillas' => fn ($q) => $q->when($effectiveProyectoId, fn ($sq) => $sq->where('cuadrillas.proyecto_id', $effectiveProyectoId)),
        ])
            ->where('estado', 'ACTIVO')
            ->orderBy('apellidos');

        if ($effectiveProyectoId) {
            $personalQuery->delProyecto((int) $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $personalQuery->deProyectos($permitidos);
        }

        if ($cuadrillaId) {
            $personalQuery->whereHas('cuadrillas', function ($q) use ($cuadrillaId) {
                $q->where('cuadrillas.id', $cuadrillaId);
            });
        }

        if ($search !== '') {
            $personalQuery->where(function ($q) use ($search) {
                $q->where('dni', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('codigo_fotocheck', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%");
            });
        }

        $personalList = $personalQuery->get();
        $personalIds = $personalList->pluck('id')->toArray();

        // Personal disponible del proyecto activo para los modales de programación individual
        $personalModalQuery = Personal::with(['proyecto', 'proyectos', 'cuadrillas'])
            ->where('estado', 'ACTIVO')
            ->orderBy('apellidos');

        if ($effectiveProyectoId) {
            $personalModalQuery->delProyecto((int) $effectiveProyectoId);
        } elseif ($permitidos !== null) {
            $personalModalQuery->deProyectos($permitidos);
        }
        $personalModalList = $personalModalQuery->get();

        // Cargar todos los turnos del periodo para los trabajadores listados
        $turnos = RosterTurno::whereIn('personal_id', $personalIds)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->get()
            ->groupBy(function ($turno) {
                return $turno->personal_id.'_'.$turno->fecha->format('Y-m-d');
            });

        // Estadísticas del día de hoy estrictamente coherentes con el personal listado
        $hoy = now()->toDateString();
        $statsBase = RosterTurno::whereDate('fecha', $hoy)
            ->whereIn('personal_id', $personalIds);

        $statsHoy = [
            'en_campo' => (clone $statsBase)->where('condicion_laboral', 'TRABAJO_CAMPO')->count(),
            'en_bajada' => (clone $statsBase)->where('condicion_laboral', 'BAJADA_DESCANSO')->count(),
            'en_campamento' => (clone $statsBase)->where('condicion_laboral', 'DESCANSO_CAMPAMENTO')->count(),
            'licencia_permiso' => (clone $statsBase)->whereIn('condicion_laboral', ['PERMISO', 'LICENCIA_MEDICA'])->count(),
        ];

        $proyectos = $user ? $user->proyectosPermitidosQuery()->where('estado', 'ACTIVO')->orderBy('nombre')->get() : Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        $cuadrillas = Cuadrilla::where('estado', 'ACTIVA')
            ->when($effectiveProyectoId, fn ($q) => $q->where('proyecto_id', $effectiveProyectoId))
            ->when(! $effectiveProyectoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('nombre')
            ->get();

        return view('roster.index', compact(
            'personalList',
            'personalModalList',
            'dias',
            'turnos',
            'mesInput',
            'inicioMes',
            'proyectos',
            'cuadrillas',
            'statsHoy',
            'proyectoId',
            'cuadrillaId',
            'grupoGuardia',
            'search'
        ));
    }

    /**
     * Store or update a single roster shift.
     */
    public function store(StoreRosterTurnoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden programar el roster.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array((int) $data['proyecto_id'], $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para programar turnos en este proyecto.');
        }

        $this->upsertTurno(
            (int) $data['personal_id'],
            (string) $data['fecha'],
            [
                'proyecto_id' => $data['proyecto_id'],
                'grupo_guardia' => $data['grupo_guardia'],
                'condicion_laboral' => $data['condicion_laboral'],
                'observaciones' => $data['observaciones'] ?? null,
            ]
        );

        $personal = Personal::find($data['personal_id']);
        $nombreTrabajador = $personal ? $personal->nombre_completo : 'el trabajador';

        return back()->with('status', "Turno de roster para {$nombreTrabajador} registrado exitosamente.");
    }

    /**
     * Generate an automated 14x7 cycle for a worker (or list of workers).
     */
    public function generarCiclo(GenerarRosterCicloRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $personalIds = $data['personal_ids'];
        $proyectoId = (int) $data['proyecto_id'];

        $user = auth()->user();
        abort_unless($user?->canManagePersonal(), 403, 'Acceso denegado: Solo Administradores y Supervisores pueden generar ciclos de roster.');
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && ! in_array($proyectoId, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para generar ciclos en este proyecto.');
        }

        $grupoGuardia = $data['grupo_guardia'];
        $fechaInicio = Carbon::parse($data['fecha_inicio']);
        $diasTrabajo = (int) $data['dias_trabajo'];
        $diasDescanso = (int) $data['dias_descanso'];
        $ciclos = (int) $data['ciclos'];

        $totalDiasGenerados = 0;

        foreach ($personalIds as $personalId) {
            $fechaActual = $fechaInicio->copy();

            for ($c = 0; $c < $ciclos; $c++) {
                // Periodo de trabajo en campo (14 días típicamente)
                for ($t = 0; $t < $diasTrabajo; $t++) {
                    $this->upsertTurno(
                        (int) $personalId,
                        $fechaActual->toDateString(),
                        [
                            'proyecto_id' => $proyectoId,
                            'grupo_guardia' => $grupoGuardia,
                            'condicion_laboral' => 'TRABAJO_CAMPO',
                            'observaciones' => "Ciclo {$grupoGuardia} (Trabajo día ".($t + 1).')',
                        ]
                    );
                    $fechaActual->addDay();
                    $totalDiasGenerados++;
                }

                // Periodo de bajada / descanso (7 días típicamente)
                for ($d = 0; $d < $diasDescanso; $d++) {
                    $this->upsertTurno(
                        (int) $personalId,
                        $fechaActual->toDateString(),
                        [
                            'proyecto_id' => $proyectoId,
                            'grupo_guardia' => $grupoGuardia,
                            'condicion_laboral' => 'BAJADA_DESCANSO',
                            'observaciones' => "Ciclo {$grupoGuardia} (Bajada día ".($d + 1).')',
                        ]
                    );
                    $fechaActual->addDay();
                    $totalDiasGenerados++;
                }
            }
        }

        $numPersonas = count($personalIds);
        $detalleDestinatario = $numPersonas === 1
            ? (Personal::find($personalIds[0])?->nombre_completo ?? '1 trabajador')
            : "{$numPersonas} trabajadores";

        return redirect()
            ->route('roster.index', ['mes' => $fechaInicio->format('Y-m')])
            ->with('status', "Programación generada exitosamente para {$detalleDestinatario} con régimen {$diasTrabajo}x{$diasDescanso} ({$totalDiasGenerados} turnos procesados).");
    }

    /**
     * AJAX/JSON endpoint to check labor condition of a worker on a specific date.
     */
    public function checkCondicion(Request $request): JsonResponse
    {
        $personalId = $request->input('personal_id');
        $fecha = $request->input('fecha', now()->toDateString());

        if (! $personalId) {
            return response()->json(['error' => 'ID de personal requerido'], 400);
        }

        $turno = RosterTurno::with('proyecto')
            ->where('personal_id', $personalId)
            ->whereDate('fecha', $fecha)
            ->first();

        if (! $turno) {
            return response()->json([
                'registrado' => false,
                'condicion_laboral' => 'SIN_PROGRAMACION',
                'es_operativo' => true,
                'mensaje' => 'Sin programación específica de roster para la fecha.',
            ]);
        }

        $esOperativo = in_array($turno->condicion_laboral, ['TRABAJO_CAMPO', 'DESCANSO_CAMPAMENTO']);

        $mensajes = [
            'BAJADA_DESCANSO' => '⚠️ ATENCIÓN: El personal se encuentra en BAJADA DE DESCANSO (Régimen 14x7).',
            'PERMISO' => '⚠️ ATENCIÓN: El personal cuenta con PERMISO registrado para esta fecha.',
            'LICENCIA_MEDICA' => '🚫 ALERTA CRÍTICA: El personal registra LICENCIA MÉDICA activa.',
            'TRABAJO_CAMPO' => '✔ Personal operativo en campo.',
            'DESCANSO_CAMPAMENTO' => 'ℹ Personal en campamento base.',
        ];

        return response()->json([
            'registrado' => true,
            'condicion_laboral' => $turno->condicion_laboral,
            'grupo_guardia' => $turno->grupo_guardia,
            'es_operativo' => $esOperativo,
            'mensaje' => $mensajes[$turno->condicion_laboral] ?? $turno->condicion_laboral,
        ]);
    }

    /**
     * Upsert a roster shift safely across SQLite and MySQL date formats.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function upsertTurno(int $personalId, string $fecha, array $attributes): RosterTurno
    {
        $turno = RosterTurno::where('personal_id', $personalId)
            ->whereDate('fecha', $fecha)
            ->first();

        if ($turno) {
            $turno->update($attributes);

            return $turno;
        }

        return RosterTurno::create(array_merge([
            'personal_id' => $personalId,
            'fecha' => $fecha,
        ], $attributes));
    }
}
