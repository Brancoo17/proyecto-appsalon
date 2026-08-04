<?php

namespace Model;

class Turno extends ActiveRecord {
    // Base de datos
    protected static string $tabla = 'turnos';
    protected static array $columnasDB = ['id', 'fecha', 'hora', 'usuario_id', 'peluquero_id'];

    public ?int $id;
    public string $fecha;
    public string $hora;
    public ?int $usuario_id;
    public ?int $peluquero_id;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->peluquero_id = $args['peluquero_id'] ?? null;
    }
}
