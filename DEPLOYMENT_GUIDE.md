# Guía Oficial de Despliegue y Mantenimiento — LogisticPCS

Este documento detalla los procedimientos técnicos recomendados para desplegar, configurar y mantener la plataforma **LogisticPCS** en entornos de producción, tanto en **Hosting Compartido (cPanel / Hostinger)** como en **Servidores Privados Virtuales (VPS / Ubuntu / Nginx)**.

---

## 📑 Índice
1. [Prerrequisitos de Servidor](#1-prerrequisitos-de-servidor)
2. [Despliegue en Hosting Compartido (cPanel / Apache)](#2-despliegue-en-hosting-compartido-cpanel--apache)
3. [Despliegue en Servidor VPS o Dedicado (Ubuntu + Nginx)](#3-despliegue-en-servidor-vps-o-dedicado-ubuntu--nginx)
4. [Estructura de la Base de Datos y Semillero](#4-estructura-de-la-base-de-datos-y-semillero)
5. [Tareas Programadas y Mantenimiento en Segundo Plano](#5-tareas-programadas-y-mantenimiento-en-segundo-plano)
6. [Resolución de Incidencias Frecuentes (Troubleshooting)](#6-resolución-de-incidencias-frecuentes-troubleshooting)

---

## 1. Prerrequisitos de Servidor

* **PHP**: Versión 8.2 o superior (Recomendado: **PHP 8.4**).
* **Extensiones PHP Requeridas**:
  * `pdo_mysql` (Conexión a base de datos)
  * `mbstring` (Manipulación de cadenas multibyte)
  * `openssl` (Cifrado de sesiones y contraseñas)
  * `zip` (Compresión y exportaciones Excel)
  * `gd` o `imagick` (Procesamiento de imágenes y logotipos)
  * `fileinfo` (Validación de tipos MIME al subir archivos)
  * `curl` (Comunicaciones HTTP salientes)
* **Base de Datos**: MySQL 8.0+ o MariaDB 10.5+
* **Zona Horaria**: `America/Lima` (UTC-5)
* **Memoria Límite (memory_limit)**: Mínimo 256MB (Recomendado: 512MB para generación de reportes PDF masivos).

---

## 2. Despliegue en Hosting Compartido (cPanel / Apache)

En hosting compartido donde no se dispone de acceso raíz SSH, se debe seguir la siguiente secuencia:

### Paso 2.1: Creación de Base de Datos y Asignación de Privilegios
1. Ingresa a tu panel de control **cPanel**.
2. Dirígete a **Bases de Datos MySQL** (*MySQL Databases*).
3. **Crear Base de Datos**: Crea la base de datos (ej. `nombreuser_logistic`).
4. **Crear Usuario MySQL**: Crea el usuario con una contraseña robusta (ej. `nombreuser_logistic`).
5. **ASOCIAR USUARIO A LA BASE DE DATOS (Paso Crítico)**:
   * En la sección **"Añadir usuario a la base de datos"**, selecciona el usuario y la base de datos creados.
   * Haz clic en **Añadir**.
   * Marca la casilla **"TODOS LOS PRIVILEGIOS"** (*ALL PRIVILEGES*).
   * Haz clic en **Hacer Cambios** (*Make Changes*).
   *(Omitir este paso genera el error MySQL `SQLSTATE[HY000] [1044] Access denied`)*.

### Paso 2.2: Importación de la Base de Datos Inicial
1. En cPanel, ingresa a **phpMyAdmin**.
2. Selecciona la base de datos recién creada en el panel izquierdo.
3. Haz clic en la pestaña superior **Importar**.
4. Selecciona el archivo [`database/clean_deploy_database.sql`](./database/clean_deploy_database.sql) incluido en el proyecto.
5. Haz clic en **Continuar** al pie de página para cargar las 31 tablas, roles y los 5 usuarios oficiales.

### Paso 2.3: Carga de Archivos vía FTP o Administrador de Archivos
* **Estructura recomendada (Directa en public_html)**:
  * Sube el contenido completo del proyecto dentro del directorio `public_html/`.
  * El proyecto cuenta con un archivo [`.htaccess`](./.htaccess) en la raíz que redirige automáticamente todas las solicitudes hacia `/public` de forma segura, bloqueando el acceso externo a archivos del sistema como `.env`, `composer.json` y `artisan`.
* **Configuración del Archivo de Entorno (`.env`)**:
  * Utiliza el archivo [`.env.production`](./.env.production) provisto en el proyecto como plantilla.
  * En el servidor, renómbralo a **`.env`** y completa las credenciales de base de datos y la URL de tu dominio:
    ```ini
    APP_NAME="LogisticPCS"
    APP_ENV=production
    APP_KEY=base64:zZ6lI/qX5xKx3k0O2mXh9gNl4wL8sR+2wJ1yD6fB8pY=
    APP_DEBUG=false
    APP_URL=https://tudominio.com

    APP_TIMEZONE=America/Lima
    APP_LOCALE=es

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nombreuser_logistic
    DB_USERNAME=nombreuser_logistic
    DB_PASSWORD=TuPasswordSegura2026
    ```

### Paso 2.4: Permisos de Directorios
* En el Administrador de Archivos de cPanel, asegúrate de que las siguientes carpetas cuenten con permisos de escritura (`775` o `755`):
  * `storage/` (y todas sus subcarpetas `app`, `framework`, `logs`)
  * `bootstrap/cache/`

### Paso 2.5: Despacho de Archivos de Storage en Hosting Compartido
* En muchos hostings compartidos, la función PHP `symlink()` está inhabilitada por seguridad del servidor.
* **LogisticPCS** incluye de forma nativa un controlador de despacho en la ruta `/storage/{path}` que sirve automáticamente los logotipos de empresa, íconos del sistema y documentos PDF aunque no exista el enlace simbólico del sistema operativo.

---

## 3. Despliegue en Servidor VPS o Dedicado (Ubuntu + Nginx)

Para servidores con acceso SSH y control de servicios del sistema operativo:

### Paso 3.1: Configuración de Nginx
Crea el bloque de configuración para el sitio en `/etc/nginx/sites-available/logisticpcs`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com www.tudominio.com;
    root /var/www/logisticpcs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Habilita el sitio y recarga Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/logisticpcs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Paso 3.2: Despliegue y Optimización mediante Artisan
Dentro de la carpeta del proyecto en el VPS:
```bash
# Otorgar permisos al usuario del servidor web
sudo chown -R www-data:www-data /var/www/logisticpcs
sudo chmod -R 775 /var/www/logisticpcs/storage /var/www/logisticpcs/bootstrap/cache

# Enlace simbólico de storage
php artisan storage:link

# Optimizar rendimiento para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Estructura de la Base de Datos y Semillero

* **Gestor de Migraciones**: Las migraciones ordenadas cronológicamente se encuentran en `database/migrations/`.
* **Semillero Base**:
  * `database/seeders/RoleSeeder.php`: Define los 5 roles y permisos de Spatie.
  * `database/seeders/UserSeeder.php`: Genera las 5 cuentas oficiales con contraseña cifrada en Bcrypt.
* **Respaldo SQL Directo**:
  * `database/clean_deploy_database.sql` puede importarse directamente en cualquier gestor MySQL sin necesidad de ejecutar comandos de terminal.

---

## 5. Tareas Programadas y Mantenimiento en Segundo Plano

Para la ejecución automática de escaneos de stock mínimo, alertas de vencimiento de calibraciones y rotación de turnos, configura una tarea Cron en el servidor:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

En cPanel:
* Dirígete a **Trabajos de Cron** (*Cron Jobs*).
* Configura la frecuencia en **Cada minuto (`* * * * *`)**.
* En comando ingresa:
  `/usr/local/bin/php /home/usuario/public_html/artisan schedule:run >> /dev/null 2>&1`

---

## 6. Resolución de Incidencias Frecuentes (Troubleshooting)

### Error 500 (Internal Server Error)
1. **Causa**: Falta la clave de cifrado de la aplicación.
   * **Solución**: Asegúrate de que `APP_KEY` esté definida en el archivo `.env`.
2. **Causa**: Permisos insuficientes en `storage/logs/`.
   * **Solución**: Asigna permisos de escritura `775` o `755` a las carpetas `storage` y `bootstrap/cache`.

### Error SQL 1044: Access Denied to Database
* **Causa**: El usuario de MySQL existe, pero no ha sido vinculado a la base de datos con privilegios en cPanel.
* **Solución**: Revisa la sección [Paso 2.1](#paso-21-creación-de-base-de-datos-y-asignación-de-privilegios) para otorgar **ALL PRIVILEGES** al usuario sobre la base de datos.

### Error "Vite Manifest Not Found"
* **Solución**: La aplicación utiliza estilos Bootstrap 5 y Blade precompilados de forma independiente. Si se personaliza la capa frontend compilada con Vite, ejecuta `npm run build` en tu entorno local antes de subir los archivos de `public/build/`.

### Revertir o Regenerar Respaldos
* El sistema cuenta con un módulo de backups integrado en `/backups` y el comando de consola:
  ```bash
  php artisan backup:generar
  ```
  Los archivos `.sql` generados se guardan de forma segura en `storage/app/backups/`.
