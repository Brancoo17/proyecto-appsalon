<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Nuevo Peluquero</h1>
<p class="descripcion-pagina">Llena todos los campos para registrar un nuevo peluquero</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<form action="/peluqueros/crear" method="POST" class="formulario">
    <?php include_once __DIR__ . '/formulario.php'; ?>
    <input type="submit" class="boton" value="Guardar Peluquero">
</form>
