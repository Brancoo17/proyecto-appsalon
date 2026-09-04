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
    <?php $vCss = file_exists(__DIR__ . '/../public/build/css/app.css') ? filemtime(__DIR__ . '/../public/build/css/app.css') : '2.0'; ?>
    <link rel="stylesheet" href="/build/css/app.css?v=<?php echo $vCss; ?>">
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

    <script>
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-toggle-password');
            if (!btn) return;
            const container = btn.closest('.contenedor-input-password') || btn.parentElement;
            const input = container ? container.querySelector('input') : null;
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
                }
            }
        });
    </script>
            
</body>
</html>