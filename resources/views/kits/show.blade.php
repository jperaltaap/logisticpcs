@extends('layouts.admin')

@section('title', 'Kit ' . $kit->codigo_kit)
@section('page_title', $kit->nombre_kit)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kits.index') }}" class="text-decoration-none fw-semibold text-primary">Kits</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $kit->codigo_kit }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        @if(auth()->user()->rol !== 'AUDITOR')
        <a href="{{ route('kits.edit', $kit) }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
            <i class="bi bi-pencil me-1"></i> Editar Kit
        </a>
        @endif
        <a href="{{ route('kits.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    @php
        $detallesPorArticulo = collect($disponibilidad['detalles'] ?? [])->keyBy('articulo_id');
    @endphp

    <div class="row g-4 mb-4">
        <!-- Ficha Resumen y Monitoreo de Almacén del Kit -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-collection"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-1">{{ $kit->nombre_kit }}</h5>
                    <div class="text-primary font-monospace fw-bold">{{ $kit->codigo_kit }}</div>
                    <div class="mt-2 d-flex flex-wrap justify-content-center gap-1">
                        @php
                            $tipoBadge = match($kit->tipo_kit) {
                                'KIT_HERRAMIENTAS' => 'badge-soft-info',
                                'KIT_EPP' => 'badge-soft-warning',
                                'KIT_EMPALME' => 'badge-soft-success',
                                default => 'badge-soft-secondary',
                            };
                        @endphp
                        <span class="badge {{ $tipoBadge }}">{{ str_replace('_', ' ', $kit->tipo_kit) }}</span>
                        <span class="badge {{ $kit->estado === 'ACTIVO' ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                            {{ $kit->estado }}
                        </span>
                    </div>
                </div>

                <div class="p-3 rounded-3 border mb-3 {{ ($disponibilidad['disponible'] ?? false) ? 'bg-success bg-opacity-10 border-success border-opacity-25' : 'bg-warning bg-opacity-10 border-warning border-opacity-25' }}">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-heading">
                            <i class="bi bi-building-check me-1 text-primary"></i> Almacén Asignado:
                        </span>
                        <span class="badge bg-primary">{{ $kit->ubicacion->nombre ?? 'Sin almacén' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Estado de Disponibilidad:</span>
                        @if($disponibilidad['disponible'] ?? false)
                            <span class="badge bg-success">COMPLETO Y DISPONIBLE</span>
                        @else
                            <span class="badge bg-warning text-dark">CON FALTANTE ({{ $disponibilidad['faltantes'] ?? 0 }})</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Paquetes Armables en Almacén:</span>
                        <span class="fw-bold fs-6 text-heading">{{ $disponibilidad['kits_armables'] ?? 0 }} kit(s)</span>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <div class="fw-bold small text-muted mb-1">Descripción del Kit:</div>
                    <p class="small text-heading mb-0">
                        {{ $kit->descripcion ?? 'Sin descripción detallada.' }}
                    </p>
                </div>

                <div class="mt-4 p-3 bg-light rounded small border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Artículos Requeridos:</span>
                        <span class="fw-bold text-heading">{{ $kit->componentes->count() }} items</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Última Modificación:</span>
                        <span class="fw-semibold text-heading">{{ $kit->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Componentes del Kit y Desvinculación Independiente -->
        <div class="col-lg-8">
            <div class="admin-card overflow-hidden h-100">
                <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light bg-opacity-25">
                    <div>
                        <div class="fw-bold text-heading">
                            <i class="bi bi-box-seam me-2 text-primary"></i> Componentes en {{ $kit->ubicacion->nombre ?? 'Almacén' }} ({{ $kit->componentes->count() }})
                        </div>
                        <div class="text-muted" style="font-size: 0.76rem;">
                            Si un elemento requiere préstamo o traslado independiente, puede desvincularlo del kit para movilizarlo sin afectar al resto de componentes.
                        </div>
                    </div>
                    @if(auth()->user()->rol !== 'AUDITOR')
                    <a href="{{ route('kits.edit', $kit) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Modificar Componentes
                    </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">SKU / Artículo</th>
                                <th class="text-center">Tipo Control</th>
                                <th class="text-end">Requerido</th>
                                <th class="text-end">Stock en Almacén</th>
                                <th class="text-center">Disponibilidad</th>
                                @if(auth()->user()->rol !== 'AUDITOR')
                                <th class="text-end pe-3">Movimiento Independiente / Kit</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kit->componentes as $comp)
                                @php
                                    $infoStock = $detallesPorArticulo->get($comp->articulo_id);
                                    $stockActual = $infoStock['disponible'] ?? 0;
                                    $cantNum = (float) $comp->cantidad;
                                    $requerido = number_format($cantNum, 2);
                                    $cumple = $infoStock['cumple'] ?? ($stockActual >= $cantNum);
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="font-monospace fw-bold text-primary">
                                            <a href="{{ route('articulos.show', $comp->articulo) }}" class="text-decoration-none">
                                                {{ $comp->articulo->codigo_sku }}
                                            </a>
                                        </div>
                                        <div class="fw-semibold text-heading">{{ $comp->articulo->descripcion }}</div>
                                        <div class="small text-muted">
                                            {{ $comp->articulo->categoria->nombre ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($comp->articulo->control_serie)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <i class="bi bi-qr-code me-1"></i> Serializado
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                <i class="bi bi-box me-1"></i> Consumible
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary">
                                            {{ $requerido }} {{ $comp->articulo->unidad_medida }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $cumple ? 'text-success' : 'text-danger' }}">
                                            {{ $stockActual }} {{ $comp->articulo->unidad_medida }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($cumple)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check-circle me-1"></i>Disponible
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="bi bi-exclamation-circle me-1"></i>Faltante
                                            </span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->rol !== 'AUDITOR')
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1">
                                            <form action="{{ route('kits.componentes.desvincular', [$kit, $comp->articulo]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="ir_a_despacho" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-warning fw-semibold" title="Desvincular del kit y registrar préstamo o traslado independiente">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Desvincular y Dar Salida
                                                </button>
                                            </form>
                                            <form action="{{ route('kits.componentes.desvincular', [$kit, $comp->articulo]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desvincular este ítem del kit {{ $kit->codigo_kit }} para que no afecte la composición del kit?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Solo desvincular del kit">
                                                    <i class="bi bi-link-45deg"></i> Desvincular
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Este kit no tiene componentes asociados actualmente.
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

