@if (session('status') || session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 admin-card mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
            <span class="fw-semibold">{{ session('success') ?? session('status') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 admin-card mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-octagon-fill fs-5 text-danger me-2"></i>
            <span class="fw-semibold">{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 admin-card mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Por favor verifica los siguientes errores:</strong>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
