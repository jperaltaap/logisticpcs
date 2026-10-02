# ESPECIFICACIÓN DE REQUISITOS DEL SISTEMA (SRS)
## Sistema de Gestión Logística, Control de Inventarios, Activos, Proyectos y Cuadrillas

---

## 1. Módulo de Gestión de Proyectos
* **Objetivo:** Administrar los centros de costo y frentes de trabajo para la asignación y control de recursos[cite: 1].
* **Atributos Requeridos:** Código único de proyecto, nombre descriptivo, cliente, dirección/ubicación geográfica, fecha de inicio programada, fecha estimada de finalización, responsable principal (FK a personal) y estado operativo (*Activo, Suspendido, Concluido*)[cite: 1].
* **Reglas de Negocio:**
  * Toda asignación de consumibles, materiales, herramientas o cuadrillas debe estar imputada a un proyecto activo[cite: 1].
  * La plataforma debe permitir el filtrado de almacenes, movimientos y saldos de inventario de forma segregada por cada proyecto[cite: 1].

---

## 2. Módulo de Personal y Control de Acceso (Users vs. Personal)
* **Objetivo:** Desacoplar la identidad laboral física de los usuarios con acceso a la plataforma web[cite: 1].
* **Entidad `personal`:**
  * **Datos:** Código de trabajador / Fotocheck (único), DNI/CE, nombres, apellidos, cargo, área de trabajo, teléfono de contacto, correo electrónico corporativo/personal, proyecto base asignado y estado laboral (*Activo, Vacaciones, Descanso Médico, Cesado*)[cite: 1].
* **Entidad `users`:**
  * **Datos:** Nombre de usuario, email de acceso, contraseña cifrada, rol (*Administrador, Almacenero, Supervisor de Obra, Auditor*) y estado de cuenta (*Activo, Inactivo*).
* **Reglas de Negocio:**
  * Todo usuario del sistema debe vincularse opcionalmente a un registro de personal, pero no todo personal en obra tiene credenciales de acceso[cite: 1].
  * Las asignaciones físicas y actas de entrega siempre se emiten a nombre del registro de `personal`[cite: 1].

---

## 3. Módulo de Categorización y Clasificación de Artículos
* **Objetivo:** Agrupar y parametrizar los bienes según su comportamiento contable y logístico[cite: 1].
* **Categorías Principales:** EPP, Herramientas Manuales/Eléctricas, Materiales de Construcción/Instalación, Consumibles de Taller/Obra, Equipos de Telecomunicaciones, Instrumentos de Medición, Artículos de Oficina[cite: 1].
* **Reglas de Negocio:**
  * Cada categoría define la naturaleza del bien: fungible (se consume y desaparece del inventario) o no fungible (retornable con control de serie o depreciación).

---

## 4. Módulo de Ubicaciones y Centros de Almacenamiento
* **Objetivo:** Trazar físicamente la posición de artículos, activos fijos y materiales[cite: 1].
* **Tipos de Ubicaciones:** Almacén Central, Almacén de Obra, Almacén Satélite (ej. Km 39), En Campo / Frente de Trabajo, Taller de Reparación, En Calibración/Mantenimiento, Zona de Baja/Chatarra[cite: 1].
* **Reglas de Negocio:**
  * Todo activo serializado o ítem en stock debe pertenecer exactamente a una ubicación física en tiempo real[cite: 1].
  * Las transferencias entre almacenes generan un movimiento de origen y destino con confirmación de recepción.

---

## 5. Módulo Maestro de Artículos, Kits y Control de Activos Serializados

### 5.1 Catálogo Maestro (`articulos`)
* **Campos:** Código SKU / Código de barras, descripción o nombre comercial, marca, modelo, unidad de medida (UND, MTR, KG, GLN, CJ), categoría (FK), bandera de control por número de serie (booleano), stock mínimo de alerta, vida útil estimada (en meses), fotografía de referencia, estado general y observaciones técnicas[cite: 1].

### 5.2 Control de Activos Serializados (`activos`)
* **Campos:** Código interno/patrimonial (código QR/placa única), número de serie de fábrica, ubicación física actual, responsable actual (FK a personal o cuadrilla), proyecto actual, estado operativo (*Operativo, En Mantenimiento, Dañado, De Baja*), fecha de ingreso a la empresa, fecha de última asignación, fecha de último retorno al almacén y bitácora de observaciones[cite: 1].
* **Reglas de Negocio:**
  * Si un artículo tiene marcada la bandera `control_serie = TRUE`, cada unidad física recibida genera un registro independiente en la tabla de activos[cite: 1].

### 5.3 Kits y Conjuntos de Herramientas / EPPs (`kits` y `componentes_kit`)
* **Definición:** Agrupaciones estandarizadas (ej. *Kit de Fibra Óptica*, *Juego de Protección para Alturas*) compuestas por varios artículos con sus cantidades requeridas[cite: 1].
* **Reglas de Negocio:**
  * Al despachar un kit, el sistema permite asociar los números de serie específicos de las herramientas que lo conforman[cite: 1].
  * El despacho del kit actualiza el estado de disponibilidad de cada uno de sus componentes de forma automática[cite: 1].

---

## 6. Módulo de Monitoreo de Stock y Motor de Alertas
* **Objetivo:** Prevenir quiebres de inventario y pérdidas por obsolescencia[cite: 1].
* **Alertas Implementadas:**
  * **Alerta de Stock Crítico:** Se dispara cuando `stock_actual <= stock_minimo` en consumibles o materiales dentro de un almacén[cite: 1].
  * **Alerta de Inmovilizado / Baja Rotación:** Identifica materiales y consumibles que no registran salidas en un periodo determinado o próximos al límite de su vida útil[cite: 1].
  * **Alerta de Retornos Vencidos:** Notifica sobre herramientas o equipos asignados en campo cuya fecha de devolución proyectada ha sido superada.
  * **Alerta de Calibraciones / Mantenimiento:** Equipos e instrumentos de precisión con fecha de vencimiento de certificado próxima.

---

## 7. Módulo de Kardex y Trazabilidad Transaccional
* **Objetivo:** Registro inmutable y cronológico de todo flujo de inventario bajo metodología PEPS o promedio ponderado[cite: 1].
* **Tipos de Transacciones Soportadas:**
  * Entrada por Compra / Recepción de Proveedor.
  * Salida por Consumo Directo a Proyecto (no retornable)[cite: 1].
  * Salida por Préstamo a Campo (retornable)[cite: 1].
  * Retorno / Devolución desde Campo[cite: 1].
  * Transferencia entre Ubicaciones / Almacenes[cite: 1].
  * Ajustes de Inventario (por faltante, sobrante o merma justificada)[cite: 1].
* **Datos Auditables:** Fecha y hora exacta, tipo de operación, almacén, código de artículo/activo, cantidad afectada, saldo anterior, saldo posterior, usuario que opera en el sistema y motivo/observación[cite: 1].

---

## 8. Módulo de Despachos, Préstamos y Devoluciones
* **Flujo Operativo:**
  1. Generación de orden de despacho vinculada a un proyecto, personal o cuadrilla[cite: 1].
  2. Pistoleo o selección de artículos (fungibles por cantidad, activos por QR/Serial)[cite: 1].
  3. Firma digital en pantalla táctil / tablet por parte del receptor de la cuadrilla.
  4. Emisión de Acta de Entrega / Guía de Despacho Interno en formato PDF.
  5. Registro de retorno parcial o total: evaluación visual del estado del activo (*Devuelto Operativo*, *Devuelto Dañado*, *Extraviado*), registrando observaciones técnicas.

---

## 9. Módulo de Cuadrillas y Programación de Roster (Jornada 14x7)
* **Objetivo:** Gestionar grupos operativos de trabajo y calendarizar la disponibilidad del personal en frentes de obra bajo regímenes atípicos[cite: 1].
* **Gestión de Cuadrillas (`cuadrillas` y `cuadrilla_miembros`):**
  * Definición de código de cuadrilla, nombre (ej. *Cuadrilla Civil 01*, *Cuadrilla Empalme 02*), líder/supervisor responsable y listado de técnicos asignados[cite: 1].
  * Las asignaciones de herramientas, kits o materiales pueden emitirse directamente a la cuadrilla, imputando la responsabilidad solidaria al líder de la misma[cite: 1].
* **Gestión de Roster y Disponibilidad Laboral:**
  * Parametrización de turnos y grupos de relevo (ej. Guardia A, Guardia B)[cite: 1].
  * Matriz de seguimiento para turnos atípicos (14 días en obra / 7 días de descanso)[cite: 1].
  * Validación automática en despacho: alerta al almacenero si se intenta asignar un equipo a un trabajador o cuadrilla que se encuentre en días de descanso o rotación[cite: 1].

---

## 10. Módulo de Reportabilidad y Exportaciones
* **Formatos de Salida Requeridos:** PDF para actas formales y hojas de cargo; Excel (.xlsx) para análisis operativo y kardex[cite: 1].
* **Reportes Clave a Generar:**
  * **Hojas de Cargo Individual / Acta de Cuadrilla:** Documento formal en PDF con detalle de ítems, números de serie y casillero de firma digital[cite: 1].
  * **Reporte de Activos en Campo:** Listado de herramientas y equipos fuera de almacén, con responsable, proyecto, días transcurridos y fecha límite de entrega[cite: 1].
  * **Kardex Físico y Movimientos:** Exportación en Excel filtrada por rango de fechas, almacén y categoría[cite: 1].
  * **Reporte de Requerimientos de Compra:** Consolidado de materiales y consumibles que alcanzaron el stock mínimo para emisión de órdenes de compra[cite: 1].
  * **Historial de Mantenimientos e Inspecciones:** Bitácora de servicios preventivos, correctivos y revisiones periódicas de EPP.