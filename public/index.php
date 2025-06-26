<?php
// public/index.php
// Este archivo manejará el formulario de login

// Incluir el archivo de conexión a la base de datos
// require_once __DIR__ . '/../src/config/db.php';
// require_once __DIR__ . '/../src/core/Auth.php'; // Para la lógica de autenticación

// Lógica de autenticación simple (se desarrollará en Día 2)
// Si el usuario ya está logueado, redirigir a bitacora.php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /bitacora.php');
    exit;
}

$pageTitle = "Login - Gtek Logistics";
include __DIR__ . '/../src/views/header.php';
?>
<div class="login-container">
    <h2>Inicio de Sesión</h2>
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
<?php include __DIR__ . '/../src/views/footer.php'; ?>