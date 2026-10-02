@php
    $nextPendingStepFound = false;
@endphp

<div class="admin-card mb-4 border border-primary border-opacity-25 shadow-sm overflow-hidden" id="onboardingStepsContainer">
    <!-- Cabecera del Asistente -->
    <div class="card-header bg-primary bg-opacity-10 px-4 py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 rounded-3 bg-primary text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-rocket-takeoff-fill fs-5"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-heading fs-6">Guía de Puesta en Marcha del Sistema</h5>
                    <span class="badge {{ $initProgressPercent === 100 ? 'bg-success' : 'bg-primary' }} text-white px-2 py-0.5 rounded-pill small fw-bold">
                        {{ $initProgressPercent }}% Listo
                    </span>
                </div>
                <p class="text-muted small mb-0">
                    Siga esta secuencia guiada para inicializar el sistema con los datos maestros y operativos requeridos.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <span class="small fw-semibold text-heading">{{ $stepsCompletedCount }} de {{ $stepsTotal }}</span>
                <span class="small text-muted">pasos completados</span>
                <div class="progress mt-1" style="height: 6px; width: 140px;" role="progressbar" aria-valuenow="{{ $initProgressPercent }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: {{ $initProgressPercent }}%;"></div>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOnboardingBody" aria-expanded="true" aria-controls="collapseOnboardingBody" title="Minimizar / Expandir Asistente">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
    </div>

    <!-- Cuerpo con los Pasos de Progreso -->
    <div class="collapse show" id="collapseOnboardingBody">
        <div class="card-body p-4 bg-body">
            <!-- Barra de Progreso en Móvil -->
            <div class="d-block d-sm-none mb-3">
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span class="fw-semibold text-heading">Progreso Global:</span>
                    <span class="text-primary fw-bold">{{ $stepsCompletedCount }}/{{ $stepsTotal }} ({{ $initProgressPercent }}%)</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: {{ $initProgressPercent }}%;"></div>
                </div>
            </div>

            <div class="row g-3">
                @foreach($initializationSteps as $step)
                    @php
                        $isCompleted = $step['completed'];
                        $isNext = !$isCompleted && !$nextPendingStepFound;
                        if ($isNext) {
                            $nextPendingStepFound = true;
                        }
                    @endphp

                    <div class="col-12 col-md-6 col-xl">
                        <div class="h-100 p-3 rounded-3 border d-flex flex-column justify-content-between position-relative transition-card {{ $isCompleted ? 'bg-success bg-opacity-10 border-success-subtle' : ($isNext ? 'bg-primary bg-opacity-10 border-primary shadow-sm' : 'bg-body-tertiary bg-opacity-50 border-secondary border-opacity-10') }}">
                            <div>
                                <!-- Indicador superior del paso -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2 d-inline-flex align-items-center justify-content-center {{ $isCompleted ? 'bg-success text-white' : ($isNext ? 'bg-primary text-white shadow-sm' : 'bg-secondary bg-opacity-25 text-muted') }}" style="width: 28px; height: 28px; font-size: 0.78rem;">
                                            @if($isCompleted)
                                                <i class="bi bi-check-lg fw-bold"></i>
                                            @else
                                                {{ $step['step'] }}
                                            @endif
                                        </span>
                                        <span class="badge {{ $isCompleted ? 'bg-success-subtle text-success border border-success-subtle' : ($isNext ? 'bg-primary text-white' : 'bg-body-secondary text-muted') }}" style="font-size: 0.68rem;">
                                            @if($isCompleted)
                                                <i class="bi bi-check2-circle me-1"></i> Listo ({{ $step['count'] }})
                                            @elseif($isNext)
                                                <i class="bi bi-play-circle-fill me-1"></i> Siguiente Paso
                                            @else
                                                Pendiente
                                            @endif
                                        </span>
                                    </div>
                                    <i class="bi {{ $step['icon'] }} fs-5 {{ $isCompleted ? 'text-success' : ($isNext ? 'text-primary' : 'text-muted opacity-50') }}"></i>
                                </div>

                                <!-- Título y descripción -->
                                <h6 class="fw-bold mb-1 text-heading fs-6">{{ $step['title'] }}</h6>
                                <p class="small text-muted mb-3" style="font-size: 0.78rem; line-height: 1.35;">
                                    {{ $step['desc'] }}
                                </p>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="mt-2 pt-2 border-top border-secondary border-opacity-10">
                                @if($isCompleted)
                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                        <a href="{{ $step['route_index'] }}" class="btn btn-outline-success btn-xs py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                                            <i class="bi bi-list-check me-1"></i> Ver {{ $step['count'] }} {{ $step['unit'] }}
                                        </a>
                                        <a href="{{ $step['route_create'] }}" class="btn btn-outline-secondary btn-xs py-1 px-1 text-muted" title="Registrar otro" style="font-size: 0.72rem;">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                @elseif($isNext)
                                    <a href="{{ $step['route_create'] }}" class="btn btn-primary btn-sm w-100 py-1 fw-bold shadow-sm" style="font-size: 0.78rem;">
                                        <i class="bi bi-arrow-right-circle-fill me-1"></i> Registrar Ahora
                                    </a>
                                @else
                                    <a href="{{ $step['route_create'] }}" class="btn btn-outline-secondary btn-sm w-100 py-1 text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Configurar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($initProgressPercent === 100)
                <div class="alert alert-success d-flex align-items-center gap-2 mb-0 mt-3 py-2 px-3 small border-0 bg-success bg-opacity-25 text-success-emphasis rounded-3">
                    <i class="bi bi-stars fs-5 text-success"></i>
                    <div>
                        <strong>¡Excelente trabajo!</strong> La base de datos cuenta con todos los registros maestros y operativos fundamentales. Ya puede realizar despachos, transferencias y control de inventario normalmente.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
