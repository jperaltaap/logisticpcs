<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — {{ config('app.name', 'LogisticPCS') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Favicon del Sistema -->
    <link rel="icon" type="image/webp" href="{{ \App\Models\EmpresaConfig::instancia()->icono_url }}">

    <style>
        :root {
            --lp-primary: #0284c7;
            --lp-primary-gradient: #0369a1;
            --lp-dark: #070e17;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: 
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 43, 72, 0.9) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 41, 59, 0.7) 0px, transparent 60%),
                var(--lp-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #f8fafc;
        }

        .login-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.4rem;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            position: relative;
        }

        .login-header {
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 2.2rem 2rem 1.6rem;
            text-align: center;
        }

        .brand-icon {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 0.85rem;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.4);
        }

        .form-control-glass {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            color: #ffffff !important;
            backdrop-filter: blur(8px);
        }

        .form-control-glass:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.3) !important;
            color: #ffffff !important;
        }

        .form-control-glass::placeholder {
            color: #94a3b8;
        }

        .input-group-text-glass {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            color: #94a3b8 !important;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.85rem;
            border-radius: 0.65rem;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }

        .btn-gradient-primary:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.5);
            color: #ffffff;
        }

    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            @php $empresaGlobal = \App\Models\EmpresaConfig::instancia(); @endphp
            <div class="mb-3 d-inline-block p-2 bg-white rounded-3 shadow">
                <img src="{{ $empresaGlobal->icono_url }}" alt="Logo" style="width: 48px; height: 48px; object-fit: contain;">
            </div>
            <h3 class="fw-bold mb-1 text-white">{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</h3>
            <p class="text-white-50 small mb-0">{{ $empresaGlobal->sistema_subtitulo ?: 'Sistema de Control Logístico & Activos Serializados' }}</p>
        </div>

        <div class="p-4">
            @if ($errors->any())
                <div class="alert alert-danger py-2 small mb-3 border-0 rounded-3 bg-danger bg-opacity-25 text-white">
                    <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success py-2 small mb-3 border-0 rounded-3 bg-success bg-opacity-25 text-white">
                    <i class="bi bi-check-circle-fill me-1 text-success"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold text-light">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-glass border-end-0"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" id="email" class="form-control form-control-glass border-start-0" value="{{ old('email') }}" required autofocus placeholder="ej. usuario@empresa.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold text-light">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-glass border-end-0"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="password" class="form-control form-control-glass border-start-0" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label small text-white-50">Recordar sesión en este equipo</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-gradient-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión en la Consola
                </button>

                <div class="text-center">
                    <a href="{{ url('/') }}" class="text-decoration-none small text-white-50 hover-text-white">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Portal de Proyectos
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
