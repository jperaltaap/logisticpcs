@extends('layouts.admin')

@section('title', 'Ficha de ' . $personal->nombre_completo)
@section('page_title', 'Ficha del Trabajador: ' . $personal->nombre_completo)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('personal.index') }}" class="text-decoration-none fw-semibold text-primary">Personal</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $personal->dni }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->canManagePersonal())
    <a href="{{ route('personal.edit', $personal) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-pencil me-1"></i> Editar Ficha
    </a>
    @endif
    <a href="{{ route('personal.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Volver al Padrón
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Profile Header Card -->
    <div class="admin-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow" style="width: 64px; height: 64px; font-size: 1.6rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                        {{ strtoupper(substr($personal->nombres, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold mb-0 text-heading">{{ $personal->nombre_completo }}</h3>
                            @if($personal->estado === 'ACTIVO')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i> ACTIVO
                                </span>
                            @elseif($personal->estado === 'VACACIONES')
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    VACACIONES
                                </span>
                            @elseif($personal->estado === 'DESCANSO_MEDICO')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    DESCANSO MÉDICO
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    CESADO
                                </span>
                            @endif
                        </div>
                        <div class="text-muted fw-semibold mb-2">
                            <span><i class="bi bi-briefcase me-1"></i>{{ $personal->cargo }}</span>
                            <span class="mx-2">•</span>
                            <span><i class="bi bi-diagram-2 me-1"></i>Área: {{ $personal->area }}</span>
                        </div>
                        <div class="small text-muted d-flex flex-wrap gap-3">
                            <span><i class="bi bi-person-vcard me-1"></i><strong>DNI/CE:</strong> {{ $personal->dni }}</span>
                            @if($personal->codigo_fotocheck)
                                <span><i class="bi bi-badge-ad me-1"></i><strong>Fotocheck:</strong> {{ $personal->codigo_fotocheck }}</span>
                            @endif
                            @if($personal->codigo_trabajador)
                                <span><i class="bi bi-hash me-1"></i><strong>Cód. Interno:</strong> {{ $personal->codigo_trabajador }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 border-start ps-lg-4">
                <div class="mb-2">
                    <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Proyecto Asignado</span>
                    @if($personal->proyecto)
                        <a href="{{ route('proyectos.show', $personal->proyecto) }}" class="fw-bold text-decoration-none text-primary fs-6">
                            {{ $personal->proyecto->codigo }} — {{ $personal->proyecto->nombre }}
                        </a>
                    @else
                        <span class="text-muted fst-italic">Sin proyecto asignado</span>
                    @endif
                </div>
                <div>
                    <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Acceso a Plataforma Web</span>
                    @if($personal->user)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> {{ $personal->user->email }} ({{ $personal->user->rol }})
                        </span>
                    @else
                        <span class="badge bg-body-secondary text-muted border">
                            Sin cuenta web (Solo personal físico)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Counters -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="admin-card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Activos en Custodia</div>
                <h3 class="fw-bold mb-0 text-heading">{{ $personal->activos_asignados_count }}</h3>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="admin-card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Cuadrillas de Pertenencia</div>
                <h3 class="fw-bold mb-0 text-heading">{{ $personal->cuadrillas_count }}</h3>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="admin-card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Historial de Despachos</div>
                <h3 class="fw-bold mb-0 text-heading">{{ $personal->despachos_count }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tools / Serialized Assets Custody -->
        <div class="col-lg-7">
            <div class="admin-card overflow-hidden h-100">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-tools text-primary me-2"></i> Herramientas & Activos a su Cargo
                    </h5>
                    <span class="badge bg-body-secondary text-body border">{{ $personal->activosAsignados->count() }} ítems</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-glass align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cód. QR / Patrimonial</th>
                                <th>Artículo / Herramienta</th>
                                <th>N° de Serie</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($personal->activosAsignados as $activo)
                                <tr>
                                    <td>
                                        <span class="badge bg-body-secondary text-body border font-monospace">
                                            {{ $activo->codigo_patrimonial }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-heading small">{{ $activo->articulo?->nombre }}</div>
                                        <small class="text-muted">{{ $activo->articulo?->modelo }}</small>
                                    </td>
                                    <td class="font-monospace small text-muted">{{ $activo->numero_serie ?: 'S/N' }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                            {{ $activo->estado_operativo }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        El trabajador no mantiene herramientas ni equipos serializados en préstamo directo actualmente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Contact & Workgroups -->
        <div class="col-lg-5">
            <div class="admin-card p-4 h-100">
                <h6 class="fw-bold mb-3 text-heading">
                    <i class="bi bi-telephone-inbound text-primary me-2"></i> Canales de Contacto
                </h6>
                <div class="p-3 bg-body-tertiary rounded-3 border mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-telephone text-primary"></i>
                        <span class="fw-semibold">Teléfono:</span>
                        <span class="text-muted">{{ $personal->telefono ?: 'No registrado' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-primary"></i>
                        <span class="fw-semibold">Correo:</span>
                        <span class="text-muted">{{ $personal->correo ?: 'No registrado' }}</span>
                    </div>
                </div>

                <h6 class="fw-bold mb-2 text-heading">
                    <i class="bi bi-diagram-3-fill text-warning me-2"></i> Cuadrillas de Trabajo
                </h6>
                <ul class="list-group list-group-flush small mb-3">
                    @forelse($personal->cuadrillas as $cuad)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <div>
                                <strong class="text-heading">{{ $cuad->codigo }}</strong> — {{ $cuad->nombre }}
                                <div class="text-muted" style="font-size: 0.72rem;">Rol: {{ $cuad->pivot->rol_en_cuadrilla ?? 'Técnico' }}</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">{{ $cuad->estado }}</span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 bg-transparent text-muted fst-italic">
                            No está asignado a ninguna cuadrilla activa.
                        </li>
                    @endforelse
                </ul>

                <div class="border-top pt-3 small text-muted">
                    <div><strong>Fecha de Alta:</strong> {{ $personal->created_at?->format('d/m/Y H:i') }}</div>
                    <div><strong>Última Modificación:</strong> {{ $personal->updated_at?->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection
