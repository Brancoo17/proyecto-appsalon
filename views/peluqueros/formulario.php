<?php /** @var object $peluquero */ ?>

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
    <input type="password" id="password" placeholder="Password de acceso" name="password" />
</div>
