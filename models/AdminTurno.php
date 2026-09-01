<?php

namespace Model;

class AdminTurno extends ActiveRecord {
    protected static string $tabla = 'turnosServicios';
    protected static array $columnasDB = ['id', 'hora', 'cliente', 'email', 'telefono', 'servicio', 'precio', 'peluquero', 'estado', 'metodo_pago'];

    public ?int $id;
    public string $hora;
    public string $cliente;
    public string $email;
    public string $telefono;
    public string $servicio;
    public string $precio;
    public string $peluquero;
    public string $estado;
    public string $metodo_pago;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->hora = $args['hora'] ?? '';
        $this->cliente = $args['cliente'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->servicio = $args['servicio'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->peluquero = $args['peluquero'] ?? '';
        $this->estado = $args['estado'] ?? '';
        $this->metodo_pago = $args['metodo_pago'] ?? '';
    }
}