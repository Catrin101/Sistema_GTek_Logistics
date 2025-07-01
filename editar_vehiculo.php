<?php
// public/editar_vehiculo.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Vehiculo.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

// Verificar que el usuario sea admin
if (Auth::getUserRole() !== 'admin') {
    header('Location: /vehiculos.php?status=error&message=' . urlencode('No tienes permisos para editar vehículos.'));
    exit;
}

$pageTitle = "Editar Vehículo - Gtek Logistics";

// Obtener el ID del vehículo de la URL
$vehiculo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vehiculo_id <= 0) {
    header('Location: /vehiculos.php?status=error&message=' . urlencode('ID de vehículo inválido.'));
    exit;
}

$vehiculoModel = new Vehiculo();
$vehiculo = $vehiculoModel->getVehiculoById($vehiculo_id);

if (!$vehiculo) {
    header('Location: /vehiculos.php?status=error&message=' . urlencode('Vehículo no encontrado.'));
    exit;
}

// Variables para almacenar los valores del formulario
$formData = $vehiculo; // Cargar datos actuales del vehículo
$errors = [];
$successMessage = '';

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST; // Actualizar con los datos del formulario
    
    // --- Validaciones ---
    if (empty($formData['nombre_conductor'])) $errors[] = "El nombre del conductor es obligatorio.";
    if (empty($formData['placas'])) $errors[] = "Las placas son obligatorias.";
    
    // Validar formato de placas
    if (!empty($formData['placas']) && !preg_match('/^[A-Z0-9-]+$/', strtoupper($formData['placas']))) {
        $errors[] = "Las placas deben contener solo letras, números y guiones.";
    }

    // Verificar que las placas no estén en uso por otro vehículo (opcional, ya que el método no existe aún)
    // Puedes implementar esta validación más adelante si es necesaria

    // Si no hay errores de validación, proceder a actualizar
    if (empty($errors)) {
        try {
            $data = [
                'nombre_conductor' => trim($formData['nombre_conductor']),
                'placas' => trim(strtoupper($formData['placas'])),
                'empresa' => !empty(trim($formData['empresa'])) ? trim($formData['empresa']) : null,
                'modelo' => !empty(trim($formData['modelo'])) ? trim($formData['modelo']) : null,
                'usuario_del_sistema_id' => $vehiculo['usuario_del_sistema_id'] // Mantener el usuario original
            ];

            $success = $vehiculoModel->updateVehiculo($vehiculo_id, $data);

            if ($success) {
                header('Location: /vehiculos.php?status=success&message=' . urlencode('Vehículo actualizado exitosamente.'));
                exit;
            } else {
                $errors[] = "Error al actualizar el vehículo. Por favor, intente de nuevo.";
            }

        } catch (Exception $e) {
            error_log("Error al procesar actualización de vehículo: " . $e->getMessage());
            $errors[] = "Error interno del servidor. Por favor, inténtelo más tarde.";
        }
    }
}

include __DIR__ . '/src/views/header.php';
?>

<style>
.page-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}

.form-container h2 {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 10px 0;
    text-align: center;
}

.vehicle-info {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 30px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #17a2b8;
}

.section-heading {
    font-size: 20px;
    font-weight: 600;
    color: #495057;
    margin: 30px 0 20px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
}

.section-heading:first-of-type {
    margin-top: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: white;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a0a5aa;
    font-style: italic;
}

.read-only-field {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: #f8f9fa;
    color: #6c757d;
    font-style: italic;
}

.required {
    color: #dc3545;
    font-weight: bold;
}

.form-actions {
    display: flex;
    justify-content: center;
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
    background: #28a745;
    color: white;
}

.btn-primary:hover {
    background: #218838;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}

.alert li:last-child {
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .page-content {
        padding: 15px;
    }
    
    .form-container {
        padding: 20px;
    }
    
    .form-container h2 {
        font-size: 24px;
    }
    
    .form-grid {
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

/* Animaciones */
.form-container {
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

/* Estilos para el contenedor principal */
body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
}
</style>

<div class="page-content">
    <div class="form-container">
        <h2>Editar Vehículo</h2>
        <div class="vehicle-info">
            <strong>ID:</strong> <?php echo htmlspecialchars($vehiculo['id']); ?> | 
            <strong>Registrado:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($vehiculo['fecha_creacion']))); ?> | 
            <strong>Por:</strong> <?php echo htmlspecialchars($vehiculo['usuario_del_sistema_username'] ?? 'Usuario Desconocido'); ?>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="edit-form">
            <h3 class="section-heading">Información del Vehículo</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_conductor">Nombre del Conductor *</label>
                    <input type="text" id="nombre_conductor" name="nombre_conductor" 
                           placeholder="Nombre completo del conductor"
                           value="<?php echo htmlspecialchars($formData['nombre_conductor'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="placas">Placas del Vehículo *</label>
                    <input type="text" id="placas" name="placas" 
                           placeholder="EJ: ABC-123-XYZ"
                           value="<?php echo htmlspecialchars($formData['placas'] ?? ''); ?>" 
                           style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="empresa">Empresa</label>
                    <input type="text" id="empresa" name="empresa" 
                           placeholder="Nombre de la empresa transportista"
                           value="<?php echo htmlspecialchars($formData['empresa'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo del Vehículo</label>
                    <input type="text" id="modelo" name="modelo" 
                           placeholder="EJ: Freightliner Cascadia 2020"
                           value="<?php echo htmlspecialchars($formData['modelo'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Información del Registro</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registrado Originalmente por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars($vehiculo['usuario_del_sistema_username'] ?? 'Usuario Desconocido'); ?></span>
                </div>
                <div class="form-group">
                    <label>Fecha de Registro Original</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($vehiculo['fecha_creacion']))); ?></span>
                </div>
                <div class="form-group">
                    <label>Editado por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(Auth::getUsername()); ?></span>
                </div>
                <div class="form-group">
                    <label>Fecha de Edición</label>
                    <span class="read-only-field"><?php echo date('d/m/Y H:i A'); ?></span>
                </div>
            </div>

            <div class="form-actions">
                <a href="/vehiculos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Vehículo</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>