@extends('layouts.admin')

@section('title', 'Dashboard Ejecutivo - LogisticPCS')
@section('page_title', 'Consola de Operaciones & Logística')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Gestión & Operaciones</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">
                {{ isset($proyectoActivo) ? $proyectoActivo->nombre : 'Consola General' }}
            </li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <div class="d-flex align-items-center gap-2">
        @if(auth()->user()->rol === 'TECNICO')
            <a href="{{ route('articulos.index') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
                <i class="bi bi-search me-1"></i> Consultar Catálogo
            </a>
            <a href="{{ route('despachos.index') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-card-checklist me-1"></i> Mis Despachos
            </a>
        @else
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-success btn-sm px-3 fw-bold shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Centro de Reportes
            </a>
            @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
                @if(in_array(auth()->user()->rol, ['ADMINISTRADOR', 'LOGISTICO']))
                    <a href="{{ route('ingresos.create') }}" class="btn btn-outline-primary btn-sm px-3 fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Nuevo Ingreso
                    </a>
                @endif
                <a href="{{ route('despachos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Despacho
                </a>
            @endif
        @endif
    </div>
@endsection

@section('content')

    <!-- Flash Alert Status -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show border-0 admin-card mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
                <span class="fw-semibold">{{ session('status') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rol === 'TECNICO')
        @include('dashboard.partials.tecnico-panel')
    @else
    <!-- Context & Project Scope Switcher Banner -->
    <div class="admin-card mb-4 p-3 bg-gradient border">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 rounded-3 bg-primary text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi {{ isset($proyectoActivo) ? 'bi-building-check fs-4' : 'bi-globe-americas fs-4' }}"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="fw-bold mb-0 text-heading">
                            @if(isset($proyectoActivo))
                                {{ $proyectoActivo->nombre }}
                            @else
                                {{ $rol === 'SUPERVISOR' ? 'Proyectos y Frentes Asignados' : 'Consolidado Global de Operaciones' }}
                            @endif
                        </h5>
                        @if(isset($proyectoActivo))
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill font-monospace fw-semibold" style="font-size: 0.72rem;">
                                {{ $proyectoActivo->codigo }}
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                Proyecto Activo
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                <i class="bi bi-grid-fill me-1"></i> Vista Global Consolidada
                            </span>
                        @endif
                    </div>
                    <p class="text-muted small mb-0 mt-0.5">
                        @if(isset($proyectoActivo))
                            Datos filtrados exclusivamente para este proyecto &bull; Frentes, almacenes locales, personal y recursos vinculados.
                        @else
                            {{ $rol === 'SUPERVISOR' ? 'Supervisión restringida a los frentes asignados a tu cuenta.' : 'Visualización integral de todos los proyectos, almacenes y movimientos logísticos de la compañía.' }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Fecha (Siempre Visible) -->
            <div class="d-flex align-items-center">
                <span class="badge bg-body border text-muted px-3 py-2 rounded-pill small shadow-xs">
                    <i class="bi bi-calendar-event me-2 text-primary"></i> {{ now()->translatedFormat('d \d\e F \d\e\l Y') }}
                </span>
            </div>
        </div>
    </div>

    <!-- 6 Role-Tailored Executive Colored KPI Cards -->
    <div class="row g-3 mb-4">
        
        @if($rol === 'SUPERVISOR')
            <!-- SUPERVISOR 1 (Green): Personal en Campo Hoy -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('roster.index') }}" class="text-decoration-none d-block h-100" title="Ver Matriz Roster">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">En Campo Hoy</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['personal_campo_hoy'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['personal_descanso_hoy'], 0) }} en bajada</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- SUPERVISOR 2 (Purple): Cuadrillas Activas -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('cuadrillas.index') }}" class="text-decoration-none d-block h-100" title="Ver Cuadrillas">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Cuadrillas</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['cuadrillas_activas'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-diagram-3-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['personal'], 0) }} asignados</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- SUPERVISOR 3 (Blue): Vales & Herramientas en Campo -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('despachos.index') }}" class="text-decoration-none d-block h-100" title="Ver Despachos">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Vales en Campo</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['despachos_en_campo'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-tools"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Actas en custodia</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- SUPERVISOR 4 (Teal): Campamento & Permisos -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('roster.index') }}" class="text-decoration-none d-block h-100" title="Ver Roster">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Campamento/Permiso</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['personal_campamento_hoy'] + $stats['personal_permiso_hoy'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-house-door-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['personal_campamento_hoy'], 0) }} camp. / {{ number_format($stats['personal_permiso_hoy'], 0) }} perm.</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- SUPERVISOR 5 (Amber): Stock en Almacén de Obra -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('inventario.stock', ['solo_bajos' => 1]) }}" class="text-decoration-none d-block h-100" title="Ver Stock Bajo">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Alertas Stock</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['alertas_stock'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Artículos bajo mínimo</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- SUPERVISOR 6 (Red): Equipos Asignados -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('activos.index') }}" class="text-decoration-none d-block h-100" title="Ver Activos Asignados">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Equipos en Frente</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['activos'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['activos_prestados'], 0) }} en uso activo</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

        @elseif($rol === 'LOGISTICO')
            <!-- LOGÍSTICO 1 (Green): Ingresos a Almacén Mes -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('ingresos.index') }}" class="text-decoration-none d-block h-100" title="Ver Ingresos">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Ingresos Mes</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['ingresos_mes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Recepción almacén</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- LOGÍSTICO 2 (Blue): Despachos y Vales Mes -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('despachos.index') }}" class="text-decoration-none d-block h-100" title="Ver Despachos">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Despachos Mes</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['despachos_mes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-truck"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['despachos_en_campo'], 0) }} activos en campo</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- LOGÍSTICO 3 (Purple): Kits Disponibles en Almacén -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('kits.index') }}" class="text-decoration-none d-block h-100" title="Ver Monitoreo de Kits">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Kits Almacén</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['kits_completos'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-boxes"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['kits_armables'], 0) }} armables en stock</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- LOGÍSTICO 4 (Teal): Activos y Series QR -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('activos.index') }}" class="text-decoration-none d-block h-100" title="Ver Activos">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Activos QR</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['activos_operativos'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-qr-code"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['activos_prestados'], 0) }} en préstamo</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- LOGÍSTICO 5 (Amber): Movimientos Kardex Mes -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('kardex.index') }}" class="text-decoration-none d-block h-100" title="Ver Kardex">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Kardex PEPS</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['movimientos_kardex_mes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Movimientos este mes</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- LOGÍSTICO 6 (Red): Stock Bajo Mínimo -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('inventario.stock', ['solo_bajos' => 1]) }}" class="text-decoration-none d-block h-100" title="Ver Reposición Requerida">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Bajo Mínimo</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['alertas_stock'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Reposición requerida</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

        @elseif($rol === 'AUDITOR')
            <!-- AUDITOR 1 (Blue): Frentes Auditados -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('proyectos.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Proyectos Auditados</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['proyectos'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Centros de costo</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- AUDITOR 2 (Green): Transacciones Kardex PEPS -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('kardex.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Kardex PEPS</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['movimientos_kardex_mes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['movimientos_kardex'], 0) }} histórico total</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- AUDITOR 3 (Amber): Actas y Despachos en Campo -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('despachos.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Vales en Custodia</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['despachos_en_campo'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Actas firmadas</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- AUDITOR 4 (Red): Calibraciones Pendientes -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('mantenimientos.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Calibraciones</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['calibraciones_pendientes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-tools"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Pendientes de cierre</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- AUDITOR 5 (Purple): Alertas No Leídas -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('alertas.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Alertas Nuevas</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['alertas_no_leidas'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Pendientes de revisión</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- AUDITOR 6 (Teal): Unidades en Stock Auditado -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('inventario.stock') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Stock Total</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['stock_total_unidades'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">Unidades auditadas</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

        @else
            <!-- ADMINISTRADOR (Executive Multi-Frente) -->
            <!-- ADMIN 1 (Blue): Proyectos / Personal -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ isset($proyectoActivo) ? route('personal.index') : route('proyectos.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">
                                    {{ isset($proyectoActivo) ? 'Personal Obra' : 'Proyectos' }}
                                </span>
                                <h3 class="fw-bold mb-0 my-1 text-white">
                                    {{ number_format(isset($proyectoActivo) ? $stats['personal'] : $stats['proyectos'], 0) }}
                                </h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi {{ isset($proyectoActivo) ? 'bi-people-fill' : 'bi-buildings-fill' }}"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ isset($proyectoActivo) ? 'Trabajadores' : 'Frentes activos' }}</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ADMIN 2 (Green): Personal en Campo Hoy -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('roster.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">En Campo Hoy</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['personal_campo_hoy'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['personal_descanso_hoy'], 0) }} en bajada</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ADMIN 3 (Amber): Vales & Activos en Campo -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('despachos.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Vales en Campo</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['despachos_en_campo'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-truck"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['despachos_mes'], 0) }} emitidos mes</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ADMIN 4 (Purple): Kits Disponibles en Almacén -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('kits.index') }}" class="text-decoration-none d-block h-100" title="Ver Gestión de Kits">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Kits Listos</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['kits_completos'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-boxes"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['kits_armables'], 0) }} armables en stock</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ADMIN 5 (Teal): Movimientos Kardex Mes -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('kardex.index') }}" class="text-decoration-none d-block h-100">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Kardex Mes</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['movimientos_kardex_mes'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['movimientos_kardex'], 0) }} transacciones</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ADMIN 6 (Red): Stock Crítico & Alertas -->
            <div class="col-12 col-sm-6 col-xl-2">
                <a href="{{ route('inventario.stock', ['solo_bajos' => 1]) }}" class="text-decoration-none d-block h-100" title="Ver Stock Crítico">
                    <div class="card kpi-card text-white p-3 h-100 shadow-sm position-relative overflow-hidden cursor-pointer" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 14px;">
                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.95;">Stock Crítico</span>
                                <h3 class="fw-bold mb-0 my-1 text-white">{{ number_format($stats['alertas_stock'], 0) }}</h3>
                            </div>
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.2); font-size: 1.2rem; line-height: 1;">
                                <i class="bi bi-exclamation-octagon-fill"></i>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top border-white border-opacity-20 d-flex align-items-center justify-content-between position-relative" style="font-size: 0.72rem; z-index: 2;">
                            <span class="opacity-95 text-white">{{ number_format($stats['alertas_no_leidas'], 0) }} alertas no leídas</span>
                            <i class="bi bi-arrow-right text-white"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endif

    </div>

    <!-- Visual Analytics: Charts Section -->
    <div class="row g-4 mb-4">
        
        <!-- Chart 1 (Left): Activity / Operations Trend (Bar + Line) -->
        <div class="col-lg-7 col-xl-8">
            <div class="admin-card h-100 p-4 border">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                            @if($rol === 'SUPERVISOR')
                                Asistencia en Campo & Despachos de Herramientas
                            @elseif($rol === 'LOGISTICO')
                                Flujo de Ingresos, Salidas & Kardex
                            @elseif($rol === 'AUDITOR')
                                Auditoría de Despachos vs Movimientos Kardex
                            @else
                                Dinámica Operativa de Almacén & Campo
                            @endif
                        </h5>
                        <p class="text-muted small mb-0">
                            @if($rol === 'SUPERVISOR')
                                Personal activo en régimen 14x7 y vales de herramientas de los últimos 7 días
                            @elseif($rol === 'LOGISTICO')
                                Recepciones a almacén, vales despachados y transacciones registradas en los últimos 7 días
                            @elseif($rol === 'AUDITOR')
                                Comparativa de actas de entrega emitidas vs transacciones de inventario PEPS
                            @else
                                Movimientos de ingresos, despachos a campo y registros de Kardex en los últimos 7 días
                            @endif
                        </p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                        Últimos 7 días
                    </span>
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartTrendCanvas" role="img" aria-label="Gráfico de tendencia de operaciones de los últimos 7 días">
                        <p class="visually-hidden">Gráfico de barras y líneas con los movimientos de ingresos, despachos y transacciones de Kardex de los últimos 7 días.</p>
                    </canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2 (Right): Distribution Donut with Centered Number -->
        <div class="col-lg-5 col-xl-4">
            <div class="admin-card h-100 p-4 d-flex flex-column justify-content-between border">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-pie-chart-fill text-primary me-2" aria-hidden="true"></i>
                                @if($rol === 'SUPERVISOR')
                                    Distribución de Turnos 14x7
                                @else
                                    Condición de Activos & Equipos
                                @endif
                            </h5>
                            <p class="text-muted small mb-0">
                                @if($rol === 'SUPERVISOR')
                                    Personal en obra hoy por condición laboral
                                @else
                                    Estado técnico y asignación de equipos serializados
                                @endif
                            </p>
                        </div>
                    </div>

                    <div style="position: relative; height: 180px; width: 100%;" class="d-flex align-items-center justify-content-center">
                        <canvas id="chartDonutCanvas" role="img" aria-label="{{ $rol === 'SUPERVISOR' ? 'Gráfico circular de distribución de turnos 14x7 del personal en obra' : 'Gráfico circular de condición técnica de activos y equipos' }}">
                            <p class="visually-hidden">{{ $rol === 'SUPERVISOR' ? 'Distribución del personal en campo, bajada, campamento y descansos' : 'Proporción de activos operativos, en préstamo y mantenimiento' }}</p>
                        </canvas>
                        <div class="position-absolute text-center" style="pointer-events: none;" aria-hidden="true">
                            <span class="d-block fw-bold text-heading" style="font-size: 1.5rem; line-height: 1;">
                                {{ $rol === 'SUPERVISOR' ? number_format($chartDonut['personal']['total'], 0) : number_format($chartDonut['activos']['total'], 0) }}
                            </span>
                            <span class="text-muted" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                {{ $rol === 'SUPERVISOR' ? 'Personal' : 'Equipos' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Custom Badged Legends -->
                <div class="mt-3 pt-3 border-top">
                    @if($rol === 'SUPERVISOR')
                        <div class="row g-2 text-center" style="font-size: 0.75rem;">
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #10b981;"></span>
                                <span class="text-muted">En Campo: <strong>{{ number_format($stats['personal_campo_hoy'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #f59e0b;"></span>
                                <span class="text-muted">En Bajada: <strong>{{ number_format($stats['personal_descanso_hoy'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #3b82f6;"></span>
                                <span class="text-muted">Campamento: <strong>{{ number_format($stats['personal_campamento_hoy'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #8b5cf6;"></span>
                                <span class="text-muted">Permisos: <strong>{{ number_format($stats['personal_permiso_hoy'], 0) }}</strong></span>
                            </div>
                        </div>
                    @else
                        <div class="row g-2 text-center" style="font-size: 0.75rem;">
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #10b981;"></span>
                                <span class="text-muted">Operativos: <strong>{{ number_format($stats['activos_operativos'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #3b82f6;"></span>
                                <span class="text-muted">En Préstamo: <strong>{{ number_format($stats['activos_prestados'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #f59e0b;"></span>
                                <span class="text-muted">Mantenimiento: <strong>{{ number_format($stats['activos_mantenimiento'], 0) }}</strong></span>
                            </div>
                            <div class="col-6">
                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #ef4444;"></span>
                                <span class="text-muted">Otros: <strong>{{ number_format(max(0, $stats['activos'] - ($stats['activos_operativos'] + $stats['activos_mantenimiento'])), 0) }}</strong></span>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>

    <!-- Operational Panels Grid 1: Kits / Cuadrillas / Calibraciones & Alertas de Reposición -->
    <div class="row g-4 mb-4">
        
        @if($rol === 'SUPERVISOR')
            <!-- Supervisor: Cuadrillas en Frentes de Obra -->
            <div class="col-lg-7">
                <div class="admin-card h-100 overflow-hidden border">
                    <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-people-fill text-primary me-2"></i> Cuadrillas Activas en Campo
                            </h5>
                            <small class="text-muted">Frentes de trabajo operativos con personal asignado</small>
                        </div>
                        <a href="{{ route('cuadrillas.index') }}" class="btn btn-sm btn-outline-secondary">Ver Todas</a>
                    </div>
                    <div class="table-responsive p-3" tabindex="0" role="region" aria-label="Tabla de cuadrillas activas en campo">
                        <table class="table table-glass align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Nombre Cuadrilla</th>
                                    <th scope="col">Proyecto</th>
                                    <th scope="col" class="text-center">Miembros</th>
                                    <th scope="col" class="text-end">Líder</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cuadrillasActivas as $cuad)
                                    <tr>
                                        <td>
                                            <a href="{{ route('cuadrillas.show', $cuad) }}" class="fw-bold font-monospace text-decoration-none">
                                                {{ $cuad->codigo_cuadrilla }}
                                            </a>
                                        </td>
                                        <td class="fw-semibold text-heading">{{ $cuad->nombre }}</td>
                                        <td>
                                            <span class="badge bg-body-secondary text-body border" style="font-size: 0.68rem;">
                                                {{ $cuad->proyecto?->nombre ?? 'Sin Proyecto' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                                                {{ $cuad->miembros_count }} pers.
                                            </span>
                                        </td>
                                        <td class="text-end small">
                                            {{ $cuad->lider ? "{$cuad->lider->nombres} {$cuad->lider->apellidos}" : 'Sin asignar' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay cuadrillas activas registradas en este frente</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($rol === 'AUDITOR')
            <!-- Auditor: Inspección & Calibración de Activos -->
            <div class="col-lg-7">
                <div class="admin-card h-100 overflow-hidden border">
                    <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-tools text-primary me-2"></i> Inspección & Calibración de Activos
                            </h5>
                            <small class="text-muted">Trazabilidad de calibraciones periódicas y mantenimientos preventivos</small>
                        </div>
                        <a href="{{ route('mantenimientos.index') }}" class="btn btn-sm btn-outline-secondary">Ver Calibraciones</a>
                    </div>
                    <div class="table-responsive p-3" tabindex="0" role="region" aria-label="Tabla de inspección y calibración de activos">
                        <table class="table table-glass align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Activo / Equipo</th>
                                    <th scope="col">Proyecto</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col" class="text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasCalibraciones as $cal)
                                    <tr>
                                        <td>
                                            <a href="{{ route('mantenimientos.show', $cal) }}" class="fw-bold text-decoration-none">
                                                {{ $cal->activo?->nombre ?? 'Activo #'.$cal->activo_id }}
                                            </a>
                                            <small class="text-muted font-monospace d-block">{{ $cal->activo?->codigo_identificador }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-body-secondary text-body border" style="font-size: 0.68rem;">
                                                {{ $cal->proyecto?->nombre ?? 'Sin Proyecto' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border px-2 py-1 rounded-pill small">
                                                {{ $cal->tipo }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $cal->fecha_ingreso ? $cal->fecha_ingreso->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $cal->estado === 'CONCLUIDO' ? 'success' : ($cal->estado === 'EN_PROCESO' ? 'warning' : 'secondary') }}-subtle text-{{ $cal->estado === 'CONCLUIDO' ? 'success' : ($cal->estado === 'EN_PROCESO' ? 'warning' : 'secondary') }} border px-2 py-1 rounded-pill small">
                                                {{ $cal->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay calibraciones registradas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Admin / Logístico: Monitoreo de Kits por Almacén -->
            <div class="col-lg-7">
                <div class="admin-card h-100 overflow-hidden border">
                    <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-boxes text-purple me-2" style="color: #8b5cf6;"></i> Monitoreo de Kits por Almacén
                            </h5>
                            <small class="text-muted">Disponibilidad de componentes y kits armables en tiempo real</small>
                        </div>
                        <a href="{{ route('kits.index') }}" class="btn btn-sm btn-outline-primary">Gestionar Kits</a>
                    </div>
                    <div class="table-responsive p-3" tabindex="0" role="region" aria-label="Tabla de monitoreo de kits por almacén">
                        <table class="table table-glass align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre del Kit</th>
                                    <th scope="col" style="min-width: 220px; width: 40%;">Almacén / Ubicación</th>
                                    <th scope="col" class="text-center" style="width: 130px;">Condición</th>
                                    <th scope="col" class="text-end" style="width: 70px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topKits as $itemKit)
                                    @php
                                        $k = $itemKit['kit'];
                                        $eval = $itemKit['eval'];
                                        $disponible = $eval['disponible'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('kits.show', $k) }}" class="fw-bold text-heading text-decoration-none">
                                                {{ $k->nombre }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-body-secondary text-body border text-truncate d-inline-flex align-items-center" style="font-size: 0.75rem; max-width: 260px;" title="{{ $k->ubicacion?->nombre ?? 'Almacén General' }}">
                                                <i class="bi bi-geo-alt me-1 text-primary"></i>{{ $k->ubicacion?->nombre ?? 'Almacén General' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($disponible)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                                    <i class="bi bi-check-circle-fill me-1"></i> COMPLETO
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> INCOMPLETO
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('kits.show', $k) }}" class="btn btn-xs btn-outline-primary" aria-label="Ver componentes del kit {{ $k->nombre }}" title="Ver componentes y almacén">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay kits registrados en este ámbito</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Panel 2: Alertas de Reposición Inmediata & Stock Crítico -->
        <div class="col-lg-5">
            <div class="admin-card h-100 overflow-hidden border">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Reposición Requerida
                        </h5>
                        <small class="text-muted">Artículos con stock inferior o igual al mínimo</small>
                    </div>
                    <a href="{{ route('inventario.stock', ['solo_bajos' => 1]) }}" class="btn btn-sm btn-outline-danger">
                        Ver Stock <span class="badge bg-danger ms-1 text-white">{{ number_format($stats['alertas_stock'], 0) }}</span>
                    </a>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-glass align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Almacén</th>
                                <th class="text-end">Stock Actual</th>
                                <th class="text-end">Mínimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alertasStock as $alt)
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block text-truncate text-heading" style="max-width: 140px;">
                                            {{ $alt->articulo?->descripcion ?? '-' }}
                                        </span>
                                        <small class="text-muted font-monospace">{{ $alt->articulo?->codigo_sku }}</small>
                                    </td>
                                    <td class="small text-muted">{{ $alt->ubicacion?->nombre ?? '-' }}</td>
                                    <td class="text-end text-danger fw-bold font-monospace">
                                        {{ number_format($alt->cantidad_actual, 0) }}
                                    </td>
                                    <td class="text-end text-muted font-monospace small">
                                        {{ number_format($alt->articulo?->stock_minimo ?? 0, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-success py-4">
                                        <i class="bi bi-check-circle me-1"></i> Todos los artículos cuentan con niveles óptimos de stock.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Dual Operational Panels: Despachos Recientes & Transacciones de Kardex -->
    <div class="row g-4 mb-4">
        
        <!-- Panel 3: Despachos Recientes en Campo -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden border">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-truck text-warning me-2"></i> Despachos Recientes en Campo
                        </h5>
                        <small class="text-muted">Últimas guías y materiales emitidos a frentes de obra</small>
                    </div>
                    <a href="{{ route('despachos.index') }}" class="btn btn-sm btn-outline-secondary">Ver Historial</a>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-glass align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Guía</th>
                                <th>Receptor</th>
                                <th>Proyecto / Cuadrilla</th>
                                <th>Fecha</th>
                                <th class="text-end">Acta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosDespachos as $dsp)
                                <tr>
                                    <td>
                                        <a href="{{ route('despachos.show', $dsp) }}" class="fw-bold font-monospace text-decoration-none">
                                            {{ $dsp->numero_guia }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $dsp->personal ? "{$dsp->personal->apellidos}, {$dsp->personal->nombres}" : 'Sin receptor' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-body-secondary text-body border" style="font-size: 0.7rem;">
                                            {{ $dsp->cuadrilla?->nombre ?? ($dsp->proyecto?->nombre ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $dsp->fecha_despacho ? $dsp->fecha_despacho->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('despachos.acta', $dsp) }}" target="_blank" class="btn btn-xs btn-outline-danger" title="Ver Acta Firmada PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay despachos recientes registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel 4: Transacciones de Kardex PEPS -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden border">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-clock-history text-success me-2"></i> Transacciones de Kardex PEPS
                        </h5>
                        <small class="text-muted">Registro inmutable de entradas, salidas y transferencias</small>
                    </div>
                    <a href="{{ route('kardex.index') }}" class="btn btn-sm btn-outline-secondary">Ver Kardex</a>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-glass align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Operación</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Saldo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosMovimientos as $mov)
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block text-truncate text-heading" style="max-width: 140px;">
                                            {{ $mov->articulo?->descripcion ?? '-' }}
                                        </span>
                                        <small class="text-muted font-monospace">{{ $mov->articulo?->codigo_sku }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ str_contains($mov->tipo_movimiento, 'ENTRADA') || str_contains($mov->tipo_movimiento, 'RETORNO') ? 'success' : 'warning' }}-subtle text-{{ str_contains($mov->tipo_movimiento, 'ENTRADA') || str_contains($mov->tipo_movimiento, 'RETORNO') ? 'success' : 'warning' }} border px-2 py-1 rounded-pill small">
                                            {{ str_replace('_', ' ', $mov->tipo_movimiento) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($mov->cantidad, 0) }}
                                    </td>
                                    <td class="text-end text-muted font-monospace small">
                                        {{ number_format($mov->stock_posterior, 0) }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $mov->fecha_movimiento ? $mov->fecha_movimiento->format('d/m H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados en kardex</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Panel 5 (For Admin & Logístico): Últimos Ingresos a Almacén -->
    @if($esAdminOLogistico && isset($ultimosIngresos) && $ultimosIngresos->count() > 0)
        <div class="admin-card mb-4 overflow-hidden border">
            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-box-arrow-in-down text-success me-2"></i> Recepciones Recientes en Almacén (Ingresos)
                    </h5>
                    <small class="text-muted">Control de ingresos de suministros, compras y devoluciones con trazabilidad</small>
                </div>
                <a href="{{ route('ingresos.index') }}" class="btn btn-sm btn-outline-secondary">Ver Todos los Ingresos</a>
            </div>
            <div class="table-responsive p-3">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>N° Documento</th>
                            <th>Proyecto</th>
                            <th>Almacén Destino</th>
                            <th>Tipo Ingreso</th>
                            <th>Registrado Por</th>
                            <th class="text-end">Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosIngresos as $ing)
                            <tr>
                                <td>
                                    <a href="{{ route('ingresos.show', $ing) }}" class="fw-bold font-monospace text-decoration-none">
                                        {{ $ing->numero_documento ?? ('ING-'.str_pad($ing->id, 5, '0', STR_PAD_LEFT)) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-body-secondary text-body border" style="font-size: 0.72rem;">
                                        {{ $ing->proyecto?->nombre ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $ing->ubicacion?->nombre ?? 'Almacén Central' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill small">
                                        {{ str_replace('_', ' ', $ing->tipo_ingreso ?? 'RECEPCIÓN') }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $ing->usuario?->name ?? 'Sistema' }}
                                </td>
                                <td class="text-end small text-muted font-monospace">
                                    {{ $ing->fecha_ingreso ? $ing->fecha_ingreso->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

    // Trend Chart (Bar + Line)
    const trendCtx = document.getElementById('chartTrendCanvas');
    if (trendCtx) {
        const trendLabels = @json($chartTrend['labels']);
        const rol = @json($rol);
        
        let datasets = [];
        if (rol === 'SUPERVISOR') {
            datasets = [
                {
                    type: 'bar',
                    label: 'Personal en Campo',
                    data: @json($chartTrend['campo']),
                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 24,
                },
                {
                    type: 'line',
                    label: 'Despachos Emitidos',
                    data: @json($chartTrend['despachos']),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                }
            ];
        } else if (rol === 'AUDITOR') {
            datasets = [
                {
                    type: 'bar',
                    label: 'Actas de Despacho',
                    data: @json($chartTrend['despachos']),
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderColor: '#d97706',
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 24,
                },
                {
                    type: 'line',
                    label: 'Transacciones Kardex PEPS',
                    data: @json($chartTrend['kardex']),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                }
            ];
        } else {
            datasets = [
                {
                    type: 'bar',
                    label: 'Ingresos a Almacén',
                    data: @json($chartTrend['ingresos']),
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderColor: '#059669',
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 16,
                },
                {
                    type: 'bar',
                    label: 'Vales Despachados',
                    data: @json($chartTrend['despachos']),
                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 16,
                },
                {
                    type: 'line',
                    label: 'Movimientos Kardex',
                    data: @json($chartTrend['kardex']),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                }
            ];
        }

        new Chart(trendCtx, {
            data: {
                labels: trendLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            boxWidth: 12,
                            font: { family: 'inherit', size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            precision: 0,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Donut Distribution Chart
    const donutCtx = document.getElementById('chartDonutCanvas');
    if (donutCtx) {
        const rol = @json($rol);
        const donutConfig = rol === 'SUPERVISOR' ? @json($chartDonut['personal']) : @json($chartDonut['activos']);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: donutConfig.labels,
                datasets: [{
                    data: donutConfig.data,
                    backgroundColor: donutConfig.colors,
                    borderWidth: 2,
                    borderColor: isDark ? '#111c2e' : '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    }
});
</script>
@endpush
