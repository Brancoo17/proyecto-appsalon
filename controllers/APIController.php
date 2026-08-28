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

        // Obtener el peluquero_id de los parámetros de la URL (?peluquero_id=X)
        $peluquero_id = $_GET['peluquero_id'] ?? '';
        
        // Sanitizar la fecha y peluquero_id para evitar inyección SQL
        $fecha = filter_var($fecha, FILTER_SANITIZE_SPECIAL_CHARS);
        $peluquero_id = filter_var($peluquero_id, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$fecha || !$peluquero_id) {
            echo json_encode([]);
            return;
        }

        // Obtener solo las citas de este peluquero en este día específico
        $query = "SELECT hora FROM turnos WHERE fecha = '{$fecha}' AND peluquero_id = '{$peluquero_id}'";
        $turnos = Turno::consultarSQL($query);
        
        // Mapear el resultado para obtener solo las horas en un arreglo plano de strings
        $horasOcupadas = array_map(function($turno) {
            // Extraer HH:MM eliminando los segundos de la base de datos (ej. "10:00:00" -> "10:00")
            return substr($turno->hora, 0, 5);
        }, $turnos);
        // Devolver la respuesta en formato JSON
        echo json_encode($horasOcupadas);
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

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $turno = Turno::find($id);
            $turno->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }
}
