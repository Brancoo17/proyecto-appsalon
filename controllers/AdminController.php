<?php

namespace Controllers;

use Model\AdminTurno;
use Model\HorarioBloqueado;
use Model\Peluquero;
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

        // Consultar bloqueos de la fecha
        $queryBloqueos = "SELECT horarios_bloqueados.*, ";
        $queryBloqueos .= "COALESCE(CONCAT(peluqueros.nombre, ' ', peluqueros.apellido), 'Todos los profesionales') as peluquero ";
        $queryBloqueos .= "FROM horarios_bloqueados ";
        $queryBloqueos .= "LEFT JOIN peluqueros ON peluqueros.id = horarios_bloqueados.peluquero_id ";
        $queryBloqueos .= "WHERE horarios_bloqueados.fecha = '{$fecha}' ";
        $queryBloqueos .= "ORDER BY horarios_bloqueados.hora_inicio ASC";
        $bloqueos = HorarioBloqueado::SQL($queryBloqueos);

        $peluqueros = Peluquero::all();
        
        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'],
            'turnos' => $turnos,
            'bloqueos' => $bloqueos,
            'peluqueros' => $peluqueros,
            'fecha' => $fecha
        ]);
    }

    public static function clientes(Router $router) {
        if(!isset($_SESSION)) session_start();

        isAdmin();

        $db = AdminTurno::getDB();

        // Consultar clientes registrados
        $consulta = "SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.confirmado,
                            COUNT(DISTINCT t.id) as total_turnos,
                            MAX(t.fecha) as ultimo_turno
                     FROM usuarios u
                     LEFT JOIN turnos t ON t.usuario_id = u.id
                     WHERE (u.admin = 0 OR u.admin IS NULL)
                       AND u.email != '' AND u.email IS NOT NULL
                     GROUP BY u.id
                     ORDER BY u.id DESC";

        $resultado = $db->query($consulta);
        $clientes = [];

        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $clientes[$row['id']] = $row;
                $clientes[$row['id']]['turnos_detalle'] = [];
            }
            $resultado->free();
        }

        // Consultar los turnos de estos clientes registrados para mostrarlos en detalle
        if(!empty($clientes)) {
            $idsClientes = implode(',', array_keys($clientes));
            $queryDetalle = "SELECT t.id, t.usuario_id, t.fecha, t.hora, t.estado, t.metodo_pago,
                                    CONCAT(p.nombre, ' ', p.apellido) as peluquero,
                                    s.nombre as servicio, s.precio
                             FROM turnos t
                             LEFT JOIN peluqueros p ON p.id = t.peluquero_id
                             LEFT JOIN turnosservicios ts ON ts.turno_id = t.id
                             LEFT JOIN servicios s ON s.id = ts.servicio_id
                             WHERE t.usuario_id IN ({$idsClientes})
                             ORDER BY t.fecha DESC, t.hora DESC";

            $resDetalle = $db->query($queryDetalle);
            if($resDetalle) {
                while($d = $resDetalle->fetch_assoc()) {
                    $uId = $d['usuario_id'];
                    $tId = $d['id'];
                    if(!isset($clientes[$uId]['turnos_detalle'][$tId])) {
                        $clientes[$uId]['turnos_detalle'][$tId] = [
                            'id' => $tId,
                            'fecha' => $d['fecha'],
                            'hora' => substr($d['hora'], 0, 5),
                            'estado' => $d['estado'] ?? 'reservado',
                            'metodo_pago' => $d['metodo_pago'] ?? 'efectivo',
                            'peluquero' => $d['peluquero'] ?: 'No asignado',
                            'servicios' => [],
                            'total' => 0
                        ];
                    }
                    if(!empty($d['servicio'])) {
                        $clientes[$uId]['turnos_detalle'][$tId]['servicios'][] = $d['servicio'];
                        $clientes[$uId]['turnos_detalle'][$tId]['total'] += (float)$d['precio'];
                    }
                }
                $resDetalle->free();
            }
        }

        // Estadísticas de resumen
        $totalClientes = count($clientes);
        $totalConfirmados = 0;
        $totalConTurnos = 0;

        foreach($clientes as $c) {
            if((int)($c['confirmado'] ?? 0) === 1) {
                $totalConfirmados++;
            }
            if((int)($c['total_turnos'] ?? 0) > 0) {
                $totalConTurnos++;
            }
        }

        $router->render('admin/clientes', [
            'nombre' => $_SESSION['nombre'],
            'clientes' => array_values($clientes),
            'totalClientes' => $totalClientes,
            'totalConfirmados' => $totalConfirmados,
            'totalConTurnos' => $totalConTurnos
        ]);
    }
}
