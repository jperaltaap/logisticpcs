@extends('layouts.admin')

@section('title', 'Historial de Kardex & Resúmenes Analíticos')
@section('page_title', 'Historial de Kardex & Resúmenes Analíticos de Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Operaciones de Almacén</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Historial de Kardex</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('reportes.export.kardex', request()->query()) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
    </a>
    <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 fw-bold">
        <i class="bi bi-printer me-1"></i> Imprimir Reporte
    </button>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Resúmenes Analíticos del Kardex -->
    @if(isset($resumen))
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Movimientos en Kardex</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ number_format($resumen['total_movimientos']) }}</h3>
                <small class="text-muted">Transacciones auditables PEPS</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Operaciones de Entrada</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-arrow-down-left-square"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($resumen['total_entradas']) }}</h3>
                <small class="text-muted">Compras, retornos y ajustes (+)</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Operaciones de Salida</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($resumen['total_salidas']) }}</h3>
                <small class="text-muted">Préstamos, consumos y bajas (-)</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">SKUs con Rotación</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($resumen['articulos_involucrados']) }}</h3>
                <small class="text-muted">Artículos distintos movilizados</small>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros de Kardex -->
    <div class="admin-card p-3 mb-4">
        <form action="{{ route('kardex.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filtro_kardex_articulo" class="form-label small fw-semibold text-muted mb-1">Artículo</label>
                <select id="filtro_kardex_articulo" name="articulo_id" class="form-select form-select-sm">
                    <option value="">Todos los artículos</option>
                    @foreach($articulos as $art)
                        <option value="{{ $art->id }}" {{ request('articulo_id') == $art->id ? 'selected' : '' }}>
                            {{ $art->codigo_sku }} - {{ $art->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_kardex_ubicacion" class="form-label small fw-semibold text-muted mb-1">Almacén</label>
                <select id="filtro_kardex_ubicacion" name="ubicacion_id" class="form-select form-select-sm">
                    <option value="">Todos los almacenes</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_kardex_tipo" class="form-label small fw-semibold text-muted mb-1">Tipo de Movimiento</label>
                <select id="filtro_kardex_tipo" name="tipo_movimiento" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <option value="INGRESO_COMPRA" {{ request('tipo_movimiento') == 'INGRESO_COMPRA' ? 'selected' : '' }}>INGRESO POR COMPRA</option>
                    <option value="SALIDA_PRESTAMO" {{ request('tipo_movimiento') == 'SALIDA_PRESTAMO' ? 'selected' : '' }}>SALIDA POR PRÉSTAMO</option>
                    <option value="RETORNO_PRESTAMO" {{ request('tipo_movimiento') == 'RETORNO_PRESTAMO' ? 'selected' : '' }}>RETORNO DE PRÉSTAMO</option>
                    <option value="SALIDA_CONSUMO" {{ request('tipo_movimiento') == 'SALIDA_CONSUMO' ? 'selected' : '' }}>SALIDA POR CONSUMO</option>
                    <option value="TRANSFERENCIA_SALIDA" {{ request('tipo_movimiento') == 'TRANSFERENCIA_SALIDA' ? 'selected' : '' }}>TRANSF. SALIDA</option>
                    <option value="TRANSFERENCIA_INGRESO" {{ request('tipo_movimiento') == 'TRANSFERENCIA_INGRESO' ? 'selected' : '' }}>TRANSF. INGRESO</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_kardex_desde" class="form-label small fw-semibold text-muted mb-1">Desde</label>
                <input type="date" id="filtro_kardex_desde" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}" aria-label="Fecha desde">
            </div>
            <div class="col-md-2">
                <label for="filtro_kardex_hasta" class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                <input type="date" id="filtro_kardex_hasta" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}" aria-label="Fecha hasta">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" aria-label="Filtrar movimientos Kardex" title="Filtrar">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </button>
                <a href="{{ route('kardex.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros del Kardex" title="Limpiar">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Cronológica del Kardex -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla cronológica de movimientos del Kardex">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3" style="width: 140px;">Fecha & Hora</th>
                        <th scope="col">Artículo / SKU</th>
                        <th scope="col">Almacén</th>
                        <th scope="col">Operación</th>
                        <th scope="col" class="text-end" style="width: 110px;">Stock Ant.</th>
                        <th scope="col" class="text-end" style="width: 110px;">Cantidad</th>
                        <th scope="col" class="text-end" style="width: 110px;">Saldo Post.</th>
                        <th scope="col">Vale / Guía</th>
                        <th scope="col">Operador</th>
                        <th scope="col" class="pe-3">Motivo / Justificación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        @php
                            $esSalida = in_array($mov->tipo_movimiento, ['SALIDA_PRESTAMO', 'SALIDA_CONSUMO', 'TRANSFERENCIA_SALIDA', 'AJUSTE_FALTANTE']);
                            $badgeClass = match($mov->tipo_movimiento) {
                                'SALIDA_PRESTAMO' => 'badge-soft-warning',
                                'RETORNO_PRESTAMO' => 'badge-soft-success',
                                'SALIDA_CONSUMO' => 'badge-soft-danger',
                                'INGRESO_COMPRA' => 'badge-soft-primary',
                                'TRANSFERENCIA_SALIDA', 'TRANSFERENCIA_INGRESO' => 'badge-soft-info',
                                default => 'badge-soft-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 small text-muted font-monospace">
                                {{ $mov->fecha_movimiento?->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="fw-bold text-heading">
                                    <a href="{{ route('articulos.show', $mov->articulo) }}" class="text-decoration-none">
                                        {{ $mov->articulo->descripcion }}
                                    </a>
                                </div>
                                <div class="small text-muted font-monospace">{{ $mov->articulo->codigo_sku }} ({{ $mov->articulo->unidad_medida }})</div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-heading">
                                    <i class="bi bi-geo-alt text-muted me-1"></i> {{ $mov->ubicacion->nombre ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $mov->tipo_movimiento) }}
                                </span>
                            </td>
                            <td class="text-end small font-monospace text-muted">
                                {{ number_format($mov->stock_anterior, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $esSalida ? 'text-danger' : 'text-success' }}">
                                {{ $esSalida ? '-' : '+' }}{{ number_format($mov->cantidad, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace text-primary">
                                {{ number_format($mov->stock_posterior, 2) }}
                            </td>
                            <td>
                                @if($mov->despacho)
                                    <a href="{{ route('despachos.show', $mov->despacho) }}" class="badge badge-soft-primary font-monospace text-decoration-none">
                                        {{ $mov->despacho->numero_guia }}
                                    </a>
                                @elseif($mov->ingreso)
                                    <a href="{{ route('ingresos.show', $mov->ingreso) }}" class="badge badge-soft-success font-monospace text-decoration-none">
                                        {{ $mov->ingreso->codigo_ingreso }}
                                    </a>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $mov->usuario->name ?? 'Sistema' }}
                            </td>
                            <td class="pe-3 small text-muted">
                                {{ $mov->motivo ?? 'Sin observaciones' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                No se encontraron movimientos de kardex registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="p-3 border-top">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

@endsection
