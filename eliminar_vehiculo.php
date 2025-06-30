<?php
// public/eliminar_vehiculo.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Vehiculo.php';

// Verificar sesión y permisos
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$success_message = '';
$error_message = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehiculo_id = (int)$_GET['id'];
    $vehiculoModel = new Vehiculo();

    if ($vehiculoModel->deleteVehiculo($vehiculo_id)) {
        // Redirigir de vuelta a la página de vehículos con un mensaje de éxito
        header('Location: /vehiculos.php?status=success&message=' . urlencode("Vehículo eliminado exitosamente."));
        exit;
    } else {
        // Redirigir de vuelta con un mensaje de error
        header('Location: /vehiculos.php?status=error&message=' . urlencode("Error al eliminar el vehículo."));
        exit;
    }
} else {
    // ID no proporcionado o inválido
    header('Location: /vehiculos.php?status=error&message=' . urlencode("ID de vehículo no válido o no especificado."));
    exit;
}