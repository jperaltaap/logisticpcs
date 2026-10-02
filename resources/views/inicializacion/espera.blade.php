<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema en Inicialización — {{ config('app.name', 'LogisticPCS') }}</title>

    @php $empresaGlobal = \App\Models\EmpresaConfig::instancia(); @endphp
    <link rel="icon" type="image/webp" href="{{ $empresaGlobal->icono_url }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.06) 0px, transparent 50%),
                #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }

        .waiting-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.1);
            width: 100%;
            max-width: 620px;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <div class="waiting-card p-4 p-md-5 text-center">
        {{-- Logo / Icono del Sistema --}}
        <div class="mb-4 d-inline-block p-3 rounded-4 bg-light border shadow-sm">
            <img src="{{ $empresaGlobal->icono_url }}" alt="Sistema" style="width: 64px; height: 64px; object-fit: contain;">
        </div>

        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-1 mb-3 rounded-pill fw-semibold">
            <i class="bi bi-gear-wide-connected me-1"></i> Configuración Inicial en Proceso
        </span>

        <h3 class="fw-bold mb-2 text-dark">{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</h3>
        <p class="text-muted mb-4 small">
            El sistema se encuentra en proceso de configuración inicial. El <strong>Administrador</strong> está registrando los datos básicos necesarios para la operación:
        </p>

        {{-- Lista de Requerimientos Básicos --}}
        <div class="text-start mb-4">
            <div class="p-3 rounded-3 bg-light border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Progreso de Puesta en Marcha</span>
                    <span class="badge bg-primary px-2 py-1 small">{{ $status['percentage'] }}% Listo</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $status['percentage'] }}%;"></div>
                </div>

                <div class="row g-2">
                    @foreach($status['steps'] as $step)
                        <div class="col-6">
                            <div class="p-2 rounded border bg-white d-flex align-items-center justify-content-between">
                                <span class="small text-truncate me-1">
                                    <i class="bi {{ $step['icon'] }} me-1 text-muted"></i> {{ $step['title'] }}
                                </span>
                                @if($step['completed'])
                                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                                @else
                                    <i class="bi bi-hourglass-split text-warning fs-6"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="alert alert-secondary border-0 small text-muted text-start mb-4 py-2">
            <i class="bi bi-info-circle-fill me-1 text-primary"></i>
            Su usuario con rol <strong>{{ auth()->user()->rol }}</strong> no tiene permisos para configurar la empresa ni la estructura base. Una vez que el Administrador complete los 4 pasos, se habilitará automáticamente su Dashboard operativo.
        </div>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i> Comprobar de Nuevo
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger px-3 fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
