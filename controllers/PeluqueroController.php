<?php

namespace Controllers;

use MVC\Router;
use Model\Peluquero;
use Model\AdminTurno;

class PeluqueroController {

    // Listado de peluqueros para el Admin
    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        $peluqueros = Peluquero::all();

        $router->render('peluqueros/index', [
            'nombre' => $_SESSION['nombre'],
            'peluqueros' => $peluqueros
        ]);
    }

    // Crear un nuevo peluquero
    public static function crear(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        $peluquero = new Peluquero;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $peluquero->sincronizar($_POST);
            $alertas = $peluquero->validar();

            if(empty($alertas)) {
                // Verificar si ya existe ese email
                $existePeluquero = Peluquero::where('email', $peluquero->email);
                if($existePeluquero) {
                    Peluquero::setAlerta('error', 'El email ya está registrado en otro peluquero');
                    $alertas = Peluquero::getAlertas();
                } else {
                    $peluquero->hashPassword();
                    $resultado = $peluquero->guardar();
                    if($resultado) {
                        header('Location: /peluqueros');
                    }
                }
            }
        }

        $router->render('peluqueros/crear', [
            'nombre' => $_SESSION['nombre'],
            'peluquero' => $peluquero,
            'alertas' => $alertas
        ]);
    }

    // Actualizar peluquero
    public static function actualizar(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        if(!is_numeric($_GET['id'])) return;
        $peluquero = Peluquero::find($_GET['id']);
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordActual = $peluquero->password;
            $peluquero->sincronizar($_POST);

            // Si no envió nuevo password, conservar el actual
            if(empty($_POST['password'])) {
                $peluquero->password = $passwordActual;
            } else {
                $peluquero->hashPassword();
            }

            $alertas = $peluquero->validar();

            if(empty($alertas)) {
                $peluquero->guardar();
                header('Location: /peluqueros');
            }
        }

        $router->render('peluqueros/actualizar', [
            'nombre' => $_SESSION['nombre'],
            'peluquero' => $peluquero,
            'alertas' => $alertas
        ]);
    }

    // Eliminar peluquero
    public static function eliminar() {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $peluquero = Peluquero::find($id);
            if($peluquero) {
                $peluquero->eliminar();
            }
            header('Location: /peluqueros');
        }
    }

    // Panel propio del Peluquero (ver solo sus turnos)
    public static function panel(Router $router) {
        if(!isset($_SESSION)) session_start();
        isPeluquero();

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $fechas = explode('-', $fecha);

        if(!checkdate($fechas[1], $fechas[2], $fechas[0])) {
            header('Location: /404');
        }

        $peluqueroId = $_SESSION['id'];

        // Consultar turnos asignados únicamente a este peluquero
        $consulta = "SELECT turnos.id, turnos.hora, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as cliente, ";
        $consulta .= " usuarios.email, usuarios.telefono, servicios.nombre as servicio, servicios.precio, ";
        $consulta .= " CONCAT(peluqueros.nombre, ' ', peluqueros.apellido) as peluquero ";
        $consulta .= " FROM turnos ";
        $consulta .= " LEFT OUTER JOIN usuarios ON turnos.usuario_id = usuarios.id ";
        $consulta .= " LEFT OUTER JOIN peluqueros ON turnos.peluquero_id = peluqueros.id ";
        $consulta .= " LEFT OUTER JOIN turnosServicios ON turnosServicios.turno_id = turnos.id ";
        $consulta .= " LEFT OUTER JOIN servicios ON servicios.id = turnosServicios.servicio_id ";
        $consulta .= " WHERE fecha = '{$fecha}' AND turnos.peluquero_id = {$peluqueroId} ";

        $turnos = AdminTurno::SQL($consulta);

        $router->render('peluquero/index', [
            'nombre' => $_SESSION['nombre'],
            'turnos' => $turnos,
            'fecha' => $fecha
        ]);
    }
}
