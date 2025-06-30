<?php
// public/eliminar_registro.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Bitacora.php';

// Verificar sesión y permisos (ej. solo administradores o usuarios con rol de eliminación)
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$success_message = '';
$error_message = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $registro_id = (int)$_GET['id'];
    $bitacoraModel = new Bitacora();

    if ($bitacoraModel->deleteRegistro($registro_id)) {
        // Redirigir de vuelta a la página de bitácora con un mensaje de éxito
        header('Location: /bitacora.php?status=success&message=' . urlencode("Registro eliminado exitosamente."));
        exit;
    } else {
        // Redirigir de vuelta con un mensaje de error
        header('Location: /bitacora.php?status=error&message=' . urlencode("Error al eliminar el registro."));
        exit;
    }
} else {
    // ID no proporcionado o inválido
    header('Location: /bitacora.php?status=error&message=' . urlencode("ID de registro no válido o no especificado."));
    exit;
}