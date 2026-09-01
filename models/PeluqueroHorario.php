<?php

namespace Model;

class PeluqueroHorario extends ActiveRecord {
    protected static string $tabla = 'peluqueros_horarios';
    protected static array $columnasDB = ['id', 'peluquero_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo'];

    public ?int $id;
    public ?int $peluquero_id;
    public int $dia_semana;
    public string $hora_inicio;
    public string $hora_fin;
    public int $activo;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->peluquero_id = $args['peluquero_id'] ?? null;
        $this->dia_semana = (int)($args['dia_semana'] ?? 1);
        $this->hora_inicio = $args['hora_inicio'] ?? '10:00:00';
        $this->hora_fin = $args['hora_fin'] ?? '20:00:00';
        $this->activo = isset($args['activo']) ? (int)$args['activo'] : 1;
    }
}
