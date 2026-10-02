@extends('layouts.admin')

@section('title', 'Backups de Base de Datos')
@section('page_title', 'Respaldos & Backups de Base de Datos')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración & Seguridad</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Backups de Base de Datos</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <form action="{{ route('backups.generar') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold shadow">
            <i class="bi bi-database-fill-add me-1"></i> Generar Nuevo Backup (.SQL)
        </button>
    </form>
    <a href="{{ route('auditoria.index', ['modulo' => 'BACKUPS_BD']) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-shield-check me-1"></i> Ver Log de Respaldos
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Tarjetas de Resumen del Motor de Base de Datos -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Respaldos Almacenados</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-hdd-stack-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $archivos->count() }}</h3>
                <small class="text-muted">Archivos .SQL disponibles para descarga</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Tablas Respaldadas</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-table"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ $tablasCount }} Tablas</h3>
                <small class="text-muted">Estructura DDL + Datos completos</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Último Respaldo</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                @if($archivos->isNotEmpty())
                    <h5 class="fw-bold mb-0 text-heading">{{ $archivos->first()['fecha']->format('d/m/Y H:i') }}</h5>
                    <small class="text-muted">{{ $archivos->first()['nombre'] }}</small>
                @else
                    <h5 class="fw-bold mb-0 text-muted">Sin respaldos</h5>
                    <small class="text-muted">Genere su primer backup ahora</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Banner Informativo y Botón de Acción Directa -->
    <div class="admin-card p-4 mb-4 border-start border-4 border-primary">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h5 class="fw-bold text-heading mb-1">
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i> Centro de Copias de Seguridad SQL
                </h5>
                <p class="small text-muted mb-0">
                    Genere instantáneamente una copia íntegra de todas las tablas del sistema (Proyectos, Personal, Cuadrillas, Roster, Inventario, Activos Serializados, Movimientos, Kardex y Auditoría). Puede descargar el archivo <code>.sql</code> para resguardo externo.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <form action="{{ route('backups.generar') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Generar Backup Ahora
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla de Archivos de Respaldo -->
    <div class="admin-card overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-archive text-primary me-2"></i> Historial de Copias de Seguridad Generadas
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                {{ $archivos->count() }} Archivos .SQL
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Archivo de Respaldo (.SQL)</th>
                        <th>Fecha y Hora de Creación</th>
                        <th>Antigüedad</th>
                        <th>Tamaño</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivos as $bkp)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-filetype-sql fs-4 text-primary"></i>
                                    <div>
                                        <div class="fw-bold font-monospace text-heading">{{ $bkp['nombre'] }}</div>
                                        <small class="text-muted">Respaldo SQL Completo</small>
                                    </div>
                                </div>
                            </td>
                            <td class="font-monospace small">
                                {{ $bkp['fecha']->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                <span class="badge badge-soft-info">{{ $bkp['fecha']->diffForHumans() }}</span>
                            </td>
                            <td class="font-monospace fw-semibold">
                                {{ $bkp['tamano'] }}
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('backups.descargar', $bkp['nombre']) }}" class="btn btn-outline-primary fw-semibold" title="Descargar archivo .SQL">
                                        <i class="bi bi-download me-1"></i> Descargar
                                    </a>
                                    <form action="{{ route('backups.eliminar', $bkp['nombre']) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar el respaldo {{ $bkp['nombre'] }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar respaldo">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-database-exclamation fs-1 d-block mb-2 opacity-50"></i>
                                Aún no se han generado copias de seguridad. Haga clic en <strong>"Generar Backup Ahora"</strong> para crear el primer respaldo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
