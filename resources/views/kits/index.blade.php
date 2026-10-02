@extends('layouts.admin')

@section('title', 'Monitoreo y Gestión de Kits por Almacén')
@section('page_title', 'Monitoreo y Gestión de Kits Disponibles por Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Inventario & Activos</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Kits por Almacén</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
    <a href="{{ route('kits.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-plus-lg me-1"></i> Configurar Nuevo Kit
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Resumen de Monitoreo de Kits por Almacén -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-card p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Kits Configurados</div>
                        <div class="fs-4 fw-bold text-heading">{{ $resumenKits['total'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 fs-4">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-card p-3 h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Kits Disponibles (Completos)</div>
                        <div class="fs-4 fw-bold text-success">{{ $resumenKits['completos'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 fs-4">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-card p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Kits con Faltante en Almacén</div>
                        <div class="fs-4 fw-bold text-warning">{{ $resumenKits['incompletos'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2 fs-4">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-card p-3 h-100 border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Capacidad Total Armable</div>
                        <div class="fs-4 fw-bold text-info">{{ $resumenKits['armables_total'] ?? 0 }} <span class="fs-6 fw-normal text-muted">kits</span></div>
                    </div>
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-2 fs-4">
                        <i class="bi bi-layers-half"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda y Monitoreo por Almacén -->
    <div class="admin-card p-3 mb-4">
        <form action="{{ route('kits.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Buscar Código o Nombre</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Ej: Kit Empalme, KIT-001..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Almacén de Ubicación</label>
                <select name="ubicacion_id" class="form-select form-select-sm">
                    <option value="">Todos los almacenes</option>
                    @foreach($ubicaciones as $ubi)
                        <option value="{{ $ubi->id }}" {{ (string) request('ubicacion_id') === (string) $ubi->id ? 'selected' : '' }}>
                            [{{ $ubi->codigo }}] {{ $ubi->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Disponibilidad Stock</label>
                <select name="disponibilidad" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="COMPLETO" {{ request('disponibilidad') === 'COMPLETO' ? 'selected' : '' }}>DISPONIBLE EN ALMACÉN</option>
                    <option value="INCOMPLETO" {{ request('disponibilidad') === 'INCOMPLETO' ? 'selected' : '' }}>CON ÍTEM FALTANTE</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Tipo de Kit</label>
                <select name="tipo_kit" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <option value="KIT_HERRAMIENTAS" {{ request('tipo_kit') == 'KIT_HERRAMIENTAS' ? 'selected' : '' }}>KIT DE HERRAMIENTAS</option>
                    <option value="KIT_EPP" {{ request('tipo_kit') == 'KIT_EPP' ? 'selected' : '' }}>KIT DE EPP (SEGURIDAD)</option>
                    <option value="KIT_EMPALME" {{ request('tipo_kit') == 'KIT_EMPALME' ? 'selected' : '' }}>KIT DE EMPALME</option>
                    <option value="KIT_MATERIALES" {{ request('tipo_kit') == 'KIT_MATERIALES' ? 'selected' : '' }}>KIT DE MATERIALES</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Monitorear
                </button>
                <a href="{{ route('kits.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros de kits" title="Limpiar filtros">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Kits -->
    <div class="row g-3">
        @forelse($kits as $kit)
            @php
                $eval = $disponibilidadMap[$kit->id] ?? $kit->evaluarDisponibilidadEnAlmacen($kit->ubicacion_id);
                $porcentajeCompleto = $eval['total_componentes'] > 0
                    ? (int) round(($eval['completos'] / $eval['total_componentes']) * 100)
                    : 0;
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge badge-soft-primary font-monospace fw-bold">
                                {{ $kit->codigo_kit }}
                            </span>
                            @php
                                $tipoBadge = match($kit->tipo_kit) {
                                    'KIT_HERRAMIENTAS' => 'badge-soft-info',
                                    'KIT_EPP' => 'badge-soft-warning',
                                    'KIT_EMPALME' => 'badge-soft-success',
                                    default => 'badge-soft-secondary',
                                };
                            @endphp
                            <span class="badge {{ $tipoBadge }}">{{ str_replace('_', ' ', $kit->tipo_kit) }}</span>
                        </div>

                        <h5 class="fw-bold text-heading mb-1">
                            <a href="{{ route('kits.show', $kit) }}" class="text-decoration-none">
                                {{ $kit->nombre_kit }}
                            </a>
                        </h5>

                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.72rem;">
                                <i class="bi bi-building-check me-1"></i>{{ $kit->ubicacion->nombre ?? 'Sin almacén asignado' }}
                            </span>
                            @if(!session('proyecto_activo_id') && $kit->proyecto)
                                <span class="badge bg-body-secondary text-body border" style="font-size: 0.68rem;">
                                    <i class="bi bi-buildings me-1"></i>{{ $kit->proyecto->nombre }}
                                </span>
                            @endif
                        </div>

                        <p class="small text-muted mb-3" style="min-height: 36px;">
                            {{ Str::limit($kit->descripcion, 100, '...') }}
                        </p>

                        <!-- Estado de Disponibilidad en Almacén -->
                        <div class="p-2 rounded border bg-body-tertiary mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                @if($eval['disponible'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i>DISPONIBLE EN ALMACÉN
                                    </span>
                                    <span class="small fw-bold text-success">{{ $eval['kits_armables'] }} kit(s) armables</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>FALTANTE DE STOCK
                                    </span>
                                    <span class="small fw-bold text-danger">{{ $eval['faltantes'] }} ítem(s) sin stock</span>
                                @endif
                            </div>
                            <div class="progress" style="height: 6px;" role="progressbar" aria-valuenow="{{ $porcentajeCompleto }}" aria-valuemin="0" aria-valuemax="100" aria-label="Disponibilidad de componentes de {{ $kit->nombre_kit }}: {{ $porcentajeCompleto }}%">
                                <div class="progress-bar {{ $eval['disponible'] ? 'bg-success' : 'bg-warning' }}"
                                     style="width: {{ $porcentajeCompleto }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted" style="font-size: 0.72rem;">
                                    {{ $eval['completos'] }} de {{ $eval['total_componentes'] }} componentes listos
                                </span>
                                @if(!$eval['disponible'] && $eval['faltantes'] > 0 && !in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
                                    <a href="{{ route('kits.show', $kit) }}" class="text-decoration-none fw-semibold" style="font-size: 0.72rem;">
                                        Gestionar / Desvincular <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1 text-muted small">
                            <i class="bi bi-collection-play text-primary" aria-hidden="true"></i>
                            <span class="fw-bold text-heading">{{ $kit->componentes_count }}</span> componentes
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('kits.show', $kit) }}" class="btn btn-outline-secondary" aria-label="Monitorear o desvincular ítems de {{ $kit->nombre_kit }}" title="Monitorear / Desvincular Ítems">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                            @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
                            <a href="{{ route('kits.edit', $kit) }}" class="btn btn-outline-primary" aria-label="Editar kit {{ $kit->nombre_kit }}" title="Editar">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                            <form action="{{ route('kits.destroy', $kit) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar el kit {{ $kit->nombre_kit }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" aria-label="Eliminar kit {{ $kit->nombre_kit }}" title="Eliminar">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="admin-card p-5 text-center text-muted">
                    <i class="bi bi-boxes fs-1 d-block mb-2 opacity-50"></i>
                    No se encontraron kits registrados en el almacén o con los filtros seleccionados.
                </div>
            </div>
        @endforelse
    </div>

    @if($kits->hasPages())
        <div class="mt-4">
            {{ $kits->links() }}
        </div>
    @endif

@endsection

