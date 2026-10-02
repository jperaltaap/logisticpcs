@extends('layouts.admin')

@section('title', 'Programación de Roster')
@section('page_title', 'Roster de Turnos & Relevos 14x7 (Regímenes Atípicos)')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cuadrillas.index') }}" class="text-decoration-none fw-semibold text-primary">Control de Personal</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Programación de Roster</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Tarjetas de Estado Hoy -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">En Campo Hoy ({{ now()->format('d/m') }})</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $statsHoy['en_campo'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-success">
                    <i class="bi bi-hammer"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">En Bajada / Descanso Hoy</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ $statsHoy['en_bajada'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-warning text-dark">
                    <i class="bi bi-house-door"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Campamento Base Hoy</span>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ $statsHoy['en_campamento'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-info text-white">
                    <i class="bi bi-buildings"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Permiso / Licencia Hoy</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $statsHoy['licencia_permiso'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-danger">
                    <i class="bi bi-bandaid"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros & Acciones Rápidas -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('roster.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted mb-1">Buscar Personal:</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" id="search" class="form-control border-start-0" placeholder="DNI, nombre o cargo..." value="{{ $search ?? '' }}">
                </div>
            </div>

            <div class="col-md-2">
                <label for="mes" class="form-label small text-muted mb-1">Mes Calendario:</label>
                <input type="month" name="mes" id="mes" class="form-control" value="{{ $mesInput }}" onchange="this.form.submit()">
            </div>

            <div class="col-md-3">
                <label for="cuadrilla_id" class="form-label small text-muted mb-1">Cuadrilla del Proyecto:</label>
                <select name="cuadrilla_id" id="cuadrilla_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas las Cuadrillas</option>
                    @foreach($cuadrillas as $cuad)
                        <option value="{{ $cuad->id }}" {{ (string) $cuadrillaId === (string) $cuad->id ? 'selected' : '' }}>
                            {{ $cuad->codigo_cuadrilla }} - {{ $cuad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary" title="Filtrar">
                    <i class="bi bi-funnel"></i>
                </button>
                @if(!empty($search) || !empty($cuadrillaId))
                    <a href="{{ route('roster.index', ['mes' => $mesInput]) }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
                <a href="{{ route('reportes.export.roster', ['mes' => $inicioMes->month, 'anio' => $inicioMes->year, 'proyecto_id' => $proyectoId]) }}" class="btn btn-success fw-bold text-nowrap" title="Exportar Roster del Mes a Excel">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </a>
                @if(auth()->user()->canManagePersonal())
                <button type="button" class="btn btn-primary fw-bold text-nowrap flex-fill" onclick="abrirProgramarPersonal(null, null)">
                    <i class="bi bi-person-gear me-1"></i> Programar Personal
                </button>
                <button type="button" class="btn btn-outline-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTurnoManual" title="Asignar Turno Puntual">
                    <i class="bi bi-calendar-plus"></i>
                </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Matriz Visual de Roster 14x7 -->
    <div class="admin-card overflow-hidden mb-4">
        <div class="card-header bg-transparent p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-calendar3-range me-2 text-primary"></i> Programación de Turnos por Personal: {{ $inicioMes->translatedFormat('F Y') }}
            </h5>
            <div class="small text-muted">
                Total: <span class="fw-bold text-heading">{{ $personalList->count() }}</span> trabajadores listados
            </div>
        </div>

        <div class="table-responsive" tabindex="0" role="region" aria-label="Matriz visual de programación de turnos y relevos 14x7" style="max-height: 600px;">
            <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 0.78rem;">
                <thead class="table-light sticky-top" style="z-index: 5;">
                    <tr>
                        <th scope="col" class="text-start ps-3 py-2 sticky-start bg-light" style="min-width: 260px; z-index: 6;">
                            Trabajador & Cuadrilla
                        </th>
                        @foreach($dias as $dia)
                            @php
                                $esHoy = $dia->isToday();
                                $esFinde = in_array($dia->dayOfWeekIso, [6, 7]);
                            @endphp
                            <th scope="col" class="p-1 {{ $esHoy ? 'bg-primary text-white border-primary' : ($esFinde ? 'bg-secondary bg-opacity-10' : '') }}" style="min-width: 28px;">
                                <div style="font-size: 0.65rem; opacity: 0.85;">{{ substr($dia->translatedFormat('D'), 0, 2) }}</div>
                                <div class="fw-bold">{{ $dia->format('d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($personalList as $p)
                        @php
                            $proyPersonalId = $proyectoId ?: ($p->proyecto_id ?: ($p->proyectos->first()?->id));
                        @endphp
                        <tr>
                            <td class="text-start ps-3 pe-2 py-2 sticky-start bg-body" style="z-index: 4;">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-heading text-truncate" style="max-width: 195px;">
                                            <a href="{{ route('personal.show', $p) }}" class="text-decoration-none">
                                                {{ $p->apellidos }}, {{ $p->nombres }}
                                            </a>
                                        </div>
                                        <div class="small text-muted text-truncate font-monospace" style="max-width: 195px;">
                                            DNI: {{ $p->dni }} · {{ $p->cuadrillas->first()?->codigo_cuadrilla ?? 'Sin Cuadrilla' }}
                                        </div>
                                    </div>
                                    @if(auth()->user()->canManagePersonal())
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-0 px-2 flex-shrink-0"
                                                onclick="abrirProgramarPersonal({{ $p->id }}, {{ $proyPersonalId ?: 'null' }})"
                                                aria-label="Programar ciclo de turnos para {{ $p->nombre_completo }}"
                                                title="Programar ciclo de turnos para {{ $p->nombre_completo }}">
                                            <i class="bi bi-magic" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @foreach($dias as $dia)
                                @php
                                    $key = $p->id . '_' . $dia->format('Y-m-d');
                                    $turno = $turnos->get($key)?->first();
                                    $esHoy = $dia->isToday();
                                    $esFinde = in_array($dia->dayOfWeekIso, [6, 7]);

                                    $cellClass = 'text-muted';
                                    $cellLetter = '—';
                                    $cellTitle = 'Sin turno programado';

                                    if ($turno) {
                                        switch ($turno->condicion_laboral) {
                                            case 'TRABAJO_CAMPO':
                                                $cellClass = 'bg-success text-white fw-bold';
                                                $cellLetter = 'T';
                                                $cellTitle = "Trabajo en Campo ({$turno->grupo_guardia})";
                                                break;
                                            case 'BAJADA_DESCANSO':
                                                $cellClass = 'bg-warning text-dark fw-bold';
                                                $cellLetter = 'D';
                                                $cellTitle = "Bajada de Descanso 14x7 ({$turno->grupo_guardia})";
                                                break;
                                            case 'DESCANSO_CAMPAMENTO':
                                                $cellClass = 'bg-info text-white fw-bold';
                                                $cellLetter = 'C';
                                                $cellTitle = "Campamento Base ({$turno->grupo_guardia})";
                                                break;
                                            case 'PERMISO':
                                                $cellClass = 'bg-primary text-white fw-bold';
                                                $cellLetter = 'P';
                                                $cellTitle = "Permiso Justificado: {$turno->observaciones}";
                                                break;
                                            case 'LICENCIA_MEDICA':
                                                $cellClass = 'bg-danger text-white fw-bold';
                                                $cellLetter = 'M';
                                                $cellTitle = "Licencia Médica: {$turno->observaciones}";
                                                break;
                                        }
                                    }
                                @endphp
                                <td class="p-0 {{ $esHoy && !$turno ? 'bg-primary bg-opacity-10' : ($esFinde && !$turno ? 'bg-secondary bg-opacity-10' : '') }}"
                                    title="{{ $dia->format('d/m/Y') }} - {{ $cellTitle }}"
                                    @if(auth()->user()->canManagePersonal())
                                        style="cursor: pointer;"
                                        onclick="abrirTurnoManual({{ $p->id }}, {{ $proyPersonalId ?: 'null' }}, '{{ $dia->format('Y-m-d') }}', '{{ $turno?->condicion_laboral ?? 'TRABAJO_CAMPO' }}', '{{ $turno?->grupo_guardia ?? 'GUARDIA A' }}')"
                                    @endif>
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 py-2 {{ $cellClass }}" style="border-radius: 2px;">
                                        {{ $cellLetter }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($dias) + 1 }}" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                No hay trabajadores registrados para el criterio de búsqueda seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Leyenda Informativa de Códigos -->
        <div class="card-footer bg-transparent p-3 border-top d-flex gap-3 flex-wrap align-items-center">
            <span class="small fw-bold text-muted text-uppercase">Leyenda Roster:</span>
            <div class="d-flex align-items-center gap-1 small">
                <span class="badge bg-success px-2 py-1">T</span>
                <span>Trabajo en Campo</span>
            </div>
            <div class="d-flex align-items-center gap-1 small">
                <span class="badge bg-warning text-dark px-2 py-1">D</span>
                <span>Bajada de Descanso (14x7)</span>
            </div>
            <div class="d-flex align-items-center gap-1 small">
                <span class="badge bg-info text-white px-2 py-1">C</span>
                <span>Campamento Base</span>
            </div>
            <div class="d-flex align-items-center gap-1 small">
                <span class="badge bg-primary text-white px-2 py-1">P</span>
                <span>Permiso</span>
            </div>
            <div class="d-flex align-items-center gap-1 small">
                <span class="badge bg-danger text-white px-2 py-1">M</span>
                <span>Licencia Médica</span>
            </div>
            <div class="d-flex align-items-center gap-1 small text-muted">
                <span>— Sin Programar</span>
            </div>
        </div>
    </div>

    <!-- Modal 1: Programación de Ciclo por Personal -->
    <div class="modal fade" id="modalGenerarCiclo" tabindex="-1" aria-labelledby="modalGenerarCicloLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('roster.generar-ciclo') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalGenerarCicloLabel">
                            <i class="bi bi-person-gear text-primary me-2"></i> Programación de Roster por Personal
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Proyecta los turnos consecutivos de trabajo en campo y bajada de descanso para el trabajador seleccionado según su régimen laboral (14x7, 5x2, 10x10, 21x7, 6x1 o personalizado).
                        </p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="gen_proyecto_id" class="form-label small fw-bold">Proyecto Asignado <span class="text-danger">*</span></label>
                                <select name="proyecto_id" id="gen_proyecto_id" class="form-select" required>
                                    @foreach($proyectos as $pry)
                                        <option value="{{ $pry->id }}" {{ (string) $proyectoId === (string) $pry->id ? 'selected' : '' }}>
                                            [{{ $pry->codigo }}] {{ $pry->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="gen_personal_id" class="form-label small fw-bold">Trabajador a Programar <span class="text-danger">*</span></label>
                                <select name="personal_id" id="gen_personal_id" class="form-select" required>
                                    <option value="">Seleccione el trabajador del proyecto...</option>
                                    @foreach($personalModalList as $per)
                                        <option value="{{ $per->id }}"
                                                data-proyectos='@json($per->proyectos_ids)'
                                                data-proyecto-default="{{ $per->proyecto_id }}">
                                            {{ $per->apellidos }}, {{ $per->nombres }} (DNI: {{ $per->dni }}) - {{ $per->cargo }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">La programación se genera individualmente para el trabajador seleccionado.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="gen_regimen_preset" class="form-label small fw-bold">Régimen Horario / Ciclo <span class="text-danger">*</span></label>
                                <select id="gen_regimen_preset" class="form-select border-primary fw-semibold">
                                    <option value="14-7" selected>14x7 (14 Trabajo / 7 Descanso)</option>
                                    <option value="5-2">5x2 (5 Trabajo / 2 Descanso)</option>
                                    <option value="10-10">10x10 (10 Trabajo / 10 Descanso)</option>
                                    <option value="21-7">21x7 (21 Trabajo / 7 Descanso)</option>
                                    <option value="6-1">6x1 (6 Trabajo / 1 Descanso)</option>
                                    <option value="custom">Personalizado (Configurable)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="gen_grupo_guardia" class="form-label small fw-bold">Grupo / Guardia <span class="text-danger">*</span></label>
                                <select name="grupo_guardia" id="gen_grupo_guardia" class="form-select" required>
                                    <option value="GUARDIA A">GUARDIA A (Inicia en Obra)</option>
                                    <option value="GUARDIA B">GUARDIA B (Relevo)</option>
                                    <option value="GUARDIA C">GUARDIA C</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="gen_fecha_inicio" class="form-label small fw-bold">Fecha Inicio Ciclo <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_inicio" id="gen_fecha_inicio" class="form-control" value="{{ $inicioMes->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label for="gen_dias_trabajo" class="form-label small fw-bold">Días Trabajo (Campo) <span class="text-danger">*</span></label>
                                <input type="number" name="dias_trabajo" id="gen_dias_trabajo" class="form-control" value="14" min="1" max="30" required>
                            </div>

                            <div class="col-md-3">
                                <label for="gen_dias_descanso" class="form-label small fw-bold">Días Descanso (Bajada) <span class="text-danger">*</span></label>
                                <input type="number" name="dias_descanso" id="gen_dias_descanso" class="form-control" value="7" min="1" max="15" required>
                            </div>

                            <div class="col-md-3">
                                <label for="gen_ciclos" class="form-label small fw-bold">Ciclos Consecutivos <span class="text-danger">*</span></label>
                                <input type="number" name="ciclos" id="gen_ciclos" class="form-control" value="2" min="1" max="6" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-play-circle me-1"></i> Proyectar Turnos del Trabajador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Asignación Individual de Turno Puntual -->
    <div class="modal fade" id="modalTurnoManual" tabindex="-1" aria-labelledby="modalTurnoManualLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('roster.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTurnoManualLabel">
                            <i class="bi bi-calendar-event text-primary me-2"></i> Registro / Ajuste Puntual de Turno
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="manual_proyecto_id" class="form-label small fw-bold">Proyecto <span class="text-danger">*</span></label>
                            <select name="proyecto_id" id="manual_proyecto_id" class="form-select" required>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ (string) $proyectoId === (string) $pry->id ? 'selected' : '' }}>
                                        [{{ $pry->codigo }}] {{ $pry->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="manual_personal_id" class="form-label small fw-bold">Trabajador <span class="text-danger">*</span></label>
                            <select name="personal_id" id="manual_personal_id" class="form-select" required>
                                <option value="">Seleccione trabajador...</option>
                                @foreach($personalModalList as $p)
                                    <option value="{{ $p->id }}" data-proyectos='@json($p->proyectos_ids)'>
                                        {{ $p->apellidos }}, {{ $p->nombres }} (DNI: {{ $p->dni }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="manual_fecha" class="form-label small fw-bold">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="manual_fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="manual_grupo_guardia" class="form-label small fw-bold">Guardia <span class="text-danger">*</span></label>
                                <input type="text" name="grupo_guardia" id="manual_grupo_guardia" class="form-control" value="GUARDIA A" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="manual_condicion_laboral" class="form-label small fw-bold">Condición Laboral <span class="text-danger">*</span></label>
                            <select name="condicion_laboral" id="manual_condicion_laboral" class="form-select" required>
                                <option value="TRABAJO_CAMPO">TRABAJO EN CAMPO (T)</option>
                                <option value="BAJADA_DESCANSO">BAJADA DE DESCANSO (D)</option>
                                <option value="DESCANSO_CAMPAMENTO">CAMPAMENTO BASE (C)</option>
                                <option value="PERMISO">PERMISO JUSTIFICADO (P)</option>
                                <option value="LICENCIA_MEDICA">LICENCIA MÉDICA (M)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="manual_observaciones" class="form-label small fw-bold">Motivo / Observaciones</label>
                            <input type="text" name="observaciones" id="manual_observaciones" class="form-control" placeholder="Ej: Relevo por descanso programado, permiso salud...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-save me-1"></i> Guardar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function filtrarSelectPersonalPorProyecto(proyectoSelectId, personalSelectId) {
        const proySelect = document.getElementById(proyectoSelectId);
        const perSelect = document.getElementById(personalSelectId);
        if (!proySelect || !perSelect) return;

        const proyId = parseInt(proySelect.value || '0', 10);
        Array.from(perSelect.options).forEach(opt => {
            if (!opt.value) return;
            const proyectos = JSON.parse(opt.getAttribute('data-proyectos') || '[]');
            const pertenece = proyId > 0 && proyectos.includes(proyId);
            opt.hidden = !pertenece;
            opt.disabled = !pertenece;
            if (!pertenece && opt.selected) {
                opt.selected = false;
                perSelect.value = '';
            }
        });
    }

    function abrirProgramarPersonal(personalId, proyectoId) {
        const proySelect = document.getElementById('gen_proyecto_id');
        const perSelect = document.getElementById('gen_personal_id');
        if (proySelect && proyectoId) {
            proySelect.value = String(proyectoId);
        }
        filtrarSelectPersonalPorProyecto('gen_proyecto_id', 'gen_personal_id');
        if (perSelect) {
            perSelect.value = personalId ? String(personalId) : '';
        }
        const modalEl = document.getElementById('modalGenerarCiclo');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function abrirTurnoManual(personalId, proyectoId, fecha, condicion, guardia) {
        const proySelect = document.getElementById('manual_proyecto_id');
        const perSelect = document.getElementById('manual_personal_id');
        const fechaInput = document.getElementById('manual_fecha');
        const condSelect = document.getElementById('manual_condicion_laboral');
        const guardiaInput = document.getElementById('manual_grupo_guardia');

        if (proySelect && proyectoId) {
            proySelect.value = String(proyectoId);
        }
        filtrarSelectPersonalPorProyecto('manual_proyecto_id', 'manual_personal_id');
        if (perSelect && personalId) {
            perSelect.value = String(personalId);
        }
        if (fechaInput && fecha) {
            fechaInput.value = fecha;
        }
        if (condSelect && condicion) {
            condSelect.value = condicion;
        }
        if (guardiaInput && guardia) {
            guardiaInput.value = guardia;
        }
        const modalEl = document.getElementById('modalTurnoManual');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const genProy = document.getElementById('gen_proyecto_id');
        const manProy = document.getElementById('manual_proyecto_id');

        if (genProy) {
            genProy.addEventListener('change', () => filtrarSelectPersonalPorProyecto('gen_proyecto_id', 'gen_personal_id'));
            filtrarSelectPersonalPorProyecto('gen_proyecto_id', 'gen_personal_id');
        }
        if (manProy) {
            manProy.addEventListener('change', () => filtrarSelectPersonalPorProyecto('manual_proyecto_id', 'manual_personal_id'));
            filtrarSelectPersonalPorProyecto('manual_proyecto_id', 'manual_personal_id');
        }

        const regimenPreset = document.getElementById('gen_regimen_preset');
        const inputTrabajo = document.getElementById('gen_dias_trabajo');
        const inputDescanso = document.getElementById('gen_dias_descanso');
        const inputCiclos = document.getElementById('gen_ciclos');

        if (regimenPreset && inputTrabajo && inputDescanso) {
            regimenPreset.addEventListener('change', () => {
                const val = regimenPreset.value;
                if (val !== 'custom') {
                    const [trab, desc] = val.split('-').map(Number);
                    inputTrabajo.value = trab;
                    inputDescanso.value = desc;
                    if (val === '5-2' || val === '6-1') {
                        inputCiclos.value = 4;
                    } else {
                        inputCiclos.value = 2;
                    }
                }
            });
        }
    });
</script>
@endpush
