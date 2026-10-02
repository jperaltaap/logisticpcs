@extends('layouts.admin')

@section('title', 'Gestión de Cuadrillas de Trabajo')
@section('page_title', 'Cuadrillas & Frentes de Obra')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Cuadrillas</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Tarjetas de Resumen Operativo -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Total Cuadrillas</span>
                    <h3 class="fw-bold text-heading mb-0 mt-1">{{ $stats['total'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Activas en Campo</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['activas'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">En Relevo / Descanso</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ $stats['en_descanso'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-warning text-dark">
                    <i class="bi bi-pause-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Disueltas / Fin Obra</span>
                    <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $stats['disueltas'] }}</h3>
                </div>
                <div class="gradient-icon-box bg-secondary">
                    <i class="bi bi-archive"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('cuadrillas.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <label for="filtro_cuad_search" class="visually-hidden">Buscar cuadrilla por código, nombre o líder</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted" aria-hidden="true"></i>
                    </span>
                    <input type="text" id="filtro_cuad_search" name="search" class="form-control border-start-0" placeholder="Buscar por código, nombre o líder..." value="{{ $search }}" aria-label="Buscar por código, nombre o líder">
                </div>
            </div>

            <div class="col-md-3">
                <label for="filtro_cuad_estado" class="visually-hidden">Estado de la cuadrilla</label>
                <select id="filtro_cuad_estado" name="estado" class="form-select" onchange="this.form.submit()" aria-label="Filtrar por estado de la cuadrilla">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVA" {{ $estado === 'ACTIVA' ? 'selected' : '' }}>ACTIVA</option>
                    <option value="EN_DESCANSO" {{ $estado === 'EN_DESCANSO' ? 'selected' : '' }}>EN DESCANSO</option>
                    <option value="DISUELTA" {{ $estado === 'DISUELTA' ? 'selected' : '' }}>DISUELTA</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtrar
                </button>
                @if($search || $estado)
                    <a href="{{ route('cuadrillas.index') }}" class="btn btn-outline-secondary" aria-label="Limpiar filtros de cuadrillas" title="Limpiar filtros">
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                    </a>
                @endif
                @if(auth()->user()->canManagePersonal())
                <a href="{{ route('cuadrillas.create') }}" class="btn btn-primary text-nowrap">
                    <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Nueva Cuadrilla
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Cuadrillas -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla de cuadrillas de trabajo">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3" style="width: 130px;">Código</th>
                        <th scope="col">Nombre de Cuadrilla</th>
                        <th scope="col">Proyecto Asignado</th>
                        <th scope="col">Líder / Responsable</th>
                        <th scope="col" class="text-center">Dotación</th>
                        <th scope="col" class="text-center">Régimen</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuadrillas as $cuad)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                <a href="{{ route('cuadrillas.show', $cuad) }}" class="text-decoration-none">
                                    {{ $cuad->codigo_cuadrilla }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">
                                    <a href="{{ route('cuadrillas.show', $cuad) }}" class="text-decoration-none text-heading">
                                        {{ $cuad->nombre }}
                                    </a>
                                </div>
                                <div class="small text-muted">
                                    {{ Str::limit($cuad->observaciones, 65) ?: 'Sin observaciones' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">
                                    <i class="bi bi-buildings text-primary me-1"></i>
                                    <a href="{{ route('proyectos.show', $cuad->proyecto) }}" class="text-decoration-none">
                                        {{ $cuad->proyecto->nombre }}
                                    </a>
                                </div>
                                <div class="small text-muted font-monospace">{{ $cuad->proyecto->codigo }}</div>
                            </td>
                            <td>
                                @if($cuad->lider)
                                    <div class="fw-semibold text-heading">
                                        <i class="bi bi-person-badge text-primary me-1"></i>
                                        <a href="{{ route('personal.show', $cuad->lider) }}" class="text-decoration-none">
                                            {{ $cuad->lider->nombre_completo }}
                                        </a>
                                    </div>
                                    <div class="small text-muted font-monospace">DNI: {{ $cuad->lider->dni }}</div>
                                @else
                                    <span class="small text-muted italic">Sin líder asignado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-info" title="Técnicos miembros">
                                    <i class="bi bi-people"></i> {{ $cuad->miembros_count }} miembros
                                </span>
                                @if($cuad->activos_asignados_count > 0)
                                    <span class="badge badge-soft-primary ms-1" title="Activos en custodia">
                                        <i class="bi bi-tools"></i> {{ $cuad->activos_asignados_count }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-secondary font-monospace">
                                    {{ $cuad->regimen_laboral }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $estClass = match($cuad->estado) {
                                        'ACTIVA' => 'badge-soft-success',
                                        'EN_DESCANSO' => 'badge-soft-warning',
                                        'DISUELTA' => 'badge-soft-secondary',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $estClass }}">
                                    {{ str_replace('_', ' ', $cuad->estado) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('cuadrillas.show', $cuad) }}" class="btn btn-outline-secondary" aria-label="Ver ficha y miembros de la cuadrilla {{ $cuad->nombre }}" title="Ver Ficha y Miembros">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    @if(auth()->user()->canManagePersonal())
                                    <a href="{{ route('cuadrillas.edit', $cuad) }}" class="btn btn-outline-primary" aria-label="Editar cuadrilla {{ $cuad->nombre }}" title="Editar Cuadrilla">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('cuadrillas.destroy', $cuad) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar la cuadrilla {{ $cuad->codigo_cuadrilla }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" aria-label="Eliminar cuadrilla {{ $cuad->codigo_cuadrilla }}" title="Eliminar">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                    No se encontraron cuadrillas de trabajo registradas con los criterios seleccionados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cuadrillas->hasPages())
            <div class="p-3 border-top">
                {{ $cuadrillas->links() }}
            </div>
        @endif
    </div>

@endsection
