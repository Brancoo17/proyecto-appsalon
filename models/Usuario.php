<?php

namespace Model;

class Usuario extends ActiveRecord {

    // Base de datos
    protected static string $tabla = 'usuarios';
    protected static array $columnasDB = ['id', 'nombre', 'apellido', 'email', 'password', 'telefono', 'admin', 'confirmado', 'token'];

    // Atributos
    public ?int $id;
    public string $nombre;
    public string $apellido;
    public string $email;
    public string $password;
    public string $telefono;
    public ?int $admin;
    public ?int $confirmado;
    public string $token;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->admin = $args['admin'] ?? 0;
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->token = $args['token'] ?? '';
    }

    // Validación del formulario para nueva cuenta
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->telefono) {
            self::$alertas['error'][] = 'El teléfono es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Validar Login
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        return self::$alertas;
    }

    // Validar Email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        return self::$alertas;
    }

    // Validar Password
    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Revisa si el usuario ya existe
    public function existeUsuario() {
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '" . $this->email . "' LIMIT 1";

        $resultado = self::$db->query($query);
        
        if($resultado->num_rows) {
            self::$alertas['error'][] = 'El usuario ya está registrado';
        }

        return $resultado;
    }

    // Hashear el password
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Generar un Token único
    public function crearToken() {
        $this->token = uniqid();
    }

    // Comprueba que el password sea correcto y que la cuenta esté confirmada
    public function comprobarPasswordAndVerificado(string $password) {

        $resultado = password_verify($password, $this->password);

        if(!$resultado) {
            self::$alertas['error'][] = 'Password incorrecto';
            return false;
        }
        
        if(!$this->confirmado) {
            self::$alertas['error'][] = 'Cuenta no confirmada';
            return false;
        }

        return true;
    }
}