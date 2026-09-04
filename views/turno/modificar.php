<?php /** @var object $turno */ ?>
<?php /** @var object|null $cliente */ ?>
<?php /** @var string $nombreCliente */ ?>
<?php /** @var string $telefonoCliente */ ?>
<?php /** @var object|null $peluqueroActual */ ?>
<?php /** @var array $serviciosAsignadosIds */ ?>
<?php /** @var string $redirectUrl */ ?>
<?php /** @var bool $esAdmin */ ?>
<?php /** @var bool $esPeluquero */ ?>
<?php /** @var bool $esCliente */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<!-- BANNER SUPERIOR DE EDICIÓN DE TURNO -->
<div class="banner-modificar-turno">
    <div class="banner-modificar-info">
        <div class="banner-modificar-badge">
            <i class="fa-solid fa-pen-to-square"></i> Modificando Turno #<?php echo s($turno->id); ?>
        </div>
        <div class="banner-modificar-detalles">
            <span><i class="fa-solid fa-user"></i> Cliente: <strong><?php echo s($nombreCliente); ?></strong></span>
            <span><i class="fa-regular fa-calendar"></i> Fecha actual: <strong><?php echo s($turno->fecha); ?> (<?php echo s(substr($turno->hora, 0, 5)); ?> hs)</strong></span>
            <span><i class="fa-solid fa-scissors"></i> Barbero actual: <strong><?php echo s($peluqueroActual ? ($peluqueroActual->nombre . ' ' . $peluqueroActual->apellido) : 'Sin asignar'); ?></strong></span>
        </div>
    </div>
    <a href="<?php echo s($redirectUrl); ?>" class="boton-volver-modificar" title="Volver sin guardar cambios">
        <i class="fa-solid fa-arrow-left"></i> Cancelar y Volver
    </a>
</div>

<h1 class="nombre-pagina">Modificar Turno</h1>
<p class="descripcion-pagina">Actualiza los servicios, fecha, horario o profesional asignado</p>

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
        <p class="text-center">Elige los nuevos servicios o conserva los actuales</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <div id="paso-2" class="seccion">
        <h2>Datos y Horario del Turno</h2>
        <p class="text-center">Selecciona la nueva fecha, hora y barbero profesional</p>

        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" placeholder="Tu Nombre completo" value="<?php echo s($nombreCliente); ?>" <?php echo $esCliente ? 'disabled' : ''; ?> />
            </div>

            <div class="campo">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" placeholder="Tu Teléfono de contacto" value="<?php echo s($telefonoCliente); ?>" <?php echo ($esCliente && !empty($telefonoCliente)) ? 'disabled' : ''; ?> />
            </div>

            <div class="campo">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" min="<?php echo date('Y-m-d'); ?>" value="<?php echo s($turno->fecha); ?>" />
            </div>

            <div class="etiqueta-horas"></div>

            <div class="campo">
                <!-- Aquí se inyectarán las horas dinámicamente -->
                <div id="horas" class="listado-horas"></div>
                <!-- Input oculto para guardar la hora seleccionada -->
                <input type="hidden" id="hora" value="<?php echo s(substr($turno->hora, 0, 5)); ?>" />
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
                        <input type="radio" name="metodo_pago" value="efectivo" <?php echo ($turno->metodo_pago !== 'transferencia') ? 'checked' : ''; ?>>
                        <span class="radio-custom"></span>
                        <span class="radio-label"><i class="fa-solid fa-money-bill-wave"></i> Efectivo</span>
                    </label>
                    <label class="opcion-radio">
                        <input type="radio" name="metodo_pago" value="transferencia" <?php echo ($turno->metodo_pago === 'transferencia') ? 'checked' : ''; ?>>
                        <span class="radio-custom"></span>
                        <span class="radio-label"><i class="fa-solid fa-building-columns"></i> Transferencia</span>
                    </label>
                </div>
            </div>

            <!-- Campos ocultos con información previa para precarga en JS -->
            <input type="hidden" id="id" value="<?php echo s($turno->usuario_id ?? ''); ?>" />
            <input type="hidden" id="es_cliente" value="<?php echo $esCliente ? '1' : '0'; ?>" />
            <input type="hidden" id="turno_id_modificar" value="<?php echo s($turno->id); ?>" />
            <input type="hidden" id="turno_modificar_servicios" value="<?php echo htmlspecialchars(json_encode($serviciosAsignadosIds), ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" id="turno_modificar_fecha" value="<?php echo s($turno->fecha); ?>" />
            <input type="hidden" id="turno_modificar_hora" value="<?php echo s(substr($turno->hora, 0, 5)); ?>" />
            <input type="hidden" id="turno_modificar_peluquero_id" value="<?php echo s($turno->peluquero_id); ?>" />
            <input type="hidden" id="turno_modificar_metodo_pago" value="<?php echo s($turno->metodo_pago ?: 'efectivo'); ?>" />
            <input type="hidden" id="redirect_url" value="<?php echo s($redirectUrl); ?>" />
        </form>
    </div>

    <div id="paso-3" class="seccion contenido-resumen">
        <h2>Resumen de Modificación</h2>
        <p class="text-center">Verifica los nuevos datos antes de guardar los cambios</p>
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
