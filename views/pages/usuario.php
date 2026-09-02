<?php 
/** @var string $nombre */
/** @var object $usuario */
/** @var string $rol */
/** @var bool $esCliente */
/** @var bool $esPeluquero */
/** @var bool $esAdmin */
/** @var array $turnos */
/** @var array $alertas */
/** @var string|null $resultado */

$diasSemana = [
    'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
];

$meses = [
    'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
    'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
    'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
    'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
];
?>

<div class="contenedor-perfil-usuario">

    <!-- BARRA SUPERIOR DE NAVEGACIÓN -->
    <div class="perfil-navegacion-superior">
        <a href="/" class="boton-volver-perfil"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>

        <div class="accesos-rapidos-rol">
            <?php if($esAdmin): ?>
                <a href="/admin" class="btn-acceso-panel"><i class="fa-solid fa-user-tie"></i> Panel Administrador</a>
            <?php elseif($esPeluquero): ?>
                <a href="/peluquero" class="btn-acceso-panel"><i class="fa-solid fa-scissors"></i> Mi Panel de Peluquero</a>
            <?php else: ?>
                <a href="/turno" class="btn-acceso-panel"><i class="fa-solid fa-calendar-plus"></i> Reservar Nuevo Turno</a>
            <?php endif; ?>
            <a href="/logout" class="btn-cerrar-sesion-perfil"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
        </div>
    </div>

    <!-- TARJETA CABECERA DE PERFIL -->
    <div class="card-cabecera-perfil">
        <div class="avatar-perfil">
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="info-perfil-header">
            <div class="nombre-y-rol">
                <h1><?php echo s($usuario->nombre . " " . $usuario->apellido); ?></h1>
                <span class="badge-rol badge-rol--<?php echo strtolower($rol); ?>"><?php echo s($rol); ?></span>
            </div>
            <div class="datos-contacto-header">
                <span><i class="fa-solid fa-envelope"></i> <?php echo s($usuario->email); ?></span>
                <?php if(!empty($usuario->telefono)): ?>
                    <span><i class="fa-solid fa-phone"></i> <?php echo s($usuario->telefono); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ALERTAS DE RESULTADO -->
    <?php if($resultado === '1'): ?>
        <div class="alerta exito">
            <i class="fa-solid fa-circle-check"></i> Tus datos han sido actualizados correctamente
        </div>
    <?php elseif($resultado === '2'): ?>
        <div class="alerta exito">
            <i class="fa-solid fa-circle-check"></i> El turno ha sido cancelado correctamente
        </div>
    <?php endif; ?>

    <!-- ALERTAS DE ERROR DE FORMULARIO -->
    <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

    <?php if($esCliente): ?>
        <!-- PESTAÑAS DE NAVEGACIÓN PARA CLIENTES -->
        <nav class="tabs-perfil">
            <button type="button" class="tab-btn-perfil actual" data-seccion="datos">
                <i class="fa-solid fa-id-card"></i> Mis Datos Personales
            </button>
            <button type="button" class="tab-btn-perfil" data-seccion="turnos">
                <i class="fa-solid fa-calendar-days"></i> Mis Turnos
                <?php if(!empty($turnos)): ?>
                    <span class="badge-contador-tab"><?php echo count($turnos); ?></span>
                <?php endif; ?>
            </button>
        </nav>
    <?php endif; ?>

    <!-- CONTENEDOR DE SECCIONES -->
    <div class="contenedor-secciones-tabs">

        <!-- SECCIÓN 1: DATOS PERSONALES -->
        <div id="seccion-datos" class="seccion-perfil-tab mostrar">
            <div class="card-perfil-formulario">
                <div class="card-seccion-header">
                    <h2><i class="fa-solid fa-id-card"></i> Mis Datos Personales</h2>
                    <p>Mantén tu información de contacto siempre actualizada</p>
                </div>

                <form action="/usuario" method="POST" class="formulario-perfil">
                    <div class="campo-perfil">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu Nombre" value="<?php echo s($usuario->nombre); ?>" required>
                    </div>

                    <div class="campo-perfil">
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" placeholder="Tu Apellido" value="<?php echo s($usuario->apellido); ?>" required>
                    </div>

                    <div class="campo-perfil">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Tu Email" value="<?php echo s($usuario->email); ?>" required>
                    </div>

                    <div class="campo-perfil">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Tu Teléfono (ej: 1123456789)" value="<?php echo s($usuario->telefono ?? ''); ?>" required>
                    </div>

                    <div class="campo-perfil">
                        <label for="password">Nueva Contraseña (Opcional):</label>
                        <div class="contenedor-input-password">
                            <input type="password" id="password" name="password" placeholder="Dejar en blanco para mantener la actual">
                            <button type="button" class="btn-toggle-password" tabindex="-1" title="Mostrar u ocultar contraseña">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="acciones-formulario-perfil">
                        <button type="submit" class="boton-guardar-perfil">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SECCIÓN 2: MIS TURNOS (SOLO CLIENTE) -->
        <?php if($esCliente): ?>
            <div id="seccion-turnos" class="seccion-perfil-tab">
                <div class="card-perfil-turnos">
                    <div class="card-seccion-header">
                        <h2><i class="fa-solid fa-calendar-days"></i> Mis Turnos</h2>
                        <p>Historial y próximas reservas agendadas en el salón</p>
                    </div>

                    <?php if(empty($turnos)): ?>
                        <div class="alerta-vacio-turnos">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>Aún no tienes turnos registrados</p>
                            <a href="/turno" class="boton-reservar-cta"><i class="fa-solid fa-calendar-plus"></i> ¡Reserva tu primer turno aquí!</a>
                        </div>
                    <?php else: ?>
                        <div class="listado-turnos-cliente">
                            <?php foreach($turnos as $turno): 
                                // Formatear fecha en español
                                $fechaTs = strtotime($turno['fecha']);
                                $diaIngles = date('l', $fechaTs);
                                $mesIngles = date('F', $fechaTs);
                                $diaNum = date('d', $fechaTs);
                                $anio = date('Y', $fechaTs);
                                
                                $diaEspanol = $diasSemana[$diaIngles] ?? $diaIngles;
                                $mesEspanol = $meses[$mesIngles] ?? $mesIngles;
                                $fechaFormateada = "{$diaEspanol} {$diaNum} de {$mesEspanol}, {$anio}";
                                
                                $esPasado = strtotime($turno['fecha'] . ' ' . $turno['hora']) < time();
                                $esCancelado = strtolower($turno['estado']) === 'cancelado';
                                $esReservado = strtolower($turno['estado']) === 'reservado';
                            ?>
                                <div class="card-turno-item card-turno-item--<?php echo strtolower($turno['estado']); ?>">
                                    <div class="cabecera-turno-item">
                                        <div class="fecha-hora-turno">
                                            <span class="fecha-texto"><i class="fa-regular fa-calendar"></i> <?php echo $fechaFormateada; ?></span>
                                            <span class="hora-badge"><i class="fa-regular fa-clock"></i> <?php echo s($turno['hora']); ?> - <?php echo s($turno['hora_fin']); ?> hs</span>
                                        </div>
                                        <span class="badge-estado badge-estado--<?php echo strtolower($turno['estado']); ?>">
                                            <?php echo ucfirst(s($turno['estado'])); ?>
                                        </span>
                                    </div>

                                    <div class="cuerpo-turno-item">
                                        <div class="fila-info-profesional">
                                            <span><i class="fa-solid fa-user-scissors"></i> Profesional:</span>
                                            <strong><?php echo s($turno['peluquero']); ?></strong>
                                        </div>

                                        <div class="fila-servicios-resumen">
                                            <span><i class="fa-solid fa-scissors"></i> Servicios:</span>
                                            <div class="tags-servicios-turno">
                                                <?php foreach($turno['servicios'] as $serv): ?>
                                                    <span class="tag-serv-cliente">
                                                        <?php echo s($serv['nombre']); ?> 
                                                        <em>(<?php echo s($serv['duracion']); ?>m - $<?php echo s($serv['precio']); ?>)</em>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="fila-pago-total">
                                            <div class="metodo-pago-tag">
                                                <span>Pago:</span>
                                                <span class="badge-pago badge-pago--<?php echo strtolower($turno['metodo_pago']); ?>">
                                                    <?php if($turno['metodo_pago'] === 'transferencia'): ?>
                                                        <i class="fa-solid fa-building-columns"></i> Transferencia
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-money-bill-wave"></i> Efectivo
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="total-monto-tag">
                                                <span>Total:</span>
                                                <strong>$<?php echo number_format($turno['total'], 0, ',', '.'); ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if($esReservado && !$esPasado): ?>
                                        <div class="pie-turno-item">
                                            <form action="/usuario/cancelar-turno" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar este turno?');">
                                                <input type="hidden" name="id" value="<?php echo $turno['id']; ?>">
                                                <button type="submit" class="btn-cancelar-turno-cliente">
                                                    <i class="fa-solid fa-ban"></i> Cancelar Turno
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBotones = document.querySelectorAll('.tab-btn-perfil');
    if(tabBotones.length > 0) {
        // Verificar si la URL trae parámetro ?tab=turnos
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if(tabParam === 'turnos') {
            activarTab('turnos');
        }

        tabBotones.forEach(btn => {
            btn.addEventListener('click', function() {
                const seccion = this.dataset.seccion;
                activarTab(seccion);
            });
        });

        function activarTab(nombreSeccion) {
            tabBotones.forEach(b => b.classList.remove('actual'));
            const btnActivo = document.querySelector(`.tab-btn-perfil[data-seccion="${nombreSeccion}"]`);
            if(btnActivo) btnActivo.classList.add('actual');

            document.querySelectorAll('.seccion-perfil-tab').forEach(sec => sec.classList.remove('mostrar'));
            const secActiva = document.querySelector(`#seccion-${nombreSeccion}`);
            if(secActiva) secActiva.classList.add('mostrar');
        }
    }
});
</script>
