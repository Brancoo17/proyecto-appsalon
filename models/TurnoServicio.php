<?php

namespace Model;

class TurnoServicio extends ActiveRecord {
    protected static string $tabla = 'turnosservicios';
    protected static array $columnasDB = ['id', 'turno_id', 'servicio_id'];

    public ?int $id;
    public ?int $turno_id;
    public ?int $servicio_id;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->turno_id = $args['turno_id'] ?? null;
        $this->servicio_id = $args['servicio_id'] ?? null;
    }
}
