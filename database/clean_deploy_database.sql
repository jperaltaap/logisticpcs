-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: logisticadb
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activos`
--

DROP TABLE IF EXISTS `activos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `articulo_id` bigint unsigned NOT NULL,
  `ingreso_id` bigint unsigned DEFAULT NULL,
  `codigo_interno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_serie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion_actual_id` bigint unsigned NOT NULL,
  `responsable_personal_id` bigint unsigned DEFAULT NULL,
  `cuadrilla_actual_id` bigint unsigned DEFAULT NULL,
  `proyecto_actual_id` bigint unsigned DEFAULT NULL,
  `estado_operativo` enum('OPERATIVO','EN_MANTENIMIENTO','DANADO','DE_BAJA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPERATIVO',
  `condicion_prestamo` enum('DISPONIBLE','PRESTADO_CAMPO','EN_TRANSFERENCIA','EXTRAVIADO','INSTALADO_PROYECTO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DISPONIBLE',
  `fecha_ingreso` date NOT NULL,
  `fecha_ultima_asignacion` datetime DEFAULT NULL,
  `fecha_ultimo_retorno` datetime DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activos_codigo_interno_unique` (`codigo_interno`),
  KEY `activos_articulo_id_foreign` (`articulo_id`),
  KEY `activos_ubicacion_actual_id_foreign` (`ubicacion_actual_id`),
  KEY `activos_responsable_personal_id_foreign` (`responsable_personal_id`),
  KEY `activos_cuadrilla_actual_id_foreign` (`cuadrilla_actual_id`),
  KEY `activos_proyecto_actual_id_foreign` (`proyecto_actual_id`),
  KEY `activos_numero_serie_index` (`numero_serie`),
  KEY `activos_ingreso_id_foreign` (`ingreso_id`),
  CONSTRAINT `activos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `activos_cuadrilla_actual_id_foreign` FOREIGN KEY (`cuadrilla_actual_id`) REFERENCES `cuadrillas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activos_proyecto_actual_id_foreign` FOREIGN KEY (`proyecto_actual_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activos_responsable_personal_id_foreign` FOREIGN KEY (`responsable_personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activos_ubicacion_actual_id_foreign` FOREIGN KEY (`ubicacion_actual_id`) REFERENCES `ubicaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activos`
--

LOCK TABLES `activos` WRITE;
/*!40000 ALTER TABLE `activos` DISABLE KEYS */;
/*!40000 ALTER TABLE `activos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articulos`
--

DROP TABLE IF EXISTS `articulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articulos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `codigo_sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UND',
  `tipo_articulo` enum('EPP','HERRAMIENTA','EQUIPO','MATERIAL','CONSUMIBLE','OFICINA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `control_serie` tinyint(1) NOT NULL DEFAULT '0',
  `es_instalable` tinyint(1) NOT NULL DEFAULT '0',
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vida_util_meses` int unsigned DEFAULT NULL,
  `foto_referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('ACTIVO','MANTENIMIENTO','BAJA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articulos_codigo_sku_unique` (`codigo_sku`),
  KEY `articulos_categoria_id_foreign` (`categoria_id`),
  KEY `articulos_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `articulos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `articulos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articulos`
--

LOCK TABLES `articulos` WRITE;
/*!40000 ALTER TABLE `articulos` DISABLE KEYS */;
/*!40000 ALTER TABLE `articulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `componentes_kit`
--

DROP TABLE IF EXISTS `componentes_kit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `componentes_kit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kit_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `componentes_kit_kit_id_foreign` (`kit_id`),
  KEY `componentes_kit_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `componentes_kit_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `componentes_kit_kit_id_foreign` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `componentes_kit`
--

LOCK TABLES `componentes_kit` WRITE;
/*!40000 ALTER TABLE `componentes_kit` DISABLE KEYS */;
/*!40000 ALTER TABLE `componentes_kit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuadrilla_personal`
--

DROP TABLE IF EXISTS `cuadrilla_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuadrilla_personal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cuadrilla_id` bigint unsigned NOT NULL,
  `personal_id` bigint unsigned NOT NULL,
  `rol_en_cuadrilla` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TECNICO',
  `fecha_incorporacion` date NOT NULL,
  `fecha_retiro` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cuadrilla_personal_cuadrilla_id_foreign` (`cuadrilla_id`),
  KEY `cuadrilla_personal_personal_id_foreign` (`personal_id`),
  CONSTRAINT `cuadrilla_personal_cuadrilla_id_foreign` FOREIGN KEY (`cuadrilla_id`) REFERENCES `cuadrillas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cuadrilla_personal_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuadrilla_personal`
--

LOCK TABLES `cuadrilla_personal` WRITE;
/*!40000 ALTER TABLE `cuadrilla_personal` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuadrilla_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuadrillas`
--

DROP TABLE IF EXISTS `cuadrillas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuadrillas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo_cuadrilla` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proyecto_id` bigint unsigned NOT NULL,
  `lider_personal_id` bigint unsigned NOT NULL,
  `regimen_laboral` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '14x7',
  `estado` enum('ACTIVA','DISUELTA','EN_DESCANSO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVA',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuadrillas_codigo_cuadrilla_unique` (`codigo_cuadrilla`),
  KEY `cuadrillas_proyecto_id_foreign` (`proyecto_id`),
  KEY `cuadrillas_lider_personal_id_foreign` (`lider_personal_id`),
  CONSTRAINT `cuadrillas_lider_personal_id_foreign` FOREIGN KEY (`lider_personal_id`) REFERENCES `personal` (`id`),
  CONSTRAINT `cuadrillas_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuadrillas`
--

LOCK TABLES `cuadrillas` WRITE;
/*!40000 ALTER TABLE `cuadrillas` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuadrillas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `despacho_detalles`
--

DROP TABLE IF EXISTS `despacho_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despacho_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `despacho_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `activo_id` bigint unsigned DEFAULT NULL,
  `kit_id` bigint unsigned DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `estado_item` enum('ENTREGADO','DEVUELTO_OPERATIVO','DEVUELTO_DANADO','EXTRAVIADO','CONSUMIDO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENTREGADO',
  `fecha_devolucion` datetime DEFAULT NULL,
  `usuario_recepcion_retorno_id` bigint unsigned DEFAULT NULL,
  `observacion_retorno` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `despacho_detalles_despacho_id_foreign` (`despacho_id`),
  KEY `despacho_detalles_articulo_id_foreign` (`articulo_id`),
  KEY `despacho_detalles_activo_id_foreign` (`activo_id`),
  KEY `despacho_detalles_kit_id_foreign` (`kit_id`),
  KEY `despacho_detalles_usuario_recepcion_retorno_id_foreign` (`usuario_recepcion_retorno_id`),
  CONSTRAINT `despacho_detalles_activo_id_foreign` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`),
  CONSTRAINT `despacho_detalles_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`),
  CONSTRAINT `despacho_detalles_despacho_id_foreign` FOREIGN KEY (`despacho_id`) REFERENCES `despachos_prestamos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `despacho_detalles_kit_id_foreign` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `despacho_detalles_usuario_recepcion_retorno_id_foreign` FOREIGN KEY (`usuario_recepcion_retorno_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despacho_detalles`
--

LOCK TABLES `despacho_detalles` WRITE;
/*!40000 ALTER TABLE `despacho_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `despacho_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `despachos_prestamos`
--

DROP TABLE IF EXISTS `despachos_prestamos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despachos_prestamos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_guia` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_movimiento` enum('SALIDA_PRESTAMO_CAMPO','DEVOLUCION_CAMPO','CONSUMO_DIRECTO','TRANSFERENCIA_UBICACION','AJUSTE_INVENTARIO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `proyecto_id` bigint unsigned NOT NULL,
  `personal_id` bigint unsigned DEFAULT NULL,
  `cuadrilla_id` bigint unsigned DEFAULT NULL,
  `ubicacion_origen_id` bigint unsigned NOT NULL,
  `ubicacion_destino_id` bigint unsigned DEFAULT NULL,
  `usuario_registro_id` bigint unsigned NOT NULL,
  `fecha_despacho` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_compromiso_retorno` date DEFAULT NULL,
  `estado` enum('PENDIENTE_ENTREGA','ENTREGADO_EN_CAMPO','PARCIALMENTE_DEVUELTO','DEVUELTO_TOTAL','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENTREGADO_EN_CAMPO',
  `firma_digital_base64` longtext COLLATE utf8mb4_unicode_ci,
  `foto_acta_respaldo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `despachos_prestamos_numero_guia_unique` (`numero_guia`),
  KEY `despachos_prestamos_proyecto_id_foreign` (`proyecto_id`),
  KEY `despachos_prestamos_personal_id_foreign` (`personal_id`),
  KEY `despachos_prestamos_cuadrilla_id_foreign` (`cuadrilla_id`),
  KEY `despachos_prestamos_ubicacion_origen_id_foreign` (`ubicacion_origen_id`),
  KEY `despachos_prestamos_ubicacion_destino_id_foreign` (`ubicacion_destino_id`),
  KEY `despachos_prestamos_usuario_registro_id_foreign` (`usuario_registro_id`),
  CONSTRAINT `despachos_prestamos_cuadrilla_id_foreign` FOREIGN KEY (`cuadrilla_id`) REFERENCES `cuadrillas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `despachos_prestamos_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL,
  CONSTRAINT `despachos_prestamos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`),
  CONSTRAINT `despachos_prestamos_ubicacion_destino_id_foreign` FOREIGN KEY (`ubicacion_destino_id`) REFERENCES `ubicaciones` (`id`),
  CONSTRAINT `despachos_prestamos_ubicacion_origen_id_foreign` FOREIGN KEY (`ubicacion_origen_id`) REFERENCES `ubicaciones` (`id`),
  CONSTRAINT `despachos_prestamos_usuario_registro_id_foreign` FOREIGN KEY (`usuario_registro_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despachos_prestamos`
--

LOCK TABLES `despachos_prestamos` WRITE;
/*!40000 ALTER TABLE `despachos_prestamos` DISABLE KEYS */;
/*!40000 ALTER TABLE `despachos_prestamos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa_config`
--

DROP TABLE IF EXISTS `empresa_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_config` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Perú',
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representante_legal` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_representante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logotipo_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN',
  `zona_horaria` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'America/Lima',
  `sistema_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LogisticPCS',
  `sistema_subtitulo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa_config`
--

LOCK TABLES `empresa_config` WRITE;
/*!40000 ALTER TABLE `empresa_config` DISABLE KEYS */;
INSERT INTO `empresa_config` VALUES (1,'Pendiente de Configuración',NULL,NULL,NULL,NULL,'Perú',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'PEN (S/)','America/Lima','LogisticPCS',NULL,'2026-10-02 14:07:10','2026-10-02 14:07:10');
/*!40000 ALTER TABLE `empresa_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingreso_detalles`
--

DROP TABLE IF EXISTS `ingreso_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingreso_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ingreso_id` bigint unsigned NOT NULL,
  `articulo_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `costo_unitario` decimal(12,2) DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ingreso_detalles_ingreso_id_foreign` (`ingreso_id`),
  KEY `ingreso_detalles_articulo_id_foreign` (`articulo_id`),
  CONSTRAINT `ingreso_detalles_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`),
  CONSTRAINT `ingreso_detalles_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingreso_detalles`
--

LOCK TABLES `ingreso_detalles` WRITE;
/*!40000 ALTER TABLE `ingreso_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingreso_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos`
--

DROP TABLE IF EXISTS `ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingresos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo_ingreso` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_ingreso` enum('COMPRA_NUEVA','AJUSTE_SOBRANTE','TRANSFERENCIA_INGRESO','DONACION_TRASPASO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMPRA_NUEVA',
  `ubicacion_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `proveedor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_comprobante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ingresos_codigo_ingreso_unique` (`codigo_ingreso`),
  KEY `ingresos_ubicacion_id_foreign` (`ubicacion_id`),
  KEY `ingresos_proyecto_id_foreign` (`proyecto_id`),
  KEY `ingresos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `ingresos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ingresos_ubicacion_id_foreign` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`),
  CONSTRAINT `ingresos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingresos`
--

LOCK TABLES `ingresos` WRITE;
/*!40000 ALTER TABLE `ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_stock`
--

DROP TABLE IF EXISTS `inventario_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_stock` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `articulo_id` bigint unsigned NOT NULL,
  `ubicacion_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `cantidad_actual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_art_ubicacion` (`articulo_id`,`ubicacion_id`),
  KEY `inventario_stock_ubicacion_id_foreign` (`ubicacion_id`),
  KEY `inventario_stock_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `inventario_stock_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `inventario_stock_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventario_stock_ubicacion_id_foreign` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_stock`
--

LOCK TABLES `inventario_stock` WRITE;
/*!40000 ALTER TABLE `inventario_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventario_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kardex_movimientos`
--

DROP TABLE IF EXISTS `kardex_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kardex_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `articulo_id` bigint unsigned NOT NULL,
  `ubicacion_id` bigint unsigned NOT NULL,
  `despacho_id` bigint unsigned DEFAULT NULL,
  `ingreso_id` bigint unsigned DEFAULT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `tipo_movimiento` enum('INGRESO_COMPRA','SALIDA_CONSUMO','SALIDA_PRESTAMO','RETORNO_PRESTAMO','TRANSFERENCIA_INGRESO','TRANSFERENCIA_SALIDA','AJUSTE_SOBRANTE','AJUSTE_FALTANTE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_anterior` decimal(12,2) NOT NULL,
  `stock_posterior` decimal(12,2) NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kardex_movimientos_articulo_id_foreign` (`articulo_id`),
  KEY `kardex_movimientos_ubicacion_id_foreign` (`ubicacion_id`),
  KEY `kardex_movimientos_despacho_id_foreign` (`despacho_id`),
  KEY `kardex_movimientos_usuario_id_foreign` (`usuario_id`),
  KEY `kardex_movimientos_proyecto_id_foreign` (`proyecto_id`),
  KEY `kardex_movimientos_ingreso_id_foreign` (`ingreso_id`),
  CONSTRAINT `kardex_movimientos_articulo_id_foreign` FOREIGN KEY (`articulo_id`) REFERENCES `articulos` (`id`),
  CONSTRAINT `kardex_movimientos_despacho_id_foreign` FOREIGN KEY (`despacho_id`) REFERENCES `despachos_prestamos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kardex_movimientos_ingreso_id_foreign` FOREIGN KEY (`ingreso_id`) REFERENCES `ingresos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kardex_movimientos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kardex_movimientos_ubicacion_id_foreign` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`),
  CONSTRAINT `kardex_movimientos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kardex_movimientos`
--

LOCK TABLES `kardex_movimientos` WRITE;
/*!40000 ALTER TABLE `kardex_movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `kardex_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kits`
--

DROP TABLE IF EXISTS `kits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `ubicacion_id` bigint unsigned DEFAULT NULL,
  `codigo_kit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_kit` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo_kit` enum('KIT_HERRAMIENTAS','KIT_EPP','KIT_EMPALME','KIT_MATERIALES') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KIT_HERRAMIENTAS',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kits_codigo_kit_unique` (`codigo_kit`),
  KEY `kits_proyecto_id_foreign` (`proyecto_id`),
  KEY `kits_ubicacion_id_foreign` (`ubicacion_id`),
  CONSTRAINT `kits_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kits_ubicacion_id_foreign` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kits`
--

LOCK TABLES `kits` WRITE;
/*!40000 ALTER TABLE `kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mantenimientos_calibraciones`
--

DROP TABLE IF EXISTS `mantenimientos_calibraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimientos_calibraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activo_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('PREVENTIVO','CORRECTIVO','CALIBRACION_LAB','CERTIFICACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedor_taller` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_salida` date DEFAULT NULL,
  `proxima_calibracion_sugerida` date DEFAULT NULL,
  `costo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `certificado_calibracion_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion_falla_o_trabajo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resultado` enum('CONFORME_OPERATIVO','NO_CONFORME_BAJA','EN_PROCESO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EN_PROCESO',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mantenimientos_calibraciones_activo_id_foreign` (`activo_id`),
  KEY `mantenimientos_calibraciones_user_id_foreign` (`user_id`),
  KEY `mantenimientos_calibraciones_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `mantenimientos_calibraciones_activo_id_foreign` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`),
  CONSTRAINT `mantenimientos_calibraciones_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mantenimientos_calibraciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mantenimientos_calibraciones`
--

LOCK TABLES `mantenimientos_calibraciones` WRITE;
/*!40000 ALTER TABLE `mantenimientos_calibraciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `mantenimientos_calibraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_26_000002_create_ubicaciones_table',1),(5,'2026_09_26_000003_create_proyectos_table',1),(6,'2026_09_26_000004_create_personal_table',1),(7,'2026_09_26_000005_create_cuadrillas_table',1),(8,'2026_09_26_000006_create_cuadrilla_personal_table',1),(9,'2026_09_26_000007_create_roster_turnos_table',1),(10,'2026_09_26_000008_create_categorias_table',1),(11,'2026_09_26_000009_create_articulos_table',1),(12,'2026_09_26_000010_create_activos_table',1),(13,'2026_09_26_000011_create_kits_table',1),(14,'2026_09_26_000012_create_componentes_kit_table',1),(15,'2026_09_26_000013_create_inventario_stock_table',1),(16,'2026_09_26_000014_create_despachos_prestamos_table',1),(17,'2026_09_26_000015_create_despacho_detalles_table',1),(18,'2026_09_26_000016_create_kardex_movimientos_table',1),(19,'2026_09_26_000017_create_mantenimientos_calibraciones_table',1),(20,'2026_09_26_000018_create_inspecciones_epp_table',1),(21,'2026_09_26_000019_create_notificaciones_alertas_table',1),(22,'2026_09_26_174151_create_permission_tables',1),(23,'2026_09_26_215329_create_empresa_config_table',2),(25,'2026_09_26_220610_add_proyecto_id_to_inventario_and_alertas',3),(26,'2026_09_27_234858_update_roles_and_add_proyecto_id_to_users_table',4),(27,'2026_09_29_043604_add_centro_acopio_to_ubicaciones_table',5),(28,'2026_09_29_193222_add_es_instalable_to_articulos_and_update_condicion_prestamo',6),(29,'2026_09_29_193228_create_ingresos_and_detalles_tables',6),(30,'2026_09_29_213000_create_proyecto_asignaciones_tables',7),(31,'2026_09_29_223000_create_system_logs_table',8),(32,'2026_09_29_232000_add_ubicacion_id_to_kits_table',9),(33,'2026_10_01_231354_add_tecnico_to_users_rol_enum',10),(34,'2026_10_02_091636_add_icono_path_to_empresa_config_table',11);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(5,'App\\Models\\User',4),(4,'App\\Models\\User',5);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones_alertas`
--

DROP TABLE IF EXISTS `notificaciones_alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones_alertas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('STOCK_MINIMO','MATERIAL_SIN_MOVIMIENTO','PRESTAMO_VENCIDO','CALIBRACION_POR_VENCER') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia_id` bigint unsigned DEFAULT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_alerta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notificaciones_alertas_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `notificaciones_alertas_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones_alertas`
--

LOCK TABLES `notificaciones_alertas` WRITE;
/*!40000 ALTER TABLE `notificaciones_alertas` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones_alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(2,'dashboard.metricas_completas','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(3,'articulos.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(4,'articulos.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(5,'activos.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(6,'activos.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(7,'kits.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(8,'kits.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(9,'ingresos.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(10,'ingresos.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(11,'despachos.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(12,'despachos.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(13,'despachos.firmar_receptor','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(14,'stock.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(15,'kardex.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(16,'alertas.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(17,'mantenimientos.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(18,'mantenimientos.registrar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(19,'mantenimientos.alta_tecnica','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(20,'reportes.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(21,'reportes.exportar_excel','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(22,'reportes.exportar_pdf','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(23,'empresa.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(24,'proyectos.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(25,'almacenes.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(26,'categorias.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(27,'usuarios.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(28,'auditoria.ver','web','2026-10-02 04:17:08','2026-10-02 04:17:08'),(29,'backups.gestionar','web','2026-10-02 04:17:08','2026-10-02 04:17:08');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal`
--

DROP TABLE IF EXISTS `personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo_trabajador` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_fotocheck` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dni` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `estado` enum('ACTIVO','VACACIONES','DESCANSO_MEDICO','CESADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_dni_unique` (`dni`),
  UNIQUE KEY `personal_codigo_trabajador_unique` (`codigo_trabajador`),
  UNIQUE KEY `personal_codigo_fotocheck_unique` (`codigo_fotocheck`),
  KEY `personal_proyecto_id_foreign` (`proyecto_id`),
  KEY `personal_user_id_foreign` (`user_id`),
  CONSTRAINT `personal_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `personal_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal`
--

LOCK TABLES `personal` WRITE;
/*!40000 ALTER TABLE `personal` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_proyecto`
--

DROP TABLE IF EXISTS `personal_proyecto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_proyecto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `personal_id` bigint unsigned NOT NULL,
  `es_responsable` tinyint(1) NOT NULL DEFAULT '0',
  `rol_en_proyecto` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_proyecto_proyecto_id_personal_id_unique` (`proyecto_id`,`personal_id`),
  KEY `personal_proyecto_personal_id_foreign` (`personal_id`),
  CONSTRAINT `personal_proyecto_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personal_proyecto_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_proyecto`
--

LOCK TABLES `personal_proyecto` WRITE;
/*!40000 ALTER TABLE `personal_proyecto` DISABLE KEYS */;
INSERT INTO `personal_proyecto` VALUES (1,1,1,1,NULL,'2026-09-30 02:28:56','2026-09-30 02:28:56'),(2,1,2,0,NULL,'2026-09-30 02:28:56','2026-09-30 02:28:56'),(4,2,4,1,NULL,'2026-09-30 02:28:56','2026-09-30 02:28:56'),(5,3,5,0,NULL,'2026-09-30 02:28:56','2026-09-30 02:28:56'),(6,3,3,1,NULL,'2026-09-30 02:28:56','2026-09-30 07:09:01'),(7,4,3,1,NULL,'2026-09-30 02:28:56','2026-09-30 07:09:01');
/*!40000 ALTER TABLE `personal_proyecto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proyecto_user`
--

DROP TABLE IF EXISTS `proyecto_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyecto_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyecto_user_proyecto_id_user_id_unique` (`proyecto_id`,`user_id`),
  KEY `proyecto_user_user_id_foreign` (`user_id`),
  CONSTRAINT `proyecto_user_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyecto_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proyecto_user`
--

LOCK TABLES `proyecto_user` WRITE;
/*!40000 ALTER TABLE `proyecto_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `proyecto_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyectos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion_direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `responsable_personal_id` bigint unsigned DEFAULT NULL,
  `estado` enum('ACTIVO','SUSPENDIDO','FINALIZADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyectos_codigo_unique` (`codigo`),
  KEY `fk_proyectos_responsable` (`responsable_personal_id`),
  CONSTRAINT `fk_proyectos_responsable` FOREIGN KEY (`responsable_personal_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proyectos`
--

LOCK TABLES `proyectos` WRITE;
/*!40000 ALTER TABLE `proyectos` DISABLE KEYS */;
/*!40000 ALTER TABLE `proyectos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(25,2),(26,2),(1,3),(3,3),(5,3),(7,3),(11,3),(12,3),(14,3),(16,3),(17,3),(18,3),(20,3),(22,3),(1,4),(2,4),(3,4),(5,4),(7,4),(9,4),(11,4),(14,4),(15,4),(16,4),(17,4),(20,4),(21,4),(22,4),(28,4),(1,5),(3,5),(5,5),(7,5),(11,5),(13,5),(14,5);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMINISTRADOR','web','2026-09-26 22:47:58','2026-09-26 22:47:58'),(2,'LOGISTICO','web','2026-09-26 22:47:58','2026-09-26 22:47:58'),(3,'SUPERVISOR','web','2026-09-26 22:47:58','2026-09-26 22:47:58'),(4,'AUDITOR','web','2026-09-26 22:47:58','2026-09-26 22:47:58'),(5,'TECNICO','web','2026-10-02 04:14:27','2026-10-02 04:14:27');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roster_turnos`
--

DROP TABLE IF EXISTS `roster_turnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roster_turnos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personal_id` bigint unsigned NOT NULL,
  `proyecto_id` bigint unsigned NOT NULL,
  `grupo_guardia` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GUARDIA A',
  `fecha` date NOT NULL,
  `condicion_laboral` enum('TRABAJO_CAMPO','DESCANSO_CAMPAMENTO','BAJADA_DESCANSO','PERMISO','LICENCIA_MEDICA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TRABAJO_CAMPO',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_personal_fecha` (`personal_id`,`fecha`),
  KEY `roster_turnos_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `roster_turnos_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roster_turnos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roster_turnos`
--

LOCK TABLES `roster_turnos` WRITE;
/*!40000 ALTER TABLE `roster_turnos` DISABLE KEYS */;
/*!40000 ALTER TABLE `roster_turnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('nTuskfRPSmmkQNqVJ1chYS1GAlzliDBILcLMxg2w',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ6SHFOdHlic2ZLOXVodWxheHZpTEpCdEVSWEJrR2pBVDFoRDRVSk1xIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9naXN0aWNwY3MudGVzdCIsInJvdXRlIjoiaG9tZSJ9fQ==',1790952425);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_logs_user_id_foreign` (`user_id`),
  KEY `system_logs_proyecto_id_foreign` (`proyecto_id`),
  KEY `system_logs_created_at_index` (`created_at`),
  KEY `system_logs_accion_index` (`accion`),
  KEY `system_logs_modulo_index` (`modulo`),
  CONSTRAINT `system_logs_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `system_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ubicaciones`
--

DROP TABLE IF EXISTS `ubicaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ubicaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('ALMACEN_CENTRAL','ALMACEN_OBRA','CENTRO_ACOPIO','EN_CAMPO','TALLER_REPARACION','BAJA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ALMACEN_CENTRAL',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ubicaciones_codigo_unique` (`codigo`),
  KEY `ubicaciones_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `ubicaciones_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ubicaciones`
--

LOCK TABLES `ubicaciones` WRITE;
/*!40000 ALTER TABLE `ubicaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `ubicaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('ADMINISTRADOR','LOGISTICO','SUPERVISOR','AUDITOR','TECNICO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LOGISTICO',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `proyecto_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `users_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador del Sistema','admin@logisticpcs.pe','$2y$12$yGRagLxS3daX4MSek5phGuNMeFri/MEOSfuxpROKrA3redqLUmgd.','ADMINISTRADOR','ACTIVO',NULL,NULL,'2026-10-02 13:44:09','2026-10-02 13:44:09',NULL),(2,'Encargado de Logística y Almacén','almacen@logisticpcs.pe','$2y$12$8Rrbz2ZFb/wJ8aUjnmjs7eZ4b3qih1ZVyUwPIw7/o5c1FEfLkisDu','LOGISTICO','ACTIVO',NULL,NULL,'2026-10-02 13:44:09','2026-10-02 13:44:09',NULL),(3,'Supervisor de Operaciones','supervisor@logisticpcs.pe','$2y$12$SbuRfTR5Ma6DtzUnoz4.9.bdXJ00WwkbyjyjCUlnDXEbThE5vG4Li','SUPERVISOR','ACTIVO',NULL,NULL,'2026-10-02 13:44:09','2026-10-02 13:44:09',NULL),(4,'Técnico de Campo','tecnico@logisticpcs.pe','$2y$12$tai.IfJOgnG62M2x.a3b0u8cERQDwsOmVEoWXqxA6DMFa0ShP8umG','TECNICO','ACTIVO',NULL,NULL,'2026-10-02 13:44:09','2026-10-02 13:44:09',NULL),(5,'Auditor de Control Interno','auditor@logisticpcs.pe','$2y$12$rbQqcRsrRzvQKDExy6dzn.R1Qi.pJ1LIpyG2AAyjujwty4vULYfli','AUDITOR','ACTIVO',NULL,NULL,'2026-10-02 13:44:09','2026-10-02 13:44:09',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'logisticadb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-02  9:57:34
