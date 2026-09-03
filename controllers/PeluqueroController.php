<?php

namespace Controllers;

use MVC\Router;
use Model\Peluquero;
use Model\Servicio;
use Model\AdminTurno;

class PeluqueroController {

    // Listado de peluqueros para el Admin
    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        $peluqueros = Peluquero::all();
        $resultado = $_GET['resultado'] ?? null;

        $router->render('peluqueros/index', [
            'nombre' => $_SESSION['nombre'],
            'peluqueros' => $peluqueros,
            'resultado' => $resultado
        ]);
    }

    // Crear un nuevo peluquero
    public static function crear(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        $peluquero = new Peluquero;
        $servicios = Servicio::all();
        $serviciosPeluquero = [];
        $horarios = [];
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
                    if($resultado && !empty($resultado['id'])) {
                        $peluquero->id = (int)$resultado['id'];
                        // Sincronizar servicios y horarios asignados
                        $peluquero->sincronizarServicios($_POST['servicios'] ?? []);
                        $peluquero->sincronizarHorarios($_POST['horarios'] ?? []);

                        header('Location: /peluqueros?resultado=1');
                        return;
                    }
                }
            }
        }

        $router->render('peluqueros/crear', [
            'nombre' => $_SESSION['nombre'],
            'peluquero' => $peluquero,
            'servicios' => $servicios,
            'serviciosPeluquero' => $serviciosPeluquero,
            'horarios' => $horarios,
            'alertas' => $alertas
        ]);
    }

    // Actualizar peluquero
    public static function actualizar(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAdmin();

        if(!is_numeric($_GET['id'])) return;
        $peluquero = Peluquero::find($_GET['id']);
        if(!$peluquero) {
            header('Location: /peluqueros');
            return;
        }

        $servicios = Servicio::all();
        $serviciosPeluquero = $peluquero->getServiciosIds();
        $horarios = $peluquero->getHorarios();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordActual = $peluquero->password;
            $peluquero->sincronizar($_POST);

            // Validar campos
            $alertas = $peluquero->validarActualizar();

            if(empty($alertas)) {
                // Si no envió nuevo password, conservar el actual; sino, hashearlo
                if(empty($_POST['password'])) {
                    $peluquero->password = $passwordActual;
                } else {
                    $peluquero->hashPassword();
                }

                $peluquero->guardar();
                // Sincronizar servicios y horarios asignados
                $peluquero->sincronizarServicios($_POST['servicios'] ?? []);
                $peluquero->sincronizarHorarios($_POST['horarios'] ?? []);

                header('Location: /peluqueros?resultado=2');
                return;
            }
        }

        $router->render('peluqueros/actualizar', [
            'nombre' => $_SESSION['nombre'],
            'peluquero' => $peluquero,
            'servicios' => $servicios,
            'serviciosPeluquero' => $serviciosPeluquero,
            'horarios' => $horarios,
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
            header('Location: /peluqueros?resultado=3');
            return;
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
        $consulta = "SELECT turnos.id, turnos.hora, TRIM(CONCAT(COALESCE(usuarios.nombre, 'Invitado'), ' ', COALESCE(usuarios.apellido, ''))) as cliente, ";
        $consulta .= " COALESCE(usuarios.email, '') as email, COALESCE(usuarios.telefono, '') as telefono, ";
        $consulta .= " servicios.nombre as servicio, servicios.precio, ";
        $consulta .= " COALESCE(servicios.duracion, 30) as duracion, ";
        $consulta .= " CONCAT(peluqueros.nombre, ' ', peluqueros.apellido) as peluquero, ";
        $consulta .= " turnos.estado, turnos.metodo_pago ";
        $consulta .= " FROM turnos ";
        $consulta .= " LEFT OUTER JOIN usuarios ON turnos.usuario_id = usuarios.id ";
        $consulta .= " LEFT OUTER JOIN peluqueros ON turnos.peluquero_id = peluqueros.id ";
        $consulta .= " LEFT OUTER JOIN turnosservicios ON turnosservicios.turno_id = turnos.id ";
        $consulta .= " LEFT OUTER JOIN servicios ON servicios.id = turnosservicios.servicio_id ";
        $consulta .= " WHERE turnos.fecha = '{$fecha}' AND turnos.peluquero_id = " . intval($peluqueroId) . " ";

        $turnos = AdminTurno::SQL($consulta);

        $router->render('peluquero/index', [
            'nombre' => $_SESSION['nombre'],
            'turnos' => $turnos,
            'fecha' => $fecha
        ]);
    }

    // Sección de Servicios y Horarios para el Peluquero (Solo Lectura)
    public static function serviciosHorarios(Router $router) {
        if(!isset($_SESSION)) session_start();
        isPeluquero();

        $peluqueroId = $_SESSION['id'];
        $peluquero = Peluquero::find($peluqueroId);

        $misServicios = $peluquero ? $peluquero->getServicios() : [];
        $misHorarios = $peluquero ? $peluquero->getHorarios() : [];

        $router->render('peluquero/servicios-horarios', [
            'nombre' => $_SESSION['nombre'],
            'misServicios' => $misServicios,
            'misHorarios' => $misHorarios
        ]);
    }
}
