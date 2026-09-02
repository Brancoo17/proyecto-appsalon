<?php 
/** @var array $servicios */ 
/** @var string|null $resultado */ 
$resultado = $resultado ?? $_GET['resultado'] ?? null;
?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Servicios</h1>
<p class="descripcion-pagina">Administración de Servicios del Salón</p>

<?php if($resultado === '1'): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i> Servicio creado correctamente
    </div>
<?php elseif($resultado === '2'): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i> Servicio actualizado correctamente
    </div>
<?php elseif($resultado === '3'): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i> Servicio eliminado correctamente
    </div>
<?php endif; ?>

<div class="panel-header">
    <h2>Listado de Servicios</h2>
    <a class="boton-nuevo" href="/servicios/crear"><i class="fa-solid fa-plus"></i> Nuevo Servicio</a>
</div>

<?php if(empty($servicios)): ?>
    <div class="alerta-vacio">
        <p><i class="fa-solid fa-info-circle"></i> No hay servicios registrados</p>
    </div>
<?php else: ?>
    <div class="contenedor-tabla">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Servicio</th>
                    <th>Duración</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($servicios as $servicio): ?>
                    <tr>
                        <td class="col-id">#<?php echo s($servicio->id); ?></td>
                        <td class="col-nombre"><strong><?php echo s($servicio->nombre); ?></strong></td>
                        <td class="col-duracion">
                            <span class="badge-duracion"><i class="fa-regular fa-clock"></i> <?php echo s($servicio->duracion ?? 30); ?> min</span>
                        </td>
                        <td class="col-precio"><strong class="precio-total">$<?php echo s($servicio->precio); ?></strong></td>
                        <td class="col-acciones">
                            <div class="acciones-tabla">
                                <a href="/servicios/actualizar?id=<?php echo $servicio->id; ?>" class="boton-editar-tabla" title="Editar Servicio">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="/servicios/eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este servicio?');">
                                    <input type="hidden" name="id" value="<?php echo $servicio->id; ?>">
                                    <button type="submit" class="boton-eliminar-tabla" title="Eliminar Servicio">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>