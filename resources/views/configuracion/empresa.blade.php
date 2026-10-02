@extends('layouts.admin')

@section('title', 'Configuración de Empresa')
@section('page_title', 'Datos y Configuración de la Empresa')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Configuración de Empresa</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <form action="{{ route('configuracion.empresa.update') }}" method="POST" enctype="multipart/form-data" id="form-empresa">
        @csrf
        @method('PUT')

        {{-- ═══════════════════════════ IDENTIDAD CORPORATIVA ═══════════════════════════ --}}
        <div class="row g-4">

            {{-- Columna Izquierda: Logotipo --}}
            <div class="col-lg-3">
                <div class="admin-card p-4 h-100 text-center">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3 letter-spacing-1">
                        <i class="bi bi-image me-1"></i> Logotipo Corporativo
                    </h6>

                    {{-- Preview logotipo --}}
                    <div class="mb-3" id="logo-preview-container">
                        <div id="logo-placeholder" class="d-flex align-items-center justify-content-center rounded-3 border border-dashed bg-light {{ $empresa->logotipo_path ? 'd-none' : '' }}"
                             style="height:140px;">
                            <div class="text-muted text-center">
                                <i class="bi bi-building display-4 opacity-25"></i>
                                <p class="small mt-1 mb-0">Sin logotipo</p>
                            </div>
                        </div>
                        <img src="{{ $empresa->logotipo_url ?? '' }}"
                             alt="Logotipo" id="logo-preview"
                             class="img-fluid rounded-3 border shadow-sm {{ ! $empresa->logotipo_path ? 'd-none' : '' }}"
                             style="max-height:140px; object-fit:contain;"
                             onerror="this.classList.add('d-none'); document.getElementById('logo-placeholder').classList.remove('d-none');">
                    </div>

                    <label for="logotipo" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="bi bi-upload me-1"></i> Subir logotipo
                        <input type="file" name="logotipo" id="logotipo" class="d-none" accept="image/*">
                    </label>
                    <p class="text-muted" style="font-size:0.72rem;">PNG, JPG, SVG, WEBP · Máx 2MB</p>

                    @if($empresa->logotipo_path)
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-3"
                                onclick="if(confirm('¿Eliminar el logotipo actual?')) { document.getElementById('form-delete-logo').submit(); }">
                            <i class="bi bi-trash3 me-1"></i> Eliminar logotipo
                        </button>
                    @endif

                    <hr class="my-3">

                    <h6 class="fw-bold text-uppercase small text-muted mb-3 letter-spacing-1">
                        <i class="bi bi-app-indicator me-1"></i> Ícono Principal / Favicon
                    </h6>

                    {{-- Preview Ícono --}}
                    <div class="mb-3 d-flex flex-column align-items-center justify-content-center">
                        <div class="p-2 border rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <img src="{{ $empresa->icono_url }}" alt="Ícono del Sistema" id="icono-preview"
                                 class="img-fluid rounded-2" style="max-width: 64px; max-height: 64px; object-fit: contain;">
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border mt-2" style="font-size: 0.68rem;">
                            {{ $empresa->icono_path ? 'Ícono Personalizado' : 'Ícono Predeterminado del Sistema' }}
                        </span>
                    </div>

                    <label for="icono" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="bi bi-upload me-1"></i> Cambiar Ícono
                        <input type="file" name="icono" id="icono" class="d-none" accept="image/*">
                    </label>
                    <p class="text-muted" style="font-size:0.72rem;">WEBP, PNG, ICO, SVG · Máx 2MB</p>

                    @if($empresa->icono_path)
                        <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-3"
                                onclick="if(confirm('¿Restablecer al ícono original del sistema?')) { document.getElementById('form-delete-icono').submit(); }">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar ícono base
                        </button>
                    @endif

                    <hr class="my-3">
                    <div class="text-start">
                        <p class="small fw-semibold mb-1 text-muted">Nombre del Sistema</p>
                        <input type="text" name="sistema_nombre" class="form-control form-control-sm mb-2 @error('sistema_nombre') is-invalid @enderror"
                               value="{{ old('sistema_nombre', $empresa->sistema_nombre) }}" placeholder="LogisticPCS">
                        @error('sistema_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror

                        <p class="small fw-semibold mb-1 text-muted">Subtítulo / Slogan</p>
                        <input type="text" name="sistema_subtitulo" class="form-control form-control-sm @error('sistema_subtitulo') is-invalid @enderror"
                               value="{{ old('sistema_subtitulo', $empresa->sistema_subtitulo) }}" placeholder="Sistema de Gestión Logística">
                        @error('sistema_subtitulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Datos de la empresa --}}
            <div class="col-lg-9">

                {{-- Card: Datos legales --}}
                <div class="admin-card p-4 mb-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3 letter-spacing-1">
                        <i class="bi bi-briefcase-fill me-1"></i> Datos Legales y Corporativos
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="razon_social" class="form-control @error('razon_social') is-invalid @enderror"
                                   value="{{ old('razon_social', $empresa->razon_social) }}" required>
                            @error('razon_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">RUC / NIT / Identificación</label>
                            <input type="text" name="ruc" class="form-control font-monospace @error('ruc') is-invalid @enderror"
                                   value="{{ old('ruc', $empresa->ruc) }}" placeholder="20xxxxxxxxx">
                            @error('ruc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nombre Comercial</label>
                            <input type="text" name="nombre_comercial" class="form-control @error('nombre_comercial') is-invalid @enderror"
                                   value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}"
                                   placeholder="Nombre con el que opera comercialmente (si difiere de la razón social)">
                            @error('nombre_comercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Representante Legal</label>
                            <input type="text" name="representante_legal" class="form-control @error('representante_legal') is-invalid @enderror"
                                   value="{{ old('representante_legal', $empresa->representante_legal) }}">
                            @error('representante_legal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cargo / Título</label>
                            <input type="text" name="cargo_representante" class="form-control @error('cargo_representante') is-invalid @enderror"
                                   value="{{ old('cargo_representante', $empresa->cargo_representante) }}"
                                   placeholder="Gerente General, Director, etc.">
                            @error('cargo_representante')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Contacto y Ubicación --}}
                <div class="admin-card p-4 mb-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-3 letter-spacing-1">
                        <i class="bi bi-geo-alt-fill me-1"></i> Contacto y Ubicación
                    </h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Dirección Principal</label>
                            <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror"
                                   value="{{ old('direccion', $empresa->direccion) }}"
                                   placeholder="Av. / Calle, Número, Piso, Urbanización...">
                            @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Ciudad / Distrito</label>
                            <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror"
                                   value="{{ old('ciudad', $empresa->ciudad) }}">
                            @error('ciudad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">País</label>
                            <input type="text" name="pais" class="form-control @error('pais') is-invalid @enderror"
                                   value="{{ old('pais', $empresa->pais ?? 'Perú') }}">
                            @error('pais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Moneda</label>
                            <select name="moneda" class="form-select @error('moneda') is-invalid @enderror">
                                <option value="PEN" {{ old('moneda', $empresa->moneda) == 'PEN' ? 'selected' : '' }}>PEN - Sol Peruano</option>
                                <option value="USD" {{ old('moneda', $empresa->moneda) == 'USD' ? 'selected' : '' }}>USD - Dólar</option>
                                <option value="EUR" {{ old('moneda', $empresa->moneda) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="CLP" {{ old('moneda', $empresa->moneda) == 'CLP' ? 'selected' : '' }}>CLP - Peso Chileno</option>
                                <option value="COP" {{ old('moneda', $empresa->moneda) == 'COP' ? 'selected' : '' }}>COP - Peso Colombiano</option>
                            </select>
                            @error('moneda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                       value="{{ old('telefono', $empresa->telefono) }}"
                                       placeholder="+51 xxx xxx xxx">
                            </div>
                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $empresa->email) }}">
                            </div>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sitio Web</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="url" name="sitio_web" class="form-control @error('sitio_web') is-invalid @enderror"
                                       value="{{ old('sitio_web', $empresa->sitio_web) }}"
                                       placeholder="https://...">
                            </div>
                            @error('sitio_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" form="form-empresa">
                        <i class="bi bi-floppy me-1"></i> Guardar Configuración
                    </button>
                </div>

            </div>{{-- /col-lg-9 --}}
        </div>{{-- /row --}}
    </form>

    @if($empresa->logotipo_path)
        <form id="form-delete-logo" action="{{ route('configuracion.empresa.logotipo.destroy') }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @if($empresa->icono_path)
        <form id="form-delete-icono" action="{{ route('configuracion.empresa.icono.destroy') }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif

@endsection

@push('scripts')
<script>
    // Preview de logotipo antes de subir
    document.getElementById('logotipo')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Preview de ícono antes de subir
    document.getElementById('icono')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('icono-preview');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
