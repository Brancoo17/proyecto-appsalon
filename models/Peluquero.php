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
}
