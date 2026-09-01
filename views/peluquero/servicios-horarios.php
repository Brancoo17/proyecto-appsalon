<?php 
/** @var string $nombre */ 
/** @var array $misServicios */
/** @var array $misHorarios */

$diasNombres = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];
?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Mis Servicios y Horarios</h1>
<p class="descripcion-pagina">Consulta los servicios que tienes asignados y tu cronograma semanal de trabajo</p>

<div class="panel-peluquero-info-grid">
    <div class="card-peluquero-info">
        <h3><i class="fa-solid fa-scissors"></i> Mis Servicios Asignados</h3>
        <?php if(empty($misServicios)): ?>
            <p class="texto-vacio-info">No tienes servicios asignados aún. Consulta con el administrador.</p>
        <?php else: ?>
            <div class="tags-servicios-peluquero">
                <?php foreach($misServicios as $serv): ?>
                    <div class="tag-servicio">
                        <span><?php echo s($serv->nombre); ?></span>
                        <span class="badge-duracion"><i class="fa-regular fa-clock"></i> <?php echo s($serv->duracion ?? 30); ?> min</span>
                        <strong>$<?php echo s($serv->precio); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-peluquero-info">
        <h3><i class="fa-solid fa-clock"></i> Mi Horario de Atención</h3>
        <div class="lista-horarios-peluquero">
            <?php foreach($diasNombres as $num => $nombreDia): 
                $h = $misHorarios[$num] ?? null;
                $activo = $h ? ((int)$h->activo === 1) : false;
            ?>
                <div class="item-horario <?php echo $activo ? 'activo' : 'inactivo'; ?>">
                    <span class="dia"><?php echo $nombreDia; ?>:</span>
                    <?php if($activo): ?>
                        <span class="horas"><?php echo substr($h->hora_inicio, 0, 5); ?> a <?php echo substr($h->hora_fin, 0, 5); ?> hs</span>
                    <?php else: ?>
                        <span class="horas no-laboral">No laboral</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
