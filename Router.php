<?php

namespace MVC;

class Router {
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn) {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn) {
        $this->postRoutes[$url] = $fn;
    }

    public function comprobarRutas() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        if ($currentUrl !== '/' && str_ends_with($currentUrl, '/')) {
            $currentUrl = rtrim($currentUrl, '/');
        }
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }


        if ( $fn ) {
            try {
                // Call user fn va a llamar una función cuando no sabemos cual sera
                call_user_func($fn, $this); // This es para pasar argumentos
            } catch (\Throwable $e) {
                echo "<div style='font-family: system-ui, sans-serif; background: #1e1e24; color: #ff6b6b; padding: 25px; border-radius: 12px; margin: 30px auto; max-width: 900px; box-shadow: 0 8px 30px rgba(0,0,0,0.5);'>";
                echo "<h2 style='color: #ff8787; margin-top: 0;'>⚠️ Error en la Ruta: " . htmlspecialchars($currentUrl) . "</h2>";
                echo "<p style='color: #ffffff; font-size: 16px;'><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p style='color: #ced4da;'><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . " (Línea: <strong>" . $e->getLine() . "</strong>)</p>";
                echo "<h3 style='color: #adb5bd; margin-top: 20px;'>Pila de Ejecución (Trace):</h3>";
                echo "<pre style='background: #121214; color: #e9ecef; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                echo "</div>";
                exit;
            }
        } else {
            echo "Página No Encontrada o Ruta no válida";
        }
    }

    public function render($view, $datos = []) {

        // Leer lo que le pasamos  a la vista
        foreach ($datos as $key => $value) {
            $$key = $value;  // Doble signo de dolar significa: variable variable, básicamente nuestra variable sigue siendo la original, pero al asignarla a otra no la reescribe, mantiene su valor, de esta forma el nombre de la variable se asigna dinamicamente
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // entonces incluimos la vista en el layout
        include_once __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean(); // Limpia el Buffer
        include_once __DIR__ . '/views/layout.php';
    }
}
