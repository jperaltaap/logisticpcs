@extends('layouts.admin')

@section('title', 'Historial de Movimientos de Almacén')
@section('page_title', 'Historial de Movimientos (Ingresos & Salidas)')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Operaciones de Almacén</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Historial de Movimientos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        @if(auth()->user()->rol !== 'AUDITOR')
            <a href="{{ route('ingresos.create') }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
                <i class="bi bi-box-arrow-in-down me-1"></i> Nuevo Ingreso
            </a>
            <a href="{{ route('despachos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Nueva Salida / Préstamo
            </a>
        @endif
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Operaciones Filtradas</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ number_format($stats['total_operaciones']) }}</h3>
                <small class="text-muted">Registro mixto de almacén</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Entradas / Ingresos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['total_ingresos']) }}</h3>
                <small class="text-muted">Compras, transferencias y ajustes</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Salidas / Despachos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_salidas']) }}</h3>
                <small class="text-muted">Consumos y préstamos a campo</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Préstamos en Campo</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['prestamos_pendientes']) }}</h3>
                <small class="text-muted">Pendientes de retorno</small>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros Avanzados -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Buscar Guía / Proveedor / Personal / Artículo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Código, comprobante, serie, DNI...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Tipo de Operación</label>
                <select name="tipo_flujo" class="form-select form-select-sm">
                    <option value="TODOS" {{ $tipoFlujo === 'TODOS' ? 'selected' : '' }}>-- Todos (Mixto) --</option>
                    <option value="INGRESO" {{ $tipoFlujo === 'INGRESO' ? 'selected' : '' }}>Solo Entradas / Ingresos</option>
                    <option value="SALIDA" {{ $tipoFlujo === 'SALIDA' ? 'selected' : '' }}>Todas las Salidas</option>
                    <option value="PRESTAMO" {{ $tipoFlujo === 'PRESTAMO' ? 'selected' : '' }}>Salidas con Retorno (Préstamos)</option>
                    <option value="CONSUMO" {{ $tipoFlujo === 'CONSUMO' ? 'selected' : '' }}>Salidas sin Retorno (Consumo)</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Proyecto / Sucursal</label>
                <select name="proyecto_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($proyectos as $pry)
                        <option value="{{ $pry->id }}" {{ (request('proyecto_id') ?: session('proyecto_activo_id')) == $pry->id ? 'selected' : '' }}>
                            {{ $pry->codigo }} — {{ Str::limit($pry->nombre, 18) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Centro de Almacén</label>
                <select name="ubicacion_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($ubicaciones as $ubi)
                        <option value="{{ $ubi->id }}" {{ request('ubicacion_id') == $ubi->id ? 'selected' : '' }}>
                            {{ $ubi->codigo }} — {{ Str::limit($ubi->nombre, 18) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-1">
                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" title="Filtrar">
                    <i class="bi bi-funnel"></i>
                </button>
                @if(request()->anyFilled(['search', 'tipo_flujo', 'proyecto_id', 'ubicacion_id', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Unificada de Movimientos -->
    <div class="admin-card overflow-hidden mb-4">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-heading">
                    <i class="bi bi-journal-arrow-up text-primary me-2"></i> Bitácora General y Detallada de Movimientos
                </h5>
                <small class="text-muted">Consolidado cronológico de Entradas (Ingresos) y Salidas (Despachos y Préstamos).</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                {{ $paginatedMovimientos->total() }} Operaciones
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-glass align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 120px;">Flujo</th>
                        <th style="width: 150px;">Código / Fecha</th>
                        <th>Proyecto & Almacén</th>
                        <th>Origen / Destinatario</th>
                        <th>Detalle Resumido de Bienes</th>
                        <th class="text-center" style="width: 125px;">Estado</th>
                        <th class="text-end" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedMovimientos as $mov)
                        <tr>
                            <td>
                                @if($mov->flujo === 'INGRESO')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-bold d-inline-block">
                                        <i class="bi bi-arrow-down-left-circle-fill me-1"></i> ENTRADA
                                    </span>
                                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $mov->subtipo }}</div>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold d-inline-block">
                                        <i class="bi bi-arrow-up-right-circle-fill me-1"></i> SALIDA
                                    </span>
                                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $mov->subtipo }}</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $mov->url_detalle }}" class="font-monospace fw-bold text-decoration-none text-heading d-block">
                                    {{ $mov->codigo }}
                                </a>
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $mov->fecha ? $mov->fecha->format('d/m/Y H:i') : '-' }}
                                </small>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $mov->documento_ref }}</small>
                            </td>
                            <td>
                                @if($mov->proyecto)
                                    <span class="badge bg-body-secondary text-body border font-monospace mb-1">
                                        {{ $mov->proyecto->codigo }}
                                    </span>
                                    <div class="small fw-semibold text-heading">{{ Str::limit($mov->proyecto->nombre, 24) }}</div>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border mb-1">GLOBAL</span>
                                @endif
                                <div class="small text-muted">
                                    <i class="bi bi-Shop me-1"></i>{{ $mov->ubicacion?->nombre ?? 'Almacén General' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold small text-heading">{{ $mov->contraparte }}</div>
                                <small class="text-muted d-block">{{ $mov->contraparte_sub }}</small>
                                <small class="text-muted" style="font-size: 0.7rem;">Reg: {{ $mov->usuario }}</small>
                            </td>
                            <td>
                                <div class="small text-body">{{ $mov->resumen_items }}</div>
                                <span class="badge bg-body-secondary text-muted border mt-1" style="font-size: 0.7rem;">
                                    {{ $mov->items_count }} {{ $mov->items_count === 1 ? 'línea' : 'líneas' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(in_array($mov->estado, ['PROCESADO', 'DEVUELTO_TOTAL', 'CONSUMIDO']))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                        {{ $mov->estado }}
                                    </span>
                                @elseif(in_array($mov->estado, ['DESPACHADO', 'DEVUELTO_PARCIAL']))
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small">
                                        {{ $mov->estado }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill small">
                                        {{ $mov->estado }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $mov->url_detalle }}" class="btn btn-outline-primary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($mov->url_impresion)
                                        <a href="{{ $mov->url_impresion }}" target="_blank" class="btn btn-outline-secondary" title="Imprimir Acta">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-right fs-1 d-block mb-2 text-muted"></i>
                                No se encontraron movimientos de ingreso ni salida con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedMovimientos->hasPages())
            <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-end">
                {{ $paginatedMovimientos->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection
