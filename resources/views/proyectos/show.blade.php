@extends('layouts.admin')

@section('title', 'Detalle de Proyecto ' . $proyecto->codigo)
@section('page_title', 'Proyecto: ' . $proyecto->nombre)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('proyectos.index') }}" class="text-decoration-none fw-semibold text-primary">Proyectos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $proyecto->codigo }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('proyectos.edit', $proyecto) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-pencil me-1"></i> Editar Proyecto
    </a>
    <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Volver al Listado
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Project Header Card -->
    <div class="admin-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-body-secondary text-body border font-monospace px-2 py-1 fs-6">
                        {{ $proyecto->codigo }}
                    </span>
                    @if($proyecto->estado === 'ACTIVO')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> ACTIVO
                        </span>
                    @elseif($proyecto->estado === 'SUSPENDIDO')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-pause-circle me-1"></i> SUSPENDIDO
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill fw-semibold">
                            FINALIZADO
                        </span>
                    @endif
                </div>
                <h3 class="fw-bold mb-1 text-heading">{{ $proyecto->nombre }}</h3>
                <p class="text-muted mb-2"><i class="bi bi-building me-1"></i><strong>Cliente:</strong> {{ $proyecto->cliente }}</p>
                <div class="small text-muted d-flex flex-wrap gap-3">
                    <span><i class="bi bi-geo-alt me-1 text-danger"></i>{{ $proyecto->ubicacion_direccion }}</span>
                    <span><i class="bi bi-calendar-check me-1 text-primary"></i>Inicio: {{ $proyecto->fecha_inicio?->format('d/m/Y') }}</span>
                    @if($proyecto->fecha_fin_estimada)
                        <span><i class="bi bi-calendar-x me-1 text-warning"></i>Fin Estimado: {{ $proyecto->fecha_fin_estimada->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 border-start ps-lg-4">
                <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.72rem;">Responsables / Residentes a Cargo</div>
                @php
                    $listaResponsables = $proyecto->responsables->isNotEmpty()
                        ? $proyecto->responsables
                        : ($proyecto->responsable ? collect([$proyecto->responsable]) : collect());
                @endphp
                @if($listaResponsables->isNotEmpty())
                    <div class="d-flex flex-column gap-2 align-items-lg-end">
                        @foreach($listaResponsables as $resp)
                            <div class="d-flex align-items-center justify-content-lg-end gap-2">
                                <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.85rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                                    {{ strtoupper(substr($resp->nombres, 0, 1)) }}
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-heading small">
                                        {{ $resp->nombre_completo }}
                                        @if($proyecto->responsable_personal_id === $resp->id)
                                            <span class="badge badge-soft-primary ms-1" style="font-size: 0.62rem;">Titular</span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $resp->cargo }} · DNI: {{ $resp->dni }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted fst-italic">Sin responsable asignado</span>
                @endif
            </div>
        </div>
    </div>

    <!-- 4 Stats Counters -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-uppercase text-muted">Personal Asignado</span>
                    <i class="bi bi-people-fill text-primary fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $proyecto->personal_count }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-uppercase text-muted">Cuadrillas Operativas</span>
                    <i class="bi bi-diagram-3-fill text-warning fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $proyecto->cuadrillas_count }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-uppercase text-muted">Activos en Custodia</span>
                    <i class="bi bi-tools text-info fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $proyecto->activos_count }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-uppercase text-muted">Despachos Imputados</span>
                    <i class="bi bi-receipt text-success fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $proyecto->despachos_count }}</h3>
            </div>
        </div>
    </div>

    <!-- Associated Personnel Table -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card overflow-hidden">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i> Personal Asignado a Obra
                    </h5>
                    <a href="{{ route('personal.create', ['proyecto_id' => $proyecto->id]) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Asignar Personal
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-glass align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fotocheck / DNI</th>
                                <th>Nombre Completo</th>
                                <th>Cargo & Área</th>
                                <th>Estado Laboral</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proyecto->personal as $pers)
                                <tr>
                                    <td>
                                        <div class="font-monospace fw-bold small">{{ $pers->codigo_fotocheck ?? 'S/F' }}</div>
                                        <small class="text-muted">DNI: {{ $pers->dni }}</small>
                                    </td>
                                    <td class="fw-bold">
                                        <a href="{{ route('personal.show', $pers) }}" class="text-decoration-none text-heading">
                                            {{ $pers->nombre_completo }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $pers->cargo }}</div>
                                        <small class="text-muted">{{ $pers->area }}</small>
                                    </td>
                                    <td>
                                        @if($pers->estado === 'ACTIVO')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">ACTIVO</span>
                                        @elseif($pers->estado === 'VACACIONES')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small">VACACIONES</span>
                                        @elseif($pers->estado === 'DESCANSO_MEDICO')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small">DESCANSO</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill small">CESADO</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('personal.show', $pers) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No hay personal asignado actualmente a este proyecto.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Details / Observaciones -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <h6 class="fw-bold mb-3 text-heading">
                    <i class="bi bi-info-circle text-primary me-2"></i> Observaciones Técnicas
                </h6>
                <div class="p-3 bg-body-tertiary rounded-3 border mb-3">
                    <p class="small text-muted mb-0">
                        {{ $proyecto->observaciones ?: 'Sin observaciones adicionales registradas.' }}
                    </p>
                </div>

                <h6 class="fw-bold mb-2 text-heading">
                    <i class="bi bi-diagram-3 text-warning me-2"></i> Cuadrillas Asignadas
                </h6>
                <ul class="list-group list-group-flush small mb-3">
                    @forelse($proyecto->cuadrillas as $cuad)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <div>
                                <strong class="text-heading">{{ $cuad->codigo }}</strong> — {{ $cuad->nombre }}
                                <div class="text-muted" style="font-size: 0.72rem;">Líder: {{ $cuad->lider?->nombre_completo ?? 'Sin asignar' }}</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">{{ $cuad->estado }}</span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 bg-transparent text-muted fst-italic">
                            No hay cuadrillas registradas en este proyecto.
                        </li>
                    @endforelse
                </ul>

                <div class="border-top pt-3 small text-muted">
                    <div><strong>Creado:</strong> {{ $proyecto->created_at?->format('d/m/Y H:i') }}</div>
                    <div><strong>Última actualización:</strong> {{ $proyecto->updated_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection
