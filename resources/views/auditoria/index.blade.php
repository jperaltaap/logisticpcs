@extends('layouts.admin')

@section('title', 'Auditoría & Log del Sistema')
@section('page_title', 'Auditoría & Log de Eventos del Sistema')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración & Seguridad</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Log del Sistema</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->rol === 'ADMINISTRADOR')
        <a href="{{ route('backups.index') }}" class="btn btn-outline-primary btn-sm px-3 fw-bold">
            <i class="bi bi-database-fill-down me-1"></i> Backups de Base de Datos
        </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- KPIs de Auditoría -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Eventos Registrados</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-journal-code"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ number_format($stats['total']) }}</h3>
                <small class="text-muted">Según filtros activos</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Nuevos Registros</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['creaciones']) }}</h3>
                <small class="text-muted">Acciones de creación</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Modificaciones</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['modificaciones']) }}</h3>
                <small class="text-muted">Actualizaciones de datos</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Eliminaciones / Backups</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['eliminaciones']) }} <span class="fs-6 text-muted fw-normal">/ {{ $stats['backups'] }} BKP</span></h3>
                <small class="text-muted">Bajas y respaldos del sistema</small>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados de Auditoría -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" action="{{ route('auditoria.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Buscar Detalle / IP</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Descripción, código, IP...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">-- Todas --</option>
                    <option value="CREACION" {{ $accion === 'CREACION' ? 'selected' : '' }}>CREACIÓN</option>
                    <option value="MODIFICACION" {{ $accion === 'MODIFICACION' ? 'selected' : '' }}>MODIFICACIÓN</option>
                    <option value="ELIMINACION" {{ $accion === 'ELIMINACION' ? 'selected' : '' }}>ELIMINACIÓN</option>
                    <option value="BACKUP" {{ $accion === 'BACKUP' ? 'selected' : '' }}>BACKUP BD</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Módulo</label>
                <select name="modulo" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($modulosDisponibles as $mod)
                        <option value="{{ $mod }}" {{ $modulo === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Usuario</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ (string) $usuarioId === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->rol }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Proyecto</label>
                <select name="proyecto_id" class="form-select form-select-sm">
                    <option value="">-- Todos los Proyectos --</option>
                    @foreach($proyectos as $p)
                        <option value="{{ $p->id }}" {{ (string) $proyectoId === (string) $p->id ? 'selected' : '' }}>
                            {{ $p->codigo }} - {{ Str::limit($p->nombre, 24) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control form-control-sm">
            </div>

            <div class="col-md-8 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filtrar Eventos
                </button>
                <a href="{{ route('auditoria.index', ['proyecto_id' => '']) }}" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-x-circle me-1"></i> Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Log del Sistema -->
    <div class="admin-card overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-shield-check text-primary me-2"></i> Bitácora de Auditoría del Sistema
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                {{ $logs->total() }} Eventos
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 155px;">Fecha & Hora</th>
                        <th style="width: 130px;">Acción</th>
                        <th style="width: 150px;">Módulo</th>
                        <th>Descripción del Evento</th>
                        <th>Usuario Responsable</th>
                        <th>Proyecto</th>
                        <th style="width: 110px;">IP</th>
                        <th class="text-end pe-3" style="width: 90px;">Cambios</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $badgeAccion = match($log->accion) {
                                'CREACION' => 'bg-success-subtle text-success border-success-subtle',
                                'MODIFICACION' => 'bg-warning-subtle text-warning border-warning-subtle',
                                'ELIMINACION' => 'bg-danger-subtle text-danger border-danger-subtle',
                                'BACKUP' => 'bg-info-subtle text-info border-info-subtle',
                                default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 small font-monospace">
                                <div class="fw-bold text-heading">{{ $log->created_at?->format('d/m/Y') }}</div>
                                <div class="text-muted">{{ $log->created_at?->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <span class="badge border {{ $badgeAccion }} px-2 py-1 fw-bold">
                                    {{ $log->accion }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary font-monospace">{{ $log->modulo }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading small">{{ $log->descripcion }}</div>
                                @if($log->modelo_id)
                                    <small class="text-muted font-monospace">Registro ID: #{{ $log->modelo_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($log->usuario)
                                    <div class="fw-semibold small text-heading">{{ $log->usuario->name }}</div>
                                    <span class="badge bg-body-secondary text-muted border" style="font-size: 0.65rem;">{{ $log->usuario->rol }}</span>
                                @else
                                    <span class="text-muted small fst-italic">Sistema / Consola</span>
                                @endif
                            </td>
                            <td>
                                @if($log->proyecto)
                                    <span class="badge badge-soft-info small" title="{{ $log->proyecto->nombre }}">
                                        {{ $log->proyecto->codigo }}
                                    </span>
                                @else
                                    <span class="text-muted small">Global</span>
                                @endif
                            </td>
                            <td class="small font-monospace text-muted">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                            <td class="text-end pe-3">
                                @if($log->datos_anteriores || $log->datos_nuevos)
                                    <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalLogDetalle{{ $log->id }}" title="Inspeccionar Datos">
                                        <i class="bi bi-code-square"></i> Ver
                                    </button>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                No se encontraron registros en el log del sistema para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modales de Detalle de Cambios -->
    @foreach($logs as $log)
        @if($log->datos_anteriores || $log->datos_nuevos)
            <div class="modal fade" id="modalLogDetalle{{ $log->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-shield-check text-primary me-2"></i> Detalle de Auditoría #{{ $log->id }} — {{ $log->accion }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted mb-3">{{ $log->descripcion }}</p>
                            <div class="row g-3">
                                @if($log->datos_anteriores)
                                    <div class="col-md-6">
                                        <h6 class="fw-bold small text-danger text-uppercase mb-2">
                                            <i class="bi bi-clock-history me-1"></i> Valores Anteriores
                                        </h6>
                                        <pre class="bg-body-tertiary p-3 rounded border small font-monospace mb-0" style="max-height: 320px; overflow: auto;">{{ json_encode($log->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                                @if($log->datos_nuevos)
                                    <div class="col-md-{{ $log->datos_anteriores ? '6' : '12' }}">
                                        <h6 class="fw-bold small text-success text-uppercase mb-2">
                                            <i class="bi bi-check2-circle me-1"></i> Valores Nuevos / Registrados
                                        </h6>
                                        <pre class="bg-body-tertiary p-3 rounded border small font-monospace mb-0" style="max-height: 320px; overflow: auto;">{{ json_encode($log->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

@endsection
