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

        // Devolver la respuesta en formato JSON
        echo json_encode($resultado);
    }

    public static function guardar() {
        $usuario_id = $_POST['usuario_id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        // Si no viene usuario_id o está vacío (es un turno de invitado)
        if(empty($usuario_id)) {
            $usuarioExistente = null;

            // Buscar si ya existe un cliente con ese teléfono
            if(!empty($telefono)) {
                $usuarioExistente = Usuario::where('telefono', $telefono);
            }

            if($usuarioExistente) {
                $usuario_id = $usuarioExistente->id;
            } else {
                // Crear un registro rápido en la tabla usuarios para el invitado
                $partesNombre = explode(' ', trim($nombre), 2);
                $nombreP = $partesNombre[0] ?? 'Invitado';
                $apellidoP = $partesNombre[1] ?? '';

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
            }

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
