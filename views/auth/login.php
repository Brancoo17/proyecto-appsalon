<?php
/** @var Model\Usuario $auth */
?>

<a href="/" style="text-decoration: none; flex-direction: flex-start; padding: 1rem; margin-top: 4rem;">
    <i class="fa-solid fa-house" style="color: #fff;"></i>
</a>

<h1 class="nombre-pagina">Iniciar Sesión</h1>
<p class="descripcion-pagina">Inicia Sesión con tu cuenta</p>

<?php
    include_once __DIR__ . "/../templates/alertas.php";
?>

<form action="/login" method="POST" class="formulario">

    <div class="campo">
        <label for="email">Email:</label>
        <input type="email" id="email" placeholder="Tu Email" name="email" value="<?php echo s($auth->email); ?>">
    </div>

    <div class="campo">
        <label for="password">Password:</label>
        <input type="password" id="password" placeholder="Tu Password" name="password">
    </div>

    <input type="submit" class="boton" value="Iniciar Sesión">
</form>

<div class="acciones">
    <a href="/crear-cuenta">¿No tienes una cuenta? Crear una</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>