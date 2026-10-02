@extends('layouts.admin')

@section('title', 'Categoría ' . $categoria->nombre)
@section('page_title', $categoria->nombre)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}" class="text-decoration-none fw-semibold text-primary">Categorías</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $categoria->codigo }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('articulos.create', ['categoria_id' => $categoria->id]) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> + Agregar Artículo a esta Categoría
    </a>
    <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-pencil me-1"></i> Editar Categoría
    </a>
@endsection

@section('content')

    <!-- Encabezado de la Categoría -->
    <div class="admin-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge badge-soft-primary font-monospace fs-6 px-3 py-1 mb-2">
                    {{ $categoria->codigo }}
                </span>
                <h3 class="fw-bold mb-1 text-heading">{{ $categoria->nombre }}</h3>
                <p class="text-muted mb-0">{{ $categoria->descripcion ?: 'Sin descripción detallada.' }}</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-inline-block text-center p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-20">
                    <span class="small text-muted text-uppercase fw-bold d-block">Total Artículos</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ $categoria->articulos_count }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Artículos en esta Categoría -->
    <div class="admin-card overflow-hidden">
        <div class="card-header bg-transparent p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-box-seam me-2 text-primary"></i> Catálogo de Artículos Asociados
            </h5>
            <span class="small text-muted">{{ $articulos->total() }} registros</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 14%;">Código SKU</th>
                        <th style="width: 36%;">Descripción / Modelo</th>
                        <th style="width: 15%;">Marca</th>
                        <th style="width: 12%;">Control Serie</th>
                        <th style="width: 11%; text-align: center;">Activos QR</th>
                        <th style="width: 12%; text-align: end;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articulos as $art)
                        <tr>
                            <td>
                                <span class="badge badge-soft-secondary font-monospace fw-bold">
                                    {{ $art->codigo_sku }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('articulos.show', $art) }}" class="fw-bold text-decoration-none">
                                    {{ $art->descripcion }}
                                </a>
                                @if($art->modelo)
                                    <small class="text-muted d-block">{{ $art->modelo }}</small>
                                @endif
                            </td>
                            <td>{{ $art->marca ?: '-' }}</td>
                            <td>
                                @if($art->control_serie)
                                    <span class="badge badge-soft-info"><i class="bi bi-upc-scan me-1"></i> SERIALIZADO</span>
                                @else
                                    <span class="badge badge-soft-secondary">CONSUMIBLE</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold">
                                {{ $art->activos->count() }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('articulos.show', $art) }}" class="btn btn-xs btn-outline-primary" title="Ver Artículo">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay artículos registrados en esta categoría aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articulos->hasPages())
            <div class="card-footer bg-transparent p-3 border-top d-flex justify-content-center">
                {{ $articulos->links() }}
            </div>
        @endif
    </div>

@endsection
