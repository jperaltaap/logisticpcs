@extends('layouts.admin')

@section('title', 'Guía de Despacho ' . $despacho->numero_guia)
@section('page_title', 'Orden de Despacho: ' . $despacho->numero_guia)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('despachos.index') }}" class="text-decoration-none fw-semibold text-primary">Despachos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $despacho->numero_guia }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('despachos.acta', $despacho) }}" target="_blank" class="btn btn-dark btn-sm px-3 fw-bold shadow">
            <i class="bi bi-printer me-1"></i> Imprimir Acta / Vale
        </a>
        @if(in_array($despacho->estado, ['ENTREGADO_EN_CAMPO', 'PARCIALMENTE_DEVUELTO']) && !in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
            <a href="{{ route('despachos.devolucion', $despacho) }}" class="btn btn-warning text-dark btn-sm px-3 fw-bold shadow">
                <i class="bi bi-arrow-return-left me-1"></i> Registrar Devolución
            </a>
        @endif
        <a href="{{ route('despachos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row g-4 mb-4">
        <!-- Resumen de la Guía y Custodio -->
        <div class="col-lg-8">
            <div class="admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge badge-soft-primary font-monospace fs-6 px-2 py-1">
                            {{ $despacho->numero_guia }}
                        </span>
                        <span class="badge badge-soft-secondary ms-2">
                            {{ str_replace('_', ' ', $despacho->tipo_movimiento) }}
                        </span>
                    </div>
                    @php
                        $estBadge = match($despacho->estado) {
                            'ENTREGADO_EN_CAMPO' => 'badge-soft-primary',
                            'PARCIALMENTE_DEVUELTO' => 'badge-soft-warning',
                            'DEVUELTO_TOTAL' => 'badge-soft-success',
                            'ANULADO' => 'badge-soft-secondary',
                            default => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $estBadge }} px-3 py-2 fw-bold">
                        {{ str_replace('_', ' ', $despacho->estado) }}
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Proyecto de Imputación:</label>
                        <div class="fw-bold text-heading fs-6">
                            <i class="bi bi-buildings text-primary me-1"></i>
                            <a href="{{ route('proyectos.show', $despacho->proyecto) }}" class="text-decoration-none">
                                {{ $despacho->proyecto->nombre }}
                            </a>
                        </div>
                        <div class="small text-muted font-monospace">{{ $despacho->proyecto->codigo }} ({{ $despacho->proyecto->cliente }})</div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Personal que Retira / Traslada:</label>
                        @if($despacho->personal)
                            <div class="fw-bold text-heading fs-6">
                                <i class="bi bi-person-check-fill text-success me-1"></i>
                                <a href="{{ route('personal.show', $despacho->personal) }}" class="text-decoration-none">
                                    {{ $despacho->personal->nombre_completo }}
                                </a>
                            </div>
                            <div class="small text-muted">DNI: {{ $despacho->personal->dni }} · Cargo: {{ $despacho->personal->cargo }}</div>
                        @else
                            <div class="text-muted italic">Sin personal individual asignado</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Almacén de Origen:</label>
                        <div class="fw-semibold text-heading">
                            <i class="bi bi-geo-alt text-danger me-1"></i> {{ $despacho->ubicacionOrigen->nombre ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Fecha y Hora de Despacho:</label>
                        <div class="fw-semibold text-heading">
                            <i class="bi bi-calendar-check text-muted me-1"></i> {{ $despacho->fecha_despacho?->format('d/m/Y H:i') ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Compromiso de Retorno:</label>
                        <div class="fw-semibold {{ ($despacho->fecha_compromiso_retorno && $despacho->fecha_compromiso_retorno < now() && $despacho->estado !== 'DEVUELTO_TOTAL') ? 'text-danger' : 'text-heading' }}">
                            <i class="bi bi-clock-history me-1"></i> {{ $despacho->fecha_compromiso_retorno?->format('d/m/Y') ?? 'No programado' }}
                            @if($despacho->fecha_compromiso_retorno && $despacho->fecha_compromiso_retorno < now() && $despacho->estado !== 'DEVUELTO_TOTAL')
                                <span class="badge bg-danger ms-1">Vencido</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Registrado por (Almacenero / Usuario):</label>
                        <div class="fw-semibold text-heading">
                            <i class="bi bi-shield-check text-primary me-1"></i> {{ $despacho->usuarioRegistro->name ?? 'Sistema' }}
                        </div>
                    </div>

                    @if($despacho->observaciones)
                        <div class="col-12">
                            <div class="p-2 bg-light rounded small border text-muted">
                                <strong>Observaciones:</strong> {{ $despacho->observaciones }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel de Firma Digital Manuscrita -->
        <div class="col-lg-4">
            <div class="admin-card p-4 text-center h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-pen-fill me-2 text-primary"></i> Constancia de Firma Digital
                    </h6>

                    @if($despacho->firma_digital_base64)
                        <div class="p-2 bg-white rounded border shadow-sm mb-3">
                            <img src="{{ $despacho->firma_digital_base64 }}" alt="Firma Digital" class="img-fluid" style="max-height: 140px;">
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success d-inline-flex align-items-center gap-1 mb-2">
                            <i class="bi bi-patch-check-fill"></i> Firma Digital Capturada en Dispositivo
                        </span>
                    @else
                        <div class="p-4 bg-light rounded border text-muted small mb-3">
                            <i class="bi bi-pen fs-1 d-block mb-1 opacity-25"></i>
                            No se registró firma digital en el momento de la emisión.
                        </div>
                    @endif
                </div>

                <div class="border-top pt-3">
                    <div class="small fw-semibold text-heading">{{ $despacho->personal->nombre_completo ?? 'Receptor de Campo' }}</div>
                    <div class="small text-muted font-monospace">DNI: {{ $despacho->personal->dni ?? 'S/D' }}</div>
                    <div class="small text-muted mt-1">Conformidad de Recepción de Bienes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada de Bienes Entregados y Estado de Devolución -->
    <div class="admin-card overflow-hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light bg-opacity-25">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-box-seam me-2 text-primary"></i> Bienes y Equipos Entregados ({{ $despacho->detalles->count() }})
            </h5>

            @if(in_array($despacho->estado, ['ENTREGADO_EN_CAMPO', 'PARCIALMENTE_DEVUELTO']))
                <a href="{{ route('despachos.devolucion', $despacho) }}" class="btn btn-sm btn-warning text-dark fw-bold">
                    <i class="bi bi-arrow-return-left me-1"></i> Registrar Devolución de Bienes
                </a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Tipo</th>
                        <th>SKU / Código</th>
                        <th>Descripción del Bien</th>
                        <th>Identificación (QR / Serie)</th>
                        <th>Kit Origen</th>
                        <th class="text-end">Cantidad</th>
                        <th>Estado de Retorno</th>
                        <th>Fecha Devolución</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($despacho->detalles as $det)
                        <tr>
                            <td class="ps-3">
                                @if($det->activo_id)
                                    <span class="badge badge-soft-success">
                                        <i class="bi bi-qr-code"></i> Activo
                                    </span>
                                @else
                                    <span class="badge badge-soft-primary">
                                        <i class="bi bi-box"></i> Material
                                    </span>
                                @endif
                            </td>
                            <td class="font-monospace fw-bold text-primary">
                                {{ $det->articulo->codigo_sku }}
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $det->articulo->descripcion }}</div>
                                <div class="small text-muted">{{ $det->articulo->marca ?? 'S/M' }} {{ $det->articulo->modelo ?? '' }}</div>
                            </td>
                            <td>
                                @if($det->activo)
                                    <div class="fw-bold font-monospace text-primary">
                                        <a href="{{ route('activos.show', $det->activo) }}" class="text-decoration-none">
                                            <i class="bi bi-qr-code me-1"></i> {{ $det->activo->codigo_interno }}
                                        </a>
                                    </div>
                                    <div class="small text-muted font-monospace">SN: {{ $det->activo->numero_serie ?? 'S/N' }}</div>
                                @else
                                    <span class="small text-muted italic">Consumible / A granel</span>
                                @endif
                            </td>
                            <td>
                                @if($det->kit)
                                    <span class="badge badge-soft-warning">
                                        {{ $det->kit->nombre_kit }}
                                    </span>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold fs-6 text-heading">
                                {{ number_format($det->cantidad, 0) }} {{ $det->articulo->unidad_medida }}
                            </td>
                            <td>
                                @php
                                    $itemBadge = match($det->estado_item) {
                                        'ENTREGADO' => 'badge-soft-primary',
                                        'DEVUELTO_OPERATIVO' => 'badge-soft-success',
                                        'DEVUELTO_DANADO' => 'badge-soft-warning',
                                        'EXTRAVIADO' => 'badge-soft-danger',
                                        'CONSUMIDO' => 'badge-soft-secondary',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $itemBadge }}">
                                    {{ str_replace('_', ' ', $det->estado_item) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $det->fecha_devolucion?->format('d/m/Y H:i') ?? 'Pendiente' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
