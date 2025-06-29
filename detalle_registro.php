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
//include __DIR__ . '/../src/views/navbar.php';
?>

<style>
.page-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.detail-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.detail-container h2 {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 10px 0;
    text-align: center;
}

.registry-header {
    text-align: center;
    margin-bottom: 30px;
}

.registry-title {
    font-size: 18px;
    font-weight: 600;
    color: #007bff;
    margin: 5px 0;
}

.registry-subtitle {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 20px;
}

.operation-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
}

.operation-badge.entrada {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.operation-badge.salida {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f1b0b7;
}

.operation-badge::before {
    content: "⬇";
    font-size: 16px;
}

.operation-badge.salida::before {
    content: "⬆";
}

.section-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 4px solid #007bff;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 600;
    color: #495057;
}

.section-icon {
    width: 20px;
    height: 20px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.detail-item {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
}

.detail-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.detail-value {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
    word-wrap: break-word;
}

.detail-value.large {
    font-size: 16px;
    font-weight: 600;
}

.detail-value.currency::before {
    content: "USD ";
    color: #28a745;
    font-weight: 600;
}

.detail-value.weight::after {
    content: " KG";
    color: #6c757d;
    font-weight: normal;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item.description {
    min-height: 80px;
}

.detail-item.description .detail-value {
    line-height: 1.5;
}

.tag {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.entry-tag {
    background: #d4edda;
    color: #155724;
}

.exit-tag {
    background: #f8d7da;
    color: #721c24;
}

.form-actions {
    display: flex;
    justify-content: flex-start;
    gap: 15px;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #e9ecef;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border-left: 4px solid;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left-color: #28a745;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-left-color: #dc3545;
}

/* Responsive */
@media (max-width: 768px) {
    .page-content {
        padding: 15px;
    }
    
    .detail-container {
        padding: 20px;
    }
    
    .detail-container h2 {
        font-size: 24px;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .detail-container {
        padding: 15px;
    }
    
    .section-header {
        font-size: 16px;
    }
}

/* Animaciones */
.detail-container {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.detail-item {
    animation: slideIn 0.4s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Estilo para el body */
body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
}
</style>

<div class="page-content">
    <div class="detail-container">
        <h2>Detalle del Registro de Bitácora</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-actions">
                <a href="/bitacora.php" class="btn btn-secondary">Volver a Bitácora</a>
            </div>
        <?php elseif ($registro): ?>
            <div class="registry-header">
                <div class="registry-title">
                    Registro #<?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?>
                </div>
                <div class="registry-subtitle">
                    Fecha de Ingreso: <?php echo htmlspecialchars(date('d/m/Y', strtotime($registro['fecha_ingreso']))); ?> • 
                    <?php echo htmlspecialchars(date('H:i', strtotime($registro['fecha_ingreso']))); ?> hrs
                </div>
                <div class="operation-badge <?php echo strtolower($registro['tipo_operacion']); ?>">
                    <?php echo htmlspecialchars($registro['tipo_operacion']); ?> de Mercancías
                </div>
            </div>

            <!-- Información General -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">ℹ</div>
                    Información General
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Fecha de Ingreso</div>
                        <div class="detail-value"><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_ingreso']))); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tipo de Contenedor</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['dimension_tipo_sellos_candados'] ?? '40\' HC'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fecha de Conclusión de Descarga</div>
                        <div class="detail-value"><?php echo !empty($registro['fecha_conclusion_descarga']) ? htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_conclusion_descarga']))) : 'N/A'; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Número de Conocimiento de Embarque</div>
                        <div class="detail-value large"><?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sellos/Candados</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['dimension_tipo_sellos_candados'] ?? 'SEAL-789456, LOCK-123789'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Número de Bultos</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['num_bultos']); ?> bultos</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Número de Registro de Buque</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['num_registro_buque_vuelo_contenedor']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Primer Puerto</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['primer_puerto_terminal']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Peso Total</div>
                        <div class="detail-value weight"><?php echo number_format(htmlspecialchars($registro['peso_unidad_medida']), 0, '.', ','); ?></div>
                    </div>
                </div>
            </div>

            <!-- Descripción de Mercancías -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">📦</div>
                    Descripción de Mercancías
                </div>
                <div class="detail-grid">
                    <div class="detail-item full-width description">
                        <div class="detail-label">Descripción Detallada</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($registro['descripcion_mercancia'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Valor Comercial</div>
                        <div class="detail-value currency"><?php echo number_format(htmlspecialchars($registro['valor_comercial']), 2, '.', ','); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Unidad de Medida</div>
                        <div class="detail-value">Kilogramos (KG)</div>
                    </div>
                </div>
            </div>

            <!-- Información del Consignatario -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">👤</div>
                    Información del Consignatario
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nombre de la Empresa</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['consignatario_nombre']); ?></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Domicilio Completo</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($registro['consignatario_domicilio'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">RFC</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['consignatario_rfc'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email de Contacto</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['consignatario_email'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Teléfono</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['consignatario_telefono'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Información del Remitente -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">🏢</div>
                    Información del Remitente
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nombre de la Empresa</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['remitente_nombre']); ?></div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Domicilio</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($registro['remitente_domicilio'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">País de Origen</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['remitente_pais_origen'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Información de Registro -->
            <div class="section-card">
                <div class="section-header">
                    <div class="section-icon">📋</div>
                    Información de Registro
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Registrado por</div>
                        <div class="detail-value"><?php echo htmlspecialchars($registro['registrado_por_username'] ?? 'Usuario Desconocido'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fecha de Registro</div>
                        <div class="detail-value"><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_ingreso']))); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">ID de Registro</div>
                        <div class="detail-value">REG-<?php echo str_pad($registro_id, 4, '0', STR_PAD_LEFT); ?>-<?php echo date('y', strtotime($registro['fecha_ingreso'])); ?>-001</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Usuario del Sistema</div>
                        <div class="detail-value">mgonzalez_capitalista</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Última Modificación</div>
                        <div class="detail-value"><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_ingreso']))); ?></div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="/bitacora.php" class="btn btn-secondary">Volver a Bitácora</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>