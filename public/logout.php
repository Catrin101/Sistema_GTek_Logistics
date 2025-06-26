<?php
// public/logout.php
require_once __DIR__ . '/../src/core/Auth.php';

Auth::logout(); // Cierra la sesión
header('Location: /index.php'); // Redirige al login
exit;
?>