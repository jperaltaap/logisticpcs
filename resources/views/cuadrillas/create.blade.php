@extends('layouts.admin')

@section('title', 'Nueva Cuadrilla de Trabajo')
@section('page_title', 'Crear Nueva Cuadrilla')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cuadrillas.index') }}" class="text-decoration-none fw-semibold text-primary">Cuadrillas</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nueva</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="admin-card p-4">
                <form action="{{ route('cuadrillas.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-people-fill me-2 text-primary"></i> Datos Operativos de la Cuadrilla
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="codigo_cuadrilla" class="form-label small fw-bold">Código de Cuadrilla <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_cuadrilla" id="codigo_cuadrilla" class="form-control font-monospace fw-bold @error('codigo_cuadrilla') is-invalid @enderror" value="{{ old('codigo_cuadrilla', 'CD-' . str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT)) }}" required>
                            @error('codigo_cuadrilla')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Identificador único del frente de trabajo.</div>
                        </div>

                        <div class="col-md-8">
                            <label for="nombre" class="form-label small fw-bold">Nombre / Denominación de la Cuadrilla <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Cuadrilla Fibra Óptica Troncal 01" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="proyecto_id" class="form-label small fw-bold">Proyecto Asignado <span class="text-danger">*</span></label>
                            @php
                                $selectedProyectoId = old('proyecto_id', session('proyecto_activo_id'));
                            @endphp
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror" required>
                                <option value="">Seleccione el proyecto de imputación...</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ (string) $selectedProyectoId === (string) $pry->id ? 'selected' : '' }}>
                                        [{{ $pry->codigo }}] {{ $pry->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Solo podrá asignar personal que pertenezca al proyecto seleccionado.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="lider_personal_id" class="form-label small fw-bold">Líder / Responsable de Cuadrilla <span class="text-danger">*</span></label>
                            <select name="lider_personal_id" id="lider_personal_id" class="form-select @error('lider_personal_id') is-invalid @enderror" required>
                                <option value="">Seleccione el supervisor / capataz líder...</option>
                                @foreach($personal as $p)
                                    <option value="{{ $p->id }}"
                                            data-proyectos='@json($p->proyectos_ids)'
                                            {{ (string) old('lider_personal_id') === (string) $p->id ? 'selected' : '' }}>
                                        {{ $p->apellidos }}, {{ $p->nombres }} (DNI: {{ $p->dni }}) - Cargo: {{ $p->cargo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lider_personal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="lider_help_text" class="form-text small">El líder debe pertenecer al mismo proyecto y será incorporado como primer miembro activo.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="regimen_laboral" class="form-label small fw-bold">Régimen Laboral / Turnos <span class="text-danger">*</span></label>
                            <select name="regimen_laboral" id="regimen_laboral" class="form-select @error('regimen_laboral') is-invalid @enderror" required>
                                <option value="14x7" {{ old('regimen_laboral') == '14x7' ? 'selected' : '' }}>14x7 (14 Días en Obra x 7 Días de Descanso - Roster Minero/Telecom)</option>
                                <option value="21x7" {{ old('regimen_laboral') == '21x7' ? 'selected' : '' }}>21x7 (21 Días en Obra x 7 Días de Descanso)</option>
                                <option value="10x4" {{ old('regimen_laboral') == '10x4' ? 'selected' : '' }}>10x4 (10 Días en Obra x 4 Días de Descanso)</option>
                                <option value="5x2" {{ old('regimen_laboral') == '5x2' ? 'selected' : '' }}>5x2 (Lunes a Viernes - Régimen Común Urbano)</option>
                                <option value="6x1" {{ old('regimen_laboral') == '6x1' ? 'selected' : '' }}>6x1 (Lunes a Sábado)</option>
                            </select>
                            @error('regimen_laboral')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="estado" class="form-label small fw-bold">Estado Inicial <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVA" {{ old('estado', 'ACTIVA') == 'ACTIVA' ? 'selected' : '' }}>ACTIVA (Operativa en campo)</option>
                                <option value="EN_DESCANSO" {{ old('estado') == 'EN_DESCANSO' ? 'selected' : '' }}>EN DESCANSO (Relevo de roster)</option>
                                <option value="DISUELTA" {{ old('estado') == 'DISUELTA' ? 'selected' : '' }}>DISUELTA (Finalizada)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones / Alcance de la Cuadrilla</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Detalles de frente asignado, zona geográfica, tramos de fibra...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('cuadrillas.index') }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Registrar Cuadrilla
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
    const proyectoSelect = document.getElementById('proyecto_id');
    const liderSelect = document.getElementById('lider_personal_id');
    const helpText = document.getElementById('lider_help_text');

    function filtrarLideresPorProyecto() {
        const proyId = parseInt(proyectoSelect.value || '0', 10);
        let visibles = 0;

        Array.from(liderSelect.options).forEach(opt => {
            if (!opt.value) return;
            const proyectos = JSON.parse(opt.getAttribute('data-proyectos') || '[]');
            const pertenece = proyId > 0 && proyectos.includes(proyId);
            opt.hidden = !pertenece;
            opt.disabled = !pertenece;
            if (pertenece) {
                visibles++;
            } else if (opt.selected) {
                opt.selected = false;
                liderSelect.value = '';
            }
        });

        if (!proyId) {
            helpText.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Seleccione primero un proyecto para habilitar el personal asignado.</span>';
        } else if (visibles === 0) {
            helpText.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>No hay personal activo asignado a este proyecto. Asigne personal al proyecto antes de crear la cuadrilla.</span>';
        } else {
            helpText.innerHTML = 'El líder debe pertenecer al mismo proyecto y será incorporado como primer miembro activo (' + visibles + ' disponibles).';
        }
    }

    proyectoSelect.addEventListener('change', filtrarLideresPorProyecto);
    filtrarLideresPorProyecto();
});
</script>
@endpush
