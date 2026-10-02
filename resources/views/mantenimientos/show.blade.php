@extends('layouts.admin')

@section('title', 'Detalle de Mantenimiento / Calibración')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-xs">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}" class="text-secondary text-decoration-none">Taller & Calibraciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Registro #{{ $mantenimiento->id }}</li>
                </ol>
            </nav>
            <h1 class="h3 font-bold text-dark dark:text-white mb-0">Detalle de Registro #{{ $mantenimiento->id }}</h1>
            <p class="text-muted text-sm mb-0">Consulta del historial técnico y estado de la calibración/mantenimiento.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('mantenimientos.edit', $mantenimiento) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Editar Registro
            </a>
            <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white dark:bg-dark py-3 px-4 border-bottom border-light dark:border-secondary">
                    <h6 class="mb-0 font-bold text-dark dark:text-white d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard2-check text-primary"></i> Información del Servicio
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Tipo de Intervención</span>
                            <span class="badge bg-secondary px-3 py-2 mt-1 fs-6 font-semibold">{{ $mantenimiento->tipo }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Dictamen / Resultado</span>
                            @if($mantenimiento->resultado == 'CONFORME_OPERATIVO')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 mt-1 fs-6 font-semibold">CONFORME OPERATIVO</span>
                            @elseif($mantenimiento->resultado == 'NO_CONFORME_BAJA')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 mt-1 fs-6 font-semibold">NO CONFORME - BAJA</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 mt-1 fs-6 font-semibold">EN PROCESO</span>
                            @endif
                        </div>

                        <div class="col-12 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Descripción / Diagnóstico Técnico</span>
                            <div class="p-3 bg-light dark:bg-gray-800 rounded-3 mt-1 text-sm font-medium">
                                {{ $mantenimiento->descripcion_falla_o_trabajo }}
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Taller / Proveedor / Laboratorio</span>
                            <span class="text-sm font-semibold text-dark dark:text-white">{{ $mantenimiento->proveedor_taller ?? 'No especificado' }}</span>
                        </div>
                        <div class="col-md-6 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Costo Registrado</span>
                            <span class="text-sm font-bold text-dark dark:text-white">
                                {{ $mantenimiento->costo ? 'S/. ' . number_format($mantenimiento->costo, 2) : 'Sin costo registrado' }}
                            </span>
                        </div>
                        <div class="col-md-4 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Fecha de Ingreso</span>
                            <span class="text-sm text-dark dark:text-white font-medium">{{ $mantenimiento->fecha_ingreso ? $mantenimiento->fecha_ingreso->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="col-md-4 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Fecha de Salida / Conclusión</span>
                            <span class="text-sm text-dark dark:text-white font-medium">{{ $mantenimiento->fecha_salida ? $mantenimiento->fecha_salida->format('d/m/Y') : 'Pendiente' }}</span>
                        </div>
                        <div class="col-md-4 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Próxima Calibración Sugerida</span>
                            <span class="text-sm text-primary font-bold">{{ $mantenimiento->proxima_calibracion_sugerida ? $mantenimiento->proxima_calibracion_sugerida->format('d/m/Y') : 'No programada' }}</span>
                        </div>
                        <div class="col-12 mt-3">
                            <span class="text-xs text-muted text-uppercase font-bold d-block">Documento / Certificado de Calibración</span>
                            <span class="text-sm font-semibold text-dark dark:text-white">{{ $mantenimiento->certificado_calibracion_pdf ?? 'Ninguno registrado' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white dark:bg-dark py-3 px-4 border-bottom border-light dark:border-secondary">
                    <h6 class="mb-0 font-bold text-dark dark:text-white d-flex align-items-center gap-2">
                        <i class="bi bi-cpu text-info"></i> Activo Vinculado
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($mantenimiento->activo)
                        <div class="text-center pb-3 border-bottom">
                            <div class="w-16 h-16 rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-2 fs-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h6 class="font-bold mb-0 text-dark dark:text-white">{{ $mantenimiento->activo->codigo_interno }}</h6>
                            <span class="text-xs text-muted">{{ $mantenimiento->activo->articulo->descripcion ?? $mantenimiento->activo->articulo->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between py-1 text-sm">
                                <span class="text-muted">Estado Actual:</span>
                                <span class="badge bg-info text-white">{{ $mantenimiento->activo->estado_operativo }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 text-sm">
                                <span class="text-muted">N° Serie:</span>
                                <span class="font-medium text-dark dark:text-white">{{ $mantenimiento->activo->numero_serie ?? 'S/N' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 text-sm">
                                <span class="text-muted">Ubicación / Almacén:</span>
                                <span class="font-medium text-dark dark:text-white">{{ $mantenimiento->activo->ubicacion->nombre ?? 'Sin almacén' }}</span>
                            </div>
                            <div class="mt-3 pt-3 border-top text-center">
                                <a href="{{ route('activos.show', $mantenimiento->activo) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-arrow-up-right-circle"></i> Ver Ficha del Activo
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-muted text-center py-4">No se encuentra el activo asociado.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
