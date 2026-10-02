@extends('layouts.admin')

@section('title', 'Ingresos de Almacén')
@section('page_title', 'Operaciones: Entradas / Ingresos de Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articulos.index') }}" class="text-decoration-none fw-semibold text-primary">Almacén</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Ingresos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
            <i class="bi bi-box-arrow-in-down me-1"></i> Registrar Nuevo Ingreso
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Filtros de Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('ingresos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filtro_ingreso_search" class="form-label small fw-semibold text-muted mb-1">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="filtro_ingreso_search" name="search" class="form-control" placeholder="Código, proveedor, factura o artículo..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label for="filtro_ingreso_tipo" class="form-label small fw-semibold text-muted mb-1">Tipo de Ingreso</label>
                <select id="filtro_ingreso_tipo" name="tipo_ingreso" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <option value="COMPRA_NUEVA" {{ request('tipo_ingreso') == 'COMPRA_NUEVA' ? 'selected' : '' }}>COMPRA NUEVA</option>
                    <option value="AJUSTE_SOBRANTE" {{ request('tipo_ingreso') == 'AJUSTE_SOBRANTE' ? 'selected' : '' }}>AJUSTE SOBRANTE</option>
                    <option value="TRANSFERENCIA_INGRESO" {{ request('tipo_ingreso') == 'TRANSFERENCIA_INGRESO' ? 'selected' : '' }}>TRANSFERENCIA INGRESO</option>
                    <option value="DONACION_TRASPASO" {{ request('tipo_ingreso') == 'DONACION_TRASPASO' ? 'selected' : '' }}>DONACIÓN / TRASPASO</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="filtro_ingreso_ubicacion" class="form-label small fw-semibold text-muted mb-1">Almacén Destino</label>
                <select id="filtro_ingreso_ubicacion" name="ubicacion_id" class="form-select form-select-sm">
                    <option value="">Todos los almacenes</option>
                    @foreach($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}" {{ request('ubicacion_id') == $ubicacion->id ? 'selected' : '' }}>
                            {{ $ubicacion->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="filtro_ingreso_desde" class="form-label small fw-semibold text-muted mb-1">Rango de Fechas</label>
                <div class="input-group input-group-sm">
                    <input type="date" id="filtro_ingreso_desde" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" title="Desde" aria-label="Fecha desde">
                    <span class="input-group-text" aria-hidden="true">a</span>
                    <input type="date" id="filtro_ingreso_hasta" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" title="Hasta" aria-label="Fecha hasta">
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtrar
                </button>
                @if(request()->anyFilled(['search', 'tipo_ingreso', 'ubicacion_id', 'fecha_desde', 'fecha_hasta', 'proyecto_id']))
                    <a href="{{ route('ingresos.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros" title="Limpiar filtros">
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Listado de Ingresos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla de ingresos a almacén">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th scope="col" class="ps-3">Código Ingreso</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Almacén Destino</th>
                        <th scope="col">Proyecto</th>
                        <th scope="col">Proveedor / Comprobante</th>
                        <th scope="col" class="text-center">Ítems</th>
                        <th scope="col">Fecha & Registrado Por</th>
                        <th scope="col" class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($ingresos as $ingreso)
                        @php
                            $tipoBadge = match($ingreso->tipo_ingreso) {
                                'COMPRA_NUEVA' => 'badge-soft-success',
                                'AJUSTE_SOBRANTE' => 'badge-soft-info',
                                'TRANSFERENCIA_INGRESO' => 'badge-soft-primary',
                                'DONACION_TRASPASO' => 'badge-soft-warning',
                                default => 'badge-soft-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 font-monospace fw-bold">
                                <a href="{{ route('ingresos.show', $ingreso) }}" class="text-decoration-none text-primary">
                                    <i class="bi bi-box-arrow-in-down me-1 text-success"></i>
                                    {{ $ingreso->codigo_ingreso }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $tipoBadge }}">
                                    {{ str_replace('_', ' ', $ingreso->tipo_ingreso) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $ingreso->ubicacion->nombre ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $ingreso->ubicacion->tipo ?? '' }}</div>
                            </td>
                            <td>
                                @if($ingreso->proyecto)
                                    <span class="badge badge-soft-info">
                                        <i class="bi bi-building"></i> {{ $ingreso->proyecto->codigo }}
                                    </span>
                                    <div class="small text-muted">{{ Str::limit($ingreso->proyecto->nombre, 25) }}</div>
                                @else
                                    <span class="small text-muted italic">Sin proyecto asignado</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $ingreso->proveedor ?? 'S/P' }}</div>
                                @if($ingreso->numero_comprobante)
                                    <div class="small text-muted font-monospace"><i class="bi bi-receipt"></i> {{ $ingreso->numero_comprobante }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-secondary px-2 py-1">
                                    <i class="bi bi-list-check me-1"></i> {{ $ingreso->detalles_count }} art.
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $ingreso->fecha_ingreso ? $ingreso->fecha_ingreso->format('d/m/Y') : '-' }}</div>
                                <div class="small text-muted"><i class="bi bi-person"></i> {{ $ingreso->usuario->name ?? 'Usuario' }}</div>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('ingresos.show', $ingreso) }}" class="btn btn-outline-primary btn-sm px-2 py-1" aria-label="Ver comprobante y detalle del ingreso {{ $ingreso->codigo_ingreso }}" title="Ver Comprobante">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No se encontraron registros de ingreso con los filtros aplicados.
                                <div class="mt-2">
                                    <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Registrar Primer Ingreso
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ingresos->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted">Mostrando {{ $ingresos->firstItem() }} a {{ $ingresos->lastItem() }} de {{ $ingresos->total() }} ingresos</span>
                {{ $ingresos->links() }}
            </div>
        @endif
    </div>

@endsection
