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
  - **Algoritmo de disponibilidad continua:** Solo habilita los horarios que disponen del tiempo consecutivo suficiente sin colisionar con turnos existentes ni superar la jornada laboral del barbero, eliminando tiempos muertos.
  - Modal para elegir profesional ("Primero disponible" con asignación automática o selección específica de un barbero).
  - Selección de método de pago (*Efectivo* o *Transferencia bancaria*).
  - Compatibilidad para clientes registrados y reservas como **Invitado**.
- **Paso 3 - Resumen y Confirmación:**
  - Detalle completo con rango horario estimado (`ej. 17:00 a 18:30 hs`).
  - Redirección automática a **WhatsApp** (`wa.me/549...`) con mensaje formateado listo para enviar.

### 👤 3. Panel "Mi Cuenta" (`/usuario`)
- Accesible para todos los roles (Clientes, Peluqueros y Administradores).
- **Mis Datos Personales:** Formulario para editar nombre, apellido, email, teléfono y cambio de contraseña opcional (con botón para mostrar/ocultar contraseña 👁️).
- **Navegación por Pestañas (Clientes):**
  - Pestaña **"Mis Datos"** para gestión de perfil.
  - Pestaña **"Mis Turnos"** con listado cronológico de turnos agendados, profesional asignado, tags de servicios y botón para **cancelar reservas activas**.

### ✂️ 4. Panel de Peluquero (`/peluquero`)
- Acceso exclusivo para profesionales del staff.
- **Mis Turnos (`/peluquero`):**
  - Listado de turnos asignados filtrados por fecha.
  - Horario de inicio y fin (`ej. 15:00 - 16:30`).
  - Modal interactivo para cambiar el estado del turno (*Reservado*, *Completado*, *Cancelado*).
- **Servicios y Horarios (`/peluquero/servicios-horarios`):**
  - Tarjetas informativas de solo lectura con los servicios asignados y el cronograma de trabajo semanal.

### 🛠️ 5. Panel de Administración (`/admin`)
- Control total del negocio para administradores.
- **Gestión de Turnos (`/admin`):**
  - Búsqueda de turnos por fecha con detalles de cliente, barbero, servicios, método de pago, monto total y estado.
  - Cambio de estado interactivo en tiempo real y opción de eliminar turnos.
- **Gestión de Servicios (`/servicios`):**
  - CRUD completo de servicios con nombre, precio y selector de duración (*15, 30, 45, 60, 90, 120 min*).
  - Alertas visuales de confirmación (*creado, actualizado, eliminado*).
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
│   ├── auth/
│   ├── pages/
│   ├── peluquero/
│   ├── peluqueros/
│   ├── servicios/
│   ├── templates/
│   ├── turno/
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
5. **`turnosServicios`**: Tabla intermedia entre turnos y servicios (relación muchos a muchos).
6. **`peluqueros_servicios`**: Tabla intermedia de servicios asignados a cada peluquero.
7. **`peluqueros_horarios`**: Horarios de trabajo semanales por día (1 = Lunes, ..., 6 = Sábado) de cada peluquero.

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
npx gulp

# O dejar escuchando cambios en desarrollo
npx gulp dev
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
| **Invitado** | `/`, `/turno`, `/login`, `/crear-cuenta` | Ver landing, reservar turnos sin cuenta, registrarse o iniciar sesión. |
| **Cliente** | `/usuario`, `/turno` | Reservar turnos, gestionar datos personales, ver historial y cancelar turnos. |
| **Peluquero** | `/peluquero`, `/peluquero/servicios-horarios`, `/usuario` | Ver turnos asignados, cambiar estados de turnos, consultar cronograma y servicios asignados. |
| **Administrador** | `/admin`, `/servicios`, `/peluqueros`, `/usuario` | Control total del salón: turnos, servicios, peluqueros, horarios y estadísticas. |

---

## 🛠️ Tecnologías Utilizadas

- **PHP 8.x** - Backend y Renderizado MVC
- **MySQL** - Base de Datos Relacional
- **JavaScript Vanilla (ES6+)** - Lógica dinámica, API Fetch y reactividad
- **SCSS / CSS3** - Diseño responsivo, mixins y temas oscuros modernos
- **SweetAlert2** - Modales y notificaciones interactivas
- **FontAwesome 6** - Iconografía vectorial
- **Gulp 4** - Automatización y optimización de assets
