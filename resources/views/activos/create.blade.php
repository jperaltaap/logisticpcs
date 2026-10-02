@extends('layouts.admin')

@section('title', 'Registrar Activo Serializado')
@section('page_title', 'Registrar Nueva Unidad Física de Activo')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('activos.index') }}" class="text-decoration-none fw-semibold text-primary">Activos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nuevo Activo</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="admin-card p-4">
                <form action="{{ route('activos.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-qr-code-scan me-2 text-primary"></i> Identificación del Activo
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="proyecto_actual_id" class="form-label small fw-bold">Proyecto al que Pertenece <span class="text-danger">*</span></label>
                            <select name="proyecto_actual_id" id="proyecto_actual_id" class="form-select @error('proyecto_actual_id') is-invalid @enderror" required>
                                <option value="">Seleccione el proyecto</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_actual_id', $proyectoActivoId ?? $proyectoActivo?->id) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} - {{ $pry->nombre }} {{ ($proyectoActivoId ?? $proyectoActivo?->id) == $pry->id ? '★ [ACTIVO]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_actual_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Proyecto propietario o responsable de esta unidad serializada.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="ubicacion_actual_id" class="form-label small fw-bold">Centro de Almacén al que Pertenece <span class="text-danger">*</span></label>
                            <select name="ubicacion_actual_id" id="ubicacion_actual_id" class="form-select @error('ubicacion_actual_id') is-invalid @enderror" required>
                                <option value="">Seleccione el centro de almacén</option>
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}" data-proyecto-id="{{ $ub->proyecto_id ?? '' }}" {{ old('ubicacion_actual_id') == $ub->id ? 'selected' : '' }}>
                                        {{ $ub->codigo }} - {{ $ub->nombre }} {{ $ub->direccion ? '(' . $ub->direccion . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_actual_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small" id="ubicacion_help">Almacén físico donde ingresa y permanece disponible el activo.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="articulo_id" class="form-label small fw-bold">Artículo Maestro (Catálogo) <span class="text-danger">*</span></label>
                            <select name="articulo_id" id="articulo_id" class="form-select @error('articulo_id') is-invalid @enderror" required>
                                <option value="">Seleccione el artículo del catálogo</option>
                                @foreach($articulos as $art)
                                    <option value="{{ $art->id }}" data-proyecto-id="{{ $art->proyecto_id ?? '' }}" {{ (old('articulo_id', request('articulo_id')) == $art->id) ? 'selected' : '' }}>
                                        {{ $art->codigo_sku }} - {{ $art->descripcion }} ({{ $art->marca ?? 'S/M' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('articulo_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Solo se listan artículos con control de serie activado.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="codigo_interno" class="form-label small fw-bold">Código QR / Placa <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_interno" id="codigo_interno" class="form-control font-monospace fw-bold @error('codigo_interno') is-invalid @enderror" value="{{ old('codigo_interno', $codigoSugerido) }}" required>
                            @error('codigo_interno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Identificador único en etiqueta QR.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="numero_serie" class="form-label small fw-bold">N° Serie de Fábrica</label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control font-monospace @error('numero_serie') is-invalid @enderror" value="{{ old('numero_serie') }}" placeholder="Ej: SN-90S-2026-01">
                            @error('numero_serie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Serial grabado por el fabricante.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="estado_operativo" class="form-label small fw-bold">Estado Operativo <span class="text-danger">*</span></label>
                            <select name="estado_operativo" id="estado_operativo" class="form-select @error('estado_operativo') is-invalid @enderror" required>
                                <option value="OPERATIVO" {{ old('estado_operativo', 'OPERATIVO') == 'OPERATIVO' ? 'selected' : '' }}>OPERATIVO (Listo para uso)</option>
                                <option value="EN_MANTENIMIENTO" {{ old('estado_operativo') == 'EN_MANTENIMIENTO' ? 'selected' : '' }}>EN MANTENIMIENTO / CALIBRACIÓN</option>
                                <option value="DANADO" {{ old('estado_operativo') == 'DANADO' ? 'selected' : '' }}>DAÑADO / AVERIADO</option>
                                <option value="DE_BAJA" {{ old('estado_operativo') == 'DE_BAJA' ? 'selected' : '' }}>DE BAJA</option>
                            </select>
                            @error('estado_operativo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Condición Inicial en Almacén</label>
                            <input type="hidden" name="condicion_prestamo" value="DISPONIBLE">
                            <div class="form-control bg-light text-success fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> DISPONIBLE EN ALMACÉN (Sin asignación inicial)
                            </div>
                            <div class="form-text small">Las asignaciones a personal o cuadrillas se realizan mediante Despachos / Préstamos.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="fecha_ingreso" class="form-label small fw-bold">Fecha de Ingreso al Sistema <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control @error('fecha_ingreso') is-invalid @enderror" value="{{ old('fecha_ingreso', date('Y-m-d')) }}" required>
                            @error('fecha_ingreso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info border-0 bg-info bg-opacity-10 text-body small mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill text-info fs-5"></i>
                                <div>
                                    <strong>Política de Custodia y Trazabilidad:</strong> Todo artículo serializado se registra inicialmente en su <strong>Proyecto</strong> y <strong>Centro de Almacén</strong> sin responsable ni cuadrilla asignada. Su entrega, préstamo o instalación en obra se gestiona desde el módulo de <span class="fw-semibold">Salidas de Almacén / Despachos</span>.
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones / Estado Físico Inicial</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Detalles de accesorios incluidos (maletín, cargador, electrodos de repuesto), condiciones de recepción...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('activos.index') }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Guardar y Generar Código QR
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
        const selArticulo = document.getElementById('articulo_id');
        if (!selProyecto || !selUbicacion) return;

        const ubicacionOpts = Array.from(selUbicacion.querySelectorAll('option[value]:not([value=""])'));
        const articuloOpts = selArticulo ? Array.from(selArticulo.querySelectorAll('option[value]:not([value=""])')) : [];

        function filterByProyecto() {
            const pid = selProyecto.value ? String(selProyecto.value) : '';
            let visibleUbCount = 0;

            ubicacionOpts.forEach(opt => {
                const ubPid = opt.getAttribute('data-proyecto-id') || '';
                const match = !pid || !ubPid || ubPid === pid;
                opt.hidden = !match;
                opt.disabled = !match;
                if (match) visibleUbCount++;
                if (!match && opt.selected) {
                    selUbicacion.value = '';
                }
            });

            if (!selUbicacion.value && visibleUbCount === 1) {
                const firstVisible = ubicacionOpts.find(o => !o.disabled);
                if (firstVisible) selUbicacion.value = firstVisible.value;
            }

            articuloOpts.forEach(opt => {
                const artPid = opt.getAttribute('data-proyecto-id') || '';
                const match = !pid || !artPid || artPid === pid;
                opt.hidden = !match;
                opt.disabled = !match;
                if (!match && opt.selected) {
                    selArticulo.value = '';
                }
            });
        }

        selProyecto.addEventListener('change', filterByProyecto);
        filterByProyecto();
    });
</script>
@endpush
