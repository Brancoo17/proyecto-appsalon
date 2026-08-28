<?php /** @var string $nombre */ ?>
<?php /** @var string $telefono */ ?>
<?php /** @var int|string $id */ ?>

<h1 class="nombre-pagina">Crear Nuevo Turno</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<a href="/" class="boton-volver"><i class="fa-solid fa-house"></i> Volver a Inicio</a>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">Servicios</button>
        <button type="button" data-paso="2">Información Turno</button>
        <button type="button" data-paso="3">Resumen</button>
    </nav>

    <div id="paso-1" class="seccion">
        <h2>Servicios</h2>
        <p class="text-center">Elige tus servicios a continuación</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <div id="paso-2" class="seccion">
        <h2>Tus Datos y Turno</h2>
        <p class="text-center">Coloca tus datos y la fecha para confirmar tu turno</p>

        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" placeholder="Tu Nombre completo" value="<?php echo s($nombre) ?>" <?php echo !empty($id) ? 'disabled' : ''; ?> />
            </div>

            <div class="campo">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" placeholder="Tu Teléfono de contacto" value="<?php echo s($telefono ?? '') ?>" <?php echo (!empty($id) && !empty($telefono)) ? 'disabled' : ''; ?> />
            </div>

            <div class="campo">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" min="<?php echo date('Y-m-d'); ?>" />
            </div>

            <!-- NUEVO: Campo de selección de Peluquero -->
            <div class="campo" id="campo-peluquero" style="display: none;">
                <label for="peluquero">Peluquero:</label>
                <select id="peluquero">
                    <option value="" disabled selected>-- Seleccione un Peluquero --</option>
                </select>
            </div>

            <div class="etiqueta-horas"></div>

            <div class="campo">
                <!-- Aquí se inyectarán las horas dinámicamente -->
                <div id="horas" class="listado-horas"></div>
                <!-- Input oculto para seguir guardando la hora seleccionada en el formulario si es necesario -->
                <input type="hidden" id="hora" />
            </div>

            <input type="hidden" id="id" value="<?php echo s($id); ?>" />
        </form>
    </div>

    <div id="paso-3" class="seccion contenido-resumen">
        <h2>Resumen</h2>
        <p class="text-center">Verifica que la información sea correcta</p>
    </div>

    <div class="paginacion">
        <button type="button" class="boton" id="anterior">&laquo; Anterior</button>
        <button type="button" class="boton" id="siguiente">Siguiente &raquo;</button>
    </div>
</div>

<?php
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='build/js/app.js'></script>
    ";
?>