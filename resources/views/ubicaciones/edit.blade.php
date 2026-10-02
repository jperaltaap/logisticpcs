@extends('layouts.admin')

@section('title', 'Editar Almacén ' . $ubicacion->codigo)
@section('page_title', 'Modificar Centro de Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ubicaciones.index') }}" class="text-decoration-none fw-semibold text-primary">Almacenes</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $ubicacion->codigo }}</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="admin-card p-4">
                <form action="{{ route('ubicaciones.update', $ubicacion) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label small fw-semibold">Código Único <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control font-monospace @error('codigo') is-invalid @enderror" value="{{ old('codigo', $ubicacion->codigo) }}" required>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="nombre" class="form-label small fw-semibold">Nombre del Almacén / Ubicación <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $ubicacion->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="proyecto_id" class="form-label small fw-semibold">Proyecto Asignado</label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror">
                                <option value="">-- Sin Proyecto (Almacén General / Global) --</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_id', $ubicacion->proyecto_id) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} - {{ $pry->nombre }} {{ $proyectoActivo && $proyectoActivo->id == $pry->id ? '★ [PROYECTO ACTIVO]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label small fw-semibold">Estado Operativo <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', $ubicacion->estado) == 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Habilitado para Despachos)</option>
                                <option value="INACTIVO" {{ old('estado', $ubicacion->estado) == 'INACTIVO' ? 'selected' : '' }}>INACTIVO (Bloqueado temporalmente)</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label small fw-semibold">Ubicación y Dirección Física</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Dirección exacta, localidad, referencia geográfica o responsable del almacén...">{{ old('descripcion', $ubicacion->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('ubicaciones.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Actualizar Almacén
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
