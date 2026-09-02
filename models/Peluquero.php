<?php

namespace Model;

class Peluquero extends ActiveRecord {
    protected static string $tabla = 'peluqueros';
    protected static array $columnasDB = ['id', 'nombre', 'apellido', 'email', 'password', 'telefono'];

    public ?int $id;
    public string $nombre;
    public string $apellido;
    public string $email;
    public string $password;
    public string $telefono;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
    }

    // Validación para cuando el Admin crea un nuevo peluquero
    public function validar() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del peluquero es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El Apellido del peluquero es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El Password es obligatorio';
        } elseif(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Validación para cuando se actualiza un peluquero existente (password opcional)
    public function validarActualizar() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del peluquero es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El Apellido del peluquero es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if(!empty($this->password) && strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Hashear password antes de guardar
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Comprobar password para el Login
    public function comprobarPassword(string $password) : bool {
        $resultado = password_verify($password, $this->password);
        if(!$resultado) {
            self::$alertas['error'][] = 'El password es incorrecto';
            return false;
        }
        return true;
    }

    // Obtener IDs de servicios asignados a este peluquero
    public function getServiciosIds() : array {
        if(!$this->id) return [];
        $query = "SELECT servicio_id FROM peluqueros_servicios WHERE peluquero_id = {$this->id}";
        $resultado = self::$db->query($query);
        $ids = [];
        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $ids[] = (int)$row['servicio_id'];
            }
            $resultado->free();
        }
        return $ids;
    }

    // Obtener los objetos Servicio asignados a este peluquero
    public function getServicios() : array {
        if(!$this->id) return [];
        $query = "SELECT servicios.* FROM servicios ";
        $query .= "INNER JOIN peluqueros_servicios ON peluqueros_servicios.servicio_id = servicios.id ";
        $query .= "WHERE peluqueros_servicios.peluquero_id = {$this->id}";
        return Servicio::consultarSQL($query);
    }

    // Sincronizar servicios asignados
    public function sincronizarServicios(array $serviciosIds = []) : void {
        if(!$this->id) return;
        $queryDelete = "DELETE FROM peluqueros_servicios WHERE peluquero_id = {$this->id}";
        self::$db->query($queryDelete);

        foreach($serviciosIds as $servicioId) {
            $servicioId = (int)$servicioId;
            if($servicioId > 0) {
                $ps = new PeluqueroServicio([
                    'peluquero_id' => $this->id,
                    'servicio_id' => $servicioId
                ]);
                $ps->guardar();
            }
        }
    }

    // Obtener horarios semanales indexados por dia_semana (1 a 6)
    public function getHorarios() : array {
        if(!$this->id) return [];
        $query = "SELECT * FROM peluqueros_horarios WHERE peluquero_id = {$this->id} ORDER BY dia_semana ASC";
        $horariosObj = PeluqueroHorario::consultarSQL($query);
        $horarios = [];
        foreach($horariosObj as $h) {
            $horarios[$h->dia_semana] = $h;
        }
        return $horarios;
    }

    // Sincronizar horarios semanales (1=Lunes a 6=Sábado)
    public function sincronizarHorarios(array $horariosData = []) : void {
        if(!$this->id) return;

        $queryDelete = "DELETE FROM peluqueros_horarios WHERE peluquero_id = {$this->id}";
        self::$db->query($queryDelete);

        for($dia = 1; $dia <= 6; $dia++) {
            $diaData = $horariosData[$dia] ?? [];
            $activo = isset($diaData['activo']) ? 1 : 0;
            $horaInicio = !empty($diaData['hora_inicio']) ? $diaData['hora_inicio'] : '10:00:00';
            $horaFin = !empty($diaData['hora_fin']) ? $diaData['hora_fin'] : '20:00:00';

            $ph = new PeluqueroHorario([
                'peluquero_id' => $this->id,
                'dia_semana' => $dia,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'activo' => $activo
            ]);
            $ph->guardar();
        }
    }
}
