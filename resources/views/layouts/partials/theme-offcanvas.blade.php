<!-- Theme Customizer Offcanvas (Clean Solid Enterprise) -->
<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="themeSettingsOffcanvas" aria-labelledby="themeSettingsLabel" style="width: 360px;">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25 p-3">
        <h5 class="offcanvas-title fw-bold fs-6 d-flex align-items-center gap-2" id="themeSettingsLabel">
            <i class="bi bi-palette-fill text-primary"></i>
            <span>Personalizar Apariencia & Temas</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4">
        <!-- Modo de Color (Light / Dark) -->
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">
                <i class="bi bi-circle-half me-1"></i> Modo Visual (Contraste Calibrado)
            </label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="themeModeRadio" id="themeLight" value="light" autocomplete="off">
                    <label class="btn btn-outline-secondary w-100 p-3 text-center d-flex flex-column align-items-center gap-2 rounded-3" for="themeLight">
                        <i class="bi bi-sun-fill text-warning fs-3"></i>
                        <span class="small fw-bold">Modo Claro</span>
                    </label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="themeModeRadio" id="themeDark" value="dark" autocomplete="off">
                    <label class="btn btn-outline-secondary w-100 p-3 text-center d-flex flex-column align-items-center gap-2 rounded-3" for="themeDark">
                        <i class="bi bi-moon-stars-fill text-info fs-3"></i>
                        <span class="small fw-bold">Modo Noche</span>
                    </label>
                </div>
            </div>
            <div class="form-text small mt-2">
                Optimizado con tipografía de alto contraste (fondo Slate cristalino en claro y Dark Mesh en noche).
            </div>
        </div>

        <hr class="my-4 border-secondary border-opacity-25">

        <!-- Paleta de Color de Acento con Degradados -->
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">
                <i class="bi bi-droplet-half me-1"></i> Paleta & Degradados de Acento
            </label>
            <p class="small text-muted mb-3">Aplica degradados en botones primarios, badges, resalte de navegación e iconos:</p>
            
            <div class="d-flex flex-wrap gap-3 pt-1 justify-content-between" id="accentColorPalette">
                <!-- Azul Corporativo -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn active" data-color="#0284c7" style="background: linear-gradient(135deg, #0284c7, #0369a1);" title="Azul Océano"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Océano</div>
                </div>
                <!-- Verde Esmeralda -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn" data-color="#059669" style="background: linear-gradient(135deg, #059669, #047857);" title="Verde Esmeralda"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Esmeralda</div>
                </div>
                <!-- Índigo Tech -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn" data-color="#4f46e5" style="background: linear-gradient(135deg, #4f46e5, #3730a3);" title="Índigo Tech"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Índigo</div>
                </div>
                <!-- Ámbar Industrial -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn" data-color="#d97706" style="background: linear-gradient(135deg, #d97706, #b45309);" title="Ámbar Industrial"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Ámbar</div>
                </div>
                <!-- Púrpura Real -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn" data-color="#7c3aed" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);" title="Púrpura Real"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Púrpura</div>
                </div>
                <!-- Carmesí -->
                <div class="text-center">
                    <button type="button" class="color-picker-btn" data-color="#e11d48" style="background: linear-gradient(135deg, #e11d48, #be123c);" title="Carmesí Operativo"></button>
                    <div class="small text-muted mt-1" style="font-size: 0.7rem;">Carmesí</div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-secondary border-opacity-25">

        <!-- Acabado Visual Enterprise -->
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">
                <i class="bi bi-shield-check me-1"></i> Diseño UI / UX Calibrado
            </label>
            <div class="p-3 rounded-3 border border-secondary border-opacity-25 bg-body-tertiary">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small fw-semibold text-body">Superficies Sólidas & Contraste AAA</span>
                    <span class="badge bg-success-subtle text-success fw-bold">Optimizado</span>
                </div>
                <small class="text-muted d-block">
                    Superficies limpias, menús 100% opacos sin distorsión visual y tipografía nítida para máxima productividad.
                </small>
            </div>
        </div>

        <!-- Botón Restablecer -->
        <div class="d-grid pt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm py-2" id="resetThemeBtn">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer Configuración Inicial
            </button>
        </div>
    </div>
</div>
