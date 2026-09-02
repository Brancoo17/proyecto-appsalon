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

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();