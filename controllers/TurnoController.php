<?php

namespace Controllers;

use MVC\Router;
use Model\Turno;
use Model\Servicio;
use Model\Peluquero;
use Model\Usuario;

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

    public static function modificar(Router $router) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        isAuth();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: /');
            exit;
        }

        $turno = Turno::find($id);
        if (!$turno) {
            header('Location: /');
            exit;
        }

        $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'];
        $esPeluquero = isset($_SESSION['peluquero']) && $_SESSION['peluquero'];
        $esCliente = !$esAdmin && !$esPeluquero;

        // Permisos y redirección según rol
        $redirectUrl = '/admin';
        if ($esAdmin) {
            $redirectUrl = '/admin';
        } elseif ($esPeluquero) {
            $redirectUrl = '/peluquero';
            // El peluquero solo puede modificar turnos que le pertenezcan
            if ((int)$turno->peluquero_id !== (int)$_SESSION['id']) {
                header('Location: /peluquero');
                exit;
            }
        } else {
            $redirectUrl = '/usuario?tab=turnos';
            // El cliente solo puede modificar sus propios turnos y que estén en estado 'reservado'
            if ((int)$turno->usuario_id !== (int)$_SESSION['id'] || strtolower($turno->estado) !== 'reservado') {
                header('Location: /usuario?tab=turnos');
                exit;
            }
        }

        // Obtener servicios actualmente asignados a este turno
        $queryServicios = "SELECT servicios.* FROM servicios ";
        $queryServicios .= "INNER JOIN turnosservicios ON turnosservicios.servicio_id = servicios.id ";
        $queryServicios .= "WHERE turnosservicios.turno_id = {$id}";
        $serviciosAsignados = Servicio::SQL($queryServicios);
        $serviciosAsignadosIds = array_map(fn($s) => (int)$s->id, $serviciosAsignados);

        // Obtener datos del cliente
        $cliente = $turno->usuario_id ? Usuario::find($turno->usuario_id) : null;
        $nombreCliente = $cliente ? trim($cliente->nombre . ' ' . $cliente->apellido) : 'Invitado';
        $telefonoCliente = $cliente ? ($cliente->telefono ?? '') : '';

        // Peluquero actual
        $peluqueroActual = $turno->peluquero_id ? Peluquero::find($turno->peluquero_id) : null;

        $router->render('turno/modificar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'turno' => $turno,
            'cliente' => $cliente,
            'nombreCliente' => $nombreCliente,
            'telefonoCliente' => $telefonoCliente,
            'peluqueroActual' => $peluqueroActual,
            'serviciosAsignadosIds' => $serviciosAsignadosIds,
            'serviciosAsignados' => $serviciosAsignados,
            'redirectUrl' => $redirectUrl,
            'esAdmin' => $esAdmin,
            'esPeluquero' => $esPeluquero,
            'esCliente' => $esCliente
        ]);
    }
}