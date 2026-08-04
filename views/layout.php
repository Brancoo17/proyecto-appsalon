<?php
    $contenido = $contenido ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/build/img/icons8-peluquero-48.png" type="image/png">

    <title>BarberShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;700;900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="/build/css/app.css">
</head>
<body>

    <div class="app-container">
        <div class="imagen"></div>
        <div class="app">
            <?php echo $contenido; ?>
        </div>
    </div>

    <?php echo $script ?? '' ?>
            
</body>
</html>