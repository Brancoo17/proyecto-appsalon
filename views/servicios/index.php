<?php /** @var array $servicios */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Servicios</h1>
<p class="descripcion-pagina">Administración de Servicios</p>

<div class="acciones">
    <a class="boton" href="/servicios/crear">Nuevo Servicio</a>
</div>

<p>Listado de Servicios:</p>

<ul class="servicios">
    <?php foreach($servicios as $servicio): ?>
        <li>
            <p>Nombre: <span><?php echo $servicio->nombre; ?></span></p>
            <p>Precio: <span>$<?php echo $servicio->precio; ?></span></p>

            <div class="acciones">
                <a href="/servicios/actualizar?id=<?php echo $servicio->id; ?>" class="boton">Actualizar</a>

                <form action="/servicios/eliminar" method="POST">
                    <input type="hidden" name="id" value="<?php echo $servicio->id; ?>">
                    <input type="submit" value="Eliminar" class="boton-eliminar">
                </form>
            </div>
        </li>
    <?php endforeach; ?>
</ul>