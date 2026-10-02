@extends('layouts.admin')

@section('title', 'Nueva Categoría de Bienes')
@section('page_title', 'Registrar Nueva Categoría')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}" class="text-decoration-none fw-semibold text-primary">Categorías</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nueva</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="admin-card p-4">
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label small fw-semibold">Código Único <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control font-monospace @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}" placeholder="Ej: CAT-SEG" required>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Identificador corto para clasificación del catálogo.</div>
                        </div>

                        <div class="col-md-8">
                            <label for="nombre" class="form-label small fw-semibold">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej: Seguridad Industrial y Señalización" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label small fw-semibold">Descripción / Alcance</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Indique qué artículos o familias de bienes comprende esta categoría...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
