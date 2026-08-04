<?php

namespace Model;

class Peluquero extends ActiveRecord {
    protected static string $tabla = 'peluqueros';
    protected static array $columnasDB = ['id', 'nombre', 'apellido'];

    public ?int $id;
    public string $nombre;
    public string $apellido;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
    }
}
