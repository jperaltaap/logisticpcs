@extends('layouts.admin')

@section('title', 'Registrar Calibración o Mantenimiento')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}" class="text-secondary text-decoration-none">Taller & Calibraciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo Registro</li>
                </ol>
            </nav>
            <h1 class="h3 font-bold text-dark dark:text-white mb-0">Registrar Mantenimiento / Calibración</h1>
            <p class="text-muted text-sm mb-0">Registre la entrada de un activo a taller especializado o laboratorio de calibración.</p>
        </div>
        <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Volver al Listado
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>
                    <h6 class="mb-0 font-bold">Por favor verifique los siguientes errores:</h6>
                    <ul class="mb-0 mt-1 ps-3 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white dark:bg-dark py-3 px-4 border-bottom border-light dark:border-secondary">
            <h6 class="mb-0 font-bold text-dark dark:text-white d-flex align-items-center gap-2">
                <i class="bi bi-tools text-primary"></i> Formulario de Mantenimiento / Calibración
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('mantenimientos.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="activo_id" class="form-label font-bold text-sm text-dark dark:text-light">Activo / Equipo <span class="text-danger">*</span></label>
                        <select name="activo_id" id="activo_id" class="form-select @error('activo_id') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Activo --</option>
                            @foreach($activos as $act)
                                <option value="{{ $act->id }}" {{ (string) old('activo_id', request('activo_id', $selectedActivoId ?? '')) === (string) $act->id ? 'selected' : '' }}>
                                    [{{ $act->codigo_interno }}] {{ $act->articulo->descripcion ?? $act->articulo->nombre ?? 'N/A' }} 
                                    - Serie: {{ $act->numero_serie ?? 'S/N' }} (Estado: {{ $act->estado_operativo }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i> El activo pasará automáticamente a estado <strong>EN MANTENIMIENTO</strong> mientras dure el servicio.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="tipo" class="form-label font-bold text-sm text-dark dark:text-light">Tipo de Servicio <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="PREVENTIVO" {{ old('tipo') == 'PREVENTIVO' ? 'selected' : '' }}>Preventivo</option>
                            <option value="CORRECTIVO" {{ old('tipo') == 'CORRECTIVO' ? 'selected' : '' }}>Correctivo</option>
                            <option value="CALIBRACION_LAB" {{ old('tipo') == 'CALIBRACION_LAB' ? 'selected' : '' }}>Calibración de Laboratorio</option>
                            <option value="CERTIFICACION" {{ old('tipo') == 'CERTIFICACION' ? 'selected' : '' }}>Certificación Anual / Patrón</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="fecha_ingreso" class="form-label font-bold text-sm text-dark dark:text-light">Fecha de Ingreso <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control @error('fecha_ingreso') is-invalid @enderror" value="{{ old('fecha_ingreso', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="proveedor_taller" class="form-label font-bold text-sm text-dark dark:text-light">Taller / Proveedor / Lab</label>
                        <input type="text" name="proveedor_taller" id="proveedor_taller" class="form-control @error('proveedor_taller') is-invalid @enderror" value="{{ old('proveedor_taller') }}" placeholder="Ej: SGS del Perú, Taller Interno">
                    </div>

                    <div class="col-md-4">
                        <label for="costo" class="form-label font-bold text-sm text-dark dark:text-light">Costo Estimado (S/.)</label>
                        <div class="input-group">
                            <span class="input-group-text">S/.</span>
                            <input type="number" step="1" min="0" name="costo" id="costo" class="form-control @error('costo') is-invalid @enderror" value="{{ old('costo') }}" placeholder="0">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="resultado" class="form-label font-bold text-sm text-dark dark:text-light">Estado Inicial del Servicio</label>
                        <select name="resultado" id="resultado" class="form-select @error('resultado') is-invalid @enderror">
                            <option value="EN_PROCESO" {{ old('resultado', 'EN_PROCESO') == 'EN_PROCESO' ? 'selected' : '' }}>EN PROCESO (En Taller/Lab)</option>
                            <option value="CONFORME_OPERATIVO" {{ old('resultado') == 'CONFORME_OPERATIVO' ? 'selected' : '' }}>CONFORME OPERATIVO (Retorno Inmediato)</option>
                            <option value="NO_CONFORME_BAJA" {{ old('resultado') == 'NO_CONFORME_BAJA' ? 'selected' : '' }}>NO CONFORME (Dar de baja)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="descripcion_falla_o_trabajo" class="form-label font-bold text-sm text-dark dark:text-light">Descripción de la Falla o Trabajo Solicitado <span class="text-danger">*</span></label>
                        <textarea name="descripcion_falla_o_trabajo" id="descripcion_falla_o_trabajo" rows="3" class="form-control @error('descripcion_falla_o_trabajo') is-invalid @enderror" placeholder="Indicar el motivo del envío, diagnóstico preliminar, fallas observadas..." required>{{ old('descripcion_falla_o_trabajo') }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="card bg-light dark:bg-gray-800 border-0 rounded-3 p-3">
                            <h6 class="font-bold text-xs text-uppercase tracking-wider text-muted mb-2">Completar si el servicio ya concluyó</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="fecha_salida" class="form-label text-xs font-bold text-secondary">Fecha de Salida / Conclusión</label>
                                    <input type="date" name="fecha_salida" id="fecha_salida" class="form-control form-control-sm" value="{{ old('fecha_salida') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="certificado_calibracion_pdf" class="form-label text-xs font-bold text-secondary">N° Certificado / Nombre de Archivo</label>
                                    <input type="text" name="certificado_calibracion_pdf" id="certificado_calibracion_pdf" class="form-control form-control-sm" value="{{ old('certificado_calibracion_pdf') }}" placeholder="Ej: CERT-2026-0041.pdf">
                                </div>
                                <div class="col-md-4">
                                    <label for="proxima_calibracion_sugerida" class="form-label text-xs font-bold text-secondary">Próxima Fecha Sugerida</label>
                                    <input type="date" name="proxima_calibracion_sugerida" id="proxima_calibracion_sugerida" class="form-control form-control-sm" value="{{ old('proxima_calibracion_sugerida') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i> Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
