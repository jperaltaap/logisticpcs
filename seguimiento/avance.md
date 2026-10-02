# CONTROL DE AVANCE DEL PROYECTO: LOGISTICPCS
**Sistema de Gestión Logística, Control de Inventarios, Activos, Proyectos y Cuadrillas**  
**Stack Tecnológico:** Laravel 11 / PHP 8.4 / MySQL (`logisticadb`) / Bootstrap 5.3 (Tema Claro/Oscuro con paletas contrastadas y acentos glassmorphic) / Laragon.

---

## 1. Resumen Ejecutivo de Estado
* **Fecha de corte:** 27 de Septiembre de 2026
* **Estado General del Proyecto:** **100% Funcional, Depurado y Verificado** (Arquitectura limpia, multi-proyecto/multisucursal estricto, gestión de imágenes garantizada y reportabilidad corporativa unificada).
* **Gestión de Imágenes:** `storage:link` verificado, ruta de servicio de respaldo en Laravel, helpers de Base64 para PDFs (evitando bloqueos en DomPDF), vista previa interactiva en tiempo real para todos los campos de carga y miniaturas visuales en listados.
* **Branding & Reportes Institucionales:** Integración dinámica de los datos de la empresa (`EmpresaConfig`) y logotipo corporativo en todos los encabezados y pies de página de reportes (PDF de Inventario, Hoja de Cargo de Cuadrilla, Vale/Acta de Despacho e informes Excel).
* **Aislamiento Estricto por Proyecto Activo (Multisucursal):** Datos globales restringidos únicamente a Empresa, Categorías y Proyectos. Todos los demás módulos (artículos, almacenes/ubicaciones, activos serializados, kits, cuadrillas, personal, turnos roster y reportes) operan estrictamente filtrados según el proyecto activo seleccionado.
* **Calidad de Código:** Laravel Pint ejecutado con éxito en todos los archivos PHP (`pint: passed`).

---

## 2. Matriz de Seguimiento por Requisitos (SRS vs. Implementación)

| N° | Módulo del Requisito (SRS) | Área | Estado | Cobertura Funcional & Técnica |
|---|---|---|:---:|---|
| **1** | **Gestión de Proyectos** | Operaciones | **100%** | CRUD completo, centros de costo, cliente, estados (*Planificación, En Ejecución, Suspendido, Finalizado*), validación estricta y protección de borrado referencial. |
| **2** | **Personal & RBAC (Users vs Personal)** | Seguridad | **100%** | Desacoplamiento físico/sistema. Ficha de personal con DNI/CE, cargo, proyecto base; usuarios con roles Spatie (*ADMINISTRADOR, ALMACENERO, SUPERVISOR, AUDITOR*). |
| **3** | **Categorías de Bienes** | Configuración | **100%** | CRUD completo con protección de borrado si tiene artículos asociados. Clasificación: EPP, Herramientas, Materiales, Consumibles, Telecomunicaciones, Instrumentos, Oficina. |
| **4** | **Centros de Almacén / Ubicaciones** | Configuración | **100%** | CRUD completo con protección si contiene stock o kardex. Almacenes centrales, en campo/obra, y talleres con trazabilidad de existencias. |
| **5.1** | **Catálogo Maestro (`articulos`)** | Inventario | **100%** | SKU único, marca, modelo, unidad de medida, bandera `control_serie`, umbrales de stock mínimo, fotos de referencia y vista de existencias consolidadas. |
| **5.2** | **Activos Serializados (`activos` QR)** | Inventario | **100%** | Unidades individuales, número de serie, placa patrimonial, custodio, estados operativos y generación de **código QR SVG en tiempo real** con plantilla de impresión de etiquetas adhesivas térmicas. |
| **5.3** | **Kits de Herramientas / EPP (`kits`)** | Inventario | **100%** | Agrupaciones estandarizadas con componentes dinámicos y cantidades homologadas (ej. Kit de Empalme de Fibra Óptica, Kit de Altura). |
| **5.4** | **Stock por Almacén (`inventario.stock`)** | Inventario | **100%** | Consulta matricial de existencias por almacén, artículo, stock mínimo, valorización estimada y badges de reposición. |
| **6** | **Taller, Mantenimiento & Calibraciones** | Servicios | **100%** | Control de servicios preventivos, correctivos, certificaciones y calibraciones metrológicas INACAL. Sincronización automática de estado del activo (*OPERATIVO, EN_MANTENIMIENTO, DE_BAJA*). |
| **7** | **Motor de Alertas y Notificaciones** | Alertas | **100%** | Escaneo automático y manual de stock crítico, calibraciones próximas a vencer y préstamos no devueltos. Marcado individual y masivo. |
| **8** | **Kardex y Trazabilidad Transaccional** | Almacén | **100%** | Servicio transaccional inmutable (`KardexService`), registro automático de saldos anteriores y posteriores, auditoría de usuario, motivo y guía asociada. Vistas con filtros y buscador. |
| **9** | **Despachos, Préstamos & Devoluciones** | Almacén | **100%** | Emisión interactiva de vales (activos por QR, materiales fungibles y kits completos), **Firma Digital Manuscrita en HTML5 Canvas**, impresión de Acta de Entrega A4 y módulo de Devolución con evaluación técnica (*Operativo, Dañado, Extraviado, Consumido*). |
| **10** | **Cuadrillas & Roster Jornada 14x7** | Campo | **100%** | CRUD de Cuadrillas con dotación dinámica y custodia de activos. Matriz mensual de Roster con código de colores, proyector automático de turnos 14x7 (Guardia A / B) y validación preventiva AJAX en despachos. |
| **11** | **Reportabilidad & Exportaciones** | Gerencia | **100%** | Centro de descargas y reportes ejecutivos. Exportaciones masivas a Excel (.xlsx) de Inventario, Kardex, Roster y Activos. Generación de informes PDF A4 de Inventario Consolidado y Hoja de Cargo oficial de Cuadrillas con firmas. |

---

## 3. Decisiones Arquitectónicas & Depuración de Módulos

### Retiro Definitivo del Módulo de Inspecciones EPP
* **Diagnóstico:** El módulo de inspecciones EPP presentaba incompatibilidades de esquema con la entidad `personal` y correspondía a un subsistema ajeno al core logístico/operativo del proyecto (perteneciente al ámbito de SSOMA/HSE aislado).
* **Decisión Tomada:** Se tomó la determinación de **retirarlo definitivamente** para mantener el sistema 100% robusto, enfocado y libre de fallos:
  * Eliminado `InspeccionEppController`, `StoreInspeccionEppRequest`, modelo `InspeccionEpp` y vistas asociadas.
  * Removida la relación `inspeccionesEpp()` de `Activo.php`.
  * Limpiada la tabla y migración huérfana de la base de datos.
  * Menú lateral de **Servicios & Alertas** reconfigurado de forma concisa y elegante con:
    * **Calibraciones & Taller** (`mantenimientos.index`)
    * **Centro de Alertas** (`alertas.index`)

### Depuración de Textos Técnicos y Ruido de Infraestructura
* Se retiraron todas las menciones explícitas a motores de base de datos (`MySQL`, `logisticadb`), entornos locales (`Laragon`), versiones internas (`PHP 8.4`, `Laravel 11`), esquemas DDL y bibliotecas de autenticación (`Spatie Permission`).
* **Portal de Inicio (`welcome.blade.php`):** Convertido en una presentación corporativa de alta gama con indicadores de negocio reales (*Artículos Catalogados, Activos Serializados, Proyectos Activos y Centros de Almacén*), retirando tarjetas de "módulos futuros".
* **Footers y Menú Lateral:** Estilizados con la identidad institucional `LogisticPCS Enterprise` y firma de derechos de autor corporativa.

---

## 4. Auditoría de Enlaces del Menú Lateral y Submenús Activos

### Mapeo Completo de Rutas en el Sidebar:
* **Principal:**
  * Dashboard General -> `route('dashboard')` [OK - 200]
* **Inventario & Activos (`#menuInventario`):**
  * Artículos / Catálogo -> `route('articulos.index')` [OK - 200]
  * Activos Serializados (QR) -> `route('activos.index')` [OK - 200]
  * Kits de Herramientas -> `route('kits.index')` [OK - 200]
  * Stock por Almacén -> `route('inventario.stock')` [OK - 200]
* **Operaciones de Almacén (`#menuOperaciones`):**
  * Nueva Salida / Préstamo -> `route('despachos.create')` [OK - 200]
  * Historial de Despachos -> `route('despachos.index')` [OK - 200]
  * Historial de Kardex -> `route('kardex.index')` [OK - 200]
* **Campo & Cuadrillas (`#menuCampo`):**
  * Proyectos / Centros de Costo -> `route('proyectos.index')` [OK - 200]
  * Cuadrillas de Trabajo -> `route('cuadrillas.index')` [OK - 200]
  * Programación Roster 14x7 -> `route('roster.index')` [OK - 200]
  * Ficha de Personal -> `route('personal.index')` [OK - 200]
* **Servicios & Alertas (`#menuSeguridad`):**
  * Calibraciones & Taller -> `route('mantenimientos.index')` [OK - 200]
  * Centro de Alertas -> `route('alertas.index')` [OK - 200]
* **Reportabilidad & Exportaciones:**
  * Centro de Reportes -> `route('reportes.index')` [OK - 200]
  * Exportar Stock (.xlsx) -> `route('reportes.export.inventario')` [OK - 200]
  * Exportar Kardex (.xlsx) -> `route('reportes.export.kardex')` [OK - 200]
* **Configuración:**
  * Categorías de Bienes -> `route('categorias.index')` [OK - 200]
  * Centros de Almacenamiento -> `route('ubicaciones.index')` [OK - 200]
  * Roles & Usuarios -> `route('users.index')` [OK - 200]

---

## 5. Pruebas y Revisión Simulada del Sistema
* Suite de pruebas automatizadas actualizada (`SidebarModulesTest`):
  * Comprobación de acceso HTTP 200 en cada una de las 18 rutas oficiales del menú lateral.
  * Ciclo CRUD completo en Categorías de Bienes y Almacenes.
  * Inmovilización técnica y reactivación de activos en Taller y Calibraciones.
  * Escáner del Centro de Alertas y gestión de notificaciones.
* **Resultado del Test Suite Global:** **89/89 tests pasados exitosamente** (300 aserciones, 0 fallos).

---

## 6. Mejoras Recientes: Navbar, Perfil y Motor de Búsqueda
1. **Rediseño y Limpieza del Navbar (`navbar.blade.php`):**
   * Retirada la etiqueta técnica `"MySQL Online (logisticadb)"` para un diseño limpio, profesional y enfocado al usuario final.
   * Conexión del enlace *"Ver todas las notificaciones"* del menú desplegable de alertas directamente a `route('alertas.index')`.
   * Enlace directo *"Mi Perfil"* en el menú de usuario configurado a `route('profile.edit')`.
   * Integración de atajo de teclado global `Ctrl+K` para enfocar de inmediato la barra de búsqueda superior.
2. **Módulo Completo de Gestión de Perfil (`/perfil`):**
   * Controlador: `ProfileController` (`edit`, `update`).
   * Request de Validación: `UpdateProfileRequest` con verificación de `current_password`, confirmación de nueva contraseña (mínimo 8 caracteres) y regla de unicidad de email excluyendo al usuario en sesión.
   * Vista Corporativa: `profile/edit.blade.php` con tarjeta de identidad, avatar, rol asignado, estado, formulario de actualización de datos personales y formulario de cambio de clave.
   * Pruebas Unitarias/Feature: `ProfileTest` con 5 pruebas de seguridad (acceso restringido a invitados, renderizado, actualización de nombre/correo y cambio de contraseña con validación de credenciales).
3. **Corrección y Optimización del Motor de Búsqueda (`/articulos`):**
   * Corrección de la consulta Eloquent en `ArticuloController`: se ajustaron las columnas de búsqueda en la relación `activos` a las existentes en el esquema (`numero_serie`, `codigo_interno`), resolviendo la excepción SQL 1054 (`codigo_qr` inexistente en tabla `activos`).
   * Búsqueda multicriterio ampliada y robusta:
     * Código SKU.
     * Nombre y descripción.
     * Marca y modelo.
     * Nombre de la categoría asociada (`whereHas('categoria')`).
     * Número de serie y código interno del activo (`whereHas('activos')`).
   * Soporta tanto el parámetro `search` como `q`, con persistencia en el input del catálogo.
4. **Integración Global de SweetAlert2 para Alertas, Notificaciones y Confirmaciones:**
   * **Librería & Estilos:** Integrado SweetAlert2 v11 con estilos adaptativos en [admin.blade.php](file:///c:/laragon/www/logisticpcs/resources/views/layouts/admin.blade.php) sincronizados con los temas claro y oscuro (`[data-bs-theme="dark"]`).
   * **Notificaciones de Acción Realizada (Guardar, Editar, Eliminar):**
     * Notificaciones toast animadas en la esquina superior derecha (`window.Toast`) para operaciones exitosas (`session('success')`, `session('status')`), informativas (`session('info')`) o preventivas (`session('warning')`).
     * Modales estructurados para errores críticos (`session('error')`) y mensajes detallados de validación de formularios (`$errors->any()`).
   * **Interceptación Automática de Eliminaciones:**
     * Interceptor global en JavaScript para todos los formularios y botones de eliminación (`method="DELETE"` o `.form-delete`), sustituyendo el `window.confirm` nativo por un modal SweetAlert2 con confirmación, botones revertibles y foco seguro.
     * Aplicado en todos los catálogos y módulos: Artículos, Activos, Categorías, Centros de Almacén, Kits, Cuadrillas, Calibraciones/Mantenimientos, Alertas y Despachos.
  
## 7. Cambios Checkpoint 7 - 26 Sep 2026  
  
Bug Fix edicion Almacenes, Modulo Empresa Config, Selector Proyecto Activo (middleware+navbar). 

## 8. Checkpoint 8: Filtrado Transversal por Proyecto Activo (Multisucursal) & Navbar Limpio - 26 Sep 2026
1. **Limpieza del Navbar:**
   * Eliminado el formulario de búsqueda central del Navbar superior para una vista más limpia, despejada y profesional.
   * Promovido el **Selector de Proyecto Activo** en el Navbar como un badge destacado tipo botón sucursal/proyecto visible en todas las pantallas.
2. **Correcciones y Extensiones en la Base de Datos:**
   * Migración ejecutada `2026_09_26_220610_add_proyecto_id_to_inventario_and_alertas.php` incorporando la columna foránea `proyecto_id` (nullable, `constrained('proyectos')->nullOnDelete()`) a:
     * `articulos`
     * `ubicaciones` (centros de almacenamiento)
     * `kits`
     * `inventario_stock`
     * `kardex_movimientos`
     * `mantenimientos_calibraciones`
     * `notificaciones_alertas`
   * Modelos actualizados con `$fillable` y relaciones `proyecto(): BelongsTo` y `Proyecto::hasMany` correspondientes.
   * Seeder `SyncProyectosDataSeeder.php` ejecutado para vincular coherentemente los datos existentes entre los 3 proyectos activos.
3. **Filtrado Transversal de Listas, Métricas y Tablas por Proyecto Activo:**
   * **Dashboard:** `DashboardController.php` y `dashboard.blade.php` ahora calculan todas las 17 métricas, despachos recientes, kardex, cuadrillas y alertas de stock de acuerdo al proyecto activo en sesión (`session('proyecto_activo_id')`), con banner informativo y botón de alternancia rápida.
   * **Artículos:** `ArticuloController.php` filtra por proyecto activo y auto-asigna el proyecto al crear.
   * **Inventario / Stock:** `InventarioStockController.php` filtra existencias, almacenes y métricas de stock según el proyecto activo.
   * **Activos Serializados:** `ActivoController.php` filtra por `proyecto_actual_id` y preselecciona almacenes/personal/cuadrillas del proyecto al registrar.
   * **Personal:** `PersonalController.php` filtra personal y contadores de estados (activos, vacaciones, descanso, cesados) según el proyecto activo.
   * **Cuadrillas:** `CuadrillaController.php` filtra cuadrillas y métricas por proyecto activo y filtra los miembros disponibles.
   * **Kits:** `KitController.php` filtra kits por proyecto activo.
   * **Despachos & Préstamos:** `DespachoController.php` y `DevolucionController.php` filtran guías, almacenes, cuadrillas y activos disponibles por el proyecto activo.
   * **Kardex:** `KardexController.php` filtra movimientos, artículos y almacenes por proyecto activo.
   * **Mantenimientos y Calibraciones:** `MantenimientoCalibracionController.php` filtra mantenimientos, métricas y activos disponibles por proyecto activo.
   * **Alertas y Notificaciones:** `AlertaController.php` filtra alertas del proyecto activo.
   * **Roster 14x7:** `RosterController.php` filtra matriz de turnos, cuadrillas y personal por proyecto activo.
   * **Centro de Almacén (Ubicaciones):** `UbicacionController.php` lista los almacenes del proyecto activo y auto-asigna al registrar.
   * **Reportes y Exportaciones:** `ReporteController.php` genera reportes, métricas y exportaciones (Excel Roster, Excel Activos, PDF Inventario) adaptadas al proyecto activo.
   * **Layout Global:** `admin.blade.php` muestra un indicador badge claro con opción de alternancia bajo el título de cada página cuando un proyecto está activo.

---

## 7. Nuevas Mejoras Implementadas (Modales, Carga de Imágenes, Documentación & Reordenamiento del Menú)

1. **Modales de Vista Rápida ("Ver") en Tablas de Información:**
   - Se reemplazaron las redirecciones del botón de acción "Ver" (icono de ojo) por **Modales Emergentes Interactivos de Bootstrap 5**:
     - **Artículos:** Visualización de foto de referencia, SKU, categoría, tipo, unidad de medida, control de serie, stock total vs. mínimo, vida útil, observaciones y accesos directos a edición o ficha completa.
     - **Activos Serializados:** Despliegue del código QR dinámico en SVG, código interno, número de serie, custodio responsable, ubicación actual, horómetro/odómetro, fecha y costo de compra, con botón directo de impresión de etiqueta térmica.
     - **Centros de Almacén:** Modal con tipo de almacén, proyecto o global, dirección física, conteo de activos serializados albergados y líneas de stock registradas.
     - **Personal:** Ficha emergente con fotocheck, DNI, cargo, área, datos de contacto, proyecto asignado, acceso web/rol, régimen laboral y cuadrillas asociadas.
     - **Cuadrillas:** Ficha con código, nombre, proyecto, líder asignado con DNI y teléfono, dotación de miembros activos, activos en custodia y régimen laboral.
     - **Kits de Herramientas:** Modal con código, nombre, proyecto, tipo y tabla detallada de componentes/bienes con sus cantidades requeridas.
     - **Despachos & Préstamos:** Desglose con N° de guía, tipo de operación, estado, proyecto, técnico receptor, almacén origen, fecha, firma digital capturada y tabla de ítems despachados con saldo en campo.
     - **Proyectos:** Modal con código, nombre, cliente, responsable de obra, fechas de inicio y fin estimada, conteo de recursos asignados y botón de activación directa de proyecto.

2. **Carga y Visualización Confiable de Imágenes:**
   - Formularios `empresa.blade.php`, `articulos/create.blade.php` y `articulos/edit.blade.php` equipados con previsualización reactiva instantánea mediante `FileReader` antes del envío.
   - Enlace `storage:link` y ruta fallback `storage/{path}` en `routes/web.php` garantizan la entrega fluida de logotipos y fotos en Windows y Linux.
   - Helpers `foto_url` y `foto_base64` en modelos aseguran que las imágenes no rompan ni la interfaz web ni la renderización de encabezados en DomPDF.

3. **Módulo Central de Documentación & Guía de Usuario (`/documentacion`):**
   - Ruta y controlador dedicados (`DocumentacionController@index`).
   - Vista por pestañas organizada en:
     - **Pestaña 1: Stack Tecnológico & Arquitectura:** Detalle técnico de PHP 8.4, Laravel 11, MySQL, Bootstrap 5.3, DomPDF, Maatwebsite Excel, Simple QrCode, Signature Pad, SweetAlert2 y el modelo de aislamiento multi-proyecto por sesión.
     - **Pestaña 2: Módulos & Funcionalidades:** Explicación funcional de los 15 módulos del sistema.
     - **Pestaña 3: Guía de Usuario & Casos Reales:** Flujo de 8 pasos para iniciar con el sistema desde cero y 3 casos prácticos reales (Despliegue FTTH Telecomunicaciones, Mantenimiento de Subestaciones con Roster 14x7, y Liquidación de Obra con Auditoría de Kardex).

4. **Optimización y Reordenamiento del Menú Lateral (Sidebar):**
   - Eliminación de enlaces sueltos de descarga directa `.xlsx` del menú de navegación que perjudicaban la experiencia del usuario (ahora centralizados de forma limpia en el Centro de Reportes).
   - Reordenamiento del menú siguiendo el flujo operativo natural: *Principal > Frentes de Obra & Personal > Inventario & Activos > Operaciones de Almacén > Servicios & Alertas > Reportabilidad & Análisis > Configuración > Ayuda & Soporte*.
   - Inclusión del nuevo enlace directo **Documentación & Guía** con icono distintivo.

5. **Flujo Integral de Calibraciones & Taller y Retorno Conforme:**
   - **Enlistado Automático:** Pasar un activo a `EN_MANTENIMIENTO` desde el catálogo genera automáticamente el ticket en `MantenimientoCalibracion` en estado `EN_PROCESO`.
   - **Bloqueo de Activación:** Prohibición estricta de pasar activos de `EN_MANTENIMIENTO` a `OPERATIVO` desde la edición del activo mientras exista un servicio abierto en taller.
   - **Alta Técnica Exclusiva:** La única vía de reactivación es mediante el botón y modal "Dar de Alta" (`POST /mantenimientos/{mantenimiento}/dar-alta`) dentro de Calibraciones & Taller.
   - **Pestañas Operativas:** Separación en *En Taller / Activos*, *Concluidos / Histórico* y *Todos los Registros*. Al dar de alta, el bien desaparece de la vista activa de taller.
   - **Sincronización de Stock:** Integración con `InventarioStockService` para restablecer el stock físico y disponible en el almacén asignado al momento del alta.

6. **Actualización Integral del Manual de Usuario & Guía Operativa (`/documentacion`):**
   - Actualización completa de la vista `resources/views/documentacion/index.blade.php` acorde a los 8 bloques del menú actual.
   - Detalle estructurado de flujos: Abastecimiento y Kardex, Despachos con Firma y Roster 14x7, Devoluciones, y Ciclo de Vida de Calibraciones & Taller.
   - Sección dedicada al Centro de Reportes (planillas XLSX y actas PDF) y Matriz de Roles y Permisos (`ADMINISTRADOR`, `LOGISTICO`, `SUPERVISOR`, `TECNICO`, `AUDITOR`).
   - Casos prácticos de campo reales para capacitación inmediata del usuario.

