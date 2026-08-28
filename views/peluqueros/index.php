<?php /** @var array $peluqueros */ ?>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h1 class="nombre-pagina">Peluqueros</h1>
<p class="descripcion-pagina">Administración de Peluqueros del Salón</p>

<div class="acciones">
    <a class="boton" href="/peluqueros/crear">Nuevo Peluquero</a>
</div>

<p>Listado de Peluqueros:</p>

<ul class="servicios">
    <?php foreach($peluqueros as $peluquero) { ?>
        <li>
            <p>Nombre: <span><?php echo s($peluquero->nombre . " " . $peluquero->apellido); ?></span></p>
            <p>Email: <span><?php echo s($peluquero->email); ?></span></p>
            <p>Teléfono: <span><?php echo s($peluquero->telefono); ?></span></p>

            <div class="acciones">
                <a class="boton" href="/peluqueros/actualizar?id=<?php echo $peluquero->id; ?>">Actualizar</a>
                <form action="/peluqueros/eliminar" method="POST">
                    <input type="hidden" name="id" value="<?php echo $peluquero->id; ?>">
                    <input type="submit" value="Borrar" class="boton-eliminar">
                </form>
            </div>
        </li>
    <?php } ?>
</ul>
