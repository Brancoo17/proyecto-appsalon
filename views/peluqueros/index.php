<?php /** @var array $peluqueros */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Peluqueros</h1>
<p class="descripcion-pagina">Administración de Peluqueros del Salón</p>

<div class="panel-header">
    <h2>Listado de Peluqueros</h2>
    <a class="boton-nuevo" href="/peluqueros/crear"><i class="fa-solid fa-user-plus"></i> Nuevo Peluquero</a>
</div>

<?php if(empty($peluqueros)): ?>
    <div class="alerta-vacio">
        <p><i class="fa-solid fa-info-circle"></i> No hay peluqueros registrados</p>
    </div>
<?php else: ?>
    <div class="contenedor-tabla">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre y Apellido</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($peluqueros as $peluquero): ?>
                    <tr>
                        <td class="col-id">#<?php echo s($peluquero->id); ?></td>
                        <td class="col-nombre">
                            <strong class="peluquero-nombre"><i class="fa-solid fa-user"></i> <?php echo s($peluquero->nombre . " " . $peluquero->apellido); ?></strong>
                        </td>
                        <td class="col-email">
                            <span><i class="fa-solid fa-envelope"></i> <?php echo s($peluquero->email); ?></span>
                        </td>
                        <td class="col-telefono">
                            <span><i class="fa-solid fa-phone"></i> <?php echo s($peluquero->telefono); ?></span>
                        </td>
                        <td class="col-acciones">
                            <div class="acciones-tabla">
                                <a class="boton-editar-tabla" href="/peluqueros/actualizar?id=<?php echo $peluquero->id; ?>" title="Editar Peluquero">
                                    <i class="fa-solid fa-user-pen"></i>
                                </a>
                                <form action="/peluqueros/eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este peluquero?');">
                                    <input type="hidden" name="id" value="<?php echo $peluquero->id; ?>">
                                    <button type="submit" class="boton-eliminar-tabla" title="Eliminar Peluquero">
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
