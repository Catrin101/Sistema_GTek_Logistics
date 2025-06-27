<?php
// public/index.php

require_once __DIR__ . '/src/config/db.php';
require_once __DIR__ . '/src/core/Auth.php';

// Iniciar sesión si aún no está iniciada (Auth::isLoggedIn() ya lo hace)
// Auth::isLoggedIn() internamente llama a session_start() si es necesario.

// Si el usuario ya está logueado, redirigir a bitacora.php
if (Auth::isLoggedIn()) {
    header('Location: /bitacora.php');
    exit;
}

$loginError = ''; // Variable para almacenar mensajes de error

// Procesar el formulario de login si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $auth = new Auth();
    if ($auth->login($username, $password)) {
        header('Location: /bitacora.php');
        exit;
    } else {
        $loginError = 'Usuario o contraseña incorrectos.';
    }
}

$pageTitle = "Login - Gtek Logistics";
include __DIR__ . '/src/views/header.php';
?>
<div class="login-container">
    <h2>Inicio de Sesión</h2>
    <?php if ($loginError): ?>
        <p class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $loginError; ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Ingresar</button>
    </form>
</div>
<?php include __DIR__ . '/src/views/footer.php'; ?>