<!-- Banner Informativo del Técnico -->
<div class="admin-card p-4 mb-4 border">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-3 fw-bold shadow-sm" style="width: 52px; height: 52px; font-size: 1.4rem; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="bi bi-wrench-adjustable"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-heading">¡Bienvenido, {{ auth()->user()->name }}!</h4>
                    <span class="badge px-3 py-1 text-white fw-bold text-uppercase" style="background-color: #06b6d4; border-radius: 6px; font-size: 0.75rem;">
                        <i class="bi bi-shield-check me-1"></i> ROL TÉCNICO
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    <span class="me-2"><i class="bi bi-briefcase me-1"></i>{{ $personal?->cargo ?? 'Técnico Operativo' }}</span>
                    @if($personal?->dni)
                        <span class="me-2">• <i class="bi bi-card-heading me-1"></i>DNI: {{ $personal->dni }}</span>
                    @endif
                    <span>• <i class="bi bi-buildings me-1"></i>{{ $proyectoActivo?->nombre ?? ($personal?->proyecto?->nombre ?? 'Asignación Global de Obra') }}</span>
                </p>
            </div>
        </div>
        <div>
            <span class="badge bg-body-secondary text-body border px-3 py-2 rounded-pill small">
                <i class="bi bi-info-circle text-info me-1"></i> Consola Básica de Consultas & Custodia
            </span>
        </div>
    </div>
</div>

<!-- 4 KPIs Operativos para Técnico -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 h-100 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-muted">Herramientas en Custodia</span>
                <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info fs-5">
                    <i class="bi bi-tools"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-heading">{{ $misActivosCustodia->count() }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">Equipos asignados a su cargo</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-muted">Préstamos Pendientes</span>
                <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning fs-5">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-warning">{{ $misPrestamosPendientesCount }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">Actas pendientes de devolución</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-muted">Despachos Recibidos</span>
                <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary fs-5">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-primary">{{ $misDespachos->count() }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">Actas firmadas como receptor</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card p-3 h-100 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-muted">Catálogo General</span>
                <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success fs-5">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-success">{{ $stats['articulos'] }}</h3>
            <small class="text-muted" style="font-size: 0.75rem;">Ítems disponibles para consulta</small>
        </div>
    </div>
</div>

<!-- Accesos Rápidos de Consulta para Técnicos -->
<div class="admin-card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0 text-heading">
            <i class="bi bi-search text-primary me-2"></i>Módulos de Consulta Disponibles
        </h6>
        <span class="text-muted small">Acceso en modo lectura operativa</span>
    </div>
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <a href="{{ route('articulos.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start h-100 border d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary fs-4">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="fw-bold text-heading">Catálogo Artículos</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Bienes, repuestos y stock</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('activos.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start h-100 border d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-info bg-opacity-10 text-info fs-4">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div>
                    <div class="fw-bold text-heading">Activos & QR</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Equipos y números de serie</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('kits.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start h-100 border d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning fs-4">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <div class="fw-bold text-heading">Kits de Almacén</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Kits armados y componentes</small>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('despachos.index') }}" class="btn btn-outline-secondary w-100 p-3 text-start h-100 border d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success fs-4">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="fw-bold text-heading">Mis Despachos</div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Actas de préstamo y firma</small>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Tabla 1: Herramientas en Custodia -->
    <div class="col-lg-7">
        <div class="admin-card overflow-hidden h-100 border">
            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-heading">
                    <i class="bi bi-tools text-info me-2"></i> Herramientas & Equipos en mi Custodia
                </h6>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-bold">
                    {{ $misActivosCustodia->count() }} ítems
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-secondary">
                        <tr style="font-size: 0.75rem;">
                            <th class="ps-3 py-2">Código / QR</th>
                            <th class="py-2">Descripción del Equipo</th>
                            <th class="py-2">N° Serie / Placa</th>
                            <th class="py-2">Ubicación Actual</th>
                            <th class="text-end pe-3 py-2">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($misActivosCustodia as $act)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-body border font-monospace text-primary fw-bold" style="font-size: 0.75rem;">
                                        {{ $act->codigo_interno }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-heading small">{{ $act->articulo?->descripcion ?? 'N/A' }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">SKU: {{ $act->articulo?->codigo_sku }}</small>
                                </td>
                                <td>
                                    <span class="font-monospace small text-muted">{{ $act->numero_serie ?: 'S/N' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $act->ubicacion?->nombre ?? 'Campo' }}</small>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('activos.show', $act) }}" class="btn btn-outline-secondary btn-sm" title="Ver Ficha y QR">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                                    No cuenta con herramientas serializadas en custodia actualmente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla 2: Despachos y Actas Asignadas -->
    <div class="col-lg-5">
        <div class="admin-card overflow-hidden h-100 border">
            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-heading">
                    <i class="bi bi-file-earmark-text text-primary me-2"></i> Mis Despachos & Actas
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-bold">
                    Últimos {{ $misDespachos->count() }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-secondary">
                        <tr style="font-size: 0.75rem;">
                            <th class="ps-3 py-2">Guía</th>
                            <th class="py-2">Fecha</th>
                            <th class="py-2">Estado</th>
                            <th class="text-end pe-3 py-2">Acta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($misDespachos as $dsp)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('despachos.show', $dsp) }}" class="fw-bold font-monospace small text-decoration-none">
                                        {{ $dsp->numero_guia }}
                                    </a>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $dsp->fecha_despacho?->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    @php
                                        $estColor = match($dsp->estado) {
                                            'ENTREGADO_EN_CAMPO' => 'badge-soft-primary',
                                            'PARCIALMENTE_DEVUELTO' => 'badge-soft-warning',
                                            'DEVUELTO_TOTAL' => 'badge-soft-success',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $estColor }}" style="font-size: 0.68rem;">
                                        {{ str_replace('_', ' ', $dsp->estado) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('despachos.acta', $dsp) }}" target="_blank" class="btn btn-outline-dark btn-sm" title="Imprimir Acta">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                                    No se registran despachos emitidos a su nombre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
