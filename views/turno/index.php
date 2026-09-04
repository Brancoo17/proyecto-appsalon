<?php /** @var string $nombre */ ?>
<?php /** @var string $telefono */ ?>
<?php /** @var int|string $id */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Crear Nuevo Turno</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<!-- BOTÓN FLOTANTE DEL CARRITO DE SERVICIOS -->
<div class="contenedor-carrito-flotante">
    <button type="button" id="btn-carrito" class="btn-flotante-carrito" title="Ver servicios seleccionados">
        <div class="icono-carrito">
            <i class="fa-solid fa-cart-shopping"></i>
            <span id="badge-contador-servicios" class="badge-contador">0</span>
        </div>
        <div class="info-carrito-texto">
            <span class="label-carrito">Mis Servicios</span>
            <strong id="badge-total-servicios" class="total-monto">$0</strong>
        </div>
    </button>
</div>

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

            <div class="etiqueta-horas"></div>

            <div class="campo">
                <!-- Aquí se inyectarán las horas dinámicamente -->
                <div id="horas" class="listado-horas"></div>
                <!-- Input oculto para guardar la hora seleccionada -->
                <input type="hidden" id="hora" />
            </div>

            <!-- Campo de selección de Peluquero con Modal -->
            <div class="campo campo-profesional" id="campo-profesional" style="display: none;">
                <label>Profesional:</label>
                <div class="selector-profesional">
                    <button type="button" id="btn-seleccionar-peluquero" class="boton-modal-peluquero">
                        <i class="fa-solid fa-scissors"></i> Ver Profesionales Disponibles
                    </button>
                    <div id="peluquero-seleccionado-info" class="peluquero-info-badge" style="display: none;"></div>
                </div>
            </div>

            <div class="campo campo-pago">
                <label>Método de Pago:</label>
                <div class="opciones-pago">
                    <label class="opcion-radio">
                        <input type="radio" name="metodo_pago" value="efectivo" checked>
                        <span class="radio-custom"></span>
                        <span class="radio-label"><i class="fa-solid fa-money-bill-wave"></i> Efectivo</span>
                    </label>
                    <label class="opcion-radio">
                        <input type="radio" name="metodo_pago" value="transferencia">
                        <span class="radio-custom"></span>
                        <span class="radio-label"><i class="fa-solid fa-building-columns"></i> Transferencia</span>
                    </label>
                </div>
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
    $vJs = file_exists(__DIR__ . '/../../public/build/js/app.js') ? filemtime(__DIR__ . '/../../public/build/js/app.js') : '2.0';
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='/build/js/app.js?v={$vJs}'></script>
    ";
?>