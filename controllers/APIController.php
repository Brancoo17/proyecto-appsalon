<?php 

namespace Controllers;

use Model\Servicio;
use Model\Turno;
use Model\Peluquero;
use Model\TurnoServicio;
use Model\Usuario;
use Model\HorarioBloqueado;

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
        $turnoIdExcluir = isset($_GET['turno_id']) && is_numeric($_GET['turno_id']) ? (int)$_GET['turno_id'] : null;

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

        // 1. Generar intervalos de 15 minutos entre las 10:00 y las 21:00
        $horasEstablecimiento = [];
        $inicioMin = 10 * 60; // 10:00 -> 600 min
        $finMin = 21 * 60;    // 21:00 -> 1260 min
        for ($m = $inicioMin; $m <= $finMin; $m += 15) {
            $h = floor($m / 60);
            $min = $m % 60;
            $horasEstablecimiento[] = sprintf('%02d:%02d', $h, $min);
        }

        // 2. Obtener todos los peluqueros
        $peluqueros = Peluquero::all();

        // 3. Obtener turnos ya agendados en esta fecha con sus duraciones reales
        $queryTurnos = "SELECT turnos.id, turnos.hora, turnos.peluquero_id, ";
        $queryTurnos .= "COALESCE(SUM(servicios.duracion), 30) as duracion ";
        $queryTurnos .= "FROM turnos ";
        $queryTurnos .= "LEFT JOIN turnosservicios ON turnosservicios.turno_id = turnos.id ";
        $queryTurnos .= "LEFT JOIN servicios ON servicios.id = turnosservicios.servicio_id ";
        $queryTurnos .= "WHERE turnos.fecha = '{$fecha}' AND (turnos.estado != 'cancelado' OR turnos.estado IS NULL) ";
        if ($turnoIdExcluir) {
            $queryTurnos .= "AND turnos.id != {$turnoIdExcluir} ";
        }
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

        // Obtener horarios bloqueados en esta fecha
        $queryBloqueos = "SELECT peluquero_id, hora_inicio, hora_fin FROM horarios_bloqueados WHERE fecha = '{$fecha}'";
        $resBloqueos = $db->query($queryBloqueos);
        if ($resBloqueos) {
            while ($rowB = $resBloqueos->fetch_assoc()) {
                $peluqueroBloqueoId = $rowB['peluquero_id'] !== null ? (int)$rowB['peluquero_id'] : null;
                $iniPartes = explode(':', substr($rowB['hora_inicio'], 0, 5));
                $finPartes = explode(':', substr($rowB['hora_fin'], 0, 5));
                $bStartMin = ((int)$iniPartes[0] * 60) + (int)($iniPartes[1] ?? 0);
                $bEndMin = ((int)$finPartes[0] * 60) + (int)($finPartes[1] ?? 0);

                if ($peluqueroBloqueoId) {
                    $intervalosOcupadosPorPeluquero[$peluqueroBloqueoId][] = [
                        'start' => $bStartMin,
                        'end' => $bEndMin
                    ];
                } else {
                    // Bloqueo global: aplica a todos los peluqueros
                    foreach($peluqueros as $p) {
                        $pId = (int)$p->id;
                        $intervalosOcupadosPorPeluquero[$pId][] = [
                            'start' => $bStartMin,
                            'end' => $bEndMin
                        ];
                    }
                }
            }
            $resBloqueos->free();
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

        $esHoy = ($fecha === date('Y-m-d'));
        $ahoraMin = ((int)date('H') * 60) + (int)date('i');

        foreach($horasEstablecimiento as $horaStr) {
            $partesSlot = explode(':', $horaStr);
            $slotStartMin = ((int)$partesSlot[0] * 60) + (int)($partesSlot[1] ?? 0);
            $slotEndMin = $slotStartMin + $duracionTotalRequerida;

            // Si la fecha es hoy y la hora del slot ya pasó o es la actual, marcar como no disponible
            if($esHoy && $slotStartMin <= $ahoraMin) {
                $horasOcupadas[] = $horaStr;
                $peluquerosPorHora[$horaStr] = [];
                continue;
            }

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
            'todasLasHoras' => $horasEstablecimiento,
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

        $peluquero_id_post = (int)($_POST['peluquero_id'] ?? 0);
        $fecha_post = $_POST['fecha'] ?? '';
        $hora_post = substr($_POST['hora'] ?? '', 0, 5);

        // Validar si colisiona con horario bloqueado
        if ($peluquero_id_post && $fecha_post && $hora_post) {
            $hParts = explode(':', $hora_post);
            $slotStart = ((int)$hParts[0] * 60) + (int)($hParts[1] ?? 0);
            $idServiciosArr = !empty($_POST["servicios"]) ? array_map('intval', explode(",", $_POST["servicios"])) : [];
            $duracionTurno = 30;
            if(!empty($idServiciosArr)) {
                $idsSani = implode(',', $idServiciosArr);
                $resDur = Servicio::getDB()->query("SELECT SUM(COALESCE(duracion, 30)) as total FROM servicios WHERE id IN ({$idsSani})");
                if($resDur && $rD = $resDur->fetch_assoc()) {
                    $duracionTurno = max(30, (int)($rD['total'] ?? 30));
                    $resDur->free();
                }
            }
            $slotEnd = $slotStart + $duracionTurno;

            $db = Turno::getDB();
            $resB = $db->query("SELECT hora_inicio, hora_fin, motivo FROM horarios_bloqueados WHERE fecha = '{$fecha_post}' AND (peluquero_id = {$peluquero_id_post} OR peluquero_id IS NULL)");
            if ($resB) {
                while($bRow = $resB->fetch_assoc()) {
                    $bIni = explode(':', substr($bRow['hora_inicio'], 0, 5));
                    $bFin = explode(':', substr($bRow['hora_fin'], 0, 5));
                    $bStart = ((int)$bIni[0] * 60) + (int)($bIni[1] ?? 0);
                    $bEnd = ((int)$bFin[0] * 60) + (int)($bFin[1] ?? 0);

                    if (max($slotStart, $bStart) < min($slotEnd, $bEnd)) {
                        $resB->free();
                        echo json_encode(['resultado' => false, 'mensaje' => 'El horario seleccionado coincide con un horario bloqueado (' . ($bRow['motivo'] ?: 'No disponible') . ').']);
                        return;
                    }
                }
                $resB->free();
            }
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

    public static function actualizarTurno() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['login'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No autenticado']);
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID de turno no válido']);
            return;
        }

        $turno = Turno::find($id);
        if(!$turno) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Turno no encontrado']);
            return;
        }

        $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'];
        $esPeluquero = isset($_SESSION['peluquero']) && $_SESSION['peluquero'];
        $esCliente = !$esAdmin && !$esPeluquero;

        $fechaOriginal = $turno->fecha;
        $redirectUrl = '/admin?resultado=3';
        if ($esAdmin) {
            $redirectUrl = '/admin?resultado=3&fecha=' . ($_POST['fecha'] ?? $fechaOriginal);
        } elseif ($esPeluquero) {
            $redirectUrl = '/peluquero?resultado=3&fecha=' . ($_POST['fecha'] ?? $fechaOriginal);
            if ((int)$turno->peluquero_id !== (int)$_SESSION['id']) {
                echo json_encode(['resultado' => false, 'mensaje' => 'No tienes permiso para modificar este turno']);
                return;
            }
        } else {
            $redirectUrl = '/usuario?resultado=3&tab=turnos';
            if ((int)$turno->usuario_id !== (int)$_SESSION['id'] || strtolower($turno->estado) !== 'reservado') {
                echo json_encode(['resultado' => false, 'mensaje' => 'No puedes modificar un turno completado o cancelado']);
                return;
            }
        }

        $fecha = filter_var($_POST['fecha'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $hora = filter_var($_POST['hora'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $peluquero_id = filter_var($_POST['peluquero_id'] ?? null, FILTER_VALIDATE_INT);
        $metodo_pago = filter_var($_POST['metodo_pago'] ?? 'efectivo', FILTER_SANITIZE_SPECIAL_CHARS);
        $serviciosStr = filter_var($_POST['servicios'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$fecha || !$hora || !$peluquero_id || empty($serviciosStr)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Faltan datos requeridos (fecha, hora, peluquero o servicios)']);
            return;
        }

        $peluquero = Peluquero::find($peluquero_id);
        if (!$peluquero) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Profesional no encontrado']);
            return;
        }

        // Validar día domingo
        $diaSemana = (int)date('N', strtotime($fecha));
        if ($diaSemana === 7) {
            echo json_encode(['resultado' => false, 'mensaje' => 'El día domingo la barbería se encuentra cerrada']);
            return;
        }

        // Si es cliente, validar que la fecha y hora no hayan pasado
        if ($esCliente) {
            $timestampTurno = strtotime("{$fecha} {$hora}");
            if ($timestampTurno < time()) {
                echo json_encode(['resultado' => false, 'mensaje' => 'No puedes agendar en una fecha u hora pasada']);
                return;
            }
        }

        $serviciosIds = array_map('intval', explode(',', $serviciosStr));
        if (empty($serviciosIds)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Debes seleccionar al menos un servicio']);
            return;
        }

        // Calcular duración total requerida
        $idsSanitizados = implode(',', $serviciosIds);
        $queryDuracion = "SELECT SUM(COALESCE(duracion, 30)) as total_minutos FROM servicios WHERE id IN ({$idsSanitizados})";
        $db = Servicio::getDB();
        $res = $db->query($queryDuracion);
        $duracionTotalRequerida = 30;
        if ($res) {
            $row = $res->fetch_assoc();
            $duracionTotalRequerida = max(30, (int)($row['total_minutos'] ?? 30));
            $res->free();
        }

        $horaPartes = explode(':', substr($hora, 0, 5));
        $slotStartMin = ((int)$horaPartes[0] * 60) + (int)($horaPartes[1] ?? 0);
        $slotEndMin = $slotStartMin + $duracionTotalRequerida;

        // Comprobar colisión con otros turnos del mismo peluquero en esa fecha (excluyendo este turno)
        $queryColision = "SELECT turnos.id, turnos.hora, COALESCE(SUM(servicios.duracion), 30) as duracion ";
        $queryColision .= "FROM turnos ";
        $queryColision .= "LEFT JOIN turnosservicios ON turnosservicios.turno_id = turnos.id ";
        $queryColision .= "LEFT JOIN servicios ON servicios.id = turnosservicios.servicio_id ";
        $queryColision .= "WHERE turnos.fecha = '{$fecha}' AND turnos.peluquero_id = {$peluquero_id} ";
        $queryColision .= "AND turnos.id != {$id} ";
        $queryColision .= "AND (turnos.estado != 'cancelado' OR turnos.estado IS NULL) ";
        $queryColision .= "GROUP BY turnos.id, turnos.hora";

        $resColision = $db->query($queryColision);
        if ($resColision) {
            while ($row = $resColision->fetch_assoc()) {
                $hParts = explode(':', substr($row['hora'], 0, 5));
                $otroStartMin = ((int)$hParts[0] * 60) + (int)($hParts[1] ?? 0);
                $otroDurMin = max(30, (int)($row['duracion'] ?? 30));
                $otroEndMin = $otroStartMin + $otroDurMin;

                if (max($slotStartMin, $otroStartMin) < min($slotEndMin, $otroEndMin)) {
                    $resColision->free();
                    echo json_encode([
                        'resultado' => false,
                        'mensaje' => 'El horario seleccionado colisiona con otro turno de este profesional.'
                    ]);
                    return;
                }
            }
            $resColision->free();
        }

        // Comprobar colisión con horarios bloqueados
        $queryColisionBloqueo = "SELECT hora_inicio, hora_fin, motivo FROM horarios_bloqueados WHERE fecha = '{$fecha}' AND (peluquero_id = {$peluquero_id} OR peluquero_id IS NULL)";
        $resColBloqueo = $db->query($queryColisionBloqueo);
        if ($resColBloqueo) {
            while ($bRow = $resColBloqueo->fetch_assoc()) {
                $bIni = explode(':', substr($bRow['hora_inicio'], 0, 5));
                $bFin = explode(':', substr($bRow['hora_fin'], 0, 5));
                $bStart = ((int)$bIni[0] * 60) + (int)($bIni[1] ?? 0);
                $bEnd = ((int)$bFin[0] * 60) + (int)($bFin[1] ?? 0);

                if (max($slotStartMin, $bStart) < min($slotEndMin, $bEnd)) {
                    $resColBloqueo->free();
                    echo json_encode([
                        'resultado' => false,
                        'mensaje' => 'El horario seleccionado coincide con un horario bloqueado (' . ($bRow['motivo'] ?: 'No disponible') . ').'
                    ]);
                    return;
                }
            }
            $resColBloqueo->free();
        }

        // Actualizar datos del turno
        $turno->fecha = $fecha;
        $turno->hora = $hora;
        $turno->peluquero_id = $peluquero_id;
        $turno->metodo_pago = $metodo_pago;
        $turno->guardar();

        // Si el cliente modificó su nombre o teléfono en el formulario y era usuario sin email
        if (!empty($_POST['nombre']) && $turno->usuario_id) {
            $clienteUsuario = Usuario::find($turno->usuario_id);
            if ($clienteUsuario && empty($clienteUsuario->email)) {
                $partesNombre = explode(' ', trim($_POST['nombre']), 2);
                $clienteUsuario->nombre = $partesNombre[0] ?? 'Invitado';
                $clienteUsuario->apellido = $partesNombre[1] ?? '';
                if (!empty($_POST['telefono'])) {
                    $clienteUsuario->telefono = trim($_POST['telefono']);
                }
                $clienteUsuario->guardar();
            }
        }

        // Actualizar servicios (reemplazo en tabla intermedia)
        $db->query("DELETE FROM turnosservicios WHERE turno_id = {$id}");
        foreach ($serviciosIds as $idServicio) {
            $args = [
                'turno_id' => $id,
                'servicio_id' => $idServicio
            ];
            $turnoServicio = new TurnoServicio($args);
            $turnoServicio->guardar();
        }

        echo json_encode([
            'resultado' => true,
            'mensaje' => 'Turno modificado correctamente',
            'redirect' => $redirectUrl
        ]);
    }

    public static function crearBloqueo() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['login'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No autenticado']);
            return;
        }

        $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'];
        $esPeluquero = isset($_SESSION['peluquero']) && $_SESSION['peluquero'];

        if (!$esAdmin && !$esPeluquero) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No tienes permisos para realizar bloqueos de horario']);
            return;
        }

        $fecha = filter_var($_POST['fecha'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $hora_inicio = filter_var($_POST['hora_inicio'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $hora_fin = filter_var($_POST['hora_fin'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $motivo = filter_var($_POST['motivo'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $motivo = trim($motivo) !== '' ? trim($motivo) : null;

        if (!$fecha || !$hora_inicio || !$hora_fin) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Fecha, hora de inicio y hora de fin son requeridas']);
            return;
        }

        $hora_inicio = substr($hora_inicio, 0, 5);
        $hora_fin = substr($hora_fin, 0, 5);

        $partesIni = explode(':', $hora_inicio);
        $partesFin = explode(':', $hora_fin);
        $startMin = ((int)$partesIni[0] * 60) + (int)($partesIni[1] ?? 0);
        $endMin = ((int)$partesFin[0] * 60) + (int)($partesFin[1] ?? 0);

        if ($startMin >= $endMin) {
            echo json_encode(['resultado' => false, 'mensaje' => 'La hora de fin debe ser posterior a la hora de inicio']);
            return;
        }

        // Determinar peluquero_id
        $peluquero_id = null;
        if ($esPeluquero) {
            $peluquero_id = (int)$_SESSION['id'];
        } else {
            $pId = $_POST['peluquero_id'] ?? '';
            $peluquero_id = (!empty($pId) && is_numeric($pId) && (int)$pId > 0) ? (int)$pId : null;
        }

        $db = Turno::getDB();

        // 1. Verificar si existen turnos activos reservados en este rango horario
        $queryColisionTurno = "SELECT turnos.id, turnos.hora, COALESCE(SUM(servicios.duracion), 30) as duracion, ";
        $queryColisionTurno .= "TRIM(CONCAT(COALESCE(usuarios.nombre, 'Invitado'), ' ', COALESCE(usuarios.apellido, ''))) as cliente ";
        $queryColisionTurno .= "FROM turnos ";
        $queryColisionTurno .= "LEFT JOIN usuarios ON usuarios.id = turnos.usuario_id ";
        $queryColisionTurno .= "LEFT JOIN turnosservicios ON turnosservicios.turno_id = turnos.id ";
        $queryColisionTurno .= "LEFT JOIN servicios ON servicios.id = turnosservicios.servicio_id ";
        $queryColisionTurno .= "WHERE turnos.fecha = '{$fecha}' AND (turnos.estado = 'reservado') ";
        if ($peluquero_id) {
            $queryColisionTurno .= "AND turnos.peluquero_id = {$peluquero_id} ";
        }
        $queryColisionTurno .= "GROUP BY turnos.id, turnos.hora, usuarios.nombre, usuarios.apellido";

        $resCol = $db->query($queryColisionTurno);
        if ($resCol) {
            while ($tRow = $resCol->fetch_assoc()) {
                $tHora = explode(':', substr($tRow['hora'], 0, 5));
                $tStart = ((int)$tHora[0] * 60) + (int)($tHora[1] ?? 0);
                $tDur = max(30, (int)($tRow['duracion'] ?? 30));
                $tEnd = $tStart + $tDur;

                if (max($startMin, $tStart) < min($endMin, $tEnd)) {
                    $resCol->free();
                    $clienteNombre = $tRow['cliente'] ?: 'Cliente';
                    $horaTurno = substr($tRow['hora'], 0, 5);
                    echo json_encode([
                        'resultado' => false,
                        'mensaje' => "Existe un turno reservado en ese horario ({$clienteNombre} a las {$horaTurno} hs). Debes cancelarlo o reprogramarlo antes de bloquear."
                    ]);
                    return;
                }
            }
            $resCol->free();
        }

        // 2. Guardar el bloqueo
        $bloqueo = new HorarioBloqueado([
            'peluquero_id' => $peluquero_id,
            'fecha' => $fecha,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin,
            'motivo' => $motivo
        ]);

        $resGuardar = $bloqueo->guardar();

        echo json_encode([
            'resultado' => true,
            'mensaje' => 'Horario bloqueado exitosamente',
            'id' => $resGuardar['id'] ?? null
        ]);
    }

    public static function eliminarBloqueo() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['resultado' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['login'])) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No autenticado']);
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['resultado' => false, 'mensaje' => 'ID no válido']);
            return;
        }

        $bloqueo = HorarioBloqueado::find($id);
        if (!$bloqueo) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Bloqueo no encontrado']);
            return;
        }

        $esAdmin = isset($_SESSION['admin']) && $_SESSION['admin'];
        $esPeluquero = isset($_SESSION['peluquero']) && $_SESSION['peluquero'];

        if (!$esAdmin) {
            if (!$esPeluquero || (int)$bloqueo->peluquero_id !== (int)$_SESSION['id']) {
                echo json_encode(['resultado' => false, 'mensaje' => 'No tienes permiso para eliminar este bloqueo']);
                return;
            }
        }

        $bloqueo->eliminar();

        echo json_encode([
            'resultado' => true,
            'mensaje' => 'Horario desbloqueado correctamente'
        ]);
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
