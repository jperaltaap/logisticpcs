<!-- Header Navbar (Glassmorphic) -->
<nav class="app-header navbar navbar-expand px-3 sticky-top">
    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <button class="nav-link btn btn-link text-body border-0 p-1 me-1 me-sm-2" id="sidebarToggle" type="button" aria-label="Alternar menú lateral" title="Alternar menú lateral">
                <i class="bi bi-list fs-4" aria-hidden="true"></i>
            </button>
        </li>

        <!-- Selector de Proyecto Activo (Visible en vista móvil y desktop) -->
        @if(isset($proyectosDisponibles) && $proyectosDisponibles->isNotEmpty())
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-1 px-2 py-1 rounded-pill border {{ isset($proyectoActivo) ? 'border-primary text-primary fw-bold' : 'border-secondary-subtle text-body' }}"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
               aria-label="Proyecto activo: {{ isset($proyectoActivo) ? $proyectoActivo->nombre : (auth()->user()?->rol === 'SUPERVISOR' ? 'Mis proyectos' : 'Todos los proyectos') }}"
               style="font-size:0.8rem; background: {{ isset($proyectoActivo) ? 'rgba(var(--admin-primary-rgb),0.12)' : 'rgba(128,128,128,0.08)' }};"
               title="Filtrar datos por proyecto activo">
                <i class="bi bi-buildings-fill text-primary" style="font-size:0.85rem;" aria-hidden="true"></i>
                <span class="d-none d-md-inline text-muted small me-1">Proyecto:</span>
                <span class="text-truncate" style="max-width: clamp(100px, 30vw, 240px); display: inline-block; vertical-align: middle;">
                    {{ isset($proyectoActivo) ? $proyectoActivo->nombre : (auth()->user()?->rol === 'SUPERVISOR' ? 'Mis proyectos' : 'Todos los proyectos') }}
                </span>
                <i class="bi bi-chevron-down ms-1" style="font-size:0.65rem;" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu shadow-lg py-0" style="min-width:260px; max-width: 320px;">
                <li class="p-2 dropdown-header-bg">
                    <div class="fw-bold small text-heading d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-diagram-3 me-1 text-primary"></i> Proyecto Activo</span>
                        @if(isset($proyectoActivo))
                            <span class="badge bg-primary text-white" style="font-size:0.65rem;">Activo</span>
                        @endif
                    </div>
                    <p class="text-muted mb-0" style="font-size:0.72rem;">Filtra datos y módulos por sucursal/obra</p>
                </li>
                <li>
                    <a class="dropdown-item py-2 {{ !isset($proyectoActivo) ? 'fw-bold text-primary active' : '' }}"
                       href="{{ request()->fullUrlWithQuery(['set_proyecto_activo' => 0]) }}">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        {{ auth()->user()?->rol === 'SUPERVISOR' ? 'Todos mis proyectos' : 'Todos los proyectos' }}
                        @if(!isset($proyectoActivo))
                            <i class="bi bi-check-lg ms-auto text-success float-end"></i>
                        @endif
                    </a>
                </li>
                <li><hr class="dropdown-divider my-0"></li>
                <div style="max-height: 280px; overflow-y: auto;">
                    @foreach($proyectosDisponibles as $proj)
                    <li>
                        <a class="dropdown-item py-2 {{ (isset($proyectoActivo) && $proyectoActivo->id === $proj->id) ? 'fw-bold text-primary active' : '' }}"
                           href="{{ request()->fullUrlWithQuery(['set_proyecto_activo' => $proj->id]) }}">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="text-truncate pe-2">
                                    <span class="badge bg-body-secondary text-body border me-1" style="font-size:0.68rem;">{{ $proj->codigo }}</span>
                                    <span class="text-truncate">{{ $proj->nombre }}</span>
                                </div>
                                @if(isset($proyectoActivo) && $proyectoActivo->id === $proj->id)
                                    <i class="bi bi-check-lg text-success flex-shrink-0"></i>
                                @endif
                            </div>
                        </a>
                    </li>
                    @endforeach
                </div>
            </ul>
        </li>
        @endif
    </ul>


    <!-- Right navbar links -->
    <ul class="navbar-nav ms-auto align-items-center gap-2">

        <!-- Quick Theme Mode Toggle (Night / Light) -->
        <li class="nav-item">
            <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" id="themeModeToggle" type="button" aria-label="Alternar modo Claro / Oscuro" title="Alternar modo Claro / Oscuro">
                <i class="bi bi-moon-stars-fill theme-icon-active" id="themeModeIcon" aria-hidden="true"></i>
            </button>
        </li>

        <!-- Theme Settings Offcanvas Trigger -->
        <li class="nav-item">
            <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#themeSettingsOffcanvas" aria-label="Personalizar tema, colores y degradados" title="Personalizar tema, colores y degradados">
                <i class="bi bi-palette-fill text-primary" aria-hidden="true"></i>
            </button>
        </li>

        <!-- Notifications Dropdown (Practical & Functional Enterprise Alert Center) -->
        <li class="nav-item dropdown">
            <a class="nav-link position-relative p-2 text-body rounded-circle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Centro de alertas operativas, {{ $conteoAlertasNoLeidas ?? 0 }} alertas pendientes" title="Alertas y Notificaciones">
                <i class="bi bi-bell-fill fs-5" aria-hidden="true"></i>
                @if(isset($conteoAlertasNoLeidas) && $conteoAlertasNoLeidas > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; padding: 0.25em 0.45em;" aria-live="polite">
                        {{ $conteoAlertasNoLeidas > 99 ? '99+' : $conteoAlertasNoLeidas }}
                        <span class="visually-hidden">Alertas no leídas</span>
                    </span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow-lg py-0 border-0" style="width: 360px; max-width: 90vw; border-radius: 12px; overflow: hidden;">
                <div class="p-3 dropdown-header-bg d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-primary"></i>
                        <span class="fw-bold small mb-0 text-heading">Alertas Operativas</span>
                    </div>
                    @if(isset($conteoAlertasNoLeidas) && $conteoAlertasNoLeidas > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill fw-bold" style="font-size: 0.72rem;">
                            {{ $conteoAlertasNoLeidas }} pendiente{{ $conteoAlertasNoLeidas > 1 ? 's' : '' }}
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold" style="font-size: 0.72rem;">
                            Al día
                        </span>
                    @endif
                </div>

                <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                    @forelse($notificacionesNavbar ?? [] as $alerta)
                        <div class="list-group-item list-group-item-action p-3 border-bottom {{ !$alerta->leida ? 'bg-primary-subtle bg-opacity-10' : '' }}">
                            <div class="d-flex align-items-start gap-2">
                                <div class="mt-1 flex-shrink-0">
                                    @if($alerta->tipo === 'STOCK_MINIMO')
                                        <span class="p-1 rounded-2 bg-warning-subtle text-warning d-inline-flex">
                                            <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                                        </span>
                                    @elseif($alerta->tipo === 'CALIBRACION_POR_VENCER')
                                        <span class="p-1 rounded-2 bg-danger-subtle text-danger d-inline-flex">
                                            <i class="bi bi-tools fs-6"></i>
                                        </span>
                                    @elseif($alerta->tipo === 'PRESTAMO_VENCIDO')
                                        <span class="p-1 rounded-2 bg-info-subtle text-info d-inline-flex">
                                            <i class="bi bi-clock-history fs-6"></i>
                                        </span>
                                    @else
                                        <span class="p-1 rounded-2 bg-primary-subtle text-primary d-inline-flex">
                                            <i class="bi bi-bell-fill fs-6"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-sm fw-bold text-heading text-truncate" title="{{ $alerta->titulo }}">
                                            {{ $alerta->titulo }}
                                        </h6>
                                        <small class="text-muted text-nowrap ms-1" style="font-size: 0.68rem;">
                                            {{ ($alerta->fecha_alerta ?? $alerta->created_at)?->diffForHumans(null, true) }}
                                        </small>
                                    </div>
                                    <p class="mb-1 text-muted small" style="font-size: 0.76rem; line-height: 1.3;">
                                        {{ Str::limit($alerta->mensaje, 75) }}
                                    </p>
                                    @if(!$alerta->leida)
                                        <form action="{{ route('alertas.marcarLeida', $alerta) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link p-0 text-xs text-primary text-decoration-none fw-semibold">
                                                <i class="bi bi-check2"></i> Marcar leída
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                            <span class="small fw-semibold text-heading">No tienes alertas pendientes</span>
                            <p class="text-xs text-muted mb-0 mt-1">El stock, calibraciones y préstamos están al día.</p>
                        </div>
                    @endforelse
                </div>

                <div class="p-2 dropdown-header-bg border-top d-flex justify-content-between align-items-center">
                    @if(isset($conteoAlertasNoLeidas) && $conteoAlertasNoLeidas > 0)
                        <form action="{{ route('alertas.marcarTodasLeidas') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm text-decoration-none text-muted p-0 small">
                                <i class="bi bi-check2-all"></i> Marcar todas
                            </button>
                        </form>
                    @else
                        <span></span>
                    @endif
                    <a href="{{ route('alertas.index') }}" class="small text-decoration-none fw-bold text-primary">
                        Centro de alertas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </li>

        <!-- User Profile Dropdown (Clean Solid Enterprise - No Glass) -->
        <li class="nav-item dropdown">
            <a class="nav-link user-profile-btn d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-label="Menú de cuenta de usuario de {{ auth()->user()->name ?? 'Administrador' }}">
                <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.85rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);" aria-hidden="true">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="d-none d-lg-block text-start lh-1">
                    <div class="fw-bold small text-truncate text-heading" style="max-width: 140px;">
                        {{ auth()->user()->name ?? 'Administrador' }}
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        {{ auth()->user()->rol ?? 'ADMINISTRADOR' }}
                    </small>
                </div>
                <i class="bi bi-chevron-down small text-muted ms-1"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg py-0" style="min-width: 250px;">
                <li class="p-3 dropdown-header-bg">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 1rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-truncate text-heading" style="font-size: 0.88rem;">{{ auth()->user()->name ?? 'Usuario' }}</div>
                            <div class="small text-muted text-truncate" style="font-size: 0.74rem;">{{ auth()->user()->email ?? 'admin@logisticpcs.test' }}</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <i class="bi bi-shield-check me-1"></i> {{ auth()->user()->rol ?? 'ADMINISTRADOR' }}
                        </span>
                    </div>
                </li>
                <li class="p-1">
                    <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 text-body" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person text-primary fs-6"></i>
                        <span class="fw-medium">Mi Perfil</span>
                    </a>
                </li>
                <li class="px-1">
                    <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 text-body" href="#" data-bs-toggle="offcanvas" data-bs-target="#themeSettingsOffcanvas">
                        <i class="bi bi-sliders text-primary fs-6"></i>
                        <span class="fw-medium">Personalizar Tema</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="p-1 mb-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-2 text-danger py-2 fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-right fs-6"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
