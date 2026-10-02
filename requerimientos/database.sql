-- =============================================================================
-- ESQUEMA DE BASE DE DATOS DEFINITIVO: LOGÍSTICA, ACTIVOS, PROYECTOS Y CUADRILLAS
-- Motor: InnoDB | Cotejamiento: utf8mb4_unicode_ci | Compatible: MySQL 8.0+ / MariaDB 10.4+
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notificaciones_alertas;
DROP TABLE IF EXISTS inspecciones_epp;
DROP TABLE IF EXISTS mantenimientos_calibraciones;
DROP TABLE IF EXISTS kardex_movimientos;
DROP TABLE IF EXISTS inventario_stock;
DROP TABLE IF EXISTS despacho_detalles;
DROP TABLE IF EXISTS despachos_prestamos;
DROP TABLE IF EXISTS componentes_kit;
DROP TABLE IF EXISTS kits;
DROP TABLE IF EXISTS activos;
DROP TABLE IF EXISTS articulos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS roster_turnos;
DROP TABLE IF EXISTS cuadrilla_personal;
DROP TABLE IF EXISTS cuadrillas;
DROP TABLE IF EXISTS personal;
DROP TABLE IF EXISTS proyectos;
DROP TABLE IF EXISTS ubicaciones;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- 1. TABLA: users (Usuarios del Sistema, Autenticación y Roles Laravel)
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('ADMINISTRADOR', 'ALMACENERO', 'SUPERVISOR', 'AUDITOR') NOT NULL DEFAULT 'ALMACENERO',
    estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. TABLA: ubicaciones (Almacén central, en obra, km 39, taller, baja, etc.)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE ubicaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,          -- Ej: ALM-CEN, ALM-OBRA-KM39, TALLER-REP, CAMPO[cite: 1]
    nombre VARCHAR(120) NOT NULL,                -- Ej: Almacén Central, Almacén Km 39, En Campo[cite: 1]
    descripcion TEXT NULL,
    tipo ENUM('ALMACEN_CENTRAL', 'ALMACEN_OBRA', 'EN_CAMPO', 'TALLER_REPARACION', 'BAJA') NOT NULL DEFAULT 'ALMACEN_CENTRAL'[cite: 1],
    estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. TABLA: proyectos (Centros de costo / Frentes de trabajo)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE proyectos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,          -- Código del proyecto[cite: 1]
    nombre VARCHAR(150) NOT NULL,                -- Nombre del proyecto[cite: 1]
    cliente VARCHAR(150) NOT NULL,               -- Cliente / Entidad mandante
    ubicacion_direccion VARCHAR(255) NOT NULL,   -- Ubicación o dirección física del proyecto[cite: 1]
    fecha_inicio DATE NOT NULL,                  -- Fecha de inicio[cite: 1]
    fecha_fin_estimada DATE NULL,                -- Posible fin de proyecto[cite: 1]
    responsable_personal_id BIGINT UNSIGNED NULL,-- Responsable asignado del proyecto (FK a personal)[cite: 1]
    estado ENUM('ACTIVO', 'SUSPENDIDO', 'FINALIZADO') NOT NULL DEFAULT 'ACTIVO'[cite: 1],
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. TABLA: personal (Ficha técnica laboral y operativa del trabajador)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE personal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_trabajador VARCHAR(30) NULL UNIQUE,   -- Código de trabajador[cite: 1]
    codigo_fotocheck VARCHAR(30) NULL UNIQUE,     -- Número de fotocheck[cite: 1]
    dni VARCHAR(15) NOT NULL UNIQUE,             -- Documento de identidad
    nombres VARCHAR(100) NOT NULL,               -- Nombres[cite: 1]
    apellidos VARCHAR(100) NOT NULL,             -- Apellidos[cite: 1]
    cargo VARCHAR(100) NOT NULL,                 -- Cargo[cite: 1]
    area VARCHAR(100) NOT NULL,                  -- Área de trabajo[cite: 1]
    telefono VARCHAR(30) NULL,                   -- Teléfono[cite: 1]
    correo VARCHAR(150) NULL,                    -- Correo[cite: 1]
    proyecto_id BIGINT UNSIGNED NULL,            -- Proyecto asignado base[cite: 1]
    user_id BIGINT UNSIGNED NULL,                -- Relación con users (solo si tiene acceso al sistema)[cite: 1]
    estado ENUM('ACTIVO', 'VACACIONES', 'DESCANSO_MEDICO', 'CESADO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_personal_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE SET NULL,
    CONSTRAINT fk_personal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignación circular del responsable de proyecto
ALTER TABLE proyectos
    ADD CONSTRAINT fk_proyectos_responsable FOREIGN KEY (responsable_personal_id) REFERENCES personal(id) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 5. TABLA: cuadrillas (Grupos operativos de trabajo en frentes de obra)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE cuadrillas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_cuadrilla VARCHAR(30) NOT NULL UNIQUE,       -- Ej: CD-CIVIL-01, CD-FIBRA-02
    nombre VARCHAR(120) NOT NULL,                       -- Nombre descriptivo de la cuadrilla[cite: 1]
    proyecto_id BIGINT UNSIGNED NOT NULL,               -- Proyecto donde labora[cite: 1]
    lider_personal_id BIGINT UNSIGNED NOT NULL,         -- Líder responsable de la cuadrilla
    regimen_laboral VARCHAR(30) NOT NULL DEFAULT '14x7',-- Régimen de jornada laboral (14*7)[cite: 1]
    estado ENUM('ACTIVA', 'DISUELTA', 'EN_DESCANSO') NOT NULL DEFAULT 'ACTIVA',
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_cuadrilla_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    CONSTRAINT fk_cuadrilla_lider FOREIGN KEY (lider_personal_id) REFERENCES personal(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. TABLA: cuadrilla_personal (Histórico y composición de miembros)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE cuadrilla_personal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cuadrilla_id BIGINT UNSIGNED NOT NULL,
    personal_id BIGINT UNSIGNED NOT NULL,
    rol_en_cuadrilla VARCHAR(80) NOT NULL DEFAULT 'TECNICO', -- LIDER, EMPALMADOR, TECNICO, AYUDANTE
    fecha_incorporacion DATE NOT NULL,
    fecha_retiro DATE NULL,                              -- Null si continúa en la cuadrilla[cite: 1]
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_cp_cuadrilla FOREIGN KEY (cuadrilla_id) REFERENCES cuadrillas(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_personal FOREIGN KEY (personal_id) REFERENCES personal(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. TABLA: roster_turnos (Cronograma y Roster 14x7 de Actividades/Descansos)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE roster_turnos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    personal_id BIGINT UNSIGNED NOT NULL,
    proyecto_id BIGINT UNSIGNED NOT NULL,
    grupo_guardia VARCHAR(30) NOT NULL DEFAULT 'GUARDIA A', -- Guardia A, Guardia B
    fecha DATE NOT NULL,
    condicion_laboral ENUM('TRABAJO_CAMPO', 'DESCANSO_CAMPAMENTO', 'BAJADA_DESCANSO', 'PERMISO', 'LICENCIA_MEDICA') NOT NULL DEFAULT 'TRABAJO_CAMPO'[cite: 1],
    observaciones VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_personal_fecha (personal_id, fecha),
    CONSTRAINT fk_roster_personal FOREIGN KEY (personal_id) REFERENCES personal(id) ON DELETE CASCADE,
    CONSTRAINT fk_roster_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. TABLA: categorias (EPP, herramientas, materiales, consumibles, oficina)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,         -- Ej: CAT-EPP, CAT-HERR, CAT-MAT, CAT-CONS, CAT-OFIC[cite: 1]
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. TABLA: articulos (Catálogo Maestro de Artículos, Materiales e Insumos)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE articulos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id BIGINT UNSIGNED NOT NULL,
    codigo_sku VARCHAR(50) NOT NULL UNIQUE,     -- Código interno / SKU / Barra[cite: 1]
    descripcion VARCHAR(200) NOT NULL,          -- Nombre o descripción del artículo[cite: 1]
    marca VARCHAR(100) NULL,                    -- Marca[cite: 1]
    modelo VARCHAR(100) NULL,                   -- Modelo[cite: 1]
    unidad_medida VARCHAR(20) NOT NULL DEFAULT 'UND', -- Unidad de medida: UND, MTR, KG, CJ, GLN, JGO[cite: 1]
    tipo_articulo ENUM('EPP', 'HERRAMIENTA', 'EQUIPO', 'MATERIAL', 'CONSUMIBLE', 'OFICINA') NOT NULL[cite: 1],
    control_serie BOOLEAN NOT NULL DEFAULT FALSE,     -- Si requiere control por número de serie[cite: 1]
    stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- Stock mínimo para alertas[cite: 1]
    vida_util_meses INT UNSIGNED NULL,          -- Vida útil si requiere[cite: 1]
    foto_referencia VARCHAR(255) NULL,          -- Foto de referencia[cite: 1]
    estado ENUM('ACTIVO', 'MANTENIMIENTO', 'BAJA') NOT NULL DEFAULT 'ACTIVO'[cite: 1],
    observaciones TEXT NULL,                    -- Observaciones adicionales[cite: 1]
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_articulos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. TABLA: activos (Activos Fijos / Serializados de la Empresa)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE activos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    articulo_id BIGINT UNSIGNED NOT NULL,
    codigo_interno VARCHAR(50) NOT NULL UNIQUE,      -- Placa o QR interno patrimonial
    numero_serie VARCHAR(100) NULL INDEX,            -- Número de serie de fábrica[cite: 1]
    ubicacion_actual_id BIGINT UNSIGNED NOT NULL,    -- Ubicación física actual[cite: 1]
    responsable_personal_id BIGINT UNSIGNED NULL,    -- Personal al que se asignó[cite: 1]
    cuadrilla_actual_id BIGINT UNSIGNED NULL,        -- O cuadrilla asignada[cite: 1]
    proyecto_actual_id BIGINT UNSIGNED NULL,         -- Proyecto donde opera
    estado_operativo ENUM('OPERATIVO', 'EN_MANTENIMIENTO', 'DANADO', 'DE_BAJA') NOT NULL DEFAULT 'OPERATIVO'[cite: 1],
    condicion_prestamo ENUM('DISPONIBLE', 'PRESTADO_CAMPO', 'EN_TRANSFERENCIA', 'EXTRAVIADO') NOT NULL DEFAULT 'DISPONIBLE',
    fecha_ingreso DATE NOT NULL,                     -- Fecha de ingreso a la empresa[cite: 1]
    fecha_ultima_asignacion DATETIME NULL,           -- Fecha de última asignación[cite: 1]
    fecha_ultimo_retorno DATETIME NULL,              -- Fecha de último retorno a almacén[cite: 1]
    observaciones TEXT NULL,                         -- Observaciones del control de activos[cite: 1]
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_activos_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON UPDATE CASCADE,
    CONSTRAINT fk_activos_ubicacion FOREIGN KEY (ubicacion_actual_id) REFERENCES ubicaciones(id),
    CONSTRAINT fk_activos_personal FOREIGN KEY (responsable_personal_id) REFERENCES personal(id) ON DELETE SET NULL,
    CONSTRAINT fk_activos_cuadrilla FOREIGN KEY (cuadrilla_actual_id) REFERENCES cuadrillas(id) ON DELETE SET NULL,
    CONSTRAINT fk_activos_proyecto FOREIGN KEY (proyecto_actual_id) REFERENCES proyectos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. TABLA: kits (Agrupamientos de herramientas, EPP o materiales)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE kits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_kit VARCHAR(30) NOT NULL UNIQUE,          -- Código del kit[cite: 1]
    nombre_kit VARCHAR(150) NOT NULL,                -- Nombre del agrupamiento[cite: 1]
    descripcion TEXT NULL,
    tipo_kit ENUM('KIT_HERRAMIENTAS', 'KIT_EPP', 'KIT_EMPALME', 'KIT_MATERIALES') NOT NULL DEFAULT 'KIT_HERRAMIENTAS'[cite: 1],
    estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. TABLA: componentes_kit (Detalle de artículos que conforman cada agrupamiento)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE componentes_kit (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kit_id BIGINT UNSIGNED NOT NULL,
    articulo_id BIGINT UNSIGNED NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_ckit_kit FOREIGN KEY (kit_id) REFERENCES kits(id) ON DELETE CASCADE,
    CONSTRAINT fk_ckit_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 13. TABLA: inventario_stock (Stock físico de consumibles/materiales por almacén)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE inventario_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    articulo_id BIGINT UNSIGNED NOT NULL,
    ubicacion_id BIGINT UNSIGNED NOT NULL,
    cantidad_actual DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_art_ubicacion (articulo_id, ubicacion_id),
    CONSTRAINT fk_inv_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON UPDATE CASCADE,
    CONSTRAINT fk_inv_ubicacion FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 14. TABLA: despachos_prestamos (Cabecera de salidas, préstamos y consumos)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE despachos_prestamos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_guia VARCHAR(30) NOT NULL UNIQUE,         -- Ej: GUIA-2026-0001
    tipo_movimiento ENUM('SALIDA_PRESTAMO_CAMPO', 'DEVOLUCION_CAMPO', 'CONSUMO_DIRECTO', 'TRANSFERENCIA_UBICACION', 'AJUSTE_INVENTARIO') NOT NULL[cite: 1],
    proyecto_id BIGINT UNSIGNED NOT NULL,            -- Imputado al proyecto[cite: 1]
    personal_id BIGINT UNSIGNED NULL,                -- Asignación a persona específica[cite: 1]
    cuadrilla_id BIGINT UNSIGNED NULL,               -- O asignación a cuadrilla completa[cite: 1]
    ubicacion_origen_id BIGINT UNSIGNED NOT NULL,    -- Almacén de salida[cite: 1]
    ubicacion_destino_id BIGINT UNSIGNED NULL,       -- Ubicación de destino[cite: 1]
    usuario_registro_id BIGINT UNSIGNED NOT NULL,    -- Usuario que emite la orden en el sistema
    fecha_despacho DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_compromiso_retorno DATE NULL,              -- Para herramientas y activos prestados
    estado ENUM('PENDIENTE_ENTREGA', 'ENTREGADO_EN_CAMPO', 'PARCIALMENTE_DEVUELTO', 'DEVUELTO_TOTAL', 'ANULADO') NOT NULL DEFAULT 'ENTREGADO_EN_CAMPO',
    firma_digital_base64 LONGTEXT NULL,              -- Firma táctil capturada en campo
    foto_acta_respaldo VARCHAR(255) NULL,            -- Respaldo fotográfico opcional
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_desp_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    CONSTRAINT fk_desp_personal FOREIGN KEY (personal_id) REFERENCES personal(id) ON DELETE SET NULL,
    CONSTRAINT fk_desp_cuadrilla FOREIGN KEY (cuadrilla_id) REFERENCES cuadrillas(id) ON DELETE SET NULL,
    CONSTRAINT fk_desp_origen FOREIGN KEY (ubicacion_origen_id) REFERENCES ubicaciones(id),
    CONSTRAINT fk_desp_destino FOREIGN KEY (ubicacion_destino_id) REFERENCES ubicaciones(id),
    CONSTRAINT fk_desp_usuario FOREIGN KEY (usuario_registro_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 15. TABLA: despacho_detalles (Líneas de la guía: activos, consumibles o kits)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE despacho_detalles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    despacho_id BIGINT UNSIGNED NOT NULL,
    articulo_id BIGINT UNSIGNED NOT NULL,
    activo_id BIGINT UNSIGNED NULL,                  -- Obligatorio si maneja serie[cite: 1]
    kit_id BIGINT UNSIGNED NULL,                     -- Referencia si salió como parte de un kit[cite: 1]
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    estado_item ENUM('ENTREGADO', 'DEVUELTO_OPERATIVO', 'DEVUELTO_DANADO', 'EXTRAVIADO', 'CONSUMIDO') NOT NULL DEFAULT 'ENTREGADO',
    fecha_devolucion DATETIME NULL,
    usuario_recepcion_retorno_id BIGINT UNSIGNED NULL,
    observacion_retorno TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_det_despacho FOREIGN KEY (despacho_id) REFERENCES despachos_prestamos(id) ON DELETE CASCADE,
    CONSTRAINT fk_det_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id),
    CONSTRAINT fk_det_activo FOREIGN KEY (activo_id) REFERENCES activos(id),
    CONSTRAINT fk_det_kit FOREIGN KEY (kit_id) REFERENCES kits(id) ON DELETE SET NULL,
    CONSTRAINT fk_det_user_retorno FOREIGN KEY (usuario_recepcion_retorno_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 16. TABLA: kardex_movimientos (Control estricto de entradas, salidas y saldos)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE kardex_movimientos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    articulo_id BIGINT UNSIGNED NOT NULL,
    ubicacion_id BIGINT UNSIGNED NOT NULL,
    despacho_id BIGINT UNSIGNED NULL,
    tipo_movimiento ENUM('INGRESO_COMPRA', 'SALIDA_CONSUMO', 'SALIDA_PRESTAMO', 'RETORNO_PRESTAMO', 'TRANSFERENCIA_INGRESO', 'TRANSFERENCIA_SALIDA', 'AJUSTE_SOBRANTE', 'AJUSTE_FALTANTE') NOT NULL[cite: 1],
    cantidad DECIMAL(10,2) NOT NULL,
    stock_anterior DECIMAL(12,2) NOT NULL,
    stock_posterior DECIMAL(12,2) NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    fecha_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_kardex_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id),
    CONSTRAINT fk_kardex_ubicacion FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id),
    CONSTRAINT fk_kardex_despacho FOREIGN KEY (despacho_id) REFERENCES despachos_prestamos(id) ON DELETE SET NULL,
    CONSTRAINT fk_kardex_usuario FOREIGN KEY (usuario_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 17. TABLA: mantenimientos_calibraciones (Control de estados y reparaciones)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE mantenimientos_calibraciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activo_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('PREVENTIVO', 'CORRECTIVO', 'CALIBRACION_LAB', 'CERTIFICACION') NOT NULL,
    proveedor_taller VARCHAR(150) NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_salida DATE NULL,
    proxima_calibracion_sugerida DATE NULL,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    certificado_calibracion_pdf VARCHAR(255) NULL,
    descripcion_falla_o_trabajo TEXT NOT NULL,
    resultado ENUM('CONFORME_OPERATIVO', 'NO_CONFORME_BAJA', 'EN_PROCESO') NOT NULL DEFAULT 'EN_PROCESO',
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_mant_activo FOREIGN KEY (activo_id) REFERENCES activos(id),
    CONSTRAINT fk_mant_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 18. TABLA: inspecciones_epp (Inspecciones periódicas de equipos de protección)
-- -----------------------------------------------------------------------------
CREATE TABLE inspecciones_epp (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activo_id BIGINT UNSIGNED NULL,
    articulo_id BIGINT UNSIGNED NOT NULL,
    personal_id BIGINT UNSIGNED NOT NULL,
    inspector_id BIGINT UNSIGNED NOT NULL,
    fecha_inspeccion DATE NOT NULL,
    resultado ENUM('CONFORME_APTO', 'OBSERVADO', 'DETERIORADO_DESTRUIR') NOT NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_iepp_activo FOREIGN KEY (activo_id) REFERENCES activos(id),
    CONSTRAINT fk_iepp_articulo FOREIGN KEY (articulo_id) REFERENCES articulos(id),
    CONSTRAINT fk_iepp_personal FOREIGN KEY (personal_id) REFERENCES personal(id),
    CONSTRAINT fk_iepp_inspector FOREIGN KEY (inspector_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 19. TABLA: notificaciones_alertas (Disparador de compras, stock y vencimientos)[cite: 1]
-- -----------------------------------------------------------------------------
CREATE TABLE notificaciones_alertas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('STOCK_MINIMO', 'MATERIAL_SIN_MOVIMIENTO', 'PRESTAMO_VENCIDO', 'CALIBRACION_POR_VENCER') NOT NULL[cite: 1],
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    referencia_id BIGINT UNSIGNED NULL,
    leida BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_alerta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;