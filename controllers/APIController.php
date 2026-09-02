<?php 

namespace Controllers;

use Model\Servicio;
use Model\Turno;
use Model\Peluquero;
use Model\TurnoServicio;
use Model\Usuario;

class APIController {

    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    // Obtener todos los peluqueros
    public static function peluqueros() {
        $peluqueros = Peluquero::all();
        echo json_encode($peluqueros);
    }

    public static function turnos() {
        // Obtener la fecha de los parámetros de la URL (?fecha=YYYY-MM-DD)
        $fecha = $_GET['fecha'] ?? '';
        $peluquero_id = $_GET['peluquero_id'] ?? '';
        
        // Sanitizar la fecha y peluquero_id para evitar inyección SQL
        $fecha = filter_var($fecha, FILTER_SANITIZE_SPECIAL_CHARS);
        $peluquero_id = filter_var($peluquero_id, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$fecha) {
            echo json_encode([]);
            return;
        }

        // Obtener las citas para esta fecha (y opcionalmente por peluquero)
        if(!empty($peluquero_id)) {
            $query = "SELECT hora, peluquero_id FROM turnos WHERE fecha = '{$fecha}' AND peluquero_id = '{$peluquero_id}'";
        } else {
            $query = "SELECT hora, peluquero_id FROM turnos WHERE fecha = '{$fecha}'";
        }

        $turnos = Turno::consultarSQL($query);
        
        // Mapear el resultado para obtener hora en formato HH:MM y peluquero_id
        $resultado = array_map(function($turno) {
            return [
                'hora' => substr($turno->hora, 0, 5),
                'peluquero_id' => $turno->peluquero_id
            ];
        }, $turnos);

        echo json_encode($resultado);
    }

    public static function disponibilidad() {
        $fecha = $_GET['fecha'] ?? '';
        $serviciosStr = $_GET['servicios'] ?? '';

        $fecha = filter_var($fecha, FILTER_SANITIZE_SPECIAL_CHARS);
        $serviciosStr = filter_var($serviciosStr, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$fecha) {
            echo json_encode([
                'horasDisponibles' => [],
                'horasOcupadas' => [],
                'peluquerosPorHora' => [],
                'duracionTotal' => 30
            ]);
            return;
        }

        $serviciosIds = !empty($serviciosStr) ? array_map('intval', explode(',', $serviciosStr)) : [];

        // 1. Calcular duración total requerida en minutos
        $duracionTotalRequerida = 30;
        if (!empty($serviciosIds)) {
            $idsSanitizados = implode(',', $serviciosIds);
            $queryDuracion = "SELECT SUM(COALESCE(duracion, 30)) as total_minutos FROM servicios WHERE id IN ({$idsSanitizados})";
            $db = Servicio::getDB();
            $res = $db->query($queryDuracion);
            if ($res) {
                $row = $res->fetch_assoc();
                $duracionTotalRequerida = (int)($row['total_minutos'] ?? 30);
                $res->free();
            }
        }
        if ($duracionTotalRequerida <= 0) $duracionTotalRequerida = 30;

        // Día de la semana (1 = Lunes, ..., 7 = Domingo)
        $diaSemana = (int)date('N', strtotime($fecha));
        if ($diaSemana === 7) {
            echo json_encode([
                'horasDisponibles' => [],
                'horasOcupadas' => [],
                'peluquerosPorHora' => [],
                'duracionTotal' => $duracionTotalRequerida,
                'mensaje' => 'Día Domingo no disponible'
            ]);
            return;
        }

        $horasEstablecimiento = [
            "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", 
            "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", 
            "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", 
            "19:00", "19:30", "20:00", "20:30", "21:00"
        ];

        // 2. Obtener todos los peluqueros
        $peluqueros = Peluquero::all();

        // 3. Obtener turnos ya agendados en esta fecha con sus duraciones reales
        $queryTurnos = "SELECT turnos.id, turnos.hora, turnos.peluquero_id, ";
        $queryTurnos .= "COALESCE(SUM(servicios.duracion), 30) as duracion ";
        $queryTurnos .= "FROM turnos ";
        $queryTurnos .= "LEFT JOIN turnosServicios ON turnosServicios.turno_id = turnos.id ";
        $queryTurnos .= "LEFT JOIN servicios ON servicios.id = turnosServicios.servicio_id ";
        $queryTurnos .= "WHERE turnos.fecha = '{$fecha}' AND (turnos.estado != 'cancelado' OR turnos.estado IS NULL) ";
        $queryTurnos .= "GROUP BY turnos.id, turnos.hora, turnos.peluquero_id";
        
        $db = Turno::getDB();
        $resTurnos = $db->query($queryTurnos);
        
        // Mapear los intervalos ocupados por cada peluquero
        $intervalosOcupadosPorPeluquero = [];
        if ($resTurnos) {
            while ($row = $resTurnos->fetch_assoc()) {
                $peluqueroId = (int)$row['peluquero_id'];
                $horaPartes = explode(':', substr($row['hora'], 0, 5));
                $startMin = ((int)$horaPartes[0] * 60) + (int)($horaPartes[1] ?? 0);
                $durMin = max(30, (int)($row['duracion'] ?? 30));
                $endMin = $startMin + $durMin;

                $intervalosOcupadosPorPeluquero[$peluqueroId][] = [
                    'start' => $startMin,
                    'end' => $endMin
                ];
            }
            $resTurnos->free();
        }

        // 4. Pre-calcular capacidades y horarios de cada peluquero
        $peluquerosData = [];
        foreach($peluqueros as $p) {
            $serviciosPeluquero = $p->getServiciosIds();
            $horariosPeluquero = $p->getHorarios();
            $horarioDia = $horariosPeluquero[$diaSemana] ?? null;

            // Filtro 1: Servicios que realiza
            $realizaServicios = true;
            if(!empty($serviciosIds) && !empty($serviciosPeluquero)) {
                foreach($serviciosIds as $reqId) {
                    if(!in_array($reqId, $serviciosPeluquero)) {
                        $realizaServicios = false;
                        break;
                    }
                }
            }

            // Filtro 2: Horario de trabajo
            $trabajaHoy = true;
            $horaInicioStr = "10:00";
            $horaFinStr = "21:00";

            if($horarioDia) {
                if((int)$horarioDia->activo !== 1) {
                    $trabajaHoy = false;
                } else {
                    $horaInicioStr = substr($horarioDia->hora_inicio, 0, 5);
                    $horaFinStr = substr($horarioDia->hora_fin, 0, 5);
                }
            }

            $partesIni = explode(':', $horaInicioStr);
            $horaInicioMin = ((int)$partesIni[0] * 60) + (int)($partesIni[1] ?? 0);

            $partesFin = explode(':', $horaFinStr);
            $horaFinMin = ((int)$partesFin[0] * 60) + (int)($partesFin[1] ?? 0);

            $peluquerosData[] = [
                'id' => (int)$p->id,
                'nombre' => $p->nombre,
                'apellido' => $p->apellido,
                'telefono' => $p->telefono,
                'realizaServicios' => $realizaServicios,
                'trabajaHoy' => $trabajaHoy,
                'horaInicioMin' => $horaInicioMin,
                'horaFinMin' => $horaFinMin
            ];
        }

        // 5. Determinar disponibilidad por cada hora del establecimiento según duración
        $horasDisponibles = [];
        $horasOcupadas = [];
        $peluquerosPorHora = [];

        foreach($horasEstablecimiento as $horaStr) {
            $partesSlot = explode(':', $horaStr);
            $slotStartMin = ((int)$partesSlot[0] * 60) + (int)($partesSlot[1] ?? 0);
            $slotEndMin = $slotStartMin + $duracionTotalRequerida;

            $disponiblesEnEstaHora = [];

            foreach($peluquerosData as $pData) {
                if(!$pData['realizaServicios']) continue;
                if(!$pData['trabajaHoy']) continue;

                // El turno debe caber completo antes del fin de jornada del peluquero
                if($slotStartMin < $pData['horaInicioMin'] || $slotEndMin > $pData['horaFinMin']) continue;

                // Verificar colisión con turnos existentes del peluquero
                $tieneColision = false;
                $intervalosDelPeluquero = $intervalosOcupadosPorPeluquero[$pData['id']] ?? [];

                foreach($intervalosDelPeluquero as $intervalo) {
                    if(max($slotStartMin, $intervalo['start']) < min($slotEndMin, $intervalo['end'])) {
                        $tieneColision = true;
                        break;
                    }
                }

                if($tieneColision) continue;

                $disponiblesEnEstaHora[] = [
                    'id' => $pData['id'],
                    'nombre' => $pData['nombre'],
                    'apellido' => $pData['apellido'],
                    'telefono' => $pData['telefono']
                ];
            }

            $peluquerosPorHora[$horaStr] = $disponiblesEnEstaHora;

            if(count($disponiblesEnEstaHora) > 0) {
                $horasDisponibles[] = $horaStr;
            } else {
                $horasOcupadas[] = $horaStr;
            }
        }

        echo json_encode([
            'horasDisponibles' => $horasDisponibles,
            'horasOcupadas' => $horasOcupadas,
            'peluquerosPorHora' => $peluquerosPorHora,
            'duracionTotal' => $duracionTotalRequerida
        ]);
    }

    public static function guardar() {
        $usuario_id = $_POST['usuario_id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        // Si no viene usuario_id o está vacío (es un turno de invitado)
        if(empty($usuario_id)) {
            $partesNombre = explode(' ', $nombre, 2);
            $nombreP = !empty($partesNombre[0]) ? $partesNombre[0] : 'Invitado';
            $apellidoP = $partesNombre[1] ?? '';

            // Crear un registro específico para el invitado sin email
            $nuevoUsuario = new Usuario([
                'nombre' => $nombreP,
                'apellido' => $apellidoP,
                'telefono' => $telefono,
                'email' => '',
                'password' => '',
                'admin' => 0,
                'confirmado' => 1
            ]);

            $resUsuario = $nuevoUsuario->guardar();
            $usuario_id = $resUsuario['id'] ?? null;

            $_POST['usuario_id'] = $usuario_id;
        }

        // Almacena el turno en la base de datos y devuelve el id
        $turno = new Turno($_POST);
        $resultado = $turno->guardar();

        $id = $resultado['id'];

        // Almacena los servicios con el id del turno en la tabla intermedia
        $idServicios = explode(",", $_POST["servicios"]);

        foreach($idServicios as $idServicio) {
            $args = [
                'turno_id' => $id,
                'servicio_id' => $idServicio
            ];
            $turnoServicio = new TurnoServicio($args);
            $turnoServicio->guardar();
        }

        echo json_encode($resultado);
    }

    public static function cambiarEstado() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? '';

            $estadosValidos = ['reservado', 'completado', 'cancelado'];
            if(!in_array($estado, $estadosValidos) || !$id) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Datos no válidos']);
                return;
            }

            $turno = Turno::find($id);
            if(!$turno) {
                echo json_encode(['resultado' => false, 'mensaje' => 'Turno no encontrado']);
                return;
            }

            $turno->estado = $estado;
            $resultado = $turno->guardar();

            echo json_encode([
                'resultado' => (bool)$resultado,
                'estado' => $estado
            ]);
        }
    }

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $turno = Turno::find($id);
            $turno->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }
}
