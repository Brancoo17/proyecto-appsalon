<?php /** @var Object $servicio */ ?>

<div class="campo">
    <label for="nombre">Servicio</label>
    <input type="text" name="nombre" id="nombre" placeholder="Nombre del Servicio" value="<?php echo s($servicio->nombre); ?>">
</div>

<div class="campo">
    <label for="precio">Precio ($)</label>
    <input type="number" name="precio" id="precio" placeholder="Precio del Servicio" value="<?php echo s($servicio->precio); ?>">
</div>

<div class="campo">
    <label for="duracion">Duración</label>
    <select name="duracion" id="duracion">
        <option value="15" <?php echo ($servicio->duracion == 15) ? 'selected' : ''; ?>>15 minutos</option>
        <option value="30" <?php echo (!isset($servicio->duracion) || $servicio->duracion == 30) ? 'selected' : ''; ?>>30 minutos (Estándar)</option>
        <option value="45" <?php echo ($servicio->duracion == 45) ? 'selected' : ''; ?>>45 minutos</option>
        <option value="60" <?php echo ($servicio->duracion == 60) ? 'selected' : ''; ?>>60 minutos (1 hora)</option>
        <option value="90" <?php echo ($servicio->duracion == 90) ? 'selected' : ''; ?>>90 minutos (1 hora y media)</option>
        <option value="120" <?php echo ($servicio->duracion == 120) ? 'selected' : ''; ?>>120 minutos (2 horas)</option>
    </select>
</div>