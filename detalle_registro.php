<?php
// public/detalle_registro.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Bitacora.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Detalle del Registro - Gtek Logistics";

$registro = null;
$error_message = '';

// Obtener el ID del registro de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $registro_id = (int)$_GET['id'];
    $bitacoraModel = new Bitacora();
    $registro = $bitacoraModel->getRegistroById($registro_id);

    if (!$registro) {
        $error_message = "Registro no encontrado.";
    }
} else {
    $error_message = "ID de registro inválido o no especificado.";
}

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="detail-container">
        <h2>Detalle del Registro de Bitácora</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-actions">
                <a href="/bitacora.php" class="btn btn-secondary">Volver a Bitácora</a>
            </div>
        <?php elseif ($registro): ?>
            <div class="detail-grid">
                <div class="detail-group">
                    <strong>Fecha de Ingreso:</strong>
                    <span><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_ingreso']))); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Tipo de Operación:</strong>
                    <span class="tag <?php echo ($registro['tipo_operacion'] === 'Entrada' ? 'entry-tag' : 'exit-tag'); ?>"><?php echo htmlspecialchars($registro['tipo_operacion']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>N° Conocimiento Embarque:</strong>
                    <span><?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>N° Registro (Buque/Vuelo/Contenedor):</strong>
                    <span><?php echo htmlspecialchars($registro['num_registro_buque_vuelo_contenedor']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Dimensión/Sellos/Candados:</strong>
                    <span><?php echo htmlspecialchars($registro['dimension_tipo_sellos_candados'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Primer Puerto/Terminal:</strong>
                    <span><?php echo htmlspecialchars($registro['primer_puerto_terminal']); ?></span>
                </div>
                <div class="detail-group full-width">
                    <strong>Descripción de la Mercancía:</strong>
                    <span><?php echo nl2br(htmlspecialchars($registro['descripcion_mercancia'])); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Peso y Unidad de Medida:</strong>
                    <span><?php echo htmlspecialchars($registro['peso_unidad_medida']); ?> KG</span>
                </div>
                <div class="detail-group">
                    <strong>Número de Bultos:</strong>
                    <span><?php echo htmlspecialchars($registro['num_bultos']); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Valor Comercial:</strong>
                    <span>$<?php echo number_format(htmlspecialchars($registro['valor_comercial']), 2, '.', ','); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Fecha Conclusión Descarga:</strong>
                    <span><?php echo !empty($registro['fecha_conclusion_descarga']) ? htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_conclusion_descarga']))) : 'N/A'; ?></span>
                </div>
                <div class="detail-group">
                    <strong>Registrado por:</strong>
                    <span><?php echo htmlspecialchars($registro['registrado_por_username'] ?? 'Usuario Desconocido'); ?></span>
                </div>
            </div>

            <h3 class="section-heading">Datos del Consignatario</h3>
            <div class="detail-grid">
                <div class="detail-group">
                    <strong>Nombre:</strong>
                    <span><?php echo htmlspecialchars($registro['consignatario_nombre']); ?></span>
                </div>
                <div class="detail-group full-width">
                    <strong>Domicilio:</strong>
                    <span><?php echo nl2br(htmlspecialchars($registro['consignatario_domicilio'])); ?></span>
                </div>
                <div class="detail-group">
                    <strong>RFC:</strong>
                    <span><?php echo htmlspecialchars($registro['consignatario_rfc'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($registro['consignatario_email'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-group">
                    <strong>Teléfono:</strong>
                    <span><?php echo htmlspecialchars($registro['consignatario_telefono'] ?? 'N/A'); ?></span>
                </div>
            </div>

            <h3 class="section-heading">Datos del Remitente</h3>
            <div class="detail-grid">
                <div class="detail-group">
                    <strong>Nombre:</strong>
                    <span><?php echo htmlspecialchars($registro['remitente_nombre']); ?></span>
                </div>
                <div class="detail-group full-width">
                    <strong>Domicilio:</strong>
                    <span><?php echo nl2br(htmlspecialchars($registro['remitente_domicilio'])); ?></span>
                </div>
                <div class="detail-group">
                    <strong>País de Origen:</strong>
                    <span><?php echo htmlspecialchars($registro['remitente_pais_origen'] ?? 'N/A'); ?></span>
                </div>
            </div>

            <div class="form-actions" style="justify-content: flex-start;">
                <a href="/bitacora.php" class="btn btn-secondary">Volver a Bitácora</a>
                </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>