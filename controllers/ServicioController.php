<?php

namespace Controllers;

use MVC\Router;
use Model\Servicio;

class ServicioController {

    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        $servicios = Servicio::all();
        $resultado = $_GET['resultado'] ?? null;

        $router->render('servicios/index', [
            'nombre' => $_SESSION['nombre'],
            'servicios' => $servicios,
            'resultado' => $resultado
        ]);
    }

    public static function crear(Router $router) {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        $servicio = new Servicio;

        $alertas = [];
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio->sincronizar($_POST);

            $alertas = $servicio->validar();

            if(empty($alertas)) {
                $servicio->guardar();
                header('Location: /servicios?resultado=1');
                return;
            }
            
        }

        $router->render('servicios/crear', [
            'nombre' => $_SESSION['nombre'],
            'servicio' => $servicio,
            'alertas' => $alertas
        ]);
    }

    public static function actualizar(Router $router) {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        if(!is_numeric($_GET['id'])) return;

        $servicio = Servicio::find($_GET['id']);

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio->sincronizar($_POST);

            $alertas = $servicio->validar();

            if(empty($alertas)) {
                $servicio->guardar();
                header('Location: /servicios?resultado=2');
                return;
            }
        }

        $router->render('servicios/actualizar', [
            'nombre' => $_SESSION['nombre'],
            'servicio' => $servicio,
            'alertas' => $alertas
        ]);
    }

    public static function eliminar() {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $servicio = Servicio::find($id);
            if($servicio) {
                $servicio->eliminar();
            }
            header('Location: /servicios?resultado=3');
            return;
        }
    }

}