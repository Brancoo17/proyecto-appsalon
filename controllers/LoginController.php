<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;
use Classes\Email;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        // Verificar si viene de recuperar password
        if(isset($_GET['resultado']) && $_GET['resultado'] === '1') {
            Usuario::setAlerta('exito', 'Password reestablecido correctamente');
        }

        $auth = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    // Verificar el password
                    $verificado = $usuario->comprobarPasswordAndVerificado($auth->password);
                    
                    if($verificado) {
                        // Autenticar al usuario
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionar al usuario
                        if($usuario->admin === 1) {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /turno');
                        }
                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                    $auth->email = '';
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas,
            'auth' => $auth
        ]);
    }

    public static function logout() {
        session_start();

        $_SESSION = [];

        header('Location: /');
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && $usuario->confirmado === 1) {
                    // Generar un Token único
                    $usuario->crearToken();
                    
                    // Guardar el usuario
                    $usuario->guardar();

                    // Enviar el Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Mostrar mensaje de éxito
                    Usuario::setAlerta('exito', 'Revisa tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router) {

        $alertas = [];
        $error = false;

        $token = s($_GET['token']);

        // Buscar el usuario por token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            // Validar que los passwords coincidan
            if($_POST['password2'] !== $_POST['password']) {
                Usuario::setAlerta('error', 'Los passwords no coinciden');
            }

            $alertas = Usuario::getAlertas();

            if(empty($alertas)) {

                $usuario->password = $password->password;
                
                // Hashear el password
                $usuario->hashPassword();

                $usuario->token = '';

                // Actualizar el Usuario
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /?resultado=1');
                }
            }
            
        }

        $alertas = Usuario::getAlertas();
        
        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;

        // Alertas vacías
        $alertas = [];

        // POST - Crear cuenta
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $usuario->sincronizar($_POST);

            // Validar que no haya campos vacios
            $alertas = $usuario->validarNuevaCuenta();

            // Validar que los passwords coincidan
            $password2 = $_POST['password2'] ?? '';
            if($usuario->password !== $password2) {
                $alertas['error'][] = 'Los passwords no coinciden';
            }

            // Revisar que no haya alertas
            if(empty($alertas)) {
                // Verificar que el usuario no esté registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password
                    $usuario->hashPassword();

                    // Generar un Token único
                    $usuario->crearToken();

                    // Enviar el Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    // Crear el Usuario
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                    
                }
                
            } 
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function confirmar(Router $router) {

        $alertas = [];

        $token = s($_GET['token']);

        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            // Mostar mensaje de error
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            //Modificar a usuario confirmado
            $usuario->confirmado = 1;
            $usuario->token = '';
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
        }
        
        // Obtener alertas
        $alertas = Usuario::getAlertas();
        
        // Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {

        $router->render('auth/mensaje', []);
    }

}
