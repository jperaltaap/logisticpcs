# ESPECIFICACIÓN DEL STACK TECNOLÓGICO Y ARQUITECTURA DE SOFTWARE
## Sistema de Gestión Logística, Control de Inventarios, Activos, Proyectos y Cuadrillas

---

## 1. Núcleo Backend & Base de Datos
* **Lenguaje:** PHP 8.2+ / 8.3.
* **Framework:** Laravel 11.x / 12.x.
* **Motor de Base de Datos:** MySQL 8.0+ / MariaDB 10.4+ (Motor InnoDB, codificación `utf8mb4_unicode_ci`).
* **Arquitectura de Software:**
  * Patrón MVC (Model-View-Controller) asistido por Capa de Servicios (*Service Layer*) para desacoplar transacciones complejas (kardex, despachos y asignaciones).
  * Form Requests para validación rigurosa de entradas.
  * Transacciones de base de datos (`DB::transaction`) obligatorias en todo movimiento logístico para garantizar integridad atómica.

---

## 2. Frontend & Plantilla Base
* **Plantilla / Framework UI:** **AdminLTE v4** (basado en **Bootstrap 5**) o **Tabler UI** (Bootstrap 5 nativo).
  * *Justificación:* Ligero, sin dependencias obsoletas de jQuery para los componentes centrales, adaptable a dispositivos móviles (tablets de almacén y smartphones en campo) y con soporte de modo oscuro.
* **Estructura de Vistas:** Blade Templating Engine con componentes reutilizables (modales, tablas, botones de acción, tarjetas de KPI).
* **Estilos:** CSS3 / SASS integrado mediante **Vite**.

---

## 3. Librerías Frontend (JavaScript & UI)
* **Tablas Dinámicas y Filtros:**
  * **DataTables.net** (vía DataTables Bootstrap 5): Paginación del lado del servidor (*Server-Side Processing*) para soportar miles de registros en inventario, activos y movimientos sin saturar la memoria del navegador.
* **Selectores Avanzados & Autocompletado:**
  * **Tom Select** o **Select2** (con wrapper Bootstrap 5): Búsqueda asíncrona de artículos por SKU, personal por DNI/fotocheck y números de serie.
* **Captura de Firma Digital en Campo:**
  * **Signature Pad (`szimek/signature_pad`):** Librería JavaScript ligera en HTML5 Canvas para registrar la firma táctil del personal/líder de cuadrilla al recibir herramientas o consumibles, exportándola en Base64/PNG.
* **Lectura de Códigos de Barras y QR por Cámara:**
  * **Html5-QRCode (`mebjas/html5-qrcode`):** Permite usar la cámara de laptops, tablets o smartphones para escanear números de serie, placas internas y códigos de kits sin requerir pistola física.
* **Alertas y Confirmaciones Modales:**
  * **SweetAlert2:** Para validación de confirmación de despachos, retornos y alertas visuales de advertencia (ej. personal en día de descanso).
* **Manejo de Fechas & Cronogramas (Roster 14x7):**
  * **Flatpickr:** Selector de rangos de fechas rápido y sin dependencias pesadas.
  * **FullCalendar.js** (o vista personalizada en CSS Grid/Table): Para renderizar visualmente el calendario y roster de turnos (14 días en obra / 7 de descanso) por trabajador y cuadrilla.

---

## 4. Paquetes y Recursos Backend de Laravel

### 4.1 Exportación a PDF (Actas de Entrega, Hojas de Cargo y Fichas de Activos)
* **Paquete:** `barryvdh/laravel-dompdf` (o `spatie/laravel-pdf` si se requiere Chromium headless).
* **Características Requeridas:**
  * Generación de Guías de Despacho Interno en formato A4 con membrete.
  * Inserción automática de la imagen de la firma digital capturada en la entrega.
  * Generación de etiquetas y códigos QR para pegado en herramientas y equipos físicos mediante `simplesoftwareio/simple-qrcode`.

### 4.2 Exportación e Importación en Excel (Kardex, Saldos e Informes)
* **Paquete:** `maatwebsite/excel` (PhpSpreadsheet).
* **Características Requeridas:**
  * Exportación de Kardex por proyecto y almacén con estilos (encabezados, filtros automáticos, totales formateados).
  * Exportación con colas (`ShouldQueue`) o chunks para grandes volúmenes de movimientos sin agotar la memoria de PHP (`memory_limit`).
  * Importación masiva de artículos, inventario inicial y personal desde archivos `.xlsx`.

### 4.3 Control de Permisos y Roles
* **Paquete:** `spatie/laravel-permission`.
* **Roles Base:**
  * `Administrador`: Acceso total, configuración, usuarios, borrado de registros.
  * `Almacenero`: Creación de despachos, devoluciones, registro de activos, ingreso de compras, kardex.
  * `Supervisor Obra`: Asignación de cuadrillas, consulta de stock por proyecto, aprobación de requerimientos.
  * `Auditor / Consulta`: Vista de solo lectura y descarga de reportes ejecutivos.

---

## 5. Entorno de Desarrollo y Configuración Local
* **Servidor Local:** PHP Built-in Server (`php artisan serve`) o stack local **Laragon / XAMPP / Docker (Laravel Sail)**.
* **Compilación de Assets:** Node.js (v20+) con Vite (`npm run dev` / `npm run build`).
* **Exposición Remota Gratuita (Sin Hosting):** **Cloudflare Zero Trust (Cloudflare Tunnels)** conectando el puerto local (ej. `localhost:8000`) a un subdominio seguro con HTTPS permanente y sin abrir puertos en el router.
* **Tareas Programadas (Scheduler):** Tarea Cron del sistema apuntando a `php artisan schedule:run` para:
  * Verificación diaria de alertas de stock mínimo.
  * Detección de préstamos vencidos no devueltos.
  * Actualización de estados del Roster 14x7 según la fecha actual.