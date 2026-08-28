<?php

function debuguear(mixed $variable) : void {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s(string $html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

// Función que revisa que el usuario esté autenticado
function isAuth() : void {
    if(!isset($_SESSION['login'])) {
        header('Location: /login');
    }
}

function isAdmin() : void {
    if(!isset($_SESSION['admin'])) {
        header('Location: /');
    }
}

// Función que revisa que el peluquero esté autenticado
function isPeluquero() : void {
    if(!isset($_SESSION['peluquero'])) {
        header('Location: /');
    }
}

function esUltimo(string $actual, string $proximo) : bool {

    if($actual !== $proximo) {
        return true;
    }

    return false;
}