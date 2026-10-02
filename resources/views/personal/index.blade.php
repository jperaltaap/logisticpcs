@extends('layouts.admin')

@section('title', 'Padrón de Personal')
@section('page_title', 'Fichas de Personal & Control de Cuadrillas')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Campo & Cuadrillas</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Ficha de Personal</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->canManagePersonal())
    <a href="{{ route('personal.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-person-plus-fill me-1"></i> Registrar Personal
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Mini KPIs Header -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Total Personal</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Activos en Obra</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['activos'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Vacaciones / Descanso</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['vacaciones'] + $stats['descanso'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Cesados</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-muted">{{ $stats['cesados'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('personal.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Buscar por DNI, nombre, fotocheck, cargo o área...">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="cuadrilla_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todas las cuadrillas --</option>
                    @foreach($cuadrillas ?? [] as $cuad)
                        <option value="{{ $cuad->id }}" {{ (string) ($cuadrillaId ?? '') === (string) $cuad->id ? 'selected' : '' }}>
                            {{ $cuad->codigo_cuadrilla }} - {{ $cuad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los estados --</option>
                    <option value="ACTIVO" {{ $estado === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                    <option value="VACACIONES" {{ $estado === 'VACACIONES' ? 'selected' : '' }}>VACACIONES</option>
                    <option value="DESCANSO_MEDICO" {{ $estado === 'DESCANSO_MEDICO' ? 'selected' : '' }}>DESCANSO MÉDICO</option>
                    <option value="CESADO" {{ $estado === 'CESADO' ? 'selected' : '' }}>CESADO</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if ($search || $estado || !empty($cuadrillaId))
                    <a href="{{ route('personal.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Personal Table Card -->
    <div class="admin-card overflow-hidden mb-4">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-person-badge text-primary me-2"></i> Padrón de Trabajadores
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                {{ $personal->total() }} Registros
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-glass align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 140px;">Fotocheck / DNI</th>
                        <th>Nombres y Apellidos</th>
                        <th>Cargo & Área</th>
                        <th>Proyectos Asignados</th>
                        <th>Acceso Web</th>
                        <th class="text-center" style="width: 120px;">Estado</th>
                        <th class="text-end" style="width: 130px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personal as $p)
                        <tr>
                            <td>
                                <div class="font-monospace fw-bold text-heading small">{{ $p->codigo_fotocheck ?: 'S/F' }}</div>
                                <small class="text-muted">DNI: {{ $p->dni }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                                        {{ strtoupper(substr($p->nombres, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('personal.show', $p) }}" class="fw-bold text-decoration-none text-heading d-block">
                                            {{ $p->nombre_completo }}
                                        </a>
                                        @if($p->telefono)
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $p->telefono }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold small text-heading">{{ $p->cargo }}</div>
                                <small class="text-muted">{{ $p->area }}</small>
                            </td>
                            <td>
                                @php
                                    $proyectosPersonal = $p->proyectos->isNotEmpty()
                                        ? $p->proyectos
                                        : ($p->proyecto ? collect([$p->proyecto]) : collect());
                                @endphp
                                @if($proyectosPersonal->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($proyectosPersonal as $proyItem)
                                            <a href="{{ route('proyectos.show', $proyItem) }}" class="text-decoration-none badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold" title="{{ $proyItem->nombre }}">
                                                {{ $proyItem->codigo }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Sin proyecto asignado</span>
                                @endif
                            </td>
                            <td>
                                @if($p->user)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle small fw-semibold" title="Usuario con acceso al sistema: {{ $p->user->email }}">
                                        <i class="bi bi-person-check-fill me-1"></i> {{ $p->user->rol }}
                                    </span>
                                @else
                                    <span class="badge bg-body-secondary text-muted border small" title="Solo trabajador físico de campo">
                                        Sin cuenta web
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->estado === 'ACTIVO')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> ACTIVO
                                    </span>
                                @elseif($p->estado === 'VACACIONES')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-sun me-1"></i> VACACIONES
                                    </span>
                                @elseif($p->estado === 'DESCANSO_MEDICO')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-heart-pulse me-1"></i> DESCANSO
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold">
                                        CESADO
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('personal.show', $p) }}" class="btn btn-outline-secondary" title="Ver Legajo">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->canManagePersonal())
                                    <a href="{{ route('personal.edit', $p) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('personal.destroy', $p) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de dar de baja a {{ $p->nombre_completo }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Dar de baja">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-muted"></i>
                                No se encontraron registros de personal con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($personal->hasPages())
            <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-end">
                {{ $personal->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection
