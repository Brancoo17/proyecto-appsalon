<?php 
/** @var array $clientes */
/** @var int $totalClientes */
/** @var int $totalConfirmados */
/** @var int $totalConTurnos */
?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div class="header-seccion-admin">
    <div class="titulos-seccion">
        <h2>Clientes Registrados</h2>
        <p class="descripcion-pagina">Registro general y métricas de clientes con cuenta activa en la barbería</p>
    </div>
</div>

<!-- TARJETAS DE MÉTRICAS -->
<div class="metricas-clientes-grid">
    <div class="card-metrica-cliente">
        <div class="icono-metrica icono-metrica--azul">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="info-metrica">
            <span class="valor-metrica"><?php echo $totalClientes; ?></span>
            <span class="etiqueta-metrica">Clientes Registrados</span>
        </div>
    </div>

    <div class="card-metrica-cliente">
        <div class="icono-metrica icono-metrica--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="info-metrica">
            <span class="valor-metrica"><?php echo $totalConfirmados; ?></span>
            <span class="etiqueta-metrica">Cuentas Confirmadas</span>
        </div>
    </div>

    <div class="card-metrica-cliente">
        <div class="icono-metrica icono-metrica--dorado">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="info-metrica">
            <span class="valor-metrica"><?php echo $totalConTurnos; ?></span>
            <span class="etiqueta-metrica">Con Turnos Agendados</span>
        </div>
    </div>
</div>

<!-- BARRA DE BÚSQUEDA Y FILTROS -->
<div class="contenedor-busqueda-clientes">
    <div class="campo-busqueda-reactiva">
        <i class="fa-solid fa-magnifying-glass icono-lupa"></i>
        <input type="text" id="buscador-clientes" placeholder="Buscar por nombre, email o teléfono..." autocomplete="off">
        <button type="button" id="btn-limpiar-busqueda" class="btn-limpiar-busqueda" title="Limpiar búsqueda" style="display: none;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="contador-resultados">
        Mostrando <span id="contador-visibles"><?php echo count($clientes); ?></span> de <?php echo count($clientes); ?> clientes
    </div>
</div>

<?php if(empty($clientes)): ?>
    <div class="alerta-vacio">
        <p><i class="fa-solid fa-users-slash"></i> No hay clientes registrados en el sistema todavía.</p>
    </div>
<?php else: ?>
    <div class="contenedor-tabla">
        <table class="tabla-admin tabla-clientes" id="tabla-clientes">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Teléfono / WhatsApp</th>
                    <th>Cuenta</th>
                    <th>Turnos</th>
                    <th>Último Turno</th>
                    <th>Historial</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clientes as $cliente): 
                    $nombreCompleto = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
                    $iniciales = mb_strtoupper(mb_substr($cliente['nombre'] ?? 'C', 0, 1) . mb_substr($cliente['apellido'] ?? '', 0, 1));
                    $esConfirmado = (int)($cliente['confirmado'] ?? 0) === 1;
                    $totalTurnos = (int)($cliente['total_turnos'] ?? 0);
                    $ultimoTurno = !empty($cliente['ultimo_turno']) ? date('d/m/y', strtotime($cliente['ultimo_turno'])) : 'Sin turnos';
                    $turnosList = array_values($cliente['turnos_detalle'] ?? []);

                    // Limpiar teléfono para enlace a WhatsApp
                    $telefonoRaw = preg_replace('/[^0-9]/', '', $cliente['telefono'] ?? '');
                    $mensajeWa = rawurlencode("Hola {$nombreCompleto}, te contactamos desde BarberShop.");
                ?>
                    <tr class="fila-cliente" 
                        data-nombre="<?php echo strtolower(s($nombreCompleto)); ?>" 
                        data-email="<?php echo strtolower(s($cliente['email'] ?? '')); ?>" 
                        data-telefono="<?php echo strtolower(s($cliente['telefono'] ?? '')); ?>">
                        
                        <td class="col-cliente-perfil">
                            <div class="avatar-cliente-circulo">
                                <?php echo !empty($iniciales) ? $iniciales : '<i class="fa-solid fa-user"></i>'; ?>
                            </div>
                            <div class="cliente-info-nombres">
                                <strong class="cliente-nombre-completo"><?php echo s($nombreCompleto); ?></strong>
                                <span class="badge-id-cliente">ID #<?php echo s($cliente['id']); ?></span>
                            </div>
                        </td>

                        <td class="col-email">
                            <a href="mailto:<?php echo s($cliente['email']); ?>" class="enlace-contacto enlace-email" title="Enviar correo">
                                <i class="fa-solid fa-envelope"></i> <?php echo s($cliente['email']); ?>
                            </a>
                        </td>

                        <td class="col-telefono">
                            <div class="grupo-contacto-telefono">
                                <?php if(!empty($cliente['telefono'])): ?>
                                    <a href="tel:<?php echo s($cliente['telefono']); ?>" class="enlace-contacto enlace-tel" title="Llamar">
                                        <i class="fa-solid fa-phone"></i> <?php echo s($cliente['telefono']); ?>
                                    </a>
                                    <?php if(!empty($telefonoRaw)): ?>
                                        <a href="https://wa.me/<?php echo $telefonoRaw; ?>?text=<?php echo $mensajeWa; ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-tabla" title="Contactar por WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="sin-dato">Sin registrar</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="col-estado-cuenta">
                            <?php if($esConfirmado): ?>
                                <span class="badge-cuenta badge-cuenta--confirmada">
                                    <i class="fa-solid fa-circle-check"></i> Confirmada
                                </span>
                            <?php else: ?>
                                <span class="badge-cuenta badge-cuenta--pendiente">
                                    <i class="fa-solid fa-clock"></i> Pendiente
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="col-turnos-contador">
                            <span class="badge-contador-turnos <?php echo $totalTurnos > 0 ? 'badge-con-turnos' : 'badge-sin-turnos'; ?>">
                                <i class="fa-solid fa-calendar-check"></i> <?php echo $totalTurnos; ?> <?php echo $totalTurnos === 1 ? 'turno' : 'turnos'; ?>
                            </span>
                        </td>

                        <td class="col-ultimo-turno">
                            <span class="fecha-ultimo-turno">
                                <i class="fa-regular fa-calendar"></i> <?php echo s($ultimoTurno); ?>
                            </span>
                        </td>

                        <td class="col-historial-btn">
                            <?php if($totalTurnos > 0): ?>
                                <button type="button" class="btn-tabla-modal btn-ver-turnos-cliente" 
                                        data-nombre="<?php echo s($nombreCompleto); ?>"
                                        data-turnos="<?php echo htmlspecialchars(json_encode($turnosList), ENT_QUOTES, 'UTF-8'); ?>"
                                        title="Ver Historial de Turnos">
                                    <i class="fa-solid fa-list-check"></i> Ver Turnos
                                </button>
                            <?php else: ?>
                                <span class="texto-sin-turnos">Sin turnos</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="sin-resultados-clientes" class="alerta-vacio alerta-sin-resultados" style="display: none;">
            <p><i class="fa-solid fa-magnifying-glass"></i> No se encontraron clientes que coincidan con la búsqueda.</p>
        </div>
    </div>
<?php endif; ?>

<?php
    $vJs = file_exists(__DIR__ . '/../../public/build/js/buscador.js') ? filemtime(__DIR__ . '/../../public/build/js/buscador.js') : '2.0';
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='/build/js/buscador.js?v={$vJs}'></script>
    ";
?>
