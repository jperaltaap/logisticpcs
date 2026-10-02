@extends('layouts.admin')

@section('title', 'Proyectos & Frentes de Obra')
@section('page_title', 'Gestión de Proyectos & Centros de Costo')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración Inicial</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Gestión de Proyectos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->rol !== 'AUDITOR')
    <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Proyecto
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
                    <span class="small fw-bold text-uppercase text-muted">Total Proyectos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-buildings"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Proyectos Activos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['activos'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Suspendidos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ $stats['suspendidos'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Finalizados</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-muted">{{ $stats['finalizados'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('proyectos.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-7">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Buscar por código, nombre, cliente o ubicación...">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los estados --</option>
                    <option value="ACTIVO" {{ $estado === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                    <option value="SUSPENDIDO" {{ $estado === 'SUSPENDIDO' ? 'selected' : '' }}>SUSPENDIDO</option>
                    <option value="FINALIZADO" {{ $estado === 'FINALIZADO' ? 'selected' : '' }}>FINALIZADO</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if ($search || $estado)
                    <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Proyectos Table Card -->
    <div class="admin-card overflow-hidden mb-4">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-folder-check text-primary me-2"></i> Listado de Proyectos (Sucursales Operativas)
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                {{ $proyectos->total() }} Registros
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-glass align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 120px;">Código</th>
                        <th>Proyecto & Cliente</th>
                        <th>Ubicación</th>
                        <th>Fechas</th>
                        <th>Responsables a Cargo</th>
                        <th>Recursos</th>
                        <th class="text-center" style="width: 110px;">Estado</th>
                        <th class="text-end" style="width: 130px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proyectos as $pry)
                        @php
                            $listaResponsables = $pry->responsables->isNotEmpty()
                                ? $pry->responsables
                                : ($pry->responsable ? collect([$pry->responsable]) : collect());
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-body-secondary text-body border font-monospace fw-bold">
                                    {{ $pry->codigo }}
                                </span>
                                @if(session('proyecto_activo_id') == $pry->id)
                                    <span class="badge bg-primary text-white d-block mt-1" style="font-size: 0.62rem;">EN SESIÓN</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('proyectos.show', $pry) }}" class="fw-bold text-decoration-none text-heading d-block">
                                    {{ $pry->nombre }}
                                </a>
                                <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $pry->cliente }}</small>
                            </td>
                            <td>
                                <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="{{ $pry->ubicacion_direccion }}">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $pry->ubicacion_direccion }}
                                </small>
                            </td>
                            <td>
                                <small class="d-block text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $pry->fecha_inicio?->format('d/m/Y') }}
                                </small>
                                @if($pry->fecha_fin_estimada)
                                    <small class="text-muted-50" style="font-size: 0.72rem;">
                                        Fin: {{ $pry->fecha_fin_estimada->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($listaResponsables->isNotEmpty())
                                    @php $firstResp = $listaResponsables->first(); @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($firstResp->nombres, 0, 1)) }}
                                        </div>
                                        <div class="small lh-1">
                                            <div class="fw-semibold text-heading">
                                                {{ $firstResp->nombre_completo }}
                                                @if($listaResponsables->count() > 1)
                                                    <span class="badge badge-soft-primary ms-1" title="{{ $listaResponsables->skip(1)->pluck('nombre_completo')->join(', ') }}">
                                                        +{{ $listaResponsables->count() - 1 }} más
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted" style="font-size: 0.7rem;">{{ $firstResp->cargo }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Sin asignar</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <span class="badge bg-body-secondary text-body border" title="Personal asignado">
                                        <i class="bi bi-people-fill text-primary me-1"></i>{{ $pry->personal_count }}
                                    </span>
                                    <span class="badge bg-body-secondary text-body border" title="Cuadrillas activas">
                                        <i class="bi bi-diagram-3-fill text-warning me-1"></i>{{ $pry->cuadrillas_count }}
                                    </span>
                                    <span class="badge bg-body-secondary text-body border" title="Activos en obra">
                                        <i class="bi bi-tools text-info me-1"></i>{{ $pry->activos_count }}
                                    </span>
                                    <span class="badge bg-body-secondary text-body border" title="Centros de Almacén">
                                        <i class="bi bi-boxes text-success me-1"></i>{{ $pry->ubicaciones_count ?? 0 }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($pry->estado === 'ACTIVO')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> ACTIVO
                                    </span>
                                @elseif($pry->estado === 'SUSPENDIDO')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-pause-circle me-1"></i> SUSPENDIDO
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold">
                                        FINALIZADO
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('proyectos.show', $pry) }}" class="btn btn-outline-secondary" title="Ver Ficha y Estadísticas">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->rol !== 'AUDITOR')
                                    <a href="{{ route('proyectos.edit', $pry) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('proyectos.destroy', $pry) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar el proyecto {{ $pry->codigo }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-muted"></i>
                                No se encontraron proyectos que coincidan con los criterios de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($proyectos->hasPages())
            <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-end">
                {{ $proyectos->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection

