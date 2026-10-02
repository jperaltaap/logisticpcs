@extends('layouts.admin')

@section('title', 'Artículo ' . $articulo->codigo_sku)
@section('page_title', $articulo->descripcion)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articulos.index') }}" class="text-decoration-none fw-semibold text-primary">Artículos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $articulo->codigo_sku }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        @if($articulo->control_serie)
            <a href="{{ route('activos.create', ['articulo_id' => $articulo->id]) }}" class="btn btn-success btn-sm px-3 fw-bold shadow">
                <i class="bi bi-qr-code me-1"></i> Registrar Nueva Unidad (QR)
            </a>
        @endif
        <a href="{{ route('articulos.edit', $articulo) }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('articulos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row g-4 mb-4">
        <!-- Ficha Técnica Resumen -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <div class="text-center mb-3">
                    @if($articulo->foto_url)
                        <img src="{{ $articulo->foto_url }}" alt="{{ $articulo->descripcion }}" class="img-fluid rounded border p-1 mb-3 shadow-sm" style="max-height: 180px; object-fit: contain;" onerror="this.style.display='none'; document.getElementById('placeholder-icon-show').classList.remove('d-none');">
                        <div id="placeholder-icon-show" class="d-none align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 mx-auto" style="width: 100px; height: 100px; font-size: 2.8rem;">
                            <i class="bi {{ $articulo->control_serie ? 'bi-cpu' : 'bi-box-seam' }}"></i>
                        </div>
                    @else
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3" style="width: 100px; height: 100px; font-size: 2.8rem;">
                            <i class="bi {{ $articulo->control_serie ? 'bi-cpu' : 'bi-box-seam' }}"></i>
                        </div>
                    @endif
                    <h5 class="fw-bold text-heading mb-1">{{ $articulo->descripcion }}</h5>
                    <div class="text-primary font-monospace fw-bold">{{ $articulo->codigo_sku }}</div>
                    <div class="mt-2">
                        <span class="badge badge-soft-secondary">{{ $articulo->categoria->nombre ?? 'Sin Categoría' }}</span>
                        <span class="badge badge-soft-primary">{{ $articulo->tipo_articulo }}</span>
                        @if($articulo->proyecto)
                            <span class="badge badge-soft-info" title="Proyecto asignado"><i class="bi bi-diagram-3 me-1"></i>{{ $articulo->proyecto->codigo }}</span>
                        @else
                            <span class="badge badge-soft-secondary" title="Global"><i class="bi bi-globe me-1"></i>Global</span>
                        @endif
                    </div>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Proyecto:</span>
                        <span class="fw-semibold {{ $articulo->proyecto ? 'text-primary' : 'text-muted' }}">
                            {{ $articulo->proyecto ? $articulo->proyecto->codigo . ' - ' . $articulo->proyecto->nombre : 'Catálogo General (Global)' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Marca / Fabricante:</span>
                        <span class="fw-semibold text-heading">{{ $articulo->marca ?? 'No especificada' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Modelo:</span>
                        <span class="fw-semibold text-heading">{{ $articulo->modelo ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Unidad de Medida:</span>
                        <span class="fw-semibold text-heading">{{ $articulo->unidad_medida }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Control de Serie / QR:</span>
                        <span class="fw-semibold {{ $articulo->control_serie ? 'text-success' : 'text-secondary' }}">
                            {{ $articulo->control_serie ? 'SÍ (Individual)' : 'NO (A Granel)' }}
                        </span>
                    </li>
                    @if($articulo->control_serie)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Modalidad Serializada:</span>
                            @if($articulo->esSerializadoConsumible())
                                <span class="badge badge-soft-info">
                                    <i class="bi bi-hdd-network me-1"></i> Serializado Consumible / Instalable
                                </span>
                            @else
                                <span class="badge badge-soft-primary">
                                    <i class="bi bi-tools me-1"></i> Serializado como Activo
                                </span>
                            @endif
                        </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Stock Mínimo Alerta:</span>
                        @if($articulo->esSerializadoActivo())
                            <span class="badge badge-soft-secondary">No aplica (Activo Serializado)</span>
                        @else
                            <span class="fw-semibold text-heading">{{ $articulo->stock_minimo }} {{ $articulo->unidad_medida }}</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Vida Útil Estimada:</span>
                        <span class="fw-semibold text-heading">{{ $articulo->vida_util_meses ? $articulo->vida_util_meses . ' meses' : 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Estado del Registro:</span>
                        <span class="badge {{ $articulo->estado === 'ACTIVO' ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-secondary bg-opacity-10 text-secondary' }} border">
                            {{ $articulo->estado }}
                        </span>
                    </li>
                </ul>

                @if($articulo->observaciones)
                    <div class="mt-3 p-3 bg-light rounded small border">
                        <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1 text-primary"></i> Observaciones:</div>
                        <div class="text-muted">{{ $articulo->observaciones }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Inventario Detallado (Serializado o Por Almacén) -->
        <div class="col-lg-8">
            @if($articulo->control_serie)
                <!-- Vista de Activos Serializados con QR -->
                <div class="admin-card overflow-hidden h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-opacity-25">
                        <div class="fw-bold text-heading">
                            <i class="bi bi-qr-code-scan me-2 text-primary"></i> Unidades Físicas Registradas ({{ $articulo->activos->count() }})
                        </div>
                        <a href="{{ route('activos.create', ['articulo_id' => $articulo->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Unidad
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Código QR / Placa</th>
                                    <th>N° Serie Fábrica</th>
                                    <th>Ubicación Actual</th>
                                    <th>Asignado A / Proyecto</th>
                                    <th>Condición</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($articulo->activos as $act)
                                    <tr>
                                        <td class="ps-3 fw-bold font-monospace text-primary">
                                            <a href="{{ route('activos.show', $act) }}" class="text-decoration-none">
                                                {{ $act->codigo_interno }}
                                            </a>
                                        </td>
                                        <td class="font-monospace small">{{ $act->numero_serie ?? 'S/N' }}</td>
                                        <td>
                                            <i class="bi bi-geo-alt text-muted me-1"></i>
                                            {{ $act->ubicacion->nombre ?? 'Sin Ubicación' }}
                                        </td>
                                        <td>
                                            @if($act->responsable)
                                                <div class="small fw-semibold text-heading">
                                                    <i class="bi bi-person text-primary me-1"></i> {{ $act->responsable->nombre_completo }}
                                                </div>
                                            @endif
                                            @if($act->proyecto)
                                                <div class="small text-muted">
                                                    <i class="bi bi-buildings me-1"></i> {{ $act->proyecto->nombre }}
                                                </div>
                                            @endif
                                            @if(!$act->responsable && !$act->proyecto)
                                                <span class="small text-muted italic">En almacén (sin asignar)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $condBadge = match($act->condicion_prestamo) {
                                                    'DISPONIBLE' => 'badge-soft-success',
                                                    'PRESTADO_CAMPO' => 'badge-soft-primary',
                                                    'EN_TRANSFERENCIA' => 'badge-soft-warning',
                                                    'EXTRAVIADO' => 'badge-soft-danger',
                                                    default => 'badge-soft-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $condBadge }}">{{ $act->condicion_prestamo }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('activos.show', $act) }}" class="btn btn-outline-secondary" title="Ver Unidad">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('activos.etiqueta', $act) }}" target="_blank" class="btn btn-outline-dark" title="Imprimir Etiqueta QR">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No hay unidades serializadas registradas para este artículo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Vista de Stock por Almacenes para Consumibles -->
                <div class="admin-card overflow-hidden h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-opacity-25">
                        <div class="fw-bold text-heading">
                            <i class="bi bi-building-check me-2 text-primary"></i> Existencias en Almacenes Físicos
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Código Almacén</th>
                                    <th>Nombre del Almacén</th>
                                    <th>Tipo</th>
                                    <th class="text-end pe-3">Cantidad Disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($articulo->inventarioStocks as $stk)
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-primary">{{ $stk->ubicacion->codigo ?? 'N/A' }}</td>
                                        <td class="fw-semibold text-heading">{{ $stk->ubicacion->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $stk->ubicacion->tipo ?? 'ALMACEN' }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="fw-bold fs-6 {{ $stk->cantidad_actual <= $articulo->stock_minimo ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($stk->cantidad_actual, 2) }} {{ $articulo->unidad_medida }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No registra movimientos ni saldos en ningún almacén actualmente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($articulo->inventarioStocks->isNotEmpty())
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="3" class="ps-3 text-uppercase">Stock Total Consolidado</td>
                                        <td class="text-end pe-3 text-primary fs-6">
                                            {{ number_format($articulo->inventarioStocks->sum('cantidad_actual'), 2) }} {{ $articulo->unidad_medida }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Kits que incluyen este artículo -->
    @if($articulo->kits->isNotEmpty())
        <div class="admin-card p-4">
            <h6 class="fw-bold mb-3 text-heading">
                <i class="bi bi-collection me-2 text-primary"></i> Kits Técnicos que incorporan este artículo
            </h6>
            <div class="row g-3">
                @foreach($articulo->kits as $kit)
                    <div class="col-md-6 col-xl-4">
                        <div class="p-3 border rounded h-100 bg-light bg-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-soft-primary font-monospace">{{ $kit->codigo_kit }}</span>
                                <span class="small fw-bold text-muted">{{ $kit->pivot->cantidad }} {{ $articulo->unidad_medida }}</span>
                            </div>
                            <div class="fw-bold text-heading">
                                <a href="{{ route('kits.show', $kit) }}" class="text-decoration-none">
                                    {{ $kit->nombre_kit }}
                                </a>
                            </div>
                            <div class="small text-muted mt-1">{{ Str::limit($kit->descripcion, 70) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
