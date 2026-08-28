<?php
/** @var bool $error */
?>

<h1 class="nombre-pagina">Reestablecer Password</h1>
<p class="descripcion-pagina">Coloca tu nuevo password a continuación</p>

<?php
    include_once __DIR__ . "/../templates/alertas.php";
?>

<?php if($error) return null; ?>

<form class="formulario" method="POST">
    <div class="campo">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Tu Nuevo Password">
    </div>

    <div class="campo">
        <label for="password2">Confirmar Password:</label>
        <input type="password" id="password2" name="password2" placeholder="Confirma tu Password">
    </div>

    <input type="submit" class="boton" value="Guardar Nuevo Password">
</form>

<div class="acciones">
    <a href="/login">¿Ya tienes cuenta? Iniciar Sesión</a>
    <a href="/crear-cuenta">¿No tienes cuenta? Crea una</a>
</div>
