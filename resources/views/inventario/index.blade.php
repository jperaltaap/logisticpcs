@extends('layouts.admin')

@section('title', 'Stock Físico por Almacén')
@section('page_title', 'Existencias & Stock Físico por Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Inventario & Activos</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Stock por Almacén</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('reportes.export.inventario', request()->query()) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Exportar Stock (.xlsx)
    </a>
    <a href="{{ route('despachos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-box-arrow-right me-1"></i> Despacho / Salida
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Tarjetas de Métricas Rápidas -->
    <!-- Tarjetas de Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Líneas de Inventario</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($metrics['total_registros']) }}</h3>
                    <small class="text-muted">{{ $metrics['total_almacenes'] }} Almacenes / Acopios</small>
                </div>
                <div class="gradient-icon-box bg-primary text-white">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Materiales Consumibles</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($metrics['total_unidades'], 2) }}</h3>
                    <small class="text-muted">Metros, unidades, cajas</small>
                </div>
                <div class="gradient-icon-box bg-success text-white">
                    <i class="bi bi-stack"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Activos Serializados</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info">{{ number_format($metrics['total_activos']) }}</h3>
                    <small class="text-muted">Equipos y herramientas con QR</small>
                </div>
                <div class="gradient-icon-box bg-info text-white">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Bajo Stock Mínimo</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning">{{ $metrics['total_bajo_minimo'] }}</h3>
                    <small class="text-muted">Requieren reposición</small>
                </div>
                <div class="gradient-icon-box bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('inventario.stock') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label for="filtro_stock_search" class="form-label small text-muted mb-1">Buscar Artículo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="filtro_stock_search" name="search" class="form-control" placeholder="SKU o Descripción..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="filtro_stock_ubicacion" class="form-label small text-muted mb-1">Almacén / Centro de Acopio</label>
                <select id="filtro_stock_ubicacion" name="ubicacion_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Almacenes & Acopios --</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ $ubicacionId == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }} ({{ $ub->codigo }}) {{ $ub->proyecto ? '['.$ub->proyecto->codigo.']' : '[Global]' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_stock_categoria" class="form-label small text-muted mb-1">Categoría</label>
                <select id="filtro_stock_categoria" name="categoria_id" class="form-select form-select-sm">
                    <option value="">-- Categorías --</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_stock_control" class="form-label small text-muted mb-1">Tipo de Control</label>
                <select id="filtro_stock_control" name="tipo_control" class="form-select form-select-sm">
                    <option value="">-- Todo el Stock --</option>
                    <option value="serializado" {{ ($tipoControl ?? '') == 'serializado' ? 'selected' : '' }}>Equipos Serializados (QR)</option>
                    <option value="consumible" {{ in_array(($tipoControl ?? ''), ['consumible', 'fungible']) ? 'selected' : '' }}>Materiales Consumibles</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-1 pt-md-3">
                <div class="form-check me-1 mb-1">
                    <input class="form-check-input" type="checkbox" name="solo_bajos" value="1" id="soloBajosCheck" {{ $soloBajos ? 'checked' : '' }}>
                    <label class="form-check-label small" for="soloBajosCheck" title="Ver solo artículos con existencias críticas">
                        Críticos
                    </label>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtrar
                </button>
                @if($search || $ubicacionId || $categoriaId || $soloBajos || ($tipoControl ?? ''))
                    <a href="{{ route('inventario.stock') }}" class="btn btn-sm btn-link text-muted p-1" aria-label="Limpiar filtros de existencias" title="Limpiar filtros">
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Existencias -->
    <div class="admin-card overflow-hidden mb-4">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla de existencias de inventario">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 11%;">Código SKU</th>
                        <th scope="col" style="width: 27%;">Descripción del Artículo</th>
                        <th scope="col" style="width: 11%;">Tipo</th>
                        <th scope="col" style="width: 12%;">Categoría</th>
                        <th scope="col" style="width: 16%;">Almacén / Acopio</th>
                        <th scope="col" style="width: 13%; text-align: right;">Stock en Almacén</th>
                        <th scope="col" style="width: 5%; text-align: right;">Mín.</th>
                        <th scope="col" style="width: 5%; text-align: center;">Kardex</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $st)
                        @php
                            $isSerialized = $st->articulo?->control_serie ?? false;
                            $isLow = $st->cantidad_actual <= ($st->articulo?->stock_minimo ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-soft-secondary font-monospace fw-bold">
                                    {{ $st->articulo?->codigo_sku }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('articulos.show', $st->articulo) }}" class="fw-bold text-decoration-none text-heading">
                                    {{ $st->articulo?->descripcion }}
                                </a>
                                @if($st->articulo?->marca)
                                    <small class="text-muted d-block">{{ $st->articulo->marca }} {{ $st->articulo->modelo }}</small>
                                @endif
                            </td>
                            <td>
                                @if($isSerialized)
                                    <span class="badge badge-soft-primary" title="Bien controlado por número de serie y código QR">
                                        <i class="bi bi-qr-code me-1"></i> Serializado
                                    </span>
                                @else
                                    <span class="badge badge-soft-secondary" title="Material consumible a granel">
                                        <i class="bi bi-box-seam me-1"></i> Consumible
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $st->articulo?->categoria?->nombre ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $st->ubicacion?->tipo === 'CENTRO_ACOPIO' ? 'badge-soft-info' : 'badge-soft-primary' }}">
                                    {{ $st->ubicacion?->nombre ?? 'N/A' }}
                                </span>
                                @if($st->ubicacion?->proyecto)
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-diagram-3 me-1"></i>{{ $st->ubicacion->proyecto->codigo }}
                                    </small>
                                @elseif(!session('proyecto_activo_id') && $st->proyecto)
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-buildings me-1"></i>{{ $st->proyecto->nombre }}
                                    </small>
                                @else
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-globe me-1"></i>Central / Global
                                    </small>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($isSerialized)
                                    <div class="d-inline-flex flex-column align-items-end">
                                        <span class="badge badge-soft-primary fs-6 fw-bold">
                                            <i class="bi bi-qr-code me-1"></i> {{ (int)$st->cantidad_actual }} {{ (int)$st->cantidad_actual === 1 ? 'Unidad' : 'Unidades' }}
                                        </span>
                                        @if(isset($st->activos_disponibles_count))
                                            <small class="text-success fw-semibold mt-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-check-circle me-1"></i>{{ $st->activos_disponibles_count }} disponibles
                                            </small>
                                        @endif
                                        <a href="{{ route('activos.index', ['articulo_id' => $st->articulo_id, 'ubicacion_id' => $st->ubicacion_id, 'proyecto_id' => session('proyecto_activo_id')]) }}" class="btn btn-xs btn-outline-primary mt-1" style="font-size: 0.68rem; padding: 1px 6px;" aria-label="Ver activos físicos serializados de {{ $st->articulo?->descripcion }}" title="Ver lista de activos físicos con QR en este almacén">
                                            <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Ver Activos
                                        </a>
                                    </div>
                                @else
                                    <span class="fs-6 fw-bold {{ $isLow ? 'text-warning' : 'text-heading' }}">
                                        {{ number_format($st->cantidad_actual, 2) }}
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $st->articulo?->unidad_medida ?? 'UND' }}</small>
                                @endif
                            </td>
                            <td style="text-align: right; color: #64748b;">
                                {{ number_format($st->articulo?->stock_minimo ?? 0, 2) }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('kardex.index', ['articulo_id' => $st->articulo_id, 'ubicacion_id' => $st->ubicacion_id]) }}" class="btn btn-xs btn-outline-secondary" aria-label="Ver movimientos en Kardex de {{ $st->articulo?->descripcion }}" title="Ver movimientos en Kardex">
                                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-boxes fs-1 d-block mb-2 opacity-50"></i>
                                No se encontraron registros de existencias con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
            <div class="card-footer bg-transparent p-3 border-top d-flex justify-content-center">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

@endsection
