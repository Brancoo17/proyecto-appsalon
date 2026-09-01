<?php /** @var array $turnos */
/** @var string $fecha */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Panel de Peluquero</h1>

<h3>Mis Turnos Asignados</h3>
<div class="busqueda">
    <form class="formulario">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fecha ?>">
        </div>
    </form>
</div>

<?php
    // Agrupar turnos por ID
    $turnosAgrupados = [];
    foreach($turnos as $t) {
        if(!isset($turnosAgrupados[$t->id])) {
            $turnosAgrupados[$t->id] = [
                'id' => $t->id,
                'hora' => substr($t->hora, 0, 5),
                'cliente' => $t->cliente,
                'email' => $t->email,
                'telefono' => $t->telefono,
                'estado' => $t->estado ?? 'reservado',
                'metodo_pago' => $t->metodo_pago ?? 'efectivo',
                'servicios' => [],
                'total' => 0
            ];
        }
        if(!empty($t->servicio)) {
            $turnosAgrupados[$t->id]['servicios'][] = [
                'nombre' => $t->servicio,
                'precio' => $t->precio
            ];
            $turnosAgrupados[$t->id]['total'] += floatval($t->precio);
        }
    }
?>

<?php if(count($turnosAgrupados) === 0): ?>
    <div class="alerta-vacio">
        <p><i class="fa-solid fa-calendar-xmark"></i> No tienes turnos asignados para esta fecha</p>
    </div>
<?php else: ?>
    <div class="contenedor-tabla">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Servicios</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($turnosAgrupados as $turno): ?>
                    <tr>
                        <td class="col-hora">
                            <span class="badge-hora"><i class="fa-regular fa-clock"></i> <?php echo s($turno['hora']); ?></span>
                        </td>
                        <td class="col-cliente">
                            <strong class="cliente-nombre"><?php echo s($turno['cliente']); ?></strong>
                            <span class="cliente-contacto"><i class="fa-solid fa-phone"></i> <?php echo s($turno['telefono'] ?: 'Sin teléfono'); ?></span>
                            <?php if(!empty($turno['email'])): ?>
                                <span class="cliente-contacto"><i class="fa-solid fa-envelope"></i> <?php echo s($turno['email']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="col-servicios">
                            <div class="lista-servicios-tabla">
                                <?php foreach($turno['servicios'] as $serv): ?>
                                    <span class="badge-servicio"><?php echo s($serv['nombre']); ?> <em>($<?php echo s($serv['precio']); ?>)</em></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="col-pago">
                            <span class="badge-pago badge-pago--<?php echo s(strtolower($turno['metodo_pago'])); ?>">
                                <?php if($turno['metodo_pago'] === 'transferencia'): ?>
                                    <i class="fa-solid fa-building-columns"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                <?php endif; ?>
                                <?php echo ucfirst(s($turno['metodo_pago'])); ?>
                            </span>
                        </td>
                        <td class="col-total">
                            <strong class="precio-total">$<?php echo s($turno['total']); ?></strong>
                        </td>
                        <td class="col-estado">
                            <span class="badge-estado badge-estado--<?php echo s(strtolower($turno['estado'])); ?>" data-id="<?php echo $turno['id']; ?>" data-estado="<?php echo s(strtolower($turno['estado'])); ?>" role="button" title="Haz clic para cambiar estado">
                                <?php echo ucfirst(s($turno['estado'])); ?> <i class="fa-solid fa-pen-to-square"></i>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='build/js/buscador.js'></script>
    ";
?>
