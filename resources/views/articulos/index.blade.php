@extends('layouts.admin')

@section('title', 'Catálogo Maestro de Artículos')
@section('page_title', 'Catálogo de Bienes, Equipos & Materiales')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Inventario & Activos</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Artículos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
    <a href="{{ route('articulos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Registrar Artículo
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Filtros de Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form action="{{ route('articulos.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filtro_art_search" class="form-label small fw-semibold text-muted mb-1">Buscar por SKU, Nombre o Marca</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="filtro_art_search" name="search" class="form-control" placeholder="Ej: Fusionadora, SKU-001..." value="{{ request('search', request('q')) }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="filtro_art_categoria" class="form-label small fw-semibold text-muted mb-1">Categoría</label>
                <select id="filtro_art_categoria" name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_art_tipo" class="form-label small fw-semibold text-muted mb-1">Tipo de Bien</label>
                <select id="filtro_art_tipo" name="tipo_articulo" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <option value="EQUIPO" {{ request('tipo_articulo') == 'EQUIPO' ? 'selected' : '' }}>EQUIPO</option>
                    <option value="HERRAMIENTA" {{ request('tipo_articulo') == 'HERRAMIENTA' ? 'selected' : '' }}>HERRAMIENTA</option>
                    <option value="EPP" {{ request('tipo_articulo') == 'EPP' ? 'selected' : '' }}>EPP</option>
                    <option value="MATERIAL" {{ request('tipo_articulo') == 'MATERIAL' ? 'selected' : '' }}>MATERIAL</option>
                    <option value="CONSUMIBLE" {{ request('tipo_articulo') == 'CONSUMIBLE' ? 'selected' : '' }}>CONSUMIBLE</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_art_control" class="form-label small fw-semibold text-muted mb-1">Control de Serie</label>
                <select id="filtro_art_control" name="control_serie" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ request('control_serie') === '1' ? 'selected' : '' }}>Serializado (Activos)</option>
                    <option value="0" {{ request('control_serie') === '0' ? 'selected' : '' }}>Consumible / A Granel</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtrar
                </button>
                <a href="{{ route('articulos.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros del catálogo" title="Limpiar filtros">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Artículos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla del catálogo de artículos">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3" style="width: 120px;">SKU</th>
                        <th scope="col">Descripción & Marca</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Control Serie</th>
                        <th scope="col" class="text-center">Existencias</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articulos as $art)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                <a href="{{ route('articulos.show', $art) }}" class="text-decoration-none">
                                    {{ $art->codigo_sku }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 40px; height: 40px;">
                                        @if($art->foto_url)
                                            <img src="{{ $art->foto_url }}" alt="{{ $art->descripcion }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                            <i class="bi bi-box-seam text-muted opacity-50 d-none"></i>
                                        @else
                                            <i class="bi {{ $art->control_serie ? 'bi-cpu' : 'bi-box-seam' }} text-muted opacity-50"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-heading">{{ $art->descripcion }}</div>
                                        <div class="small text-muted">
                                            {{ $art->marca ?? 'S/M' }} {{ $art->modelo ? '· Mod: ' . $art->modelo : '' }} · Und: <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $art->unidad_medida }}</span>
                                            @if(!session('proyecto_activo_id') && $art->proyecto)
                                                · <span class="badge bg-body-secondary text-body border" style="font-size: 0.68rem;"><i class="bi bi-buildings me-1"></i>{{ $art->proyecto->nombre }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $art->categoria->nombre ?? 'Sin Categoría' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $tipoBadge = match($art->tipo_articulo) {
                                        'EQUIPO' => 'badge-soft-info',
                                        'HERRAMIENTA' => 'badge-soft-primary',
                                        'EPP' => 'badge-soft-warning',
                                        'MATERIAL' => 'badge-soft-success',
                                        'CONSUMIBLE' => 'badge-soft-secondary',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $tipoBadge }}">{{ $art->tipo_articulo }}</span>
                            </td>
                            <td>
                                @if($art->control_serie)
                                    <span class="badge badge-soft-success">
                                        <i class="bi bi-qr-code"></i> Serializado
                                    </span>
                                    @if($art->esSerializadoConsumible())
                                        <span class="badge badge-soft-info d-block mt-1" title="Serializado y Consumible: considera stock mínimo y vida útil">
                                            <i class="bi bi-hdd-network"></i> Consumible / Instalable
                                        </span>
                                    @else
                                        <span class="badge badge-soft-primary d-block mt-1" title="Serializado como Activo: sin límite mínimo de stock, controla vida útil">
                                            <i class="bi bi-tools"></i> Activo Operativo
                                        </span>
                                    @endif
                                    @if($art->vida_util_meses)
                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-hourglass-split"></i> Vida útil: {{ $art->vida_util_meses }}m
                                        </div>
                                    @endif
                                @else
                                    <span class="badge badge-soft-secondary">
                                        <i class="bi bi-box"></i> Consumible / Granel
                                    </span>
                                    @if($art->vida_util_meses)
                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-hourglass-split"></i> Vida útil: {{ $art->vida_util_meses }}m
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if($art->control_serie)
                                    <a href="{{ route('activos.index', array_filter(['articulo_id' => $art->id, 'proyecto_id' => session('proyecto_activo_id')])) }}" class="badge badge-soft-primary px-2 py-1 text-decoration-none" title="Ver unidades serializadas">
                                        <i class="bi bi-cpu"></i> {{ $art->activos_count }} unidades
                                    </a>
                                    @if($art->esSerializadoConsumible())
                                        <div class="small mt-1 {{ $art->activos_count <= $art->stock_minimo ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.72rem;">
                                            Mín: {{ number_format($art->stock_minimo, 0) }} {{ $art->unidad_medida }}
                                            @if($art->activos_count <= $art->stock_minimo)
                                                <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Bajo stock mínimo ({{ $art->stock_minimo }})"></i>
                                            @endif
                                        </div>
                                    @else
                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Sin stock mín.</div>
                                    @endif
                                @else
                                    <span class="fw-bold {{ ($art->stock_total ?? 0) <= $art->stock_minimo ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($art->stock_total ?? 0, 2) }} {{ $art->unidad_medida }}
                                    </span>
                                    @if(($art->stock_total ?? 0) <= $art->stock_minimo)
                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Bajo stock mínimo ({{ $art->stock_minimo }})"></i>
                                    @endif
                                    <div class="small text-muted" style="font-size: 0.7rem;">Mín: {{ number_format($art->stock_minimo, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $art->estado === 'ACTIVO' ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                    {{ $art->estado }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('articulos.show', $art) }}" class="btn btn-outline-secondary" aria-label="Ver detalles de {{ $art->descripcion }}" title="Ver Detalles">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
                                    <a href="{{ route('articulos.edit', $art) }}" class="btn btn-outline-primary" aria-label="Editar artículo {{ $art->codigo_sku }}" title="Editar">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('articulos.destroy', $art) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar el artículo {{ $art->codigo_sku }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" aria-label="Eliminar artículo {{ $art->codigo_sku }}" title="Eliminar">
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
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No se encontraron artículos con los criterios seleccionados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articulos->hasPages())
            <div class="p-3 border-top">
                {{ $articulos->links() }}
            </div>
        @endif
    </div>

@endsection
