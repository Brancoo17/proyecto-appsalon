<?php

namespace Controllers;

use MVC\Router;

class TurnoController {

    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();

        $router->render('turno/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'telefono' => $_SESSION['telefono'] ?? '',
            'id' => $_SESSION['id'] ?? ''
        ]);
    }
}