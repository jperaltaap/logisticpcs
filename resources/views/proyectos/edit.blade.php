@extends('layouts.admin')

@section('title', 'Editar Proyecto ' . $proyecto->codigo)
@section('page_title', 'Modificar Proyecto: ' . $proyecto->codigo)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('proyectos.index') }}" class="text-decoration-none fw-semibold text-primary">Proyectos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Editar {{ $proyecto->codigo }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('proyectos.show', $proyecto) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-eye me-1"></i> Ver Ficha
    </a>
    <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1"></i> Volver al Listado
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
                            <i class="bi bi-pencil-square text-primary me-2"></i> Editar Datos de Proyecto
                        </h5>
                        <small class="text-muted">Actualización de parámetros operativos y centros de costo.</small>
                    </div>
                    <span class="badge bg-primary font-monospace">{{ $proyecto->codigo }}</span>
                </div>

                <form action="{{ route('proyectos.update', $proyecto) }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Código y Estado -->
                        <div class="col-md-6">
                            <label for="codigo" class="form-label small fw-bold text-muted text-uppercase">Código Único de Proyecto *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-qr-code"></i></span>
                                <input type="text" name="codigo" id="codigo" class="form-control font-monospace @error('codigo') is-invalid @enderror" value="{{ old('codigo', $proyecto->codigo) }}" required>
                                @error('codigo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="estado" class="form-label small fw-bold text-muted text-uppercase">Estado Operativo *</label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', $proyecto->estado) === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Permite despachos y asignaciones)</option>
                                <option value="SUSPENDIDO" {{ old('estado', $proyecto->estado) === 'SUSPENDIDO' ? 'selected' : '' }}>SUSPENDIDO (Bloqueo temporal de recursos)</option>
                                <option value="FINALIZADO" {{ old('estado', $proyecto->estado) === 'FINALIZADO' ? 'selected' : '' }}>FINALIZADO (Obra concluida)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nombre del Proyecto -->
                        <div class="col-md-12">
                            <label for="nombre" class="form-label small fw-bold text-muted text-uppercase">Nombre Descriptivo de Obra / Proyecto *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $proyecto->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cliente y Ubicación -->
                        <div class="col-md-6">
                            <label for="cliente" class="form-label small fw-bold text-muted text-uppercase">Cliente Contratante *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-building"></i></span>
                                <input type="text" name="cliente" id="cliente" class="form-control @error('cliente') is-invalid @enderror" value="{{ old('cliente', $proyecto->cliente) }}" required>
                                @error('cliente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="ubicacion_direccion" class="form-label small fw-bold text-muted text-uppercase">Ubicación Geográfica / Dirección de Obra *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body text-muted"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="ubicacion_direccion" id="ubicacion_direccion" class="form-control @error('ubicacion_direccion') is-invalid @enderror" value="{{ old('ubicacion_direccion', $proyecto->ubicacion_direccion) }}" required>
                                @error('ubicacion_direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6">
                            <label for="fecha_inicio" class="form-label small fw-bold text-muted text-uppercase">Fecha de Inicio Programada *</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror" value="{{ old('fecha_inicio', $proyecto->fecha_inicio?->format('Y-m-d')) }}" required>
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_fin_estimada" class="form-label small fw-bold text-muted text-uppercase">Fecha Estimada de Finalización</label>
                            <input type="date" name="fecha_fin_estimada" id="fecha_fin_estimada" class="form-control @error('fecha_fin_estimada') is-invalid @enderror" value="{{ old('fecha_fin_estimada', $proyecto->fecha_fin_estimada?->format('Y-m-d')) }}">
                            @error('fecha_fin_estimada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Responsable Principal de Proyecto -->
                        <div class="col-md-12">
                            <label for="responsable_personal_id" class="form-label small fw-bold text-muted text-uppercase">Responsable Principal / Ingeniero Residente</label>
                            <select name="responsable_personal_id" id="responsable_personal_id" class="form-select @error('responsable_personal_id') is-invalid @enderror">
                                <option value="">-- Sin asignar (se puede asignar posteriormente) --</option>
                                @foreach($personalDisponibles as $pers)
                                    <option value="{{ $pers->id }}" {{ old('responsable_personal_id', $proyecto->responsable_personal_id) == $pers->id ? 'selected' : '' }}>
                                        {{ $pers->nombre_completo }} — {{ $pers->cargo }} (DNI: {{ $pers->dni }})
                                    </option>
                                @endforeach
                            </select>
                            @error('responsable_personal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Co-Responsables / Supervisores Adicionales del Proyecto -->
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people-fill text-primary me-1"></i> Equipo de Responsables / Supervisores del Proyecto (Múltiples)</span>
                                <span class="badge badge-soft-primary fw-normal">Sucursal / Multi-Responsable</span>
                            </label>
                            @php
                                $currentResps = $proyecto->responsables->pluck('id')->all();
                                if ($proyecto->responsable_personal_id && !in_array($proyecto->responsable_personal_id, $currentResps)) {
                                    $currentResps[] = $proyecto->responsable_personal_id;
                                }
                                $selectedResps = collect(old('responsables_ids', $currentResps))->map(fn($v) => (int)$v)->all();
                            @endphp
                            <div class="p-3 rounded-3 border bg-body-tertiary" style="max-height: 210px; overflow-y: auto;">
                                <div class="row g-2">
                                    @forelse($personalDisponibles as $pers)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="responsables_ids[]" value="{{ $pers->id }}" id="resp_{{ $pers->id }}" {{ in_array($pers->id, $selectedResps) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="resp_{{ $pers->id }}">
                                                    <span class="fw-semibold text-heading">{{ $pers->nombre_completo }}</span>
                                                    <span class="text-muted d-block" style="font-size: 0.72rem;">{{ $pers->cargo }} · DNI {{ $pers->dni }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 small text-muted fst-italic">No hay personal activo disponible.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="form-text small">Selecciona todos los responsables o supervisores autorizados a cargo de este proyecto.</div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12">
                            <label for="observaciones" class="form-label small fw-bold text-muted text-uppercase">Observaciones Técnicas</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $proyecto->observaciones) }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary px-4 fw-semibold">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Actualizar Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
