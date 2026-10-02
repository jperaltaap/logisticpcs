@extends('layouts.admin')

@section('title', 'Inicialización del Sistema')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('inicializacion.index') }}">Configuración</a></li>
            <li class="breadcrumb-item active" aria-current="page">Puesta en Marcha</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid py-2">

    {{-- Banner de Notificaciones Flash --}}
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 admin-card mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-warning me-2"></i>
                <div>
                    <strong>Paso Requerido:</strong> {{ session('warning') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 admin-card mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header de la Ventana de Inicialización --}}
    <div class="admin-card p-4 mb-4 border">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                @php $empresaGlobal = \App\Models\EmpresaConfig::instancia(); @endphp
                <div class="p-2 rounded-3 bg-white border shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <img src="{{ $empresaGlobal->icono_url }}" alt="Sistema" style="max-width: 44px; max-height: 44px; object-fit: contain;">
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold mb-0 text-heading">Asistente de Puesta en Marcha del Sistema</h4>
                        @if($status['is_complete'])
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                <i class="bi bi-check2-all me-1"></i> Sistema Inicializado
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">
                                <i class="bi bi-shield-lock-fill me-1"></i> Configuración Requerida
                            </span>
                        @endif
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Para habilitar el acceso a los módulos operativos, debe completar los <strong>4 datos esenciales</strong> del sistema.
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if($status['is_complete'])
                    <a href="{{ route('dashboard') }}" class="btn btn-success fw-bold px-4 shadow">
                        <i class="bi bi-speedometer2 me-1"></i> Entrar al Dashboard
                    </a>
                @else
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar Estado
                    </button>
                @endif
            </div>
        </div>

        {{-- Barra de Progreso General --}}
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">
                    Progreso de Inicialización
                </span>
                <span class="badge {{ $status['is_complete'] ? 'bg-success' : 'bg-primary' }} px-3 py-1 fs-6 fw-bold">
                    {{ $status['completed_count'] }} de {{ $status['total_steps'] }} Requerimientos ({{ $status['percentage'] }}%)
                </span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 8px; background-color: rgba(148, 163, 184, 0.2);">
                <div class="progress-bar {{ $status['is_complete'] ? 'bg-success' : 'bg-primary' }} progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     style="width: {{ $status['percentage'] }}%; transition: width 0.6s ease;"
                     aria-valuenow="{{ $status['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

    {{-- Banner de Éxito al Completar los 4 Requerimientos --}}
    @if($status['is_complete'])
        <div class="admin-card p-4 mb-4 border border-success bg-success-subtle shadow-sm">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-success text-white rounded-circle shadow">
                        <i class="bi bi-trophy-fill fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-1">¡Felicitaciones! Datos Básicos Configurados con Éxito</h5>
                        <p class="mb-0 text-dark-emphasis small">
                            Se han completado los 4 requisitos obligatorios (Empresa, Proyecto, Almacén y Categorías). Todos los módulos operativos y el Dashboard del sistema están desbloqueados.
                        </p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg px-4 fw-bold shadow">
                    <i class="bi bi-arrow-right-circle me-1"></i> Ir al Dashboard
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-info border-0 admin-card mb-4 p-3 shadow-sm d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-4 text-info flex-shrink-0"></i>
            <div class="small">
                <strong>Modo de Inicialización Activo:</strong> Los módulos como Artículos, Ingresos, Despachos, Kardex, Cuadrillas, Roster y Reportes permanecerán restringidos para todos los usuarios hasta que se completen los pasos pendientes a continuación.
            </div>
        </div>
    @endif

    {{-- Grilla de los 4 Pasos Obligatorios --}}
    <div class="row g-4 mb-4">
        @foreach($status['steps'] as $step)
            <div class="col-md-6 col-xl-3">
                <div class="admin-card h-100 p-4 border d-flex flex-column justify-content-between {{ $step['completed'] ? 'border-success-subtle' : 'border-warning-subtle shadow-sm' }}"
                     style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    
                    <div>
                        {{-- Top Badge & Icon --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="p-2 rounded-3 {{ $step['completed'] ? 'bg-success text-white' : 'bg-primary text-white' }} shadow-sm d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi {{ $step['icon'] }} fs-5"></i>
                            </div>
                            @if($step['completed'])
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small">
                                    <i class="bi bi-check-circle-fill me-1"></i> Completado
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 small">
                                    <i class="bi bi-clock-history me-1"></i> Pendiente
                                </span>
                            @endif
                        </div>

                        {{-- Step Info --}}
                        <div class="small text-uppercase fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Paso {{ $step['step_number'] }} de 4
                        </div>
                        <h5 class="fw-bold mb-2 text-heading">{{ $step['title'] }}</h5>
                        <p class="text-muted small mb-3 lh-sm">
                            {{ $step['description'] }}
                        </p>
                    </div>

                    {{-- Bottom Action & Counter --}}
                    <div class="pt-3 border-top mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Registros:</span>
                            <span class="fw-bold {{ $step['completed'] ? 'text-success' : 'text-danger' }}">
                                {{ $step['current'] }} / mín. {{ $step['required'] }}
                            </span>
                        </div>

                        @if($step['completed'])
                            <a href="{{ $step['route'] }}" class="btn btn-outline-success btn-sm w-100 fw-semibold">
                                <i class="bi bi-check2 me-1"></i> {{ $step['action_label'] }}
                            </a>
                        @else
                            <a href="{{ $step['route'] }}" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm">
                                <i class="bi bi-plus-circle me-1"></i> {{ $step['action_label'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Ayuda Adicional y Pasos Siguientes --}}
    <div class="admin-card p-4 border bg-body-tertiary">
        <h6 class="fw-bold text-heading mb-2">
            <i class="bi bi-lightbulb-fill text-warning me-1"></i> ¿Qué ocurre después de completar estos 4 pasos?
        </h6>
        <p class="text-muted small mb-0">
            Una vez registrados la Empresa, al menos un Proyecto, un Almacén y una Categoría, el sistema abrirá automáticamente el Dashboard principal para los 5 perfiles de usuario (Administrador, Almacén, Supervisor, Técnico y Auditor). A partir de ese momento, el personal de almacén podrá cargar el catálogo maestro de artículos, serializar activos e iniciar los ingresos y despachos con trazabilidad Kardex en tiempo real.
        </p>
    </div>

</div>
@endsection
