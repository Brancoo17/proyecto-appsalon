<?php
    $contenido = $contenido ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/build/img/icons8-peluquero-48.png" type="image/png">

    <title><?php echo $titulo ?? 'BarberShop'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <link rel="stylesheet" href="/build/css/app.css">
</head>
<body class="<?php echo isset($is_home) && $is_home ? 'home-body' : ''; ?>">

    <?php if(isset($is_home) && $is_home): ?>
        <?php echo $contenido; ?>
    <?php else: ?>
        <div class="app-container">
            <div class="imagen"></div>
            <div class="app">
                <?php echo $contenido; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php echo $script ?? '' ?>
            
</body>
</html>