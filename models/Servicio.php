<?php

namespace Model;

use Override;

class Servicio extends ActiveRecord {
    // Base de Datos
    protected static string $tabla = 'servicios';
    protected static array $columnasDB = ['id', 'nombre', 'precio', 'duracion'];

    public ?int $id;
    public string $nombre;
    public string $precio;
    public ?int $duracion;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->duracion = isset($args['duracion']) ? (int)$args['duracion'] : 30;
    }

    #[Override]
    public function validar() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'Nombre del servicio es requerido';
        }

        if(strlen($this->nombre) < 3) {
            self::$alertas['error'][] = 'El nombre del servicio debe tener al menos 3 caracteres';
        }

        if(!$this->precio) {
            self::$alertas['error'][] = 'Precio del servicio es requerido';
        }

        if(!is_numeric($this->precio)) {
            self::$alertas['error'][] = 'El precio debe ser un número';
        }

        if(!$this->duracion || !is_numeric($this->duracion) || $this->duracion <= 0) {
            self::$alertas['error'][] = 'La duración debe ser un número mayor a 0 (minutos)';
        }

        return self::$alertas;
    }
}