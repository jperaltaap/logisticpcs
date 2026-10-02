# LogisticPCS — Sistema de Gestión Logística, Control de Activos & Operaciones

<p align="center">
  <img src="storage/app/public/sistema/icono.webp" width="100" height="100" alt="LogisticPCS Icono">
</p>

<p align="center">
  <strong>Plataforma integral para la administración de inventarios PEPS, activos serializados con código QR, programación de cuadrillas en régimen 14x7, taller y servicios técnicos.</strong>
</p>

---

## 📋 Resumen del Proyecto

**LogisticPCS** es una solución web empresarial desarrollada sobre **Laravel 12** y **PHP 8.4**, diseñada para optimizar y asegurar la trazabilidad operativa en empresas contratistas, constructoras y de servicios técnicos de campo.

### Características Principales:
* **Catálogo Maestro & Activos Serializados**: Control de artículos fungibles y no fungibles, serialización de equipos con generación de etiquetas QR térmicas y placas patrimoniales.
* **Operaciones de Almacén & Kardex PEPS**: Recepción de stock por guías de ingreso, vales de salida con firma manuscrita digitalizada en pantalla (Canvas HTML5), actas de entrega en formato PDF A4 y cálculo automatizado de costos según método PEPS.
* **Control de Frentes & Cuadrillas (Roster 14x7)**: Registro de trabajadores, conformación de cuadrillas con custodia colectiva de herramientas y calendario interactivo de guardias rotativas (Guardia A / Guardia B) con validación automática de descanso legal.
* **Taller, Calibraciones & Metrología**: Control de estado operativo (Operativo, En Mantenimiento, Dañado, De Baja), inmovilización de equipos por calibración vencida y alertas de reposición por stock mínimo.
* **Seguridad y Perfiles Granulares (Spatie Permission)**: 5 roles estrictos de sistema (Administrador, Almacén, Supervisor, Técnico y Auditor).
* **Asistente de Inicialización Guiada (Onboarding)**: Restricción automática de acceso que guía al administrador a registrar los 4 pilares obligatorios (Empresa, Proyecto, Almacén y Categorías) antes de desbloquear las operaciones de la organización.
* **Zona Horaria Oficial**: Configurado bajo zona horaria **America/Lima (UTC-5)** para consistencia total en registros, logs y respaldos.

---

## 🛠️ Stack Tecnológico

* **Lenguaje**: PHP 8.4+
* **Framework Backend**: Laravel 12
* **Base de Datos**: MySQL 8.0+ / MariaDB 10.5+ (SQLite soportado para pruebas)
* **Frontend**: Blade Templates, Bootstrap 5.3, Bootstrap Icons, HTML5 Canvas
* **Generación de Reportes**: Barryvdh DomPDF & Maatwebsite Excel
* **Seguridad**: Spatie Laravel-Permission & Cifrado Bcrypt
* **Control de Calidad**: PHPUnit (133 tests automatizados) & Laravel Pint

---

## 🚀 Puesta en Marcha en Entorno Local

### Prerrequisitos
* PHP >= 8.4 (con extensiones `pdo_mysql`, `mbstring`, `openssl`, `zip`, `gd`, `fileinfo`)
* Composer >= 2.x
* Servidor MySQL / MariaDB (ej. Laragon o XAMPP)

### Pasos de Instalación:
```bash
# 1. Clonar el repositorio
git clone <URL_DEL_REPOSITORIO_GITHUB>
cd logisticpcs

# 2. Instalar dependencias PHP
composer install --optimize-autoloader

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=logisticadb
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Ejecutar migraciones y semillero de roles y usuarios base
php artisan migrate --seed

# 6. Crear enlace simbólico de almacenamiento
php artisan storage:link

# 7. Iniciar el servidor local
php artisan serve
```

---

## 👥 Cuentas de Acceso Base del Sistema

El semillero inicial crea 5 perfiles oficiales listos para producción con la contraseña por defecto: `Password123!`

| Perfil / Rol | Correo Electrónico | Alcance de Permisos |
|---|---|---|
| **ADMINISTRADOR** | `admin@logisticpcs.pe` | Control total, configuración de empresa, usuarios y auditoría. |
| **ALMACÉN** | `almacen@logisticpcs.pe` | Catálogo de artículos, ingresos, despachos, devoluciones y kardex. |
| **SUPERVISOR** | `supervisor@logisticpcs.pe` | Gestión de proyectos asignados, cuadrillas y programación de turnos. |
| **TÉCNICO** | `tecnico@logisticpcs.pe` | Consulta de catálogo maestro, actas de préstamo y firma de vales. |
| **AUDITOR** | `auditor@logisticpcs.pe` | Consulta de solo lectura, logs del sistema y exportación de reportes. |

---

## 🧪 Pruebas Automatizadas & Calidad de Código

El proyecto cuenta con una cobertura integral de pruebas de funcionalidad:

```bash
# Ejecutar todas las pruebas unitarias y de integración
php artisan test

# O ejecutar mediante phpunit directamente
vendor/bin/phpunit

# Formatear el código bajo el estándar PSR-12 / Laravel Pint
vendor/bin/pint --format agent
```

---

## 📖 Documentación Adicional

* Consulta el archivo [`DEPLOYMENT_GUIDE.md`](./DEPLOYMENT_GUIDE.md) para instrucciones detalladas paso a paso sobre el despliegue en **Hosting Compartido (cPanel / Hostinger)** y servidores **VPS (Ubuntu / Nginx)**.

---

## 📄 Licencia

Este proyecto cuenta con licencia comercial privada. Todos los derechos reservados.
