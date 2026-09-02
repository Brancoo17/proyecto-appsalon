<?php

namespace Controllers;

use MVC\Router;

class TurnoController {

    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();

        // Solo pre-cargar datos si es un cliente común autenticado (no admin ni peluquero)
        $esClienteLogueado = isset($_SESSION['login']) && $_SESSION['login'] && !isset($_SESSION['admin']) && !isset($_SESSION['peluquero']);

        $router->render('turno/index', [
            'nombre' => $esClienteLogueado ? ($_SESSION['nombre'] ?? '') : '',
            'telefono' => $esClienteLogueado ? ($_SESSION['telefono'] ?? '') : '',
            'id' => $esClienteLogueado ? ($_SESSION['id'] ?? '') : ''
        ]);
    }
}