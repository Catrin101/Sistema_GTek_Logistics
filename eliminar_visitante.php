<?php
// public/eliminar_visitante.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Visitante.php';

// Verificar sesión y permisos
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$success_message = '';
$error_message = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visitante_id = (int)$_GET['id'];
    $visitanteModel = new Visitante();

    if ($visitanteModel->deleteVisitante($visitante_id)) {
        // Redirigir de vuelta a la página de visitantes con un mensaje de éxito
        header('Location: /visitantes.php?status=success&message=' . urlencode("Visitante eliminado exitosamente."));
        exit;
    } else {
        // Redirigir de vuelta con un mensaje de error
        header('Location: /visitantes.php?status=error&message=' . urlencode("Error al eliminar el visitante."));
        exit;
    }
} else {
    // ID no proporcionado o inválido
    header('Location: /visitantes.php?status=error&message=' . urlencode("ID de visitante no válido o no especificado."));
    exit;
}