@extends('layouts.admin')

@section('title', 'Detalle de Usuario ' . $user->name)
@section('page_title', 'Usuario: ' . $user->name)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none fw-semibold text-primary">Usuarios & Roles</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $user->email }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-pencil me-1" aria-hidden="true"></i> Editar Usuario
    </a>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver al Listado
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- User Header Card -->
    <div class="admin-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h3 class="fw-bold mb-0 text-heading">{{ $user->name }}</h3>
                            @if($user->estado === 'ACTIVO')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i> ACTIVO
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small fw-semibold">
                                    INACTIVO
                                </span>
                            @endif
                        </div>
                        <div class="text-muted fw-semibold mb-1">
                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                        </div>
                        <span class="badge bg-primary px-3 py-1 rounded-pill fw-bold">
                            <i class="bi bi-shield-lock-fill me-1"></i> Rol: {{ $user->rol }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 border-start ps-lg-4">
                <span class="small text-muted text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Ficha Física Asociada</span>
                @if($user->personal)
                    <div class="fw-bold text-heading fs-6 mt-1">{{ $user->personal->nombre_completo }}</div>
                    <small class="text-muted d-block">DNI: {{ $user->personal->dni }} • {{ $user->personal->cargo }}</small>
                    <a href="{{ route('personal.show', $user->personal) }}" class="btn btn-outline-primary btn-sm mt-2 fw-semibold">
                        <i class="bi bi-person-vcard me-1"></i> Ver Ficha de Personal
                    </a>
                @else
                    <span class="badge bg-body-secondary text-muted border my-2">
                        Sin trabajador físico vinculado
                    </span>
                    <div class="small text-muted">Cuenta puramente administrativa o de auditoría.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Security & RBAC Summary -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="admin-card p-4 h-100">
                <h5 class="fw-bold mb-3 text-heading">
                    <i class="bi bi-shield-check text-primary me-2"></i> Privilegios Spatie RBAC
                </h5>
                <p class="small text-muted mb-3">
                    El usuario tiene asignado el rol <strong>{{ $user->rol }}</strong>, con alcance sobre los módulos del sistema:
                </p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @forelse($user->roles as $role)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 rounded-3">
                            <i class="bi bi-key-fill me-1"></i> Rol: {{ $role->name }}
                        </span>
                    @empty
                        <span class="text-muted small fst-italic">Rol directo: {{ $user->rol }}</span>
                    @endforelse
                </div>

                <div class="p-3 bg-body-tertiary rounded-3 border">
                    <h6 class="fw-semibold text-heading small mb-1">Alcance Operativo:</h6>
                    <ul class="mb-0 ps-3 small text-muted">
                        @if($user->rol === 'ADMINISTRADOR')
                            <li>Acceso irrestricto a todos los módulos, ajustes del sistema y configuración.</li>
                            <li>Creación, modificación y baja de centros de costo, personal y activos.</li>
                        @elseif($user->rol === 'LOGISTICO' || $user->rol === 'ALMACENERO')
                            <li>Gestión operativa del proyecto asignado: inventario, activos, despachos, devoluciones y kardex.</li>
                            <li>Pistoleo de códigos QR y control de retornos de campo.</li>
                        @elseif($user->rol === 'SUPERVISOR')
                            <li>Gestión de cuadrillas de obra y programación de roster 14x7 del proyecto asignado.</li>
                            <li>Recepción de actas con firma digital en tablets y frentes de trabajo.</li>
                        @else
                            <li>Visualización de reportes analíticos, exportación a Excel e inspección de auditoría.</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="admin-card p-4 h-100">
                <h5 class="fw-bold mb-3 text-heading">
                    <i class="bi bi-clock-history text-primary me-2"></i> Registro & Trazabilidad
                </h5>

                <div class="p-3 bg-body-tertiary rounded-3 border mb-3">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="small fw-semibold text-muted">ID de Cuenta:</span>
                        <span class="small font-monospace fw-bold text-heading">#{{ $user->id }}</span>
                    </div>
                    @if($user->proyecto)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="small fw-semibold text-muted">Proyecto Asignado:</span>
                        <span class="small fw-bold text-primary font-monospace">[{{ $user->proyecto->codigo }}] {{ $user->proyecto->nombre }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="small fw-semibold text-muted">Fecha de Creación:</span>
                        <span class="small text-muted">{{ $user->created_at?->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small fw-semibold text-muted">Última Modificación:</span>
                        <span class="small text-muted">{{ $user->updated_at?->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>

                <h6 class="fw-bold mb-2 text-heading small">Últimos Despachos Emitidos por este Usuario:</h6>
                <ul class="list-group list-group-flush small">
                    @forelse($user->despachosRegistrados as $desp)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <div>
                                <strong class="text-heading">{{ $desp->codigo_orden }}</strong>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $desp->created_at?->format('d/m/Y') }}</div>
                            </div>
                            <span class="badge bg-body-secondary text-body border">{{ $desp->tipo_operacion }}</span>
                        </li>
                    @empty
                        <li class="list-group-item px-0 bg-transparent text-muted fst-italic">
                            No registra operaciones de despacho emitidas hasta el momento.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

@endsection
