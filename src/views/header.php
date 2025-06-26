<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Gtek Logistics - Bitácora'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    </head>
<body>
    <div class="main-container">
        <header class="app-header">
            <div class="logo">
                <img src="/assets/img/gtek_logo.png" alt="Gtek Logistics Logo">
                <h1>Gtek Logistics</h1>
            </div>
            <div class="user-info">
                <?php
                // Incluir Auth para verificar sesión
                require_once __DIR__ . '/../core/Auth.php';
                if (Auth::isLoggedIn()) {
                    echo '<span>Usuario: <strong>' . Auth::getUsername() . '</strong></span>';
                    echo '<a href="/logout.php" class="btn btn-logout">Cerrar Sesión</a>'; // Botón de logout
                }
                ?>
            </div>
        </header>
        <div class="content-wrapper">
            <?php 
            // Solo incluir navbar si el usuario está logueado
            if (Auth::isLoggedIn()) {
                include __DIR__ . '/navbar.php'; 
            }
            ?>
            <main class="main-content">