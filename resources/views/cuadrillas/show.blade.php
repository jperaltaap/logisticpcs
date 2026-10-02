@extends('layouts.admin')

@section('title', 'Cuadrilla ' . $cuadrilla->codigo_cuadrilla . ' - ' . $cuadrilla->nombre)
@section('page_title', 'Frente de Obra: ' . $cuadrilla->nombre)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cuadrillas.index') }}" class="text-decoration-none fw-semibold text-primary">Cuadrillas</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $cuadrilla->codigo_cuadrilla }}</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Encabezado de la Cuadrilla -->
    <div class="row g-4 mb-4">
        <!-- Resumen General -->
        <div class="col-lg-7">
            <div class="admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge badge-soft-primary font-monospace fs-6 px-2 py-1">
                            {{ $cuadrilla->codigo_cuadrilla }}
                        </span>
                        <span class="badge badge-soft-secondary font-monospace ms-2">
                            Régimen {{ $cuadrilla->regimen_laboral }}
                        </span>
                    </div>
                    @php
                        $estClass = match($cuadrilla->estado) {
                            'ACTIVA' => 'badge-soft-success',
                            'EN_DESCANSO' => 'badge-soft-warning',
                            'DISUELTA' => 'badge-soft-secondary',
                            default => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $estClass }} px-3 py-2 fw-bold">
                        {{ str_replace('_', ' ', $cuadrilla->estado) }}
                    </span>
                </div>

                <h4 class="fw-bold text-heading mb-2">{{ $cuadrilla->nombre }}</h4>

                <p class="small text-muted mb-4">
                    {{ $cuadrilla->observaciones ?: 'Sin observaciones operativas registradas.' }}
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Proyecto Asignado:</label>
                        <div class="fw-bold text-heading fs-6">
                            <i class="bi bi-buildings text-primary me-1"></i>
                            <a href="{{ route('proyectos.show', $cuadrilla->proyecto) }}" class="text-decoration-none">
                                {{ $cuadrilla->proyecto->nombre }}
                            </a>
                        </div>
                        <span class="badge badge-soft-secondary font-monospace mt-1">{{ $cuadrilla->proyecto->codigo }}</span>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Dotación Actual:</label>
                        <div class="fw-bold text-heading fs-6">
                            <i class="bi bi-people text-info me-1"></i>
                            {{ $cuadrilla->miembrosPivot->whereNull('fecha_retiro')->count() }} Técnicos Activos
                        </div>
                        <div class="small text-muted">Histórico: {{ $cuadrilla->miembrosPivot->count() }} personas</div>
                    </div>
                </div>

                <div class="d-flex gap-2 pt-4 border-top mt-4 flex-wrap">
                    <a href="{{ route('reportes.cuadrilla.pdf', $cuadrilla) }}" class="btn btn-outline-danger btn-sm fw-bold shadow-sm" title="Descargar Acta y Hoja de Cargo Oficial">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Hoja de Cargo PDF
                    </a>
                    @if(auth()->user()->canManagePersonal())
                    <a href="{{ route('cuadrillas.edit', $cuadrilla) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Editar Cuadrilla
                    </a>
                    @endif
                    <a href="{{ route('roster.index', ['cuadrilla_id' => $cuadrilla->id, 'proyecto_id' => $cuadrilla->proyecto_id]) }}" class="btn btn-outline-warning text-dark btn-sm fw-bold">
                        <i class="bi bi-calendar3 me-1"></i> Ver Roster 14x7
                    </a>
                    @if(auth()->user()->rol !== 'AUDITOR')
                    <a href="{{ route('despachos.create', ['cuadrilla_id' => $cuadrilla->id, 'proyecto_id' => $cuadrilla->proyecto_id]) }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Despachar a esta Cuadrilla
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ficha del Líder de Cuadrilla -->
        <div class="col-lg-5">
            <div class="admin-card p-4 h-100">
                <h6 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                    <i class="bi bi-person-badge-fill me-2 text-primary"></i> Líder / Supervisor a Cargo
                </h6>

                @if($cuadrilla->lider)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-heading mb-0">
                                <a href="{{ route('personal.show', $cuadrilla->lider) }}" class="text-decoration-none">
                                    {{ $cuadrilla->lider->nombre_completo }}
                                </a>
                            </h5>
                            <span class="badge badge-soft-info">{{ $cuadrilla->lider->cargo }}</span>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Documento (DNI/CE):</span>
                            <span class="fw-bold font-monospace text-heading">{{ $cuadrilla->lider->dni }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Fotocheck:</span>
                            <span class="font-monospace text-heading">{{ $cuadrilla->lider->codigo_fotocheck ?: 'No asignado' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Teléfono de Contacto:</span>
                            <span class="fw-semibold text-heading">{{ $cuadrilla->lider->telefono ?: 'No registrado' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Estado del Líder:</span>
                            <span class="badge {{ $cuadrilla->lider->estado === 'ACTIVO' ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                {{ $cuadrilla->lider->estado }}
                            </span>
                        </li>
                    </ul>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                        No se ha asignado un líder para esta cuadrilla.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Gestión de Miembros de la Cuadrilla -->
    <div class="admin-card mb-4 overflow-hidden">
        <div class="card-header bg-transparent p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-people me-2 text-primary"></i> Dotación de Personal & Roles
            </h5>
            @if(auth()->user()->canManagePersonal())
            <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddMiembro">
                <i class="bi bi-person-plus me-1"></i> Incorporar Técnico
            </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Técnico / Trabajador</th>
                        <th>Documento</th>
                        <th>Rol en la Cuadrilla</th>
                        <th>Fecha de Ingreso</th>
                        <th>Fecha de Retiro</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuadrilla->miembrosPivot as $miembro)
                        @php
                            $estaActivo = is_null($miembro->fecha_retiro);
                        @endphp
                        <tr class="{{ $estaActivo ? '' : 'opacity-75 bg-light bg-opacity-25' }}">
                            <td class="ps-3">
                                <div class="fw-bold text-heading">
                                    <a href="{{ route('personal.show', $miembro->personal) }}" class="text-decoration-none">
                                        {{ $miembro->personal->nombre_completo }}
                                    </a>
                                </div>
                                <div class="small text-muted">{{ $miembro->personal->cargo }}</div>
                            </td>
                            <td class="font-monospace text-heading">
                                {{ $miembro->personal->dni }}
                            </td>
                            <td>
                                <span class="badge {{ str_contains(strtoupper($miembro->rol_en_cuadrilla), 'LIDER') ? 'badge-soft-primary' : 'badge-soft-info' }}">
                                    {{ $miembro->rol_en_cuadrilla }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $miembro->fecha_incorporacion?->format('d/m/Y') }}
                            </td>
                            <td class="small text-muted">
                                {{ $miembro->fecha_retiro?->format('d/m/Y') ?: '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $estaActivo ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                    {{ $estaActivo ? 'ACTIVO' : 'RETIRADO' }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                @if($estaActivo && auth()->user()->canManagePersonal())
                                    <form action="{{ route('cuadrillas.miembros.retirar', [$cuadrilla, $miembro->personal]) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Registrar el retiro de {{ $miembro->personal->nombre_completo }} de esta cuadrilla?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Retirar de Cuadrilla">
                                            <i class="bi bi-person-dash me-1"></i> Retirar
                                        </button>
                                    </form>
                                @elseif($estaActivo)
                                    <span class="badge badge-soft-success">Activo</span>
                                @else
                                    <span class="small text-muted italic">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No hay miembros registrados en esta cuadrilla.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activos Asignados & Despachos de la Cuadrilla -->
    <div class="row g-4">
        <!-- Activos en custodia actual -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden">
                <div class="card-header bg-transparent p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-tools me-2 text-warning"></i> Activos Serializados en Custodia ({{ $cuadrilla->activosAsignados->count() }})
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Código QR</th>
                                <th>Descripción</th>
                                <th>Serie</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuadrilla->activosAsignados as $act)
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-primary">
                                        <a href="{{ route('activos.show', $act) }}" class="text-decoration-none">
                                            {{ $act->codigo_interno }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $act->articulo->descripcion }}</div>
                                        <div class="small text-muted">{{ $act->articulo->marca ?? 'S/M' }}</div>
                                    </td>
                                    <td class="font-monospace small text-muted">
                                        {{ $act->numero_serie ?: 'S/N' }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('activos.show', $act) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Ver Activo">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        Esta cuadrilla no mantiene activos asignados actualmente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vales de Despacho Recientes -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden">
                <div class="card-header bg-transparent p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-box-arrow-right me-2 text-primary"></i> Vales de Despacho Emitidos ({{ $cuadrilla->despachos->count() }})
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">N° Guía</th>
                                <th>Fecha</th>
                                <th>Almacén</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuadrilla->despachos as $dsp)
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-primary">
                                        <a href="{{ route('despachos.show', $dsp) }}" class="text-decoration-none">
                                            {{ $dsp->numero_guia }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $dsp->fecha_despacho?->format('d/m/Y') }}
                                    </td>
                                    <td class="small">
                                        {{ $dsp->ubicacionOrigen->nombre ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $dsp->estado === 'ENTREGADO_EN_CAMPO' ? 'badge-soft-primary' : 'badge-soft-success' }}">
                                            {{ str_replace('_', ' ', $dsp->estado) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('despachos.acta', $dsp) }}" target="_blank" class="btn btn-outline-dark btn-sm py-0 px-2" title="Imprimir Acta A4">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        No registra vales de despacho emitidos a este frente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Incorporar Técnico Miembro -->
    <div class="modal fade" id="modalAddMiembro" tabindex="-1" aria-labelledby="modalAddMiembroLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('cuadrillas.miembros.add', $cuadrilla) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalAddMiembroLabel">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i> Incorporar Técnico a la Cuadrilla
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modal_personal_id" class="form-label small fw-bold">Trabajador / Técnico <span class="text-danger">*</span></label>
                            <select name="personal_id" id="modal_personal_id" class="form-select" required>
                                <option value="">Seleccione personal disponible...</option>
                                @foreach($personalDisponible as $per)
                                    <option value="{{ $per->id }}">
                                        {{ $per->apellidos }}, {{ $per->nombres }} (DNI: {{ $per->dni }}) - Cargo: {{ $per->cargo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="modal_rol_en_cuadrilla" class="form-label small fw-bold">Rol en la Cuadrilla <span class="text-danger">*</span></label>
                            <input type="text" name="rol_en_cuadrilla" id="modal_rol_en_cuadrilla" class="form-control" list="rolesSugeridos" placeholder="Ej: TECNICO EMPALMADOR" value="TECNICO" required>
                            <datalist id="rolesSugeridos">
                                <option value="LIDER DE CUADRILLA">
                                <option value="CAPATAZ DE OBRA">
                                <option value="TECNICO EMPALMADOR / FUSIONADOR">
                                <option value="TECNICO TENDIDO AEREO">
                                <option value="TECNICO CANALIZACION">
                                <option value="CHOFER OPERADOR">
                                <option value="AYUDANTE DE CAMPO">
                                <option value="PREVENCIONISTA / SEGURISTA">
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="modal_fecha_incorporacion" class="form-label small fw-bold">Fecha de Incorporación <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_incorporacion" id="modal_fecha_incorporacion" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Incorporar a Cuadrilla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
