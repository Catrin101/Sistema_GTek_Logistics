<?php
// public/detalle_visitante.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Visitante.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Detalle del Visitante - Gtek Logistics";

$visitante = null;
$error_message = '';

// Obtener el ID del visitante de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visitante_id = (int)$_GET['id'];
    $visitanteModel = new Visitante();
    $visitante = $visitanteModel->getVisitanteById($visitante_id);

    if (!$visitante) {
        $error_message = "Visitante no encontrado.";
    }
} else {
    $error_message = "ID de visitante inválido o no especificado.";
}

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="detail-container">
        <h2>Detalle del Visitante</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-actions">
                <a href="/visitantes.php" class="btn btn-secondary">Volver a Visitantes</a>
            </div>
        <?php elseif ($visitante): ?>
            <div class="detail-grid">
                <div class="detail-group">
                    <strong>Nombre del Visitante:</strong>
                    <span><?php echo htmlspecialchars($visitante['nombre']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Número de Verificación:</strong>
                    <span><?php echo htmlspecialchars($visitante['numero_verificacion'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Fecha de Registro:</strong>
                    <span><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($visitante['fecha_creacion']))); ?></span>
                </div>
            </div>

            <div class="form-actions" style="justify-content: flex-start;">
                <a href="/visitantes.php" class="btn btn-secondary">Volver a Visitantes</a>
                </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>