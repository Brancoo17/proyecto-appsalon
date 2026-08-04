<?php

namespace Model;

class AdminTurno extends ActiveRecord {
    protected static string $tabla = 'turnosServicios';
    protected static array $columnasDB = ['id', 'hora', 'cliente', 'email', 'telefono', 'servicio', 'precio', 'peluquero'];

    public ?int $id;
    public string $hora;
    public string $cliente;
    public string $email;
    public string $telefono;
    public string $servicio;
    public string $precio;
    public string $peluquero;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->hora = $args['hora'] ?? '';
        $this->cliente = $args['cliente'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->servicio = $args['servicio'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->peluquero = $args['peluquero'] ?? '';
    }
}