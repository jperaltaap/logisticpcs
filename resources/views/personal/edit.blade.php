@extends('layouts.admin')

@section('title', 'Editar Personal ' . $personal->nombre_completo)
@section('page_title', 'Modificar Ficha: ' . $personal->nombre_completo)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('personal.index') }}" class="text-decoration-none fw-semibold text-primary">Personal</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Editar {{ $personal->dni }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('personal.show', $personal) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-eye me-1"></i> Ver Ficha
    </a>
    <a href="{{ route('personal.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Volver al Padrón
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="admin-card overflow-hidden">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-pencil-square text-primary me-2"></i> Editar Datos de Trabajador
                        </h5>
                        <small class="text-muted">Actualización de datos laborales, proyecto y cuenta de acceso.</small>
                    </div>
                    <span class="badge bg-primary font-monospace">{{ $personal->codigo_fotocheck ?: "DNI {$personal->dni}" }}</span>
                </div>

                <form action="{{ route('personal.update', $personal) }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Identificación Oficial -->
                        <div class="col-md-4">
                            <label for="dni" class="form-label small fw-bold text-muted text-uppercase">DNI / CE (Único) *</label>
                            <input type="text" name="dni" id="dni" class="form-control font-monospace @error('dni') is-invalid @enderror" value="{{ old('dni', $personal->dni) }}" maxlength="15" required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="codigo_fotocheck" class="form-label small fw-bold text-muted text-uppercase">Código de Fotocheck</label>
                            <input type="text" name="codigo_fotocheck" id="codigo_fotocheck" class="form-control font-monospace @error('codigo_fotocheck') is-invalid @enderror" value="{{ old('codigo_fotocheck', $personal->codigo_fotocheck) }}">
                            @error('codigo_fotocheck')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="codigo_trabajador" class="form-label small fw-bold text-muted text-uppercase">Código Interno de Trabajador</label>
                            <input type="text" name="codigo_trabajador" id="codigo_trabajador" class="form-control font-monospace @error('codigo_trabajador') is-invalid @enderror" value="{{ old('codigo_trabajador', $personal->codigo_trabajador) }}">
                            @error('codigo_trabajador')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nombres y Apellidos -->
                        <div class="col-md-6">
                            <label for="nombres" class="form-label small fw-bold text-muted text-uppercase">Nombres *</label>
                            <input type="text" name="nombres" id="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres', $personal->nombres) }}" required>
                            @error('nombres')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label small fw-bold text-muted text-uppercase">Apellidos *</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $personal->apellidos) }}" required>
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cargo y Área -->
                        <div class="col-md-6">
                            <label for="cargo" class="form-label small fw-bold text-muted text-uppercase">Cargo Laboral *</label>
                            <input type="text" name="cargo" id="cargo" class="form-control @error('cargo') is-invalid @enderror" value="{{ old('cargo', $personal->cargo) }}" required>
                            @error('cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="area" class="form-label small fw-bold text-muted text-uppercase">Área Operativa *</label>
                            <input type="text" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', $personal->area) }}" required>
                            @error('area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contacto -->
                        <div class="col-md-6">
                            <label for="telefono" class="form-label small fw-bold text-muted text-uppercase">Teléfono de Contacto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $personal->telefono) }}">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label small fw-bold text-muted text-uppercase">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" id="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo', $personal->correo) }}">
                                @error('correo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Proyecto y Estado Laboral -->
                        <div class="col-md-4">
                            <label for="proyecto_id" class="form-label small fw-bold text-muted text-uppercase">Proyecto Principal / Base</label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror">
                                <option value="">-- Sin proyecto asignado --</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_id', $personal->proyecto_id) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} — {{ $pry->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label small fw-bold text-muted text-uppercase">Estado Laboral *</label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', $personal->estado) === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Habilitado para despachos)</option>
                                <option value="VACACIONES" {{ old('estado', $personal->estado) === 'VACACIONES' ? 'selected' : '' }}>VACACIONES</option>
                                <option value="DESCANSO_MEDICO" {{ old('estado', $personal->estado) === 'DESCANSO_MEDICO' ? 'selected' : '' }}>DESCANSO MÉDICO</option>
                                <option value="CESADO" {{ old('estado', $personal->estado) === 'CESADO' ? 'selected' : '' }}>CESADO (Inhabilitado)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vinculación opcional con Usuario del Sistema -->
                        <div class="col-md-4">
                            <label for="user_id" class="form-label small fw-bold text-muted text-uppercase">Cuenta Web Asociada</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">-- Ninguna (Solo trabajador físico) --</option>
                                @foreach($usuariosDisponibles as $usr)
                                    <option value="{{ $usr->id }}" {{ old('user_id', $personal->user_id) == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ $usr->email }}) — {{ $usr->rol }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Múltiples Proyectos Asignados -->
                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold text-muted text-uppercase d-block mb-1">
                                <i class="bi bi-buildings text-primary me-1"></i> Proyectos / Sucursales a los que pertenece (Multiselección)
                            </label>
                            <div class="border rounded-3 p-3 bg-body-tertiary" style="max-height: 185px; overflow-y: auto;">
                                <div class="row g-2">
                                    @php
                                        $currentProyectosIds = $personal->proyectos->pluck('id')->all();
                                        if (empty($currentProyectosIds) && $personal->proyecto_id) {
                                            $currentProyectosIds = [$personal->proyecto_id];
                                        }
                                        $selectedProyectos = old('proyectos_ids', $currentProyectosIds);
                                    @endphp
                                    @forelse($proyectos as $pry)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="proyectos_ids[]" value="{{ $pry->id }}" id="pry_pers_edit_{{ $pry->id }}"
                                                    {{ in_array($pry->id, $selectedProyectos) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="pry_pers_edit_{{ $pry->id }}">
                                                    <span class="fw-bold font-monospace text-primary">{{ $pry->codigo }}</span> — {{ $pry->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted small">No hay proyectos activos registrados.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="form-text small">Permite asignar al trabajador a varios proyectos simultáneamente.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('personal.index') }}" class="btn btn-outline-secondary px-4 fw-semibold">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Actualizar Ficha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
