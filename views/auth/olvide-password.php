<h1 class="nombre-pagina">Olvidé Password</h1>
<p class="descripcion-pagina">Ingresa tu email para restablecer la contraseña</p>

<?php
    include_once __DIR__ . "/../templates/alertas.php";
?>

<form action="/olvide" class="formulario" method="POST">
    <div class="campo">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Tu Email">
    </div>

    <input type="submit" value="Enviar Instrucciones" class="boton">
</form>

<div class="acciones">
    <a href="/">¿Ya tienes cuenta? Iniciar Sesión</a>
    <a href="/crear-cuenta">¿No tienes cuenta? Crear una</a>
</div>