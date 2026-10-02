@extends('layouts.admin')

@section('title', 'Almacén ' . $ubicacion->nombre)
@section('page_title', $ubicacion->nombre)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ubicaciones.index') }}" class="text-decoration-none fw-semibold text-primary">Almacenes</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $ubicacion->codigo }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('reportes.export.inventario', ['ubicacion_id' => $ubicacion->id]) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Exportar Stock Excel
    </a>
    @if(auth()->user()->rol !== 'AUDITOR')
    <a href="{{ route('ubicaciones.edit', $ubicacion) }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-pencil me-1"></i> Editar Almacén
    </a>
    @endif
@endsection

@section('content')

    <!-- Encabezado del Almacén -->
    <div class="admin-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge badge-soft-primary font-monospace fs-6 px-3 py-1 mb-2">
                    {{ $ubicacion->codigo }}
                </span>
                @if($ubicacion->proyecto)
                    <span class="badge badge-soft-info ms-2">
                        <i class="bi bi-buildings me-1"></i> {{ $ubicacion->proyecto->codigo }} — {{ $ubicacion->proyecto->nombre }}
                    </span>
                @else
                    <span class="badge badge-soft-secondary ms-2">
                        <i class="bi bi-globe me-1"></i> Almacén General / Global
                    </span>
                @endif
                <span class="badge {{ $ubicacion->estado === 'ACTIVO' ? 'badge-soft-success' : 'badge-soft-danger' }} ms-1">
                    {{ $ubicacion->estado }}
                </span>
                <h3 class="fw-bold mb-1 text-heading mt-2">{{ $ubicacion->nombre }}</h3>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt-fill text-primary me-1"></i>{{ $ubicacion->descripcion ?: 'Sin dirección física detallada.' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex gap-2">
                    <div class="text-center p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-20">
                        <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 0.68rem;">Activos Físicos</span>
                        <h4 class="fw-bold mb-0 text-primary">{{ $ubicacion->activos_count }}</h4>
                    </div>
                    <div class="text-center p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-20">
                        <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 0.68rem;">Registros Stock</span>
                        <h4 class="fw-bold mb-0 text-success">{{ $ubicacion->inventario_stocks_count }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Existencias y Activos -->
    <div class="admin-card overflow-hidden">
        <div class="card-header bg-transparent p-0 border-bottom">
            <ul class="nav nav-tabs px-3 pt-2" id="almacenTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stockTabPane" type="button">
                        <i class="bi bi-box-seam me-1 text-primary"></i> Existencias Físicas / Stock ({{ $stocks->total() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activosTabPane" type="button">
                        <i class="bi bi-qr-code me-1 text-success"></i> Activos Serializados en Almacén ({{ $activos->total() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="almacenTabContent">
            <!-- Pestaña 1: Stock -->
            <div class="tab-pane fade show active" id="stockTabPane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Artículo</th>
                                <th>Categoría</th>
                                <th style="text-align: right;">Cantidad Actual</th>
                                <th style="text-align: right;">Stock Mínimo</th>
                                <th style="text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $st)
                                @php
                                    $isLow = $st->cantidad_actual <= ($st->articulo?->stock_minimo ?? 0);
                                @endphp
                                <tr>
                                    <td><span class="badge badge-soft-secondary font-monospace">{{ $st->articulo?->codigo_sku }}</span></td>
                                    <td>
                                        <a href="{{ route('articulos.show', $st->articulo) }}" class="fw-bold text-decoration-none">
                                            {{ $st->articulo?->descripcion }}
                                        </a>
                                    </td>
                                    <td>{{ $st->articulo?->categoria?->nombre ?? 'N/A' }}</td>
                                    <td style="text-align: right; font-weight: bold;">{{ number_format($st->cantidad_actual, 2) }}</td>
                                    <td style="text-align: right; color: #64748b;">{{ number_format($st->articulo?->stock_minimo ?? 0, 2) }}</td>
                                    <td style="text-align: center;">
                                        @if($isLow)
                                            <span class="badge badge-soft-warning">ALERTA MÍNIMO</span>
                                        @else
                                            <span class="badge badge-soft-success">ÓPTIMO</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay existencias registradas en este almacén.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stocks->hasPages())
                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $stocks->links() }}
                    </div>
                @endif
            </div>

            <!-- Pestaña 2: Activos Serializados -->
            <div class="tab-pane fade" id="activosTabPane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código Interno</th>
                                <th>Serie Fabricante</th>
                                <th>Artículo / Equipo</th>
                                <th>Estado Operativo</th>
                                <th>Condición Préstamo</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activos as $act)
                                <tr>
                                    <td>
                                        <a href="{{ route('activos.show', $act) }}" class="fw-bold font-monospace text-decoration-none">
                                            {{ $act->codigo_interno }}
                                        </a>
                                    </td>
                                    <td>{{ $act->numero_serie ?: '-' }}</td>
                                    <td>{{ $act->articulo?->descripcion ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-soft-success">{{ $act->estado_operativo }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-primary">{{ $act->condicion_prestamo }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('activos.show', $act) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay activos serializados ubicados físicamente en este almacén.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activos->hasPages())
                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $activos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
