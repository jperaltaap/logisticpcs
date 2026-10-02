@extends('layouts.admin')

@section('title', 'Taller & Calibraciones')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Taller & Calibraciones</li>
                </ol>
            </nav>
            <h1 class="h3 font-bold text-dark dark:text-white mb-0">Gestión de Taller & Calibraciones de Equipos</h1>
            <p class="text-muted text-sm mb-0">Control de mantenimiento preventivo, correctivo, calibraciones de laboratorio e inmovilización de activos.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('mantenimientos.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-tools"></i> Enviar a Taller / Calibrar
            </a>
        </div>
    </div>

    <!-- Métricas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Total Servicios</span>
                        <h3 class="font-bold text-dark dark:text-white mb-0 mt-1">{{ number_format($metricas['total']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">En Taller / Pendiente</span>
                        <h3 class="font-bold text-warning mb-0 mt-1">{{ number_format($metricas['en_taller']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Calibraciones Lab</span>
                        <h3 class="font-bold text-info mb-0 mt-1">{{ number_format($metricas['calibraciones']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-speedometer"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white dark:bg-dark p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-xs font-bold text-uppercase tracking-wider">Conformes / Operativos</span>
                        <h3 class="font-bold text-success mb-0 mt-1">{{ number_format($metricas['operativos']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Seguimiento de Equipos -->
    <ul class="nav nav-pills mb-3 gap-2" id="tallerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="{{ route('mantenimientos.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'en_taller'])) }}" 
               class="nav-link fw-bold px-3 py-2 rounded-3 {{ ($tab ?? 'en_taller') === 'en_taller' ? 'active shadow-sm' : 'bg-light text-dark' }}">
                <i class="bi bi-clock-history me-1"></i> Equipos en Taller / Calibración Activa
                <span class="badge {{ ($tab ?? 'en_taller') === 'en_taller' ? 'bg-white text-primary' : 'bg-warning text-dark' }} ms-1">{{ $metricas['en_taller'] }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('mantenimientos.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'concluidos'])) }}" 
               class="nav-link fw-bold px-3 py-2 rounded-3 {{ ($tab ?? '') === 'concluidos' ? 'active shadow-sm' : 'bg-light text-dark' }}">
                <i class="bi bi-check-circle-fill me-1"></i> Historial de Servicios Concluidos
                <span class="badge {{ ($tab ?? '') === 'concluidos' ? 'bg-white text-primary' : 'bg-success' }} ms-1">{{ $metricas['concluidos'] }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('mantenimientos.index', array_merge(request()->except(['page', 'tab']), ['tab' => 'todos'])) }}" 
               class="nav-link fw-bold px-3 py-2 rounded-3 {{ ($tab ?? '') === 'todos' ? 'active shadow-sm' : 'bg-light text-dark' }}">
                <i class="bi bi-list-task me-1"></i> Todos los Registros
                <span class="badge {{ ($tab ?? '') === 'todos' ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $metricas['total'] }}</span>
            </a>
        </li>
    </ul>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('mantenimientos.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $tab ?? 'en_taller' }}">
                <div class="col-md-5">
                    <label for="filtro_busqueda" class="visually-hidden">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white dark:bg-dark border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" id="filtro_busqueda" class="form-control border-start-0" placeholder="Buscar por código de activo, serie, taller o detalle..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="filtro_tipo" class="visually-hidden">Tipo</label>
                    <select name="tipo" id="filtro_tipo" class="form-select">
                        <option value="">-- Todos los Tipos --</option>
                        <option value="PREVENTIVO" {{ request('tipo') == 'PREVENTIVO' ? 'selected' : '' }}>Preventivo</option>
                        <option value="CORRECTIVO" {{ request('tipo') == 'CORRECTIVO' ? 'selected' : '' }}>Correctivo</option>
                        <option value="CALIBRACION_LAB" {{ request('tipo') == 'CALIBRACION_LAB' ? 'selected' : '' }}>Calibración de Laboratorio</option>
                        <option value="CERTIFICACION" {{ request('tipo') == 'CERTIFICACION' ? 'selected' : '' }}>Certificación Anual / Patrón</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtro_resultado" class="visually-hidden">Estado</label>
                    <select name="resultado" id="filtro_resultado" class="form-select">
                        <option value="">-- Todos los Estados --</option>
                        <option value="EN_PROCESO" {{ request('resultado') == 'EN_PROCESO' ? 'selected' : '' }}>En Proceso</option>
                        <option value="CONFORME_OPERATIVO" {{ request('resultado') == 'CONFORME_OPERATIVO' ? 'selected' : '' }}>Conforme Operativo</option>
                        <option value="NO_CONFORME_BAJA" {{ request('resultado') == 'NO_CONFORME_BAJA' ? 'selected' : '' }}>No Conforme / Baja</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100" aria-label="Filtrar servicios"><i class="bi bi-funnel"></i> Filtrar</button>
                    @if(request()->hasAny(['q', 'tipo', 'resultado']))
                        <a href="{{ route('mantenimientos.index', ['tab' => $tab ?? 'en_taller']) }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Listado de servicios de mantenimiento y calibración">
            <table class="table align-middle mb-0">
                <thead class="bg-light dark:bg-gray-800 text-xs text-uppercase font-bold text-muted">
                    <tr>
                        <th scope="col" class="ps-4">Código Activo / Equipo</th>
                        <th scope="col">Tipo Servicio</th>
                        <th scope="col">Proveedor / Taller</th>
                        <th scope="col">Fecha Ingreso</th>
                        <th scope="col">Fecha Retorno</th>
                        <th scope="col">Próxima Calibración</th>
                        <th scope="col" class="text-center">Resultado</th>
                        <th scope="col" class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($mantenimientos as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-10 h-10 rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center text-secondary font-bold">
                                        <i class="bi bi-cpu"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('activos.show', $item->activo) }}" class="font-bold text-dark dark:text-white text-decoration-none text-sm">
                                             {{ $item->activo->codigo_interno }}
                                        </a>
                                        <div class="text-xs text-muted">{{ $item->activo->articulo->descripcion ?? $item->activo->articulo->nombre ?? 'N/A' }} | S/N: {{ $item->activo->numero_serie ?? 'S/N' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary px-2 py-1 text-xs">{{ $item->tipo }}</span>
                            </td>
                            <td class="text-sm font-medium text-dark dark:text-light">
                                {{ $item->proveedor_taller ?? 'Taller Interno' }}
                            </td>
                            <td class="text-sm text-muted">
                                {{ $item->fecha_ingreso ? $item->fecha_ingreso->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-sm text-muted">
                                @if($item->fecha_salida)
                                    <span class="text-success font-semibold">{{ $item->fecha_salida->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">En Taller</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                @if($item->proxima_calibracion_sugerida)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle font-semibold">
                                        {{ $item->proxima_calibracion_sugerida->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->resultado == 'CONFORME_OPERATIVO')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">CONFORME (OPERATIVO)</span>
                                @elseif($item->resultado == 'NO_CONFORME_BAJA')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">DADO DE BAJA</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">EN PROCESO</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if($item->resultado === 'EN_PROCESO')
                                        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAlta-{{ $item->id }}" aria-label="Dar de alta al activo {{ $item->activo->codigo_interno }}">
                                            <i class="bi bi-check2-circle"></i> Dar de Alta
                                        </button>
                                    @endif
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('mantenimientos.show', $item) }}" class="btn btn-outline-info" title="Ver Detalle" aria-label="Ver detalle de servicio">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('mantenimientos.edit', $item) }}" class="btn btn-outline-primary" title="Editar" aria-label="Editar servicio">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('mantenimientos.destroy', $item) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar este registro?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar" aria-label="Eliminar servicio">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if($item->resultado === 'EN_PROCESO')
                                    <!-- Modal Dar de Alta para este registro -->
                                    <div class="modal fade text-start" id="modalAlta-{{ $item->id }}" tabindex="-1" aria-labelledby="modalAltaLabel-{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('mantenimientos.dar-alta', $item) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white py-3 px-4">
                                                        <h5 class="modal-title fw-bold" id="modalAltaLabel-{{ $item->id }}">
                                                            <i class="bi bi-check-circle-fill me-2"></i> Dar de Alta / Retorno a Almacén
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <div class="fw-bold text-dark fs-6">{{ $item->activo->codigo_interno }} - {{ $item->activo->articulo->descripcion ?? $item->activo->articulo->nombre }}</div>
                                                            <div class="small text-muted font-monospace">Serie: {{ $item->activo->numero_serie ?? 'S/N' }} | Almacén Base: {{ $item->activo->ubicacion->nombre ?? 'N/A' }}</div>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label for="fecha_salida_{{ $item->id }}" class="form-label small fw-bold">Fecha de Retorno / Alta <span class="text-danger">*</span></label>
                                                                <input type="date" name="fecha_salida" id="fecha_salida_{{ $item->id }}" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="resultado_{{ $item->id }}" class="form-label small fw-bold">Evaluación Técnica <span class="text-danger">*</span></label>
                                                                <select name="resultado" id="resultado_{{ $item->id }}" class="form-select" required>
                                                                    <option value="CONFORME_OPERATIVO" selected>CONFORME (Retorna a OPERATIVO)</option>
                                                                    <option value="NO_CONFORME_BAJA">NO CONFORME (Dar de BAJA)</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="proxima_calibracion_{{ $item->id }}" class="form-label small fw-bold">Próxima Calibración / Revisión</label>
                                                                <input type="date" name="proxima_calibracion_sugerida" id="proxima_calibracion_{{ $item->id }}" class="form-control" value="{{ $item->proxima_calibracion_sugerida?->format('Y-m-d') }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="costo_{{ $item->id }}" class="form-label small fw-bold">Costo Final (S/.)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">S/.</span>
                                                                    <input type="number" step="0.01" min="0" name="costo" id="costo_{{ $item->id }}" class="form-control" value="{{ $item->costo > 0 ? $item->costo : '' }}" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <label for="descripcion_falla_{{ $item->id }}" class="form-label small fw-bold">Informe de Conformidad / Observaciones</label>
                                                                <textarea name="descripcion_falla_o_trabajo" id="descripcion_falla_{{ $item->id }}" rows="2" class="form-control" placeholder="Indicar conformidad de calibración o reparación..."></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light py-2 px-4 border-top">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">
                                                            <i class="bi bi-box-arrow-in-down-left me-1"></i> Confirmar Alta y Retorno
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-tools fs-1 d-block mb-2 text-secondary"></i>
                                @if(($tab ?? 'en_taller') === 'en_taller')
                                    No hay equipos pendientes en taller o calibración activa en este momento.
                                @else
                                    No se encontraron registros de mantenimiento o calibración.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mantenimientos->hasPages())
            <div class="card-footer bg-white dark:bg-dark border-top border-light dark:border-secondary py-3 px-4">
                {{ $mantenimientos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
