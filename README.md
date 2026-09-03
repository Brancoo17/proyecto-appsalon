# 💈 BarberShop - Sistema de Gestión y Reserva de Turnos

Un sistema web completo, moderno y responsive para la administración integral de barberías y salones de belleza. Desarrollado con arquitectura **MVC (Modelo-Vista-Controlador)** en **PHP**, **JavaScript Vanilla**, **SCSS** y base de datos relacional **MySQL**.

---

## 🚀 Características Principales

### 🌐 1. Landing Page Comercial (`/`)
- Portada moderna y atractiva orientada a la conversión de clientes.
- Catálogo interactivo de servicios con botón **"Reservar Este Servicio"** que preselecciona automáticamente el servicio en el flujo de reserva.
- Presentación del equipo de barberos profesionales.
- Ubicación interactiva, horarios de atención y opiniones de clientes.
- Navegación dinámica según el estado de sesión (Invitado, Cliente, Peluquero o Administrador).

### 📅 2. Reserva de Turnos Inteligente (`/turno`)
- **Paso 1 - Selección de Servicios:**
  - Selección de múltiples servicios con indicación visual de precio y duración (`⏱️ min`).
  - **Carrito de Servicios Flotante:** Badge con contador y monto en tiempo real. Abre un modal interactivo para revisar el tiempo total estimado, eliminar servicios o avanzar directamente al siguiente paso.
- **Paso 2 - Fecha, Horarios y Profesionales:**
  - Grilla interactiva de horarios en intervalos de **15 minutos** que calcula la **duración total acumulada** de los servicios seleccionados (15, 30, 45, 60, 75, 90, 120 min).
  - **Algoritmo de disponibilidad continua:** Solo habilita los horarios que disponen del tiempo consecutivo suficiente sin colisionar con turnos existentes ni con **horarios bloqueados**, respetando la jornada laboral del barbero y eliminando tiempos muertos.
  - Modal para elegir profesional ("Primero disponible" con asignación automática o selección específica de un barbero).
  - Selección de método de pago (*Efectivo* o *Transferencia bancaria*).
  - Compatibilidad para clientes registrados y reservas como **Invitado**.
- **Paso 3 - Resumen y Confirmación:**
  - Detalle completo con rango horario estimado (`ej. 17:00 a 18:30 hs`).
  - Redirección automática a **WhatsApp** (`wa.me/549...`) con mensaje formateado listo para enviar.

### ✏️ 3. Modificación de Turnos Multirrol (`/turno/modificar`)
- **Disponible para Clientes, Peluqueros y Administradores:**
  - Los **Clientes** pueden modificar sus turnos reservados desde *"Mi Cuenta"* (cambiar servicios, fecha, horario o peluquero asignado).
  - Los **Peluqueros** pueden modificar los turnos asignados a su agenda.
  - Los **Administradores** pueden modificar cualquier turno del salón.
- **Interfaz Interactiva**:
  - Precarga automática de todos los datos actuales del turno (servicios seleccionados, profesional, fecha, hora y método de pago).
  - Selector de horarios en intervalos de 15 minutos sincronizado con el algoritmo de disponibilidad en tiempo real.
  - Alertas de validación que impiden la selección de fechas pasadas o colisiones de agenda.
- **Notificación Automática por WhatsApp al Peluquero:**
  - Cuando un cliente confirma la modificación de su turno, el sistema lo redirecciona automáticamente a **WhatsApp** con un mensaje prearmado (`"Modificación de Turno"`) que detalla la nueva fecha, hora, servicios y total, informando de inmediato al barbero asignado.

### 🔒 4. Bloqueo de Horarios con Motivo Opcional
- **Control de Franjas No Laborales:**
  - Permite al **Administrador** y a los **Peluqueros** bloquear intervalos de horario específicos (ej. 13:00 a 14:30) para descansos, almuerzos, consultas médicas o trámites.
  - **Alcance Flexible:** El Administrador puede bloquear a un profesional en particular o aplicar un **bloqueo global** para todo el salón (*"Todos los Profesionales"*). Los peluqueros pueden bloquear sus propios horarios.
- **Motivo Visible y Sugerencias Rápidas:**
  - Modal interactivo (SweetAlert2) con campo para ingresar un motivo opcional y **chips de sugerencias con un solo clic** (*Almuerzo*, *Descanso*, *Médico*, *Trámite personal*, *Capacitación*).
  - El motivo se muestra de manera visible en la agenda diaria en la columna *Estado / Motivo* (`.badge-motivo-bloqueo`) tanto en el panel de admin como en el de peluquero.
- **Integración con la Agenda y Disponibilidad:**
  - Agenda unificada cronológica que intercala turnos y bloqueos en orden temporal natural.
  - Los horarios bloqueados quedan inhabilitados automáticamente en el flujo de reserva de clientes (`/turno`) y en modificaciones (`/turno/modificar`).
  - Protección contra pisar turnos activos: alerta si ya existe un cliente agendado en esa franja horaria.
  - Botón de **desbloqueo directo** con confirmación para liberar la franja horaria en cualquier momento.

### 👥 5. Sección "Clientes Registrados" (`/admin/clientes`)
- **Panel Administrativo Exclusivo:**
  - Nueva sección accesible desde la barra de navegación del panel de administración (`/admin/clientes`).
- **Tarjetas de Métricas en Tiempo Real:**
  - Total de Clientes registrados en el sistema.
  - Clientes Activos (con al menos un turno reservado o completado).
  - Nuevos Clientes incorporados durante el mes en curso.
- **Buscador Reactivo en Vivo:**
  - Filtro instantáneo sin recargar la página por **Nombre**, **Apellido**, **Email** o **Teléfono**, con contador dinámico de resultados y botón de limpieza rápida.
- **Historial de Turnos y Contacto Directo:**
  - Botón interactivo para abrir un modal con el historial completo de turnos de cada cliente (con fechas en formato `dd/mm/yy`, chips individuales por servicio y montos).
  - Acceso directo con botón de **WhatsApp** para contactar al cliente con un solo clic.

### 👤 6. Panel "Mi Cuenta" (`/usuario`)
- Accesible para todos los roles (Clientes, Peluqueros y Administradores).
- **Mis Datos Personales:** Formulario para editar nombre, apellido, email, teléfono y cambio de contraseña opcional (con botón para mostrar/ocultar contraseña 👁️).
- **Navegación por Pestañas (Clientes):**
  - Pestaña **"Mis Datos"** para gestión de perfil.
  - Pestaña **"Mis Turnos"** con listado cronológico de turnos agendados, profesional asignado, tags de servicios, botón para **Modificar Turno** y botón para **Cancelar Turno**.

### ✂️ 7. Panel de Peluquero (`/peluquero`)
- Acceso exclusivo para profesionales del staff.
- **Agenda del Día (`/peluquero`):**
  - Listado cronológico de turnos asignados y horarios bloqueados filtrados por fecha.
  - Botón **"Bloquear Horario"** con contador de bloqueos activos.
  - Modificación de turnos asignados (`/turno/modificar?id=X`).
  - Cambio de estado interactivo del turno (*Reservado*, *Completado*, *Cancelado*).
  - Botón de desbloqueo rápido para liberar franjas bloqueadas.
- **Servicios y Horarios (`/peluquero/servicios-horarios`):**
  - Tarjetas informativas de solo lectura con los servicios asignados y el cronograma de trabajo semanal.

### 🛠️ 8. Panel de Administración (`/admin`)
- Control total del negocio para administradores.
- **Gestión de Turnos y Agenda (`/admin`):**
  - Búsqueda por fecha con visualización cronológica combinada de turnos y bloqueos.
  - Botón **"Bloquear Horario"** (por barbero o global para todo el salón) con motivo visible.
  - Modificación de turnos, cambio de estado y eliminación.
- **Clientes Registrados (`/admin/clientes`):**
  - Métricas, buscador en vivo, consulta de historial y contacto vía WhatsApp.
- **Gestión de Servicios (`/servicios`):**
  - CRUD completo de servicios con nombre, precio y selector de duración (*15, 30, 45, 60, 90, 120 min*).
- **Gestión de Peluqueros (`/peluqueros`):**
  - CRUD completo de barberos del salón.
  - Asignación de servicios que cada barbero puede atender.
  - Asignación de horarios de atención semanales (Lunes a Sábado) con selector de hora de entrada, salida y estado activo/no laboral.
  - Modales en tabla para consultar rápidamente los servicios y horarios de cada barbero.

---

## 🏗️ Estructura del Proyecto

```text
proyecto-appsalon/
├── classes/                # Clases auxiliares (Email, etc.)
├── controllers/            # Controladores del patrón MVC
│   ├── AdminController.php
│   ├── APIController.php
│   ├── LoginController.php
│   ├── PaginasController.php
│   ├── PeluqueroController.php
│   ├── ServicioController.php
│   ├── TurnoController.php
│   └── UsuarioController.php
├── includes/               # Configuración de base de datos y helpers
│   ├── app.php
│   ├── database.php
│   └── funciones.php
├── models/                 # Modelos ActiveRecord
│   ├── ActiveRecord.php
│   ├── AdminTurno.php
│   ├── HorarioBloqueado.php
│   ├── Peluquero.php
│   ├── PeluqueroHorario.php
│   ├── PeluqueroServicio.php
│   ├── Servicio.php
│   ├── Turno.php
│   ├── TurnoServicio.php
│   └── Usuario.php
├── public/                 # Directorio raíz del servidor web
│   ├── build/              # Assets compilados (CSS, JS, Imágenes)
│   └── index.php           # Punto de entrada y definición de rutas
├── src/                    # Código fuente Frontend
│   ├── js/                 # Scripts JS (app.js, buscador.js)
│   └── scss/               # Estilos modulares SCSS
├── views/                  # Vistas PHP / Templates
│   ├── admin/
│   │   ├── clientes.php    # Vista de Clientes Registrados y métricas
│   │   └── index.php       # Agenda de turnos y bloqueos
│   ├── auth/
│   ├── pages/
│   ├── peluquero/
│   ├── peluqueros/
│   ├── servicios/
│   ├── templates/
│   ├── turno/
│   │   ├── index.php       # Flujo de reserva de turnos
│   │   └── modificar.php   # Interfaz de modificación de turnos
│   └── layout.php
├── gulpfile.js             # Automatización de tareas (Sass, JS bundles)
└── package.json            # Dependencias Node.js
```

---

## 🗄️ Esquema de Base de Datos (MySQL)

El sistema utiliza las siguientes tablas relacionales:

1. **`usuarios`**: Almacena clientes y administradores (`admin = 1`).
2. **`peluqueros`**: Almacena los profesionales del staff con credenciales de acceso.
3. **`servicios`**: Catálogo de servicios con nombre, precio y `duracion` (en minutos).
4. **`turnos`**: Registros de turnos agendados con fecha, hora, método de pago y estado.
5. **`turnosservicios`**: Tabla intermedia entre turnos y servicios (relación muchos a muchos).
6. **`peluqueros_servicios`**: Tabla intermedia de servicios asignados a cada peluquero.
7. **`peluqueros_horarios`**: Horarios de trabajo semanales por día (1 = Lunes, ..., 6 = Sábado) de cada peluquero.
8. **`horarios_bloqueados`**: Franjas horarias no disponibles (bloqueos por profesional o globales) con fecha, hora inicio, hora fin y motivo opcional.

### Script SQL para Nuevas Tablas / Modificaciones:
```sql
-- Columna de duración en servicios
ALTER TABLE `servicios` ADD COLUMN `duracion` INT NOT NULL DEFAULT 30 AFTER `precio`;

-- Tabla de servicios por peluquero
CREATE TABLE IF NOT EXISTS `peluqueros_servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `peluquero_id` INT NOT NULL,
    `servicio_id` INT NOT NULL,
    FOREIGN KEY (`peluquero_id`) REFERENCES `peluqueros`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servicio_id`) REFERENCES `servicios`(`id`) ON DELETE CASCADE
);

-- Tabla de horarios por peluquero
CREATE TABLE IF NOT EXISTS `peluqueros_horarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `peluquero_id` INT NOT NULL,
    `dia_semana` INT NOT NULL,
    `hora_inicio` TIME NOT NULL DEFAULT '10:00:00',
    `hora_fin` TIME NOT NULL DEFAULT '20:00:00',
    `activo` TINYINT NOT NULL DEFAULT 1,
    FOREIGN KEY (`peluquero_id`) REFERENCES `peluqueros`(`id`) ON DELETE CASCADE
);

-- Tabla de horarios bloqueados (creada automáticamente al iniciar la app)
CREATE TABLE IF NOT EXISTS `horarios_bloqueados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `peluquero_id` INT NULL DEFAULT NULL,
    `fecha` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `motivo` VARCHAR(150) NULL DEFAULT NULL,
    INDEX `idx_fecha` (`fecha`),
    INDEX `idx_peluquero` (`peluquero_id`),
    FOREIGN KEY (`peluquero_id`) REFERENCES `peluqueros`(`id`) ON DELETE CASCADE
);
```

---

## ⚙️ Instalación y Puesta en Marcha

### 1. Requisitos Previos
- **PHP** >= 8.0
- **Composer**
- **Node.js** >= 18 y **npm**
- **MySQL** / MariaDB

### 2. Configurar la Base de Datos
1. Crear una base de datos MySQL (por ejemplo `appsalon_mvc`).
2. Configurar las credenciales en `includes/database.php` o archivo `.env`:
```php
$db = mysqli_connect('localhost', 'root', 'tu_password', 'appsalon_mvc');
```

### 3. Instalar Dependencias Frontend
```bash
npm install
```

### 4. Compilar Assets (SCSS & JS)
```bash
# Compilar una sola vez
npm run build

# O dejar escuchando cambios en desarrollo
npm run dev
```

### 5. Iniciar Servidor Local
```bash
# Desde la carpeta raíz del proyecto apuntando a public/
php -S localhost:3000 -t public
```

---

## 🔐 Roles y Accesos del Sistema

| Rol | Acceso | Funcionalidades |
|---|---|---|
| **Invitado** | `/`, `/turno`, `/login`, `/crear-cuenta` | Ver landing page, reservar turnos sin cuenta obligatoria, registrarse o iniciar sesión. |
| **Cliente** | `/usuario`, `/turno`, `/turno/modificar` | Reservar turnos, modificar turnos existentes (con aviso automático a WhatsApp del peluquero), cancelar turnos y gestionar datos personales. |
| **Peluquero** | `/peluquero`, `/peluquero/servicios-horarios`, `/turno/modificar`, `/usuario` | Ver agenda del día, bloquear horarios propios con motivo opcional, modificar turnos asignados, cambiar estados de turnos y consultar cronograma semanal. |
| **Administrador** | `/admin`, `/admin/clientes`, `/servicios`, `/peluqueros`, `/turno/modificar`, `/usuario` | Control total del salón: turnos, bloqueo de horarios (propios o globales con motivo), sección Clientes Registrados con métricas e historial, modificación de citas, gestión de servicios y peluqueros. |

---

## 🛠️ Tecnologías Utilizadas

- **PHP 8.x** - Backend y Renderizado MVC
- **MySQL** - Base de Datos Relacional con soporte de integridad referencial
- **JavaScript Vanilla (ES6+)** - Lógica dinámica, API Fetch, debounce y reactividad
- **SCSS / CSS3** - Diseño responsivo, estética oscura, glassmorphism y microinteracciones
- **SweetAlert2** - Modales interactivos, diálogos de confirmación y alertas visuales
- **FontAwesome 6** - Iconografía vectorial
- **Gulp 4 & Terser** - Automatización, minificación y optimización de assets
