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
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['login'])) {
        header('Location: /login');
        exit;
    }
}

function isAdmin() : void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        header('Location: /');
        exit;
    }
}

// Función que revisa que el peluquero esté autenticado
function isPeluquero() : void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['peluquero']) || !$_SESSION['peluquero']) {
        header('Location: /');
        exit;
    }
}

function esUltimo(string $actual, string $proximo) : bool {

    if($actual !== $proximo) {
        return true;
    }

    return false;
}