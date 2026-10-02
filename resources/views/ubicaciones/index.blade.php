@extends('layouts.admin')

@section('title', 'Centros de Almacenamiento & Ubicaciones')
@section('page_title', 'Centros de Almacenamiento & Ubicaciones Físicas')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Almacenes</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->rol !== 'AUDITOR')
    <a href="{{ route('ubicaciones.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Centro de Almacén
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    @if($proyectoActivo)
        <div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 d-flex justify-content-between align-items-center py-2 px-3 mb-3">
            <div class="small">
                <i class="bi bi-buildings-fill text-primary me-2"></i>
                Gestionando Centros de Almacén del Proyecto Activo: <strong>{{ $proyectoActivo->codigo }} — {{ $proyectoActivo->nombre }}</strong>
                @if($proyectoActivo->ubicacion)
                    <span class="text-muted ms-1">({{ $proyectoActivo->ubicacion }})</span>
                @endif
            </div>
            <span class="badge bg-primary">Proyecto Activo</span>
        </div>
    @endif

    <!-- Filtros de Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('ubicaciones.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por código, nombre, ubicación o dirección..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="proyecto_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Todos los Proyectos / Almacenes --</option>
                    <option value="global" {{ $proyectoFiltroId === 'global' ? 'selected' : '' }}>Solo Almacenes Globales / Centrales</option>
                    @foreach($proyectos as $pry)
                        <option value="{{ $pry->id }}" {{ (string) $proyectoFiltroId === (string) $pry->id ? 'selected' : '' }}>
                            {{ $pry->codigo }} - {{ Str::limit($pry->nombre, 26) }}{{ $proyectoActivo && $proyectoActivo->id == $pry->id ? ' ★' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if($search || request()->filled('proyecto_id'))
                    <a href="{{ route('ubicaciones.index') }}" class="btn btn-link text-muted btn-sm d-flex align-items-center">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid de Almacenes -->
    <div class="row g-3 mb-4">
        @forelse($ubicaciones as $ub)
            <div class="col-md-6 col-xl-4">
                <div class="admin-card p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-soft-primary font-monospace fw-bold px-2 py-1">
                                {{ $ub->codigo }}
                            </span>
                            <span class="badge {{ $ub->estado === 'ACTIVO' ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                {{ $ub->estado }}
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1 text-heading">
                            <a href="{{ route('ubicaciones.show', $ub) }}" class="text-decoration-none">
                                {{ $ub->nombre }}
                            </a>
                        </h5>
                        <p class="small text-muted mb-3" style="min-height: 38px;">
                            <i class="bi bi-geo-alt text-primary me-1"></i>{{ Str::limit($ub->descripcion ?: 'Sin dirección física especificada.', 85) }}
                        </p>

                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if($ub->proyecto)
                                <span class="badge badge-soft-primary small" title="Proyecto asignado: {{ $ub->proyecto->nombre }}">
                                    <i class="bi bi-diagram-3 me-1"></i> {{ $ub->proyecto->codigo }} ({{ Str::limit($ub->proyecto->nombre, 18) }})
                                </span>
                            @else
                                <span class="badge badge-soft-secondary small" title="Almacén Central / Global">
                                    <i class="bi bi-globe me-1"></i> Global / Central
                                </span>
                            @endif
                            <span class="badge badge-soft-secondary small">
                                <i class="bi bi-qr-code me-1"></i> {{ $ub->activos_count }} Activos QR
                            </span>
                            <span class="badge badge-soft-info small">
                                <i class="bi bi-boxes me-1"></i> {{ $ub->inventario_stocks_count }} Stock
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('ubicaciones.show', $ub) }}" class="btn btn-xs btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i> Ver Existencias de Stock
                        </a>
                        @if(auth()->user()->rol !== 'AUDITOR')
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('ubicaciones.edit', $ub) }}" class="btn btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('ubicaciones.destroy', $ub) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar este centro de almacén?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="admin-card p-5 text-center text-muted">
                    <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron almacenes registrados con los filtros aplicados.
                </div>
            </div>
        @endforelse
    </div>


    <!-- Paginación -->
    @if($ubicaciones->hasPages())
        <div class="d-flex justify-content-center">
            {{ $ubicaciones->links() }}
        </div>
    @endif

@endsection
