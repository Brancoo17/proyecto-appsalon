<?php

namespace Controllers;

use MVC\Router;

class TurnoController {

    public static function index(Router $router) {

        if(!isset($_SESSION)) session_start();

        isAuth();

        $router->render('turno/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'id' => $_SESSION['id'] ?? ''
        ]);
    }
}