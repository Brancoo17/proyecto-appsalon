<?php

namespace Controllers;

use MVC\Router;
use Model\Servicio;
use Model\Peluquero;

class PaginasController {
    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();

        $servicios = Servicio::all();
        $peluqueros = Peluquero::all();

        $router->render('pages/index', [
            'titulo' => 'BarberShop | Estilo, Calidad y Cuidado Masculino',
            'is_home' => true,
            'servicios' => $servicios,
            'peluqueros' => $peluqueros,
            'auth' => $_SESSION['login'] ?? false,
            'nombre' => $_SESSION['nombre'] ?? '',
            'admin' => $_SESSION['admin'] ?? null,
            'peluquero_sesion' => $_SESSION['peluquero'] ?? null
        ]);
    }
}
