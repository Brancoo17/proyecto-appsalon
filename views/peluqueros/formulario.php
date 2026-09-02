<?php 
/** @var object $peluquero */ 
/** @var array $servicios */
/** @var array $serviciosPeluquero */
/** @var array $horarios */

$diasSemana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];
?>

<div class="campo">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" placeholder="Nombre Peluquero" name="nombre" value="<?php echo s($peluquero->nombre); ?>" />
</div>

<div class="campo">
    <label for="apellido">Apellido</label>
    <input type="text" id="apellido" placeholder="Apellido Peluquero" name="apellido" value="<?php echo s($peluquero->apellido); ?>" />
</div>

<div class="campo">
    <label for="email">Email</label>
    <input type="email" id="email" placeholder="Email del Peluquero" name="email" value="<?php echo s($peluquero->email); ?>" />
</div>

<div class="campo">
    <label for="telefono">Teléfono</label>
    <input type="tel" id="telefono" placeholder="Teléfono del Peluquero" name="telefono" value="<?php echo s($peluquero->telefono); ?>" />
</div>

<div class="campo">
    <label for="password">Password</label>
    <div class="contenedor-input-password">
        <input type="password" id="password" placeholder="Password de acceso (dejar en blanco para no cambiar)" name="password" />
        <button type="button" class="btn-toggle-password" tabindex="-1" title="Mostrar u ocultar contraseña">
            <i class="fa-regular fa-eye"></i>
        </button>
    </div>
</div>

<!-- SECCIÓN: SERVICIOS ASIGNADOS -->
<div class="seccion-formulario-peluquero">
    <h3><i class="fa-solid fa-scissors"></i> Servicios que realiza</h3>
    <p class="descripcion-seccion">Marca los servicios que este profesional está capacitado para atender:</p>

    <div class="listado-servicios-checkbox">
        <?php foreach($servicios as $servicio): 
            $checked = in_array((int)$servicio->id, $serviciosPeluquero ?? []) ? 'checked' : '';
        ?>
            <label class="item-checkbox-servicio">
                <input type="checkbox" name="servicios[]" value="<?php echo $servicio->id; ?>" <?php echo $checked; ?>>
                <div class="info-servicio-check">
                    <strong><?php echo s($servicio->nombre); ?></strong>
                    <span>$<?php echo s($servicio->precio); ?></span>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<!-- SECCIÓN: HORARIOS DE ATENCIÓN -->
<div class="seccion-formulario-peluquero">
    <h3><i class="fa-solid fa-clock"></i> Horarios Semanales de Atención</h3>
    <p class="descripcion-seccion">Configura los días que trabaja y su rango de horario laboral:</p>

    <div class="tabla-horarios-peluquero">
        <?php foreach($diasSemana as $numDia => $nombreDia): 
            $horarioDia = $horarios[$numDia] ?? null;
            $activo = $horarioDia ? ((int)$horarioDia->activo === 1) : true;
            $horaInicio = $horarioDia ? substr($horarioDia->hora_inicio, 0, 5) : '10:00';
            $horaFin = $horarioDia ? substr($horarioDia->hora_fin, 0, 5) : '20:00';
        ?>
            <div class="fila-horario-dia">
                <div class="col-dia">
                    <label class="switch-dia">
                        <input type="checkbox" name="horarios[<?php echo $numDia; ?>][activo]" value="1" <?php echo $activo ? 'checked' : ''; ?>>
                        <span class="dia-nombre"><?php echo $nombreDia; ?></span>
                    </label>
                </div>
                <div class="col-horas-rango">
                    <div class="rango-input">
                        <label>Desde:</label>
                        <input type="time" name="horarios[<?php echo $numDia; ?>][hora_inicio]" value="<?php echo $horaInicio; ?>">
                    </div>
                    <div class="rango-input">
                        <label>Hasta:</label>
                        <input type="time" name="horarios[<?php echo $numDia; ?>][hora_fin]" value="<?php echo $horaFin; ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
