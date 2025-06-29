<?php
// public/detalle_vehiculo.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Vehiculo.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Detalle del Vehículo - Gtek Logistics";

$vehiculo = null;
$error_message = '';

// Obtener el ID del vehículo de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehiculo_id = (int)$_GET['id'];
    $vehiculoModel = new Vehiculo();
    $vehiculo = $vehiculoModel->getVehiculoById($vehiculo_id);

    if (!$vehiculo) {
        $error_message = "Vehículo no encontrado.";
    }
} else {
    $error_message = "ID de vehículo inválido o no especificado.";
}

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="detail-container">
        <h2>Detalle del Vehículo</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-actions">
                <a href="/vehiculos.php" class="btn btn-secondary">Volver a Vehículos</a>
            </div>
        <?php elseif ($vehiculo): ?>
            <div class="detail-grid">
                <div class="detail-group">
                    <strong>Nombre del Conductor:</strong>
                    <span><?php echo htmlspecialchars($vehiculo['nombre_conductor']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Placas:</strong>
                    <span><?php echo htmlspecialchars($vehiculo['placas']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Empresa:</strong>
                    <span><?php echo htmlspecialchars($vehiculo['empresa'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Modelo:</strong>
                    <span><?php echo htmlspecialchars($vehiculo['modelo'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Registrado por:</strong>
                    <span><?php echo htmlspecialchars($vehiculo['usuario_del_sistema_username'] ?? 'Usuario Desconocido'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Fecha de Registro:</strong>
                    <span><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($vehiculo['fecha_creacion']))); ?></span>
                </div>
            </div>

            <div class="form-actions" style="justify-content: flex-start;">
                <a href="/vehiculos.php" class="btn btn-secondary">Volver a Vehículos</a>
                </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>