@extends('layouts.admin')

@section('title', 'Mi Perfil de Usuario')
@section('page_title', 'Configuración de Perfil de Usuario')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Cuenta</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Mi Perfil</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid px-0">

    @include('layouts.partials.alerts')

    <div class="row g-4">
        <!-- Columna Izquierda: Tarjeta de Identidad -->
        <div class="col-lg-4">
            <div class="admin-card text-center p-4 mb-4">
                <div class="user-avatar text-white d-inline-flex align-items-center justify-content-center rounded-circle fw-bold shadow mb-3" style="width: 84px; height: 84px; font-size: 2.2rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold text-heading mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-semibold">
                        <i class="bi bi-shield-check me-1"></i> {{ $user->rol ?? 'ADMINISTRADOR' }}
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ $user->estado ?? 'ACTIVO' }}
                    </span>
                </div>
                <div class="border-top pt-3 text-start small text-muted">
                    <div class="d-flex justify-content-between py-1">
                        <span>Miembro desde:</span>
                        <span class="fw-semibold text-body">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Inicial' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span>Última actualización:</span>
                        <span class="fw-semibold text-body">{{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Políticas de Seguridad -->
            <div class="admin-card p-4">
                <h6 class="fw-bold text-heading mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-lock-fill text-primary"></i> Seguridad de la Cuenta
                </h6>
                <ul class="text-muted small ps-3 mb-0">
                    <li class="mb-2">La contraseña debe tener un mínimo de <strong>8 caracteres</strong>.</li>
                    <li class="mb-2">Se recomienda combinar letras mayúsculas, minúsculas, números y símbolos.</li>
                    <li class="mb-0">Nunca comparta sus credenciales con otros colaboradores del sistema.</li>
                </ul>
            </div>
        </div>

        <!-- Columna Derecha: Formularios de Edición -->
        <div class="col-lg-8">
            <div class="admin-card p-4 mb-4">
                <div class="border-bottom pb-3 mb-4">
                    <h5 class="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill text-primary"></i> Información Personal
                    </h5>
                    <p class="text-muted small mb-0">Actualice sus datos de identidad y correo electrónico de acceso.</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold text-muted">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Rol en el Sistema (Solo Lectura)</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->rol ?? 'ADMINISTRADOR' }}" readonly disabled>
                            <div class="form-text text-muted">Los roles son asignados exclusivamente por los administradores.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Estado de Cuenta</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->estado ?? 'ACTIVO' }}" readonly disabled>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-4">
                        <h6 class="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-key-fill text-primary"></i> Cambio de Contraseña (Opcional)
                        </h6>
                        <p class="text-muted small mb-3">Deje estos campos en blanco si no desea modificar su contraseña actual.</p>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="current_password" class="form-label small fw-semibold text-muted">Contraseña Actual</label>
                                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Ingrese su contraseña actual para confirmar el cambio">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold text-muted">Nueva Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label small fw-semibold text-muted">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repita la nueva contraseña">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
