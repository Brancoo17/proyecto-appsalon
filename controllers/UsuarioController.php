<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Peluquero;
use Model\Turno;
use Model\AdminTurno;

class UsuarioController {

    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();
        isAuth();

        $usuarioId = $_SESSION['id'];
        $esPeluquero = isset($_SESSION['peluquero']) && $_SESSION['peluquero'];
        $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'];
        $esCliente = !$esPeluquero && !$esAdmin;

        $alertas = [];
        $resultado = $_GET['resultado'] ?? null;

        // Cargar modelo según el rol
        if($esPeluquero) {
            $usuario = Peluquero::find($usuarioId);
            $rol = 'Peluquero';
        } else {
            $usuario = Usuario::find($usuarioId);
            $rol = $esAdmin ? 'Administrador' : 'Cliente';
        }

        if(!$usuario) {
            header('Location: /logout');
            return;
        }

        // Si es cliente, cargar todos sus turnos
        $turnosAgrupados = [];
        if($esCliente) {
            $queryTurnos = "SELECT turnos.id, turnos.fecha, turnos.hora, turnos.estado, turnos.metodo_pago, ";
            $queryTurnos .= "servicios.nombre as servicio, servicios.precio, COALESCE(servicios.duracion, 30) as duracion, ";
            $queryTurnos .= "CONCAT(peluqueros.nombre, ' ', peluqueros.apellido) as peluquero ";
            $queryTurnos .= "FROM turnos ";
            $queryTurnos .= "LEFT JOIN turnosServicios ON turnosServicios.turno_id = turnos.id ";
            $queryTurnos .= "LEFT JOIN servicios ON servicios.id = turnosServicios.servicio_id ";
            $queryTurnos .= "LEFT JOIN peluqueros ON peluqueros.id = turnos.peluquero_id ";
            $queryTurnos .= "WHERE turnos.usuario_id = {$usuarioId} ";
            $queryTurnos .= "ORDER BY turnos.fecha DESC, turnos.hora DESC";

            $db = Turno::getDB();
            $res = $db->query($queryTurnos);

            if($res) {
                while($row = $res->fetch_assoc()) {
                    $idTurno = $row['id'];
                    if(!isset($turnosAgrupados[$idTurno])) {
                        $turnosAgrupados[$idTurno] = [
                            'id' => $idTurno,
                            'fecha' => $row['fecha'],
                            'hora' => substr($row['hora'], 0, 5),
                            'estado' => $row['estado'] ?? 'reservado',
                            'metodo_pago' => $row['metodo_pago'] ?? 'efectivo',
                            'peluquero' => $row['peluquero'] ?: 'No asignado',
                            'servicios' => [],
                            'total' => 0,
                            'duracionTotal' => 0
                        ];
                    }
                    if(!empty($row['servicio'])) {
                        $dur = (int)($row['duracion'] ?? 30);
                        $turnosAgrupados[$idTurno]['servicios'][] = [
                            'nombre' => $row['servicio'],
                            'precio' => $row['precio'],
                            'duracion' => $dur
                        ];
                        $turnosAgrupados[$idTurno]['total'] += (float)$row['precio'];
                        $turnosAgrupados[$idTurno]['duracionTotal'] += $dur;
                    }
                }
                $res->free();
            }

            // Calcular hora fin para cada turno
            foreach($turnosAgrupados as $id => $t) {
                $durMin = max(30, $t['duracionTotal']);
                $horaInicioSeg = strtotime("2000-01-01 " . $t['hora']);
                $horaFinSeg = $horaInicioSeg + ($durMin * 60);
                $turnosAgrupados[$id]['hora_fin'] = date('H:i', $horaFinSeg);
            }
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);

            // Validar campos básicos
            if(!$usuario->nombre) {
                $alertas['error'][] = 'El nombre es obligatorio';
            }
            if(!$usuario->apellido) {
                $alertas['error'][] = 'El apellido es obligatorio';
            }
            if(!$usuario->email) {
                $alertas['error'][] = 'El email es obligatorio';
            }
            if(!$usuario->telefono) {
                $alertas['error'][] = 'El teléfono es obligatorio';
            }

            // Validar cambio de contraseña opcional
            if(!empty($_POST['password'])) {
                if(strlen($_POST['password']) < 6) {
                    $alertas['error'][] = 'La nueva contraseña debe tener al menos 6 caracteres';
                } else {
                    $usuario->password = $_POST['password'];
                    $usuario->hashPassword();
                }
            }

            if(empty($alertas)) {
                // Verificar que el email no pertenezca a otra cuenta
                if($esPeluquero) {
                    $existe = Peluquero::where('email', $usuario->email);
                    if($existe && $existe->id != $usuario->id) {
                        $alertas['error'][] = 'El email ya está registrado por otro usuario';
                    }
                } else {
                    $existe = Usuario::where('email', $usuario->email);
                    if($existe && $existe->id != $usuario->id) {
                        $alertas['error'][] = 'El email ya está registrado por otro usuario';
                    }
                }

                if(empty($alertas)) {
                    $usuario->guardar();

                    // Actualizar variables de sesión
                    $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                    $_SESSION['email'] = $usuario->email;
                    if(isset($usuario->telefono)) {
                        $_SESSION['telefono'] = $usuario->telefono;
                    }

                    header('Location: /usuario?resultado=1');
                    return;
                }
            }
        }

        $router->render('pages/usuario', [
            'nombre' => $_SESSION['nombre'],
            'usuario' => $usuario,
            'rol' => $rol,
            'esCliente' => $esCliente,
            'esPeluquero' => $esPeluquero,
            'esAdmin' => $esAdmin,
            'turnos' => $turnosAgrupados,
            'alertas' => $alertas,
            'resultado' => $resultado
        ]);
    }

    // Cancelar turno por parte del cliente
    public static function cancelarTurno() {
        if(!isset($_SESSION)) session_start();
        isAuth();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turnoId = (int)($_POST['id'] ?? 0);
            $usuarioId = (int)$_SESSION['id'];

            if($turnoId > 0) {
                $turno = Turno::find($turnoId);
                // Verificar que el turno pertenezca a este cliente
                if($turno && (int)$turno->usuario_id === $usuarioId) {
                    $turno->estado = 'cancelado';
                    $turno->guardar();
                    header('Location: /usuario?resultado=2&tab=turnos');
                    return;
                }
            }
            header('Location: /usuario?tab=turnos');
        }
    }
}
