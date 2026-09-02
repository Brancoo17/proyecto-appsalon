<?php 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\LoginController;
use Controllers\TurnoController;
use Controllers\APIController;
use Controllers\AdminController;
use Controllers\ServicioController;
use Controllers\PeluqueroController;
use Controllers\PaginasController;
use Controllers\UsuarioController;

$router = new Router();

// Página Principal (Landing Page)
$router->get('/', [PaginasController::class, 'index']);

// Mi Cuenta / Perfil de Usuario
$router->get('/usuario', [UsuarioController::class, 'index']);
$router->post('/usuario', [UsuarioController::class, 'index']);
$router->post('/usuario/cancelar-turno', [UsuarioController::class, 'cancelarTurno']);

// Login & Autenticación
$router->get('/login', [LoginController::class, 'login']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);

// Recuperar Password
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);
$router->get('/recuperar', [LoginController::class, 'recuperar']);
$router->post('/recuperar', [LoginController::class, 'recuperar']);

// Crear Cuenta
$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);

// Confirmar Cuenta
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);

// AREA PRIVADA
$router->get('/turno', [TurnoController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);

// CRUD de Peluqueros (Solo Administrador)
$router->get('/peluqueros', [PeluqueroController::class, 'index']);
$router->get('/peluqueros/crear', [PeluqueroController::class, 'crear']);
$router->post('/peluqueros/crear', [PeluqueroController::class, 'crear']);
$router->get('/peluqueros/actualizar', [PeluqueroController::class, 'actualizar']);
$router->post('/peluqueros/actualizar', [PeluqueroController::class, 'actualizar']);
$router->post('/peluqueros/eliminar', [PeluqueroController::class, 'eliminar']);

// Panel Privado del Peluquero
$router->get('/peluquero', [PeluqueroController::class, 'panel']);
$router->get('/peluquero/servicios-horarios', [PeluqueroController::class, 'serviciosHorarios']);

// API de Turnos
$router->get('/api/servicios', [APIController::class, 'index']);
$router->get('/api/turnos', [APIController::class, 'turnos']);
$router->get('/api/disponibilidad', [APIController::class, 'disponibilidad']);
$router->get('/api/peluqueros', [APIController::class, 'peluqueros']);
$router->post('/api/turnos', [APIController::class, 'guardar']);
$router->post('/api/turnos/estado', [APIController::class, 'cambiarEstado']);
$router->post('/api/eliminar', [APIController::class, 'eliminar']);

// CRUD de Servicios
$router->get('/servicios', [ServicioController::class, 'index']);
$router->get('/servicios/crear', [ServicioController::class, 'crear']);
$router->post('/servicios/crear', [ServicioController::class, 'crear']);
$router->get('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/eliminar', [ServicioController::class, 'eliminar']);

// Diagnóstico del Sistema y Base de Datos (Útil para deployment)
$router->get('/diagnostico', function() {
    global $db;
    header('Content-Type: text/html; charset=utf-8');
    echo "<div style='font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 20px auto; max-width: 950px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.6);'>";
    echo "<h1 style='color: #38bdf8; border-bottom: 2px solid #1e293b; padding-bottom: 15px;'>🔍 Diagnóstico del Sistema BarberShop</h1>";
    echo "<p><strong>Versión de PHP:</strong> " . PHP_VERSION . "</p>";

    if(!$db) {
        echo "<p style='color:#ef4444; font-size: 18px;'>❌ Error crítico: No se pudo conectar a MySQL.</p>";
        return;
    }
    echo "<p style='color:#22c55e; font-size: 18px;'>✅ Conexión con MySQL exitosa.</p>";

    // Tablas existentes
    echo "<h3 style='color:#38bdf8; margin-top:25px;'>1. Tablas en la Base de Datos:</h3><ul style='columns: 2;'>";
    $res = $db->query("SHOW TABLES");
    $tablas = [];
    if($res) {
        while($row = $res->fetch_array()) {
            $tablas[] = $row[0];
            echo "<li><strong>" . htmlspecialchars($row[0]) . "</strong></li>";
        }
    }
    echo "</ul>";

    // Columnas clave
    echo "<h3 style='color:#38bdf8; margin-top:25px;'>2. Estructura de Tablas Críticas:</h3>";
    foreach(['turnos', 'servicios', 'usuarios', 'peluqueros', 'turnosServicios'] as $t) {
        $existe = in_array($t, $tablas);
        if($existe) {
            echo "<h4 style='color:#a5b4fc; margin-bottom:5px;'>Tabla: <code>$t</code></h4><ul style='columns: 2; margin-top:0;'>";
            $cRes = $db->query("SHOW COLUMNS FROM `$t`");
            if($cRes) {
                while($cRow = $cRes->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($cRow['Field']) . " <span style='color:#94a3b8;'>(" . htmlspecialchars($cRow['Type']) . ")</span></li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p style='color:#ef4444;'>❌ ¡La tabla <code>$t</code> NO existe en la base de datos!</p>";
        }
    }

    // Test de la consulta de Admin / Peluquero
    echo "<h3 style='color:#38bdf8; margin-top:25px;'>3. Prueba de Consulta SQL de Turnos:</h3>";
    $fecha = date('Y-m-d');
    $consulta = "SELECT turnos.id, turnos.hora, TRIM(CONCAT(COALESCE(usuarios.nombre, 'Invitado'), ' ', COALESCE(usuarios.apellido, ''))) as cliente,
                 COALESCE(usuarios.email, '') as email, COALESCE(usuarios.telefono, '') as telefono,
                 servicios.nombre as servicio, servicios.precio,
                 COALESCE(servicios.duracion, 30) as duracion,
                 CONCAT(peluqueros.nombre, ' ', peluqueros.apellido) as peluquero,
                 turnos.estado, turnos.metodo_pago
                 FROM turnos
                 LEFT OUTER JOIN usuarios ON turnos.usuario_id = usuarios.id
                 LEFT OUTER JOIN peluqueros ON turnos.peluquero_id = peluqueros.id
                 LEFT OUTER JOIN turnosServicios ON turnosServicios.turno_id = turnos.id
                 LEFT OUTER JOIN servicios ON servicios.id = turnosServicios.servicio_id
                 WHERE turnos.fecha = '{$fecha}'";
    $testQ = $db->query($consulta);
    if($testQ) {
        echo "<p style='color:#22c55e;'>✅ Consulta ejecutada correctamente. Filas encontradas para hoy ($fecha): " . $testQ->num_rows . "</p>";
    } else {
        echo "<p style='color:#ef4444;'>❌ Error en la consulta SQL: <strong>" . htmlspecialchars($db->error) . "</strong></p>";
        echo "<pre style='background:#1e293b; padding:10px; border-radius:6px; color:#cbd5e1; font-size:12px;'>" . htmlspecialchars($consulta) . "</pre>";
    }

    // Sesiones
    echo "<h3 style='color:#38bdf8; margin-top:25px;'>4. Estado de Sesiones:</h3>";
    echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? '🟢 Activa' : '🔴 Inactiva') . "</p>";
    echo "<p>Session ID: <code>" . session_id() . "</code></p>";
    echo "<p>Directorio de guardado: <code>" . (session_save_path() ?: sys_get_temp_dir()) . "</code></p>";
    echo "<p>Permiso de escritura en directorio de sesión: " . (is_writable(session_save_path() ?: sys_get_temp_dir()) ? '🟢 SÍ' : '🔴 NO (Error de permisos de sesión)') . "</p>";
    echo "<p>Variables en \$_SESSION: <pre style='background:#1e293b; padding:10px; border-radius:6px; color:#cbd5e1;'>" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "</pre></p>";
    echo "</div>";
});

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();