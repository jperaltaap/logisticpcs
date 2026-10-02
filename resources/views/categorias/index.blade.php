@extends('layouts.admin')

@section('title', 'Categorías de Bienes')
@section('page_title', 'Categorías Parametrizadas del Catálogo')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Categorías</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('categorias.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Nueva Categoría
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Barra de Filtros y Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('categorias.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por código, nombre o descripción..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if($search)
                    <a href="{{ route('categorias.index') }}" class="btn btn-link text-decoration-none text-muted">Limpiar</a>
                @endif
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge badge-soft-info p-2 px-3">
                    <i class="bi bi-tags-fill me-1"></i> Total: {{ $categorias->total() }} Categorías
                </span>
            </div>
        </form>
    </div>

    <!-- Grid de Categorías -->
    <div class="row g-3 mb-4">
        @forelse($categorias as $cat)
            <div class="col-md-6 col-xl-4">
                <div class="admin-card p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-soft-primary font-monospace fw-bold px-2 py-1">
                                {{ $cat->codigo }}
                            </span>
                            <span class="badge badge-soft-info">
                                {{ $cat->articulos_count }} Artículos
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1 text-heading">
                            <a href="{{ route('categorias.show', $cat) }}" class="text-decoration-none">
                                {{ $cat->nombre }}
                            </a>
                        </h5>
                        <p class="small text-muted mb-3" style="min-height: 38px;">
                            {{ Str::limit($cat->descripcion ?: 'Sin descripción detallada.', 80) }}
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('categorias.show', $cat) }}" class="btn btn-xs btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i> Ver Catálogo
                        </a>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('categorias.edit', $cat) }}" class="btn btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('categorias.destroy', $cat) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar esta categoría?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="admin-card p-5 text-center text-muted">
                    <i class="bi bi-tags fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron categorías registradas con los criterios ingresados.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($categorias->hasPages())
        <div class="d-flex justify-content-center">
            {{ $categorias->links() }}
        </div>
    @endif

@endsection
