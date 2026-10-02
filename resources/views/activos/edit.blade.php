@extends('layouts.admin')

@section('title', 'Editar Activo ' . $activo->codigo_interno)
@section('page_title', 'Modificar Ficha de Activo: ' . $activo->codigo_interno)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('activos.index') }}" class="text-decoration-none fw-semibold text-primary">Activos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('activos.show', $activo) }}" class="text-decoration-none fw-semibold text-primary">{{ $activo->codigo_interno }}</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="admin-card p-4">
                <form action="{{ route('activos.update', $activo) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-pencil-square me-2 text-primary"></i> Modificar Datos de la Unidad
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="proyecto_actual_id" class="form-label small fw-bold">Proyecto al que Pertenece <span class="text-danger">*</span></label>
                            <select name="proyecto_actual_id" id="proyecto_actual_id" class="form-select @error('proyecto_actual_id') is-invalid @enderror" required>
                                <option value="">Seleccione el proyecto</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_actual_id', $activo->proyecto_actual_id) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} - {{ $pry->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_actual_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="ubicacion_actual_id" class="form-label small fw-bold">Centro de Almacén al que Pertenece <span class="text-danger">*</span></label>
                            <select name="ubicacion_actual_id" id="ubicacion_actual_id" class="form-select @error('ubicacion_actual_id') is-invalid @enderror" required>
                                <option value="">Seleccione el centro de almacén</option>
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}" data-proyecto-id="{{ $ub->proyecto_id ?? '' }}" {{ old('ubicacion_actual_id', $activo->ubicacion_actual_id) == $ub->id ? 'selected' : '' }}>
                                        {{ $ub->codigo }} - {{ $ub->nombre }} {{ $ub->direccion ? '(' . $ub->direccion . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_actual_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="articulo_id" class="form-label small fw-bold">Artículo Maestro <span class="text-danger">*</span></label>
                            <select name="articulo_id" id="articulo_id" class="form-select @error('articulo_id') is-invalid @enderror" required>
                                @foreach($articulos as $art)
                                    <option value="{{ $art->id }}" data-proyecto-id="{{ $art->proyecto_id ?? '' }}" {{ old('articulo_id', $activo->articulo_id) == $art->id ? 'selected' : '' }}>
                                        {{ $art->codigo_sku }} - {{ $art->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('articulo_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="codigo_interno" class="form-label small fw-bold">Código QR / Placa <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_interno" id="codigo_interno" class="form-control font-monospace fw-bold @error('codigo_interno') is-invalid @enderror" value="{{ old('codigo_interno', $activo->codigo_interno) }}" required>
                            @error('codigo_interno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="numero_serie" class="form-label small fw-bold">N° Serie de Fábrica</label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control font-monospace @error('numero_serie') is-invalid @enderror" value="{{ old('numero_serie', $activo->numero_serie) }}">
                            @error('numero_serie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="estado_operativo" class="form-label small fw-bold">Estado Operativo <span class="text-danger">*</span></label>
                            @if($activo->estado_operativo === 'EN_MANTENIMIENTO')
                                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3 mb-2 border-warning" role="alert">
                                    <i class="bi bi-tools text-warning fs-5 flex-shrink-0"></i>
                                    <div class="small">
                                        <strong>Equipo en taller/calibración:</strong> Para restablecerlo a <strong>OPERATIVO</strong>, debe registrarse el retorno y conformidad técnica en el módulo de <a href="{{ route('mantenimientos.index', ['q' => $activo->codigo_interno]) }}" class="alert-link text-decoration-underline fw-bold">Calibraciones & Taller</a>.
                                    </div>
                                </div>
                            @endif
                            <select name="estado_operativo" id="estado_operativo" class="form-select @error('estado_operativo') is-invalid @enderror" required>
                                <option value="OPERATIVO" {{ old('estado_operativo', $activo->estado_operativo) == 'OPERATIVO' ? 'selected' : '' }} {{ $activo->estado_operativo === 'EN_MANTENIMIENTO' ? 'disabled' : '' }}>
                                    OPERATIVO {{ $activo->estado_operativo === 'EN_MANTENIMIENTO' ? '(Requiere dar de alta en Calibraciones & Taller)' : '' }}
                                </option>
                                <option value="EN_MANTENIMIENTO" {{ old('estado_operativo', $activo->estado_operativo) == 'EN_MANTENIMIENTO' ? 'selected' : '' }}>
                                    EN MANTENIMIENTO (Se enlista en Calibraciones & Taller)
                                </option>
                                <option value="DANADO" {{ old('estado_operativo', $activo->estado_operativo) == 'DANADO' ? 'selected' : '' }}>DAÑADO</option>
                                <option value="DE_BAJA" {{ old('estado_operativo', $activo->estado_operativo) == 'DE_BAJA' ? 'selected' : '' }}>DE BAJA</option>
                            </select>
                            <div class="form-text small text-muted">
                                Al seleccionar <strong>EN MANTENIMIENTO</strong>, el activo se enlista automáticamente en Calibraciones & Taller para seguimiento técnico.
                            </div>
                            @error('estado_operativo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_ingreso" class="form-label small fw-bold">Fecha de Ingreso <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control @error('fecha_ingreso') is-invalid @enderror" value="{{ old('fecha_ingreso', $activo->fecha_ingreso?->format('Y-m-d')) }}" required>
                            @error('fecha_ingreso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-shield-check me-2 text-primary"></i> Estado de Asignación Actual (Gestionado por Almacén)
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Condición de Préstamo</label>
                            <div class="form-control bg-light fw-semibold small">
                                {{ $activo->condicion_prestamo }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Personal Custodio</label>
                            <div class="form-control bg-light small">
                                {{ $activo->responsable?->nombre_completo ?? 'Sin asignar (En almacén)' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Cuadrilla Asignada</label>
                            <div class="form-control bg-light small">
                                {{ $activo->cuadrilla?->nombre ?? 'Sin cuadrilla asignada' }}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-text small text-muted">
                                <i class="bi bi-info-circle me-1"></i> Las asignaciones de custodia a personal o cuadrillas se gestionan mediante las operaciones de <a href="{{ route('despachos.index') }}" class="text-decoration-none fw-semibold">Salidas de Almacén y Préstamos</a>.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones / Historial</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $activo->observaciones) }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('activos.show', $activo) }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selProyecto = document.getElementById('proyecto_actual_id');
        const selUbicacion = document.getElementById('ubicacion_actual_id');
        if (!selProyecto || !selUbicacion) return;

        const ubicacionOpts = Array.from(selUbicacion.querySelectorAll('option[value]:not([value=""])'));

        function filterByProyecto() {
            const pid = selProyecto.value ? String(selProyecto.value) : '';
            ubicacionOpts.forEach(opt => {
                const ubPid = opt.getAttribute('data-proyecto-id') || '';
                const match = !pid || !ubPid || ubPid === pid;
                opt.hidden = !match;
                opt.disabled = !match;
                if (!match && opt.selected) {
                    selUbicacion.value = '';
                }
            });
        }

        selProyecto.addEventListener('change', filterByProyecto);
        filterByProyecto();
    });
</script>
@endpush
