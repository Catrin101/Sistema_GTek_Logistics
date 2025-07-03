<?php
// public/registro_vehiculo.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Vehiculo.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Registrar Nuevo Vehículo - Gtek Logistics";

// Variables para almacenar los valores del formulario (si hay errores)
$formData = $_POST; // Guardamos todos los datos del POST para repoblado
$errors = [];
$successMessage = '';

$vehiculoModel = new Vehiculo();

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Validaciones ---
    if (empty($formData['nombre_conductor'])) $errors[] = "El nombre del conductor es obligatorio.";
    if (empty($formData['placas'])) $errors[] = "Las placas son obligatorias.";
    
    // Validar formato de placas (opcional, puedes ajustar según tus necesidades)
    if (!empty($formData['placas']) && !preg_match('/^[A-Z0-9-]+$/', strtoupper($formData['placas']))) {
        $errors[] = "Las placas deben contener solo letras, números y guiones.";
    }

    // Si no hay errores de validación, proceder a guardar
    if (empty($errors)) {
        try {
            $data = [
                'nombre_conductor' => trim($formData['nombre_conductor']),
                'placas' => trim(strtoupper($formData['placas'])), // Convertir placas a mayúsculas
                'empresa' => !empty(trim($formData['empresa'])) ? trim($formData['empresa']) : null,
                'modelo' => !empty(trim($formData['modelo'])) ? trim($formData['modelo']) : null,
                'fecha_salida' => !empty(trim($formData['fecha_salida'])) ? trim($formData['fecha_salida']) : null,
                'usuario_del_sistema_id' => Auth::getUserId(), // Obtener el ID del usuario logueado
            ];

            $vehiculo_id = $vehiculoModel->createVehiculo($data);

            if ($vehiculo_id) {
                $successMessage = "Vehículo registrado exitosamente con ID: " . $vehiculo_id;
                // Limpiar el formulario después de un éxito para un nuevo registro
                $formData = []; 
            } else {
                $errors[] = "Error al registrar el vehículo. Por favor, intente de nuevo.";
            }

        } catch (Exception $e) {
            error_log("Error al procesar registro de vehículo: " . $e->getMessage());
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

.normativa {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 30px;
    font-style: italic;
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

.form-group label::after {
    content: " *";
    color: #dc3545;
    display: none;
}

.form-group input[required] + label::after,
.form-group textarea[required] + label::after,
label[for] + input[required]::before,
label[for] + textarea[required]::before {
    display: none;
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

/* Estilos específicos para campos de fecha y hora */
input[type="datetime-local"] {
    position: relative;
}

input[type="number"] {
    -moz-appearance: textfield;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
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

@media (max-width: 480px) {
    .form-container {
        padding: 15px;
    }
    
    .section-heading {
        font-size: 18px;
    }
}

/* Animaciones sutiles */
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

.form-group {
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

/* Mejoras visuales adicionales */
.form-group input:valid,
.form-group textarea:valid {
    border-color: #28a745;
}

.form-group input:invalid:not(:placeholder-shown),
.form-group textarea:invalid:not(:placeholder-shown) {
    border-color: #dc3545;
}

/* Estilos para campos requeridos */
.form-group input[required],
.form-group textarea[required] {
    position: relative;
}

/* Indicador visual para campos requeridos */
.form-group label {
    position: relative;
}

/* Hover effects para mejorar UX */
.form-group:hover input,
.form-group:hover textarea,
.form-group:hover select {
    border-color: #007bff;
}

/* Estilo para el contenedor principal similar a bitácora */
body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
}
</style>

<div class="page-content">
    <div class="form-container">
        <h2>Registrar Nuevo Vehículo</h2>
        <p class="normativa">Sistema de Gestión Logística - Campos requeridos</p>

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

        <form action="" method="POST" class="register-form">
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
                <div class="form-group">
                    <label for="fecha_salida">Fecha de Salida</label>
                    <input type="datetime-local" id="fecha_salida" name="fecha_salida"
                           value="<?php echo htmlspecialchars($formData['fecha_salida'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Información del Registro</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registrado por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(Auth::getUsername()); ?></span>
                    <input type="hidden" name="usuario_del_sistema_id" value="<?php echo htmlspecialchars(Auth::getUserId()); ?>">
                </div>
                <div class="form-group">
                    <label>Fecha de Registro</label>
                    <span class="read-only-field"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </div>

            <div class="form-actions">
                <a href="/vehiculos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Vehículo</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>