<?php 
use Model\ActiveRecord;
require __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
    error_log("Error cargando .env: " . $e->getMessage());
}

require 'funciones.php';
require 'database.php';


date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conectarnos a la base de datos
ActiveRecord::setDB($db);
\Model\HorarioBloqueado::crearTablaSiNoExiste();