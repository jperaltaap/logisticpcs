@extends('layouts.admin')

@section('title', 'Despachos & Vales de Salida')
@section('page_title', 'Gestión de Despachos, Préstamos & Vales de Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Operaciones de Almacén</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Despachos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
    <a href="{{ route('despachos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Nueva Orden de Despacho
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Filtros de Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form action="{{ route('despachos.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filtro_despacho_search" class="form-label small fw-semibold text-muted mb-1">Buscar por N° Guía, Personal o Proyecto</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="filtro_despacho_search" name="search" class="form-control" placeholder="Ej: DSP-2026-0001, Juan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="filtro_despacho_proyecto" class="form-label small fw-semibold text-muted mb-1">Proyecto</label>
                <select id="filtro_despacho_proyecto" name="proyecto_id" class="form-select form-select-sm">
                    <option value="">Todos los proyectos</option>
                    @foreach($proyectos as $pry)
                        <option value="{{ $pry->id }}" {{ request('proyecto_id') == $pry->id ? 'selected' : '' }}>
                            {{ $pry->codigo }} - {{ $pry->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_despacho_tipo" class="form-label small fw-semibold text-muted mb-1">Tipo Movimiento</label>
                <select id="filtro_despacho_tipo" name="tipo_movimiento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="SALIDA_PRESTAMO_CAMPO" {{ request('tipo_movimiento') == 'SALIDA_PRESTAMO_CAMPO' ? 'selected' : '' }}>PRÉSTAMO CAMPO</option>
                    <option value="CONSUMO_DIRECTO" {{ request('tipo_movimiento') == 'CONSUMO_DIRECTO' ? 'selected' : '' }}>CONSUMO DIRECTO</option>
                    <option value="TRANSFERENCIA_UBICACION" {{ request('tipo_movimiento') == 'TRANSFERENCIA_UBICACION' ? 'selected' : '' }}>TRANSFERENCIA</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_despacho_estado" class="form-label small fw-semibold text-muted mb-1">Estado</label>
                <select id="filtro_despacho_estado" name="estado" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    <option value="ENTREGADO_EN_CAMPO" {{ request('estado') == 'ENTREGADO_EN_CAMPO' ? 'selected' : '' }}>EN CAMPO</option>
                    <option value="PARCIALMENTE_DEVUELTO" {{ request('estado') == 'PARCIALMENTE_DEVUELTO' ? 'selected' : '' }}>PARCIALMENTE DEVUELTO</option>
                    <option value="DEVUELTO_TOTAL" {{ request('estado') == 'DEVUELTO_TOTAL' ? 'selected' : '' }}>DEVUELTO TOTAL</option>
                    <option value="ANULADO" {{ request('estado') == 'ANULADO' ? 'selected' : '' }}>ANULADO</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtrar
                </button>
                <a href="{{ route('despachos.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros de búsqueda" title="Limpiar filtros">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Despachos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla de órdenes de despacho">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3" style="width: 140px;">N° Guía / Vale</th>
                        <th scope="col">Tipo Operación</th>
                        <th scope="col">Proyecto Asignado</th>
                        <th scope="col">Personal que Retira / Traslada</th>
                        <th scope="col">Almacén Origen</th>
                        <th scope="col">Fecha Despacho</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Firma</th>
                        <th scope="col" class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($despachos as $dsp)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                <a href="{{ route('despachos.show', $dsp) }}" class="text-decoration-none">
                                    {{ $dsp->numero_guia }}
                                </a>
                                <div class="small text-muted">{{ $dsp->detalles_count }} items</div>
                            </td>
                            <td>
                                @php
                                    $tipoBadge = match($dsp->tipo_movimiento) {
                                        'SALIDA_PRESTAMO_CAMPO' => 'badge-soft-primary',
                                        'CONSUMO_DIRECTO' => 'badge-soft-danger',
                                        'TRANSFERENCIA_UBICACION' => 'badge-soft-info',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $tipoBadge }}">{{ str_replace('_', ' ', $dsp->tipo_movimiento) }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $dsp->proyecto->nombre ?? 'N/A' }}</div>
                                <div class="small text-muted font-monospace">{{ $dsp->proyecto->codigo ?? '' }}</div>
                            </td>
                            <td>
                                @if($dsp->personal)
                                    <div class="fw-semibold text-heading">
                                        <i class="bi bi-person text-primary me-1" aria-hidden="true"></i> {{ $dsp->personal->nombre_completo }}
                                    </div>
                                @else
                                    <span class="text-muted small">Sin asignar</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-heading">
                                    <i class="bi bi-geo-alt text-muted me-1" aria-hidden="true"></i> {{ $dsp->ubicacionOrigen->nombre ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="small text-muted">
                                {{ $dsp->fecha_despacho?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $estBadge = match($dsp->estado) {
                                        'ENTREGADO_EN_CAMPO' => 'badge-soft-primary',
                                        'PARCIALMENTE_DEVUELTO' => 'badge-soft-warning',
                                        'DEVUELTO_TOTAL' => 'badge-soft-success',
                                        'ANULADO' => 'badge-soft-secondary',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $estBadge }}">{{ str_replace('_', ' ', $dsp->estado) }}</span>
                            </td>
                            <td>
                                @if($dsp->firma_digital_base64)
                                    <span class="badge badge-soft-success" title="Firmado Digitalmente">
                                        <i class="bi bi-pen-fill" aria-hidden="true"></i> Firmado
                                    </span>
                                @else
                                    <span class="badge badge-soft-secondary">Sin Firma</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('despachos.show', $dsp) }}" class="btn btn-outline-secondary" aria-label="Ver detalles de la guía {{ $dsp->numero_guia }}" title="Ver Detalles">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('despachos.acta', $dsp) }}" target="_blank" class="btn btn-outline-dark" aria-label="Imprimir acta de entrega de la guía {{ $dsp->numero_guia }}" title="Imprimir Acta de Entrega">
                                        <i class="bi bi-printer" aria-hidden="true"></i>
                                    </a>
                                    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true) && in_array($dsp->estado, ['ENTREGADO_EN_CAMPO', 'PARCIALMENTE_DEVUELTO']))
                                        <a href="{{ route('despachos.devolucion', $dsp) }}" class="btn btn-outline-warning text-dark fw-bold" aria-label="Registrar devolución de la guía {{ $dsp->numero_guia }}" title="Registrar Devolución">
                                            <i class="bi bi-arrow-return-left" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No se encontraron órdenes de despacho registradas.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($despachos->hasPages())
            <div class="p-3 border-top">
                {{ $despachos->links() }}
            </div>
        @endif
    </div>

@endsection

