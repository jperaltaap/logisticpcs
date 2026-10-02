@extends('layouts.admin')

@section('title', 'Nuevo Usuario del Sistema')
@section('page_title', 'Registrar Nuevo Usuario (Acceso Web)')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none fw-semibold text-primary">Usuarios & Roles</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nuevo Usuario</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver al Listado
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="admin-card overflow-hidden">
                <div class="card-header bg-transparent py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-person-lock text-primary me-2"></i> Credenciales & Privilegios
                    </h5>
                    <small class="text-muted">Crea una cuenta para interactuar con la plataforma y asigna su rol Spatie RBAC.</small>
                </div>

                <form action="{{ route('users.store') }}" method="POST" class="p-4">
                    @csrf

                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-bold text-muted text-uppercase">Nombre del Usuario / Titular *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Juan Pérez" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-bold text-muted text-uppercase">Correo de Acceso (Único) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="usuario@logisticpcs.test" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-bold text-muted text-uppercase">Contraseña de Acceso *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 6 caracteres" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rol del Sistema -->
                        <div class="col-md-6">
                            <label for="rol" class="form-label small fw-bold text-muted text-uppercase">Rol & Permisos (RBAC) *</label>
                            <select name="rol" id="rol" class="form-select @error('rol') is-invalid @enderror" required onchange="toggleProyectoSelect(this.value)">
                                <option value="ADMINISTRADOR" {{ old('rol') === 'ADMINISTRADOR' ? 'selected' : '' }}>ADMINISTRADOR (Acceso total a todos los módulos)</option>
                                <option value="LOGISTICO" {{ old('rol', 'LOGISTICO') === 'LOGISTICO' ? 'selected' : '' }}>LOGÍSTICO (Gestión operativa de proyecto)</option>
                                <option value="SUPERVISOR" {{ old('rol') === 'SUPERVISOR' ? 'selected' : '' }}>SUPERVISOR (Control de cuadrillas y frentes)</option>
                                <option value="TECNICO" {{ old('rol') === 'TECNICO' ? 'selected' : '' }}>TÉCNICO (Visualización y consultas básicas)</option>
                                <option value="AUDITOR" {{ old('rol') === 'AUDITOR' ? 'selected' : '' }}>AUDITOR (Solo lectura y reportabilidad)</option>
                            </select>
                            @error('rol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Asignación de Proyecto Principal -->
                        <div class="col-md-6" id="proyectoGroup">
                            <label for="proyecto_id" class="form-label small fw-bold text-muted text-uppercase">
                                Proyecto / Sucursal Principal
                            </label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror">
                                <option value="">-- Sin asignación de proyecto fija --</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_id') == $pry->id ? 'selected' : '' }}>
                                        [{{ $pry->codigo }}] {{ $pry->nombre }} ({{ $pry->cliente }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Define la sucursal predeterminada del usuario al iniciar sesión.</div>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <label for="estado" class="form-label small fw-bold text-muted text-uppercase">Estado de la Cuenta *</label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Permite inicio de sesión)</option>
                                <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO (Acceso bloqueado)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vinculación Ficha de Personal -->
                        <div class="col-md-12">
                            <label for="personal_id" class="form-label small fw-bold text-muted text-uppercase">Vincular con Ficha de Personal (Opcional)</label>
                            <select name="personal_id" id="personal_id" class="form-select @error('personal_id') is-invalid @enderror">
                                <option value="">-- Sin vincular a trabajador físico --</option>
                                @foreach($personalSinUsuario as $pers)
                                    <option value="{{ $pers->id }}" {{ old('personal_id') == $pers->id ? 'selected' : '' }}>
                                        {{ $pers->nombre_completo }} (DNI: {{ $pers->dni }} — {{ $pers->cargo }})
                                    </option>
                                @endforeach
                            </select>
                            @error('personal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Conecta este acceso web con la identidad física del trabajador en obra.</div>
                        </div>

                        <!-- Múltiples Proyectos a Cargo -->
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-buildings-fill text-primary me-1"></i> Proyectos / Sucursales Autorizadas a su Cargo (Múltiples)</span>
                                <span class="badge badge-soft-primary fw-normal">Multi-Proyecto</span>
                            </label>
                            <div class="p-3 rounded-3 border bg-body-tertiary" style="max-height: 200px; overflow-y: auto;">
                                <div class="row g-2">
                                    @php $oldPrys = collect(old('proyectos_ids', []))->map(fn($v) => (int)$v)->all(); @endphp
                                    @forelse($proyectos as $pry)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="proyectos_ids[]" value="{{ $pry->id }}" id="u_pry_{{ $pry->id }}" {{ in_array($pry->id, $oldPrys) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="u_pry_{{ $pry->id }}">
                                                    <span class="badge bg-body-secondary text-body border font-monospace me-1">{{ $pry->codigo }}</span>
                                                    <span class="fw-semibold text-heading">{{ $pry->nombre }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 small text-muted fst-italic">No hay proyectos activos registrados.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="form-text small">Solo el rol <strong>ADMINISTRADOR</strong> posee acceso globalizado automático. Para los demás roles, puedes asignar uno o varios proyectos a su cargo.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4 fw-semibold">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-check-lg me-1"></i> Crear Cuenta de Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
