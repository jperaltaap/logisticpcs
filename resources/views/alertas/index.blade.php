@extends('layouts.admin')

@section('title', 'Notificaciones y Alertas del Sistema')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Centro de Alertas</li>
                </ol>
            </nav>
            <h1 class="h3 font-bold text-dark dark:text-white mb-0">Centro de Alertas Operativas</h1>
            <p class="text-muted text-sm mb-0">Control de stock por agotarse, préstamos/salidas a campo vencidos y calibraciones por vencer.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('alertas.escanear') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat"></i> Escanear Alertas Ahora
                </button>
            </form>
            @if($metricas['no_leidas'] > 0)
                <form action="{{ route('alertas.marcarTodasLeidas') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-all"></i> Marcar Todas como Leídas
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Métricas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Total Alertas</span>
                        <h3 class="font-bold text-dark dark:text-white mb-0 mt-1">{{ number_format($metricas['total']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-bell"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">No Leídas</span>
                        <h3 class="font-bold text-danger mb-0 mt-1">{{ number_format($metricas['no_leidas']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Stock Mínimo</span>
                        <h3 class="font-bold text-warning mb-0 mt-1">{{ number_format($metricas['stock_minimo']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Préstamos Vencidos</span>
                        <h3 class="font-bold text-danger mb-0 mt-1">{{ number_format($metricas['prestamos_vencidos'] ?? 0) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Calibraciones</span>
                        <h3 class="font-bold text-info mb-0 mt-1">{{ number_format($metricas['calibraciones']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('alertas.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select name="tipo" class="form-select">
                        <option value="">-- Todos los Tipos de Alerta --</option>
                        <option value="STOCK_MINIMO" {{ request('tipo') == 'STOCK_MINIMO' ? 'selected' : '' }}>Stock Mínimo / Por Agotarse</option>
                        <option value="PRESTAMO_VENCIDO" {{ request('tipo') == 'PRESTAMO_VENCIDO' ? 'selected' : '' }}>Préstamo / Salida a Campo Vencido</option>
                        <option value="CALIBRACION_POR_VENCER" {{ request('tipo') == 'CALIBRACION_POR_VENCER' ? 'selected' : '' }}>Calibración Por Vencer</option>
                        <option value="MATERIAL_SIN_MOVIMIENTO" {{ request('tipo') == 'MATERIAL_SIN_MOVIMIENTO' ? 'selected' : '' }}>Material Sin Movimiento</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="leida" class="form-select">
                        <option value="">-- Todos los Estados --</option>
                        <option value="0" {{ request('leida') === '0' ? 'selected' : '' }}>Solo No Leídas</option>
                        <option value="1" {{ request('leida') === '1' ? 'selected' : '' }}>Solo Leídas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    @if(request()->hasAny(['tipo', 'leida']))
                        <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="list-group list-group-flush">
            @forelse($alertas as $alerta)
                <div class="list-group-item p-4 {{ !$alerta->leida ? 'bg-primary-subtle/10 border-start border-4 border-primary' : '' }}">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex gap-3">
                            <div class="mt-1">
                                @if($alerta->tipo == 'STOCK_MINIMO')
                                    <span class="badge bg-warning text-dark p-2 rounded-3 fs-5"><i class="bi bi-exclamation-triangle"></i></span>
                                @elseif($alerta->tipo == 'CALIBRACION_POR_VENCER')
                                    <span class="badge bg-info text-white p-2 rounded-3 fs-5"><i class="bi bi-tools"></i></span>
                                @elseif($alerta->tipo == 'PRESTAMO_VENCIDO')
                                    <span class="badge bg-danger text-white p-2 rounded-3 fs-5"><i class="bi bi-clock-history"></i></span>
                                @else
                                    <span class="badge bg-secondary text-white p-2 rounded-3 fs-5"><i class="bi bi-bell"></i></span>
                                @endif
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h6 class="font-bold text-dark dark:text-white mb-0">{{ $alerta->titulo }}</h6>
                                    @if(!$alerta->leida)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs">Nueva</span>
                                    @endif
                                </div>
                                <p class="text-sm text-secondary mb-2">{{ $alerta->mensaje }}</p>
                                <span class="text-xs text-muted d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-clock"></i> {{ $alerta->fecha_alerta ? $alerta->fecha_alerta->diffForHumans() : $alerta->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            @if($alerta->tipo === 'STOCK_MINIMO')
                                <a href="{{ route('inventario.stock', ['estado_stock' => 'bajo']) }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold" title="Ver Stock por Agotarse">
                                    <i class="bi bi-box-seam me-1"></i> Stock
                                </a>
                            @elseif($alerta->tipo === 'PRESTAMO_VENCIDO' && $alerta->referencia_id)
                                <a href="{{ route('despachos.show', $alerta->referencia_id) }}" class="btn btn-sm btn-outline-primary fw-semibold" title="Ver Salida / Préstamo">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver Préstamo
                                </a>
                            @elseif($alerta->tipo === 'CALIBRACION_POR_VENCER')
                                <a href="{{ route('mantenimientos.index') }}" class="btn btn-sm btn-outline-info fw-semibold" title="Ir a Calibraciones & Taller">
                                    <i class="bi bi-tools me-1"></i> Taller
                                </a>
                            @endif
                            @if(!$alerta->leida)
                                <form action="{{ route('alertas.marcarLeida', $alerta) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar como leída">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('alertas.destroy', $alerta) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar esta alerta?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                    No hay notificaciones ni alertas registradas en este momento.
                </div>
            @endforelse
        </div>

        @if($alertas->hasPages())
            <div class="card-footer bg-white dark:bg-dark border-top border-light dark:border-secondary py-3 px-4">
                {{ $alertas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
