<?php

namespace Controllers;

use Model\AdminTurno;
use MVC\Router;

class AdminController {
    public static function index(Router $router) {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $fechas = explode('-', $fecha);

        if(!checkdate($fechas[1], $fechas[2], $fechas[0])) {
            header('Location: /404');
        }

        // Consultar la base de datos
        $consulta = "SELECT turnos.id,
                            turnos.hora,
                            TRIM(CONCAT(COALESCE(usuarios.nombre, 'Invitado'), ' ', COALESCE(usuarios.apellido, ''))) as cliente,
                            COALESCE(usuarios.email, '') as email,
                            COALESCE(usuarios.telefono, '') as telefono,
                            servicios.nombre as servicio,
                            servicios.precio,
                            COALESCE(servicios.duracion, 30) as duracion,
                            CONCAT(peluqueros.nombre, ' ', peluqueros.apellido) as peluquero,
                            turnos.estado,
                            turnos.metodo_pago ";

        $consulta .= " FROM turnos ";
        $consulta .= " LEFT OUTER JOIN usuarios ";
        $consulta .= " ON turnos.usuario_id = usuarios.id ";
        
        $consulta .= " LEFT OUTER JOIN peluqueros ";
        $consulta .= " ON turnos.peluquero_id = peluqueros.id ";

        $consulta .= " LEFT OUTER JOIN turnosservicios ";
        $consulta .= " ON turnosservicios.turno_id = turnos.id ";
        
        $consulta .= " LEFT OUTER JOIN servicios ";
        $consulta .= " ON servicios.id = turnosservicios.servicio_id ";

        $consulta .= " WHERE turnos.fecha = '{$fecha}' ";

        $turnos = AdminTurno::SQL($consulta);
        
        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'],
            'turnos' => $turnos,
            'fecha' => $fecha
        ]);
    }
}
