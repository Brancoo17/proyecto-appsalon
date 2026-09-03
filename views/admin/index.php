<?php /** @var array $turnos */
/** @var array $bloqueos */
/** @var array $peluqueros */
/** @var string $fecha */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h2>Buscar Turnos</h2>
<div class="busqueda busqueda-con-acciones">
    <form class="formulario">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo $fecha ?>">
        </div>
    </form>
    <div class="acciones-cabecera-admin">
        <button type="button" id="btn-bloquear-horario" class="boton-bloquear-horario" title="Bloquear un intervalo de horario">
            <i class="fa-solid fa-lock"></i> Bloquear Horario
        </button>
        <?php if(!empty($bloqueos)): ?>
            <span class="badge-resumen-bloqueos" title="Horarios bloqueados en esta fecha">
                <i class="fa-solid fa-shield-halved"></i> <?php echo count($bloqueos); ?> Bloqueo(s)
            </span>
        <?php endif; ?>
    </div>
</div>

<?php
    // Agrupar turnos por ID para soportar múltiples servicios en un solo turno
    $turnosAgrupados = [];
    foreach($turnos as $t) {
        if(!isset($turnosAgrupados[$t->id])) {
            $turnosAgrupados[$t->id] = [
                'id' => $t->id,
                'hora' => substr($t->hora, 0, 5),
                'duracionTotal' => 0,
                'cliente' => $t->cliente,
                'email' => $t->email,
                'telefono' => $t->telefono,
                'peluquero' => $t->peluquero,
                'estado' => $t->estado ?? 'reservado',
                'metodo_pago' => $t->metodo_pago ?? 'efectivo',
                'servicios' => [],
                'total' => 0
            ];
        }
        if(!empty($t->servicio)) {
            $dur = (int)($t->duracion ?? 30);
            $turnosAgrupados[$t->id]['servicios'][] = [
                'nombre' => $t->servicio,
                'precio' => $t->precio,
                'duracion' => $dur
            ];
            $turnosAgrupados[$t->id]['duracionTotal'] += $dur;
            $turnosAgrupados[$t->id]['total'] += floatval($t->precio);
        }
    }

    // Calcular hora de finalización para cada turno
    foreach($turnosAgrupados as $id => $turnoItem) {
        $duracionMin = max(30, $turnoItem['duracionTotal']);
        $horaInicioSeg = strtotime("2000-01-01 " . $turnoItem['hora']);
        $horaFinSeg = $horaInicioSeg + ($duracionMin * 60);
        $turnosAgrupados[$id]['hora_fin'] = date('H:i', $horaFinSeg);
    }

    // Construir la agenda combinada del día (turnos de clientes + horarios bloqueados)
    $agendaDelDia = [];
    foreach($turnosAgrupados as $tItem) {
        $agendaDelDia[] = array_merge($tItem, [
            'tipo' => 'turno'
        ]);
    }

    foreach($bloqueos as $b) {
        $agendaDelDia[] = [
            'tipo' => 'bloqueo',
            'id' => $b->id,
            'hora' => substr($b->hora_inicio, 0, 5),
            'hora_fin' => substr($b->hora_fin, 0, 5),
            'peluquero' => $b->peluquero ?: 'Todos los profesionales',
            'motivo' => $b->motivo ?: 'Sin motivo especificado'
        ];
    }

    // Ordenar cronológicamente por hora de inicio
    usort($agendaDelDia, function($a, $b) {
        return strcmp($a['hora'], $b['hora']);
    });
?>

<?php if(count($agendaDelDia) === 0): ?>
    <div class="alerta-vacio">
        <p><i class="fa-solid fa-calendar-xmark"></i> No hay turnos ni horarios bloqueados para esta fecha</p>
    </div>
<?php else: ?>
    <div class="contenedor-tabla">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Cliente</th>
                    <th>Peluquero</th>
                    <th>Servicios</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado / Motivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($agendaDelDia as $item): ?>
                    <?php if($item['tipo'] === 'bloqueo'): ?>
                        <tr class="fila-bloqueo">
                            <td class="col-hora">
                                <span class="badge-hora badge-hora--bloqueo">
                                    <i class="fa-solid fa-lock"></i> <?php echo s($item['hora']); ?> - <?php echo s($item['hora_fin']); ?>
                                </span>
                            </td>
                            <td class="col-cliente">
                                <strong class="cliente-bloqueado-label"><i class="fa-solid fa-user-slash"></i> Franja No Disponible</strong>
                                <span class="cliente-contacto">Bloqueo de horario</span>
                            </td>
                            <td class="col-peluquero">
                                <span class="peluquero-nombre"><i class="fa-solid fa-scissors"></i> <?php echo s($item['peluquero']); ?></span>
                            </td>
                            <td class="col-servicios">
                                <span class="badge-bloqueo-tag"><i class="fa-solid fa-ban"></i> HORARIO BLOQUEADO</span>
                            </td>
                            <td class="col-pago">
                                <span class="texto-atenuado">—</span>
                            </td>
                            <td class="col-total">
                                <span class="texto-atenuado">—</span>
                            </td>
                            <td class="col-estado">
                                <span class="badge-motivo-bloqueo" title="Motivo del bloqueo">
                                    <i class="fa-solid fa-tag"></i> <?php echo s($item['motivo']); ?>
                                </span>
                            </td>
                            <td class="col-acciones">
                                <div class="acciones-tabla">
                                    <button type="button" class="boton-desbloquear-tabla btn-eliminar-bloqueo" data-id="<?php echo $item['id']; ?>" title="Eliminar bloqueo y liberar horario">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td class="col-hora">
                                <span class="badge-hora"><i class="fa-regular fa-clock"></i> <?php echo s($item['hora']); ?> - <?php echo s($item['hora_fin']); ?></span>
                            </td>
                            <td class="col-cliente">
                                <strong class="cliente-nombre"><?php echo s($item['cliente']); ?></strong>
                                <span class="cliente-contacto"><i class="fa-solid fa-phone"></i> <?php echo s($item['telefono'] ?: 'Sin teléfono'); ?></span>
                                <?php if(!empty($item['email'])): ?>
                                    <span class="cliente-contacto"><i class="fa-solid fa-envelope"></i> <?php echo s($item['email']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="col-peluquero">
                                <span class="peluquero-nombre"><i class="fa-solid fa-scissors"></i> <?php echo s($item['peluquero'] ?: 'No asignado'); ?></span>
                            </td>
                            <td class="col-servicios">
                                <div class="lista-servicios-tabla">
                                    <?php foreach($item['servicios'] as $serv): ?>
                                        <span class="badge-servicio"><?php echo s($serv['nombre']); ?> <em>($<?php echo s($serv['precio']); ?>)</em></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="col-pago">
                                <span class="badge-pago badge-pago--<?php echo s(strtolower($item['metodo_pago'])); ?>">
                                    <?php if($item['metodo_pago'] === 'transferencia'): ?>
                                        <i class="fa-solid fa-building-columns"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                    <?php endif; ?>
                                    <?php echo ucfirst(s($item['metodo_pago'])); ?>
                                </span>
                            </td>
                            <td class="col-total">
                                <strong class="precio-total">$<?php echo s($item['total']); ?></strong>
                            </td>
                            <td class="col-estado">
                                <span class="badge-estado badge-estado--<?php echo s(strtolower($item['estado'])); ?>" data-id="<?php echo $item['id']; ?>" data-estado="<?php echo s(strtolower($item['estado'])); ?>" role="button" title="Haz clic para cambiar estado">
                                    <?php echo ucfirst(s($item['estado'])); ?> <i class="fa-solid fa-pen-to-square"></i>
                                </span>
                            </td>
                            <td class="col-acciones">
                                <div class="acciones-tabla">
                                    <a href="/turno/modificar?id=<?php echo $item['id']; ?>" class="boton-editar-tabla" title="Modificar Turno">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="/api/eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este turno?');">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="boton-eliminar-tabla" title="Eliminar Turno">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Metadatos para el modal de bloqueo en JS -->
<div id="datos-bloqueo" 
     data-es-admin="1" 
     data-fecha="<?php echo s($fecha); ?>" 
     data-peluqueros='<?php echo json_encode(array_map(fn($p) => ['id' => (int)$p->id, 'nombre' => $p->nombre . ' ' . $p->apellido], $peluqueros ?? [])); ?>'>
</div>

<?php
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='/build/js/buscador.js'></script>
    ";
?>