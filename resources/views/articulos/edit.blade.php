@extends('layouts.admin')

@section('title', 'Editar Artículo ' . $articulo->codigo_sku)
@section('page_title', 'Modificar Artículo: ' . $articulo->codigo_sku)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articulos.index') }}" class="text-decoration-none fw-semibold text-primary">Artículos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('articulos.show', $articulo) }}" class="text-decoration-none fw-semibold text-primary">{{ $articulo->codigo_sku }}</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="admin-card p-4">
                <form action="{{ route('articulos.update', $articulo) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-pencil-square me-2 text-primary"></i> Modificar Información del Bien
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="codigo_sku" class="form-label small fw-bold">Código SKU <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_sku" id="codigo_sku" class="form-control @error('codigo_sku') is-invalid @enderror" value="{{ old('codigo_sku', $articulo->codigo_sku) }}" required>
                            @error('codigo_sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="descripcion" class="form-label small fw-bold">Descripción del Artículo <span class="text-danger">*</span></label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $articulo->descripcion) }}" required>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="categoria_id" class="form-label small fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_id', $articulo->categoria_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_articulo" class="form-label small fw-bold">Tipo de Artículo <span class="text-danger">*</span></label>
                            <select name="tipo_articulo" id="tipo_articulo" class="form-select @error('tipo_articulo') is-invalid @enderror" required>
                                <option value="EQUIPO" {{ old('tipo_articulo', $articulo->tipo_articulo) == 'EQUIPO' ? 'selected' : '' }}>EQUIPO</option>
                                <option value="HERRAMIENTA" {{ old('tipo_articulo', $articulo->tipo_articulo) == 'HERRAMIENTA' ? 'selected' : '' }}>HERRAMIENTA</option>
                                <option value="EPP" {{ old('tipo_articulo', $articulo->tipo_articulo) == 'EPP' ? 'selected' : '' }}>EPP (Seguridad)</option>
                                <option value="MATERIAL" {{ old('tipo_articulo', $articulo->tipo_articulo) == 'MATERIAL' ? 'selected' : '' }}>MATERIAL</option>
                                <option value="CONSUMIBLE" {{ old('tipo_articulo', $articulo->tipo_articulo) == 'CONSUMIBLE' ? 'selected' : '' }}>CONSUMIBLE</option>
                            </select>
                            @error('tipo_articulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="proyecto_id" class="form-label small fw-bold">Proyecto Asignado</label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror">
                                <option value="">-- Sin Proyecto (Catálogo General) --</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_id', $articulo->proyecto_id) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} - {{ $pry->nombre }} {{ $proyectoActivo && $proyectoActivo->id == $pry->id ? '★ [ACTIVO]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="unidad_medida" class="form-label small fw-bold">Unidad de Medida <span class="text-danger">*</span></label>
                            <select name="unidad_medida" id="unidad_medida" class="form-select @error('unidad_medida') is-invalid @enderror" required>
                                @foreach(['UND', 'METRO', 'PAR', 'LITRO', 'CAJA', 'PAQUETE', 'ROLLO'] as $um)
                                    <option value="{{ $um }}" {{ old('unidad_medida', $articulo->unidad_medida) == $um ? 'selected' : '' }}>
                                        {{ $um }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unidad_medida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marca" class="form-label small fw-bold">Marca</label>
                            <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca', $articulo->marca) }}">
                            @error('marca')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="modelo" class="form-label small fw-bold">Modelo</label>
                            <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo', $articulo->modelo) }}">
                            @error('modelo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="estado" class="form-label small fw-bold">Estado en Catálogo <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', $articulo->estado) == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                                <option value="INACTIVO" {{ old('estado', $articulo->estado) == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-gear-wide-connected me-2 text-primary"></i> Control de Inventario & Serialización
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light bg-opacity-25 h-100">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="control_serie" name="control_serie" value="1" {{ old('control_serie', $articulo->control_serie) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="control_serie">Control por Número de Serie / QR Individual</label>
                                </div>
                                <p class="small text-muted mb-2">
                                    Si cambia este valor a serializado, el inventario se gestionará mediante el registro individual de <strong>Activos</strong> físicos con QR.
                                </p>
                                <div class="form-check form-switch mt-2 pt-2 border-top">
                                    <input class="form-check-input" type="checkbox" role="switch" id="es_instalable" name="es_instalable" value="1" {{ old('es_instalable', $articulo->es_instalable) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-info-emphasis" for="es_instalable">
                                        <i class="bi bi-hdd-network me-1"></i> Material Seriado Instalable en Obra (Consumible en Proyecto)
                                    </label>
                                    <div class="form-text small text-muted">
                                        Ejemplos: Switches, Routers, Patch Panels, ONTs, Antenas. Se controlan por serie al ingresar y despachar, pero se instalan permanentemente en el sitio sin retorno de almacén.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3" id="wrap_stock_minimo">
                            <label for="stock_minimo" class="form-label small fw-bold">
                                Stock Mínimo de Alerta <span class="text-danger" id="req_stock_minimo">*</span>
                            </label>
                            <input type="number" step="1" min="0" name="stock_minimo" id="stock_minimo" class="form-control @error('stock_minimo') is-invalid @enderror" value="{{ old('stock_minimo', (int) round((float) $articulo->stock_minimo)) }}" required>
                            <div id="stock_minimo_no_aplica" class="form-control bg-light text-muted small d-none align-items-center gap-1">
                                <i class="bi bi-slash-circle text-secondary"></i> No aplica (Activo Serializado)
                            </div>
                            @error('stock_minimo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text small" id="help_stock_minimo">Umbral para alertas de reabastecimiento.</div>
                        </div>

                        <div class="col-md-3" id="wrap_vida_util">
                            <label for="vida_util_meses" class="form-label small fw-bold">
                                Vida Útil Estimada (Meses) <span class="text-danger d-none" id="req_vida_util">*</span>
                            </label>
                            <input type="number" min="1" name="vida_util_meses" id="vida_util_meses" class="form-control @error('vida_util_meses') is-invalid @enderror" value="{{ old('vida_util_meses', $articulo->vida_util_meses) }}">
                            @error('vida_util_meses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small" id="help_vida_util">Obligatorio en artículos serializados (activos y consumibles).</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Fotografía o Ficha Técnica</label>
                            <div class="d-flex align-items-center gap-3 p-3 border rounded bg-light bg-opacity-25">
                                <div id="foto-preview-container" class="rounded border bg-white d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width: 100px; height: 100px; min-width: 100px;">
                                    @if($articulo->foto_referencia)
                                        <img id="foto-preview-img" src="{{ $articulo->foto_url }}" alt="{{ $articulo->descripcion }}" class="w-100 h-100" style="object-fit: contain;" onerror="this.classList.add('d-none'); document.getElementById('foto-placeholder-icon').classList.remove('d-none');">
                                        <i id="foto-placeholder-icon" class="bi bi-image text-muted opacity-50 display-6 d-none"></i>
                                    @else
                                        <img id="foto-preview-img" src="" alt="Vista previa" class="d-none w-100 h-100" style="object-fit: contain;">
                                        <i id="foto-placeholder-icon" class="bi bi-image text-muted opacity-50 display-6"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="foto_referencia" id="foto_referencia" class="form-control @error('foto_referencia') is-invalid @enderror" accept="image/*">
                                    @error('foto_referencia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small text-muted">
                                        {{ $articulo->foto_referencia ? 'Suba una nueva imagen si desea reemplazar la actual.' : 'Formatos admitidos: JPG, PNG, WEBP. Máx: 2MB.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones o Instrucciones Especiales</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $articulo->observaciones) }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('articulos.show', $articulo) }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Actualizar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('foto_referencia')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = document.getElementById('foto-preview-img');
            const icon = document.getElementById('foto-placeholder-icon');
            if (img) {
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            if (icon) icon.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const chkSerie = document.getElementById('control_serie');
        const chkInstalable = document.getElementById('es_instalable');
        const selTipo = document.getElementById('tipo_articulo');
        const inpStock = document.getElementById('stock_minimo');
        const divNoAplica = document.getElementById('stock_minimo_no_aplica');
        const reqStock = document.getElementById('req_stock_minimo');
        const helpStock = document.getElementById('help_stock_minimo');
        const inpVida = document.getElementById('vida_util_meses');
        const reqVida = document.getElementById('req_vida_util');
        let lastStockVal = inpStock?.value && parseInt(inpStock.value) > 0 ? String(parseInt(inpStock.value)) : '5';

        function syncReglasSerializacion() {
            if (!chkSerie || !chkInstalable || !selTipo || !inpStock || !inpVida) return;

            const esSeriado = chkSerie.checked;
            if (!esSeriado) {
                chkInstalable.checked = false;
                chkInstalable.disabled = true;
            } else {
                chkInstalable.disabled = false;
                if (selTipo.value === 'CONSUMIBLE') {
                    chkInstalable.checked = true;
                }
            }

            const esConsumibleSeriado = esSeriado && (chkInstalable.checked || selTipo.value === 'CONSUMIBLE');
            const esActivoSeriado = esSeriado && !esConsumibleSeriado;

            if (esActivoSeriado) {
                if (inpStock.value && parseFloat(inpStock.value) > 0) {
                    lastStockVal = inpStock.value;
                }
                inpStock.value = '0';
                inpStock.required = false;
                inpStock.classList.add('d-none');
                divNoAplica?.classList.remove('d-none');
                divNoAplica?.classList.add('d-flex');
                reqStock?.classList.add('d-none');
                if (helpStock) helpStock.textContent = 'Serializado como Activo: no considera límite mínimo de stock.';
            } else {
                if (inpStock.value === '0' || inpStock.value === '0.00' || inpStock.value === '') {
                    inpStock.value = lastStockVal;
                }
                inpStock.required = true;
                inpStock.classList.remove('d-none');
                divNoAplica?.classList.add('d-none');
                divNoAplica?.classList.remove('d-flex');
                reqStock?.classList.remove('d-none');
                if (helpStock) {
                    helpStock.textContent = esConsumibleSeriado
                        ? 'Serializado y Consumible: considera stock mínimo y vida útil.'
                        : 'Umbral para alertas de reabastecimiento.';
                }
            }

            if (esSeriado) {
                inpVida.required = true;
                reqVida?.classList.remove('d-none');
                if (!inpVida.value) inpVida.value = '36';
            } else {
                inpVida.required = false;
                reqVida?.classList.add('d-none');
            }
        }

        chkSerie?.addEventListener('change', syncReglasSerializacion);
        chkInstalable?.addEventListener('change', syncReglasSerializacion);
        selTipo?.addEventListener('change', syncReglasSerializacion);
        syncReglasSerializacion();
    });
</script>
@endpush
