<a href="/servicios" class="boton-volver">&laquo; Volver</a>

<h1 class="nombre-pagina">Actualizar Servicio</h1>
<p class="descripcion-pagina">A continuación, puedes actualizar los datos de tu servicio</p>

<?php 
    include_once __DIR__ . '/../templates/alertas.php'; 
?>

<form method="POST" class="formulario">
    <?php include_once __DIR__ . '/formulario.php'; ?>

    <input type="submit" class="boton" value="Actualizar Servicio">
</form>