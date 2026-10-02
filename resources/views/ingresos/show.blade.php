@extends('layouts.admin')

@section('title', 'Comprobante de Ingreso ' . $ingreso->codigo_ingreso)
@section('page_title', 'Comprobante de Ingreso: ' . $ingreso->codigo_ingreso)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}" class="text-decoration-none fw-semibold text-primary">Ingresos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $ingreso->codigo_ingreso }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-dark btn-sm px-3 fw-bold shadow">
            <i class="bi bi-printer me-1"></i> Imprimir Comprobante
        </button>
        <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Ingreso
        </a>
        <a href="{{ route('ingresos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row g-4 mb-4">
        <!-- 1. Cabecera y Metadatos -->
        <div class="col-lg-8">
            <div class="admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge badge-soft-success font-monospace fs-6 px-3 py-1">
                            <i class="bi bi-box-arrow-in-down me-1"></i> {{ $ingreso->codigo_ingreso }}
                        </span>
                        <span class="badge badge-soft-primary ms-2">
                            {{ str_replace('_', ' ', $ingreso->tipo_ingreso) }}
                        </span>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> PROCESADO EN KARDEX
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Almacén Físico de Recepción:</span>
                        <strong class="text-heading fs-6">{{ $ingreso->ubicacion->nombre ?? 'N/A' }}</strong>
                        <div class="small text-muted">{{ $ingreso->ubicacion->direccion ?? $ingreso->ubicacion->tipo }}</div>
                    </div>

                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Proyecto Vinculado:</span>
                        @if($ingreso->proyecto)
                            <strong class="text-heading fs-6">[{{ $ingreso->proyecto->codigo }}] {{ $ingreso->proyecto->nombre }}</strong>
                            <div class="small text-muted">{{ $ingreso->proyecto->cliente ?? 'Proyecto Obra' }}</div>
                        @else
                            <span class="badge badge-soft-secondary">Almacén General / Sin Proyecto</span>
                        @endif
                    </div>

                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Proveedor / Origen:</span>
                        <strong class="text-heading">{{ $ingreso->proveedor ?? 'No especificado' }}</strong>
                    </div>

                    <div class="col-sm-6">
                        <span class="text-muted small d-block">N° Documento / Guía / Factura:</span>
                        <span class="font-monospace fw-bold text-primary">{{ $ingreso->numero_comprobante ?? 'Sin comprobante' }}</span>
                    </div>

                    @if($ingreso->observaciones)
                        <div class="col-12">
                            <span class="text-muted small d-block">Observaciones de la Recepción:</span>
                            <div class="p-2 bg-light rounded small mt-1 text-secondary">
                                {{ $ingreso->observaciones }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Tarjeta Lateral de Auditoría -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <h6 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                    <i class="bi bi-person-check text-primary me-1"></i> Registro & Auditoría
                </h6>

                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Fecha del Ingreso:</span>
                        <span class="fw-bold text-heading">{{ $ingreso->fecha_ingreso ? $ingreso->fecha_ingreso->format('d/m/Y') : '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Registrado en Sistema:</span>
                        <span class="fw-semibold text-heading">{{ $ingreso->created_at ? $ingreso->created_at->format('d/m/Y H:i') : '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Usuario Receptor:</span>
                        <span class="fw-semibold text-heading">{{ $ingreso->usuario->name ?? 'Usuario' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Correo Electrónico:</span>
                        <span class="text-muted">{{ $ingreso->usuario->email ?? '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between border-top pt-2">
                        <span class="text-muted">Total de Bienes Recibidos:</span>
                        <span class="fw-bold fs-6 text-success">{{ number_format($ingreso->detalles->sum('cantidad'), 0) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 3. Detalle de Ítems / Materiales Recibidos -->
    <div class="admin-card mb-4 overflow-hidden">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-heading">
                <i class="bi bi-list-check text-primary me-1"></i> Artículos / Materiales Ingresados ({{ $ingreso->detalles->count() }})
            </h6>
            <span class="badge badge-soft-secondary">
                {{ number_format($ingreso->detalles->sum('cantidad'), 0) }} Unidades Totales
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th class="ps-3" style="width: 5%;">#</th>
                        <th style="width: 18%;">Código SKU</th>
                        <th style="width: 42%;">Descripción del Artículo</th>
                        <th style="width: 20%;">Categoría / Tipo</th>
                        <th class="text-end pe-3" style="width: 15%;">Cantidad Recibida</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingreso->detalles as $idx => $det)
                        <tr>
                            <td class="ps-3 text-muted font-monospace">{{ $idx + 1 }}</td>
                            <td class="font-monospace fw-bold text-primary">
                                <a href="{{ route('articulos.show', $det->articulo) }}" class="text-decoration-none">
                                    {{ $det->articulo->codigo_sku }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $det->articulo->descripcion }}</div>
                                <div class="small text-muted">{{ $det->articulo->marca ?? 'S/M' }} {{ $det->articulo->modelo ?? '' }}</div>
                                @if($det->observaciones)
                                    <div class="small text-info mt-1"><i class="bi bi-chat-left-text"></i> {{ $det->observaciones }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-primary small">{{ $det->articulo->categoria->nombre ?? 'General' }}</span>
                                <div class="small text-muted mt-1">
                                    {{ $det->articulo->tipo_articulo }}
                                    @if($det->articulo->control_serie)
                                        <span class="badge badge-soft-success py-0 px-1"><i class="bi bi-qr-code"></i> Seriado</span>
                                    @endif
                                    @if($det->articulo->es_instalable)
                                        <span class="badge badge-soft-info py-0 px-1" title="Instalable en obra"><i class="bi bi-hdd-network"></i> Instalable</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-3 fw-bold fs-6">
                                {{ number_format($det->cantidad, 0) }} <span class="small text-muted fw-normal">{{ $det->articulo->unidad_medida }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Equipos / Activos Serializados Registrados Individualmente -->
    @if($ingreso->activos->isNotEmpty())
        <div class="admin-card overflow-hidden mb-4">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-heading">
                    <i class="bi bi-qr-code-scan text-success me-1"></i> Unidades Serializadas Registradas como Activos ({{ $ingreso->activos->count() }})
                </h6>
                <span class="small text-muted">Cada serie ingresó con condición <strong>DISPONIBLE</strong> en almacén</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">Código Interno</th>
                            <th>N° de Serie de Fábrica</th>
                            <th>Artículo / Equipo</th>
                            <th>Tipo de Control</th>
                            <th>Condición Actual</th>
                            <th class="text-end pe-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ingreso->activos as $act)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold">
                                    <a href="{{ route('activos.show', $act) }}" class="text-decoration-none">
                                        {{ $act->codigo_interno }}
                                    </a>
                                </td>
                                <td class="font-monospace fw-semibold text-dark">
                                    <i class="bi bi-upc-scan text-muted me-1"></i>
                                    {{ $act->numero_serie ?? 'S/N' }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-heading">{{ $act->articulo->descripcion }}</div>
                                    <div class="small text-muted">{{ $act->articulo->marca ?? '' }} {{ $act->articulo->modelo ?? '' }}</div>
                                </td>
                                <td>
                                    @if($act->articulo->es_instalable)
                                        <span class="badge badge-soft-info" title="Equipo/Material seriado que se instala permanentemente en obra">
                                            <i class="bi bi-hdd-network me-1"></i> Material Seriado Instalable
                                        </span>
                                    @else
                                        <span class="badge badge-soft-primary">
                                            <i class="bi bi-tools me-1"></i> Equipo/Herramienta de Operación
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeCond = match($act->condicion_prestamo) {
                                            'DISPONIBLE' => 'badge-soft-success',
                                            'PRESTADO_CAMPO' => 'badge-soft-primary',
                                            'INSTALADO_PROYECTO' => 'badge-soft-info',
                                            'EN_TRANSFERENCIA' => 'badge-soft-warning',
                                            'EXTRAVIADO' => 'badge-soft-danger',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeCond }}">
                                        {{ $act->condicion_prestamo }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('activos.etiqueta', $act) }}" target="_blank" class="btn btn-outline-dark btn-sm py-0 px-2" title="Imprimir Etiqueta QR">
                                        <i class="bi bi-qr-code"></i> QR
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
