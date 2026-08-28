<?php /** @var string $nombre */ ?>

<div class="barra">
    <p>Hola: <span><?php echo s($nombre) ?? ''; ?></span></p>
    <a class="boton" href="/logout">Cerrar Sesión</a>
</div>

<?php if(isset($_SESSION['admin'])) { ?>
    <div class="barra-servicios">
        <a class="boton" href="/admin">Ver Turnos</a>
        <a class="boton" href="/servicios">Administrar Servicios</a>
        <a class="boton" href="/peluqueros">Administrar Peluqueros</a>
    </div>
<?php } ?>