<?php

namespace Model;

class PeluqueroServicio extends ActiveRecord {
    protected static string $tabla = 'peluqueros_servicios';
    protected static array $columnasDB = ['id', 'peluquero_id', 'servicio_id'];

    public ?int $id;
    public ?int $peluquero_id;
    public ?int $servicio_id;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->peluquero_id = $args['peluquero_id'] ?? null;
        $this->servicio_id = $args['servicio_id'] ?? null;
    }
}
