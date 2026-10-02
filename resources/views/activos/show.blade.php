@extends('layouts.admin')

@section('title', 'Activo ' . $activo->codigo_interno)
@section('page_title', 'Ficha Técnica del Activo: ' . $activo->codigo_interno)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('activos.index') }}" class="text-decoration-none fw-semibold text-primary">Activos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $activo->codigo_interno }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('activos.etiqueta', $activo) }}" target="_blank" class="btn btn-dark btn-sm px-3 fw-bold shadow">
            <i class="bi bi-printer me-1"></i> Imprimir Etiqueta QR
        @if($activo->estado_operativo === 'EN_MANTENIMIENTO')
            <a href="{{ route('mantenimientos.index', ['q' => $activo->codigo_interno]) }}" class="btn btn-warning btn-sm px-3 fw-bold">
                <i class="bi bi-tools me-1"></i> Ver en Taller / Dar de Alta
            </a>
        @elseif($activo->estado_operativo === 'OPERATIVO' || $activo->estado_operativo === 'DANADO')
            <a href="{{ route('mantenimientos.create', ['activo_id' => $activo->id]) }}" class="btn btn-outline-warning btn-sm px-3 fw-bold">
                <i class="bi bi-tools me-1"></i> Enviar a Calibración / Taller
            </a>
        @endif
        <a href="{{ route('activos.edit', $activo) }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('activos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    @if($activo->estado_operativo === 'EN_MANTENIMIENTO')
        <div class="alert alert-warning border-warning shadow-sm d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded-3 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-25 p-2 text-warning fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Equipo actualmente en Taller o Calibración</h6>
                    <span class="small text-muted">Este activo se encuentra en proceso de mantenimiento/calibración técnica. Para registrar su retorno y darle de alta operativa, diríjase al módulo de Calibraciones & Taller.</span>
                </div>
            </div>
            <a href="{{ route('mantenimientos.index', ['q' => $activo->codigo_interno]) }}" class="btn btn-warning btn-sm fw-bold px-3 text-nowrap">
                <i class="bi bi-box-arrow-up-right me-1"></i> Ir a Taller & Dar de Alta
            </a>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Tarjeta QR & Identificación Rápida -->
        <div class="col-lg-4">
            <div class="admin-card p-4 text-center h-100">
                <div class="p-3 bg-white rounded-3 shadow-sm border d-inline-block mb-3">
                    <div class="qr-container">
                        {!! $qrSvg !!}
                    </div>
                </div>

                <h4 class="fw-bold font-monospace text-primary mb-1">{{ $activo->codigo_interno }}</h4>
                <div class="small text-muted font-monospace mb-3">
                    SN: <strong class="text-heading">{{ $activo->numero_serie ?? 'SIN SERIE' }}</strong>
                </div>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    @php
                        $opBadge = match($activo->estado_operativo) {
                            'OPERATIVO' => 'badge-soft-success',
                            'EN_MANTENIMIENTO' => 'badge-soft-warning',
                            'DANADO' => 'badge-soft-danger',
                            'DE_BAJA' => 'badge-soft-secondary',
                            default => 'badge-soft-secondary',
                        };
                        $prestBadge = match($activo->condicion_prestamo) {
                            'DISPONIBLE' => 'badge-soft-success',
                            'PRESTADO_CAMPO' => 'badge-soft-primary',
                            'INSTALADO_PROYECTO' => 'badge-soft-info',
                            'EN_TRANSFERENCIA' => 'badge-soft-warning',
                            'EXTRAVIADO' => 'badge-soft-danger',
                            default => 'badge-soft-secondary',
                        };
                    @endphp
                    <span class="badge {{ $opBadge }} px-2 py-1">{{ $activo->estado_operativo }}</span>
                    <span class="badge {{ $prestBadge }} px-2 py-1">{{ $activo->condicion_prestamo }}</span>
                </div>

                <a href="{{ route('activos.etiqueta', $activo) }}" target="_blank" class="btn btn-outline-dark btn-sm w-100 fw-bold">
                    <i class="bi bi-tag-fill me-1"></i> Vista de Etiqueta Térmica
                </a>
            </div>
        </div>

        <!-- Ficha Técnica Completa y Ubicación -->
        <div class="col-lg-8">
            <div class="admin-card p-4 h-100">
                <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                    <i class="bi bi-info-circle me-2 text-primary"></i> Información del Bien & Trazabilidad
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted d-block">Artículo Maestro:</label>
                        <div class="fw-bold text-heading fs-6">
                            <a href="{{ route('articulos.show', $activo->articulo) }}" class="text-decoration-none">
                                {{ $activo->articulo->descripcion }}
                            </a>
                        </div>
                        <div class="small text-muted font-monospace">SKU: {{ $activo->articulo->codigo_sku }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="small text-muted d-block">Marca:</label>
                        <div class="fw-semibold text-heading">{{ $activo->articulo->marca ?? 'N/A' }}</div>
                    </div>

                    <div class="col-md-3">
                        <label class="small text-muted d-block">Modelo:</label>
                        <div class="fw-semibold text-heading">{{ $activo->articulo->modelo ?? 'N/A' }}</div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Ubicación Física Actual:</label>
                        <div class="fw-semibold text-heading d-flex align-items-center gap-1">
                            <i class="bi bi-geo-alt-fill text-danger"></i>
                            {{ $activo->ubicacion->nombre ?? 'Sin Ubicación' }} ({{ $activo->ubicacion->tipo ?? '' }})
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Custodio / Responsable de Campo:</label>
                        @if($activo->responsable)
                            <div class="fw-semibold text-heading d-flex align-items-center gap-1">
                                <i class="bi bi-person-check-fill text-success"></i>
                                <a href="{{ route('personal.show', $activo->responsable) }}" class="text-decoration-none">
                                    {{ $activo->responsable->nombre_completo }}
                                </a>
                                <span class="small text-muted">({{ $activo->responsable->cargo }})</span>
                            </div>
                        @else
                            <div class="text-muted italic small">Sin custodio actual (disponible en almacén)</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Proyecto Vinculado:</label>
                        @if($activo->proyecto)
                            <div class="fw-semibold text-heading">
                                <a href="{{ route('proyectos.show', $activo->proyecto) }}" class="text-decoration-none">
                                    <i class="bi bi-buildings text-primary me-1"></i> {{ $activo->proyecto->codigo }} - {{ $activo->proyecto->nombre }}
                                </a>
                            </div>
                        @else
                            <div class="text-muted italic small">No asignado a proyecto</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted d-block">Cuadrilla Asignada:</label>
                        <div class="fw-semibold text-heading">
                            {{ $activo->cuadrilla->nombre ?? 'Sin cuadrilla asignada' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Fecha de Ingreso:</label>
                        <div class="fw-semibold text-heading">{{ $activo->fecha_ingreso?->format('d/m/Y') ?? 'N/A' }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Última Asignación:</label>
                        <div class="fw-semibold text-heading">{{ $activo->fecha_ultima_asignacion?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="small text-muted d-block">Último Retorno:</label>
                        <div class="fw-semibold text-heading">{{ $activo->fecha_ultimo_retorno?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                    </div>

                    @if($activo->observaciones)
                        <div class="col-12">
                            <label class="small text-muted d-block">Observaciones de Registro:</label>
                            <div class="p-2 bg-light rounded small border text-muted">
                                {{ $activo->observaciones }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Historial Operativo (Despachos y Mantenimientos) -->
    <div class="admin-card overflow-hidden">
        <div class="border-bottom p-3 bg-light bg-opacity-25 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-heading">
                <i class="bi bi-clock-history me-2 text-primary"></i> Historial Operativo del Activo
            </h6>
        </div>

        <ul class="nav nav-tabs px-3 pt-2" id="activoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small" id="despachos-tab" data-bs-toggle="tab" data-bs-target="#despachos" type="button" role="tab">
                    <i class="bi bi-arrow-left-right me-1"></i> Préstamos & Despachos ({{ $activo->despachoDetalles->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small" id="mantenimiento-tab" data-bs-toggle="tab" data-bs-target="#mantenimiento" type="button" role="tab">
                    <i class="bi bi-tools me-1"></i> Calibraciones & Taller ({{ $activo->mantenimientos->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content p-3" id="activoTabsContent">
            <!-- Despachos -->
            <div class="tab-pane fade show active" id="despachos" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Despacho / Vale</th>
                                <th>Fecha</th>
                                <th>Responsable Receptor</th>
                                <th>Proyecto Destino</th>
                                <th>Estado Devolución</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activo->despachoDetalles as $det)
                                <tr>
                                    <td class="font-monospace fw-bold text-primary">{{ $det->despacho->numero_despacho ?? 'N/A' }}</td>
                                    <td>{{ $det->despacho->fecha_despacho?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $det->despacho->receptorPersonal->nombre_completo ?? 'N/A' }}</td>
                                    <td>{{ $det->despacho->proyecto->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $det->estado_devolucion ?? 'PENDIENTE' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        No registra salidas ni préstamos en el historial del sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mantenimientos -->
            <div class="tab-pane fade" id="mantenimiento" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tipo de Servicio</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Retorno</th>
                                <th>Proveedor / Taller</th>
                                <th>Resultado / Calibración</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activo->mantenimientos as $mant)
                                <tr>
                                    <td class="fw-semibold">{{ $mant->tipo }}</td>
                                    <td>{{ $mant->fecha_ingreso?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $mant->fecha_salida?->format('d/m/Y') ?? 'En proceso (En taller)' }}</td>
                                    <td>{{ $mant->proveedor_taller ?? 'Taller Interno' }}</td>
                                    <td>
                                        @if($mant->resultado === 'CONFORME_OPERATIVO')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">CONFORME OPERATIVO</span>
                                        @elseif($mant->resultado === 'NO_CONFORME_BAJA')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">NO CONFORME (BAJA)</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">EN PROCESO</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('mantenimientos.show', $mant) }}" class="btn btn-outline-info btn-sm py-0 px-2" title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        No registra ingresos a taller ni calibraciones preventivas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
