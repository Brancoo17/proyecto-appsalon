<?php 
    /** @var string $nombre */ 
    $usuarioNombre = $nombre ?? $_SESSION['nombre'] ?? '';
    $uriActual = $_SERVER['REQUEST_URI'] ?? '';
?>

<div class="barra-home">
    <a href="/">
        <p><i class="fa-solid fa-house"></i> &nbsp;INICIO</p>
    </a>
</div>

<div class="barra">
    <div class="usuario-saludo">
        <div class="avatar-icono">
            <i class="fa-solid fa-user"></i>
        </div>
        <p><span><?php echo (isset($_SESSION['login']) && $_SESSION['login']) ? s($usuarioNombre) : 'Invitado'; ?></span></p>
    </div>

    <?php if(isset($_SESSION['login']) && $_SESSION['login']): ?>
        <div class="acciones-barra-usuario">
            <a class="boton-barra boton-barra--cuenta" href="/usuario">
                <i class="fa-solid fa-user-gear"></i>
                <span>Mi Cuenta</span>
            </a>
            <a class="boton-barra boton-barra--logout" href="/logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    <?php else: ?>
        <a class="boton-barra boton-barra--login" href="/login">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>Iniciar Sesión</span>
        </a>
    <?php endif; ?>
</div>

<?php if(isset($_SESSION['admin'])) { ?>
    <h1 class="nombre-pagina">Panel de Administración</h1>

    <nav class="barra-servicios">
        <a class="boton-nav <?php echo (strpos($uriActual, '/admin') !== false || $uriActual === '/admin') ? 'activo' : ''; ?>" href="/admin">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Ver Turnos</span>
        </a>
        <a class="boton-nav <?php echo (strpos($uriActual, '/servicios') !== false) ? 'activo' : ''; ?>" href="/servicios">
            <i class="fa-solid fa-scissors"></i>
            <span>Administrar Servicios</span>
        </a>
        <a class="boton-nav <?php echo (strpos($uriActual, '/peluqueros') !== false) ? 'activo' : ''; ?>" href="/peluqueros">
            <i class="fa-solid fa-users-gear"></i>
            <span>Administrar Peluqueros</span>
        </a>
    </nav>
<?php } ?>

<?php if(isset($_SESSION['peluquero'])) { ?>
    <h1 class="nombre-pagina">Panel de Peluquero</h1>

    <nav class="barra-servicios">
        <a class="boton-nav <?php echo (strpos($uriActual, '/peluquero/servicios-horarios') === false && (strpos($uriActual, '/peluquero') !== false || $uriActual === '/peluquero')) ? 'activo' : ''; ?>" href="/peluquero">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Mis Turnos</span>
        </a>
        <a class="boton-nav <?php echo (strpos($uriActual, '/peluquero/servicios-horarios') !== false) ? 'activo' : ''; ?>" href="/peluquero/servicios-horarios">
            <i class="fa-solid fa-scissors"></i>
            <span>Servicios y Horarios</span>
        </a>
    </nav>
<?php } ?>