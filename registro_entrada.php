<?php
// public/registro_entrada.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Bitacora.php';
require_once __DIR__ . '/src/models/Consignatario.php';
require_once __DIR__ . '/src/models/Remitente.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Registro de Entrada - Gtek Logistics";

// Variables para almacenar los valores del formulario (si hay errores)
$formData = $_POST; // Guardamos todos los datos del POST para repoblado
$errors = [];
$successMessage = '';

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Validaciones (Tarea 3) ---
    // General
    if (empty($formData['fecha_ingreso'])) $errors[] = "La Fecha de Ingreso es obligatoria.";
    if (empty($formData['num_conocimiento_embarque'])) $errors[] = "El Número de Conocimiento de Embarque es obligatorio.";
    if (empty($formData['num_registro_buque_vuelo_contenedor'])) $errors[] = "El Número de Registro (Buque/Vuelo/Contenedor) es obligatorio.";
    if (empty($formData['primer_puerto_terminal'])) $errors[] = "El Primer Puerto/Aeropuerto/Terminal es obligatorio.";
    if (empty($formData['descripcion_mercancia'])) $errors[] = "La Descripción de la Mercancía es obligatoria.";
    if (!isset($formData['peso_unidad_medida']) || !is_numeric($formData['peso_unidad_medida'])) $errors[] = "El Peso y Unidad de Medida es obligatorio y debe ser un número.";
    if (!isset($formData['num_bultos']) || !is_numeric($formData['num_bultos']) || $formData['num_bultos'] < 0) $errors[] = "El Número de Bultos es obligatorio y debe ser un número entero positivo.";
    if (!isset($formData['valor_comercial']) || !is_numeric($formData['valor_comercial']) || $formData['valor_comercial'] < 0) $errors[] = "El Valor Comercial es obligatorio y debe ser un número positivo.";

    // Consignatario
    if (empty($formData['consignatario_nombre'])) $errors[] = "El Nombre del Consignatario es obligatorio.";
    if (empty($formData['consignatario_domicilio'])) $errors[] = "El Domicilio del Consignatario es obligatorio.";

    // Remitente
    if (empty($formData['remitente_nombre'])) $errors[] = "El Nombre de quien envía (Remitente) es obligatorio.";
    if (empty($formData['remitente_domicilio'])) $errors[] = "El Domicilio de quien envía (Remitente) es obligatorio.";


    // Si no hay errores de validación, proceder a guardar
    if (empty($errors)) {
        try {
            $consignatarioModel = new Consignatario();
            $remitenteModel = new Remitente();
            $bitacoraModel = new Bitacora();

            // 1. Encontrar o crear Consignatario
            $consignatario_id = $consignatarioModel->findOrCreate([
                'nombre' => $formData['consignatario_nombre'],
                'domicilio' => $formData['consignatario_domicilio'],
                'rfc' => $formData['consignatario_rfc'] ?? null,
                'email' => $formData['consignatario_email'] ?? null,
                'telefono' => $formData['consignatario_telefono'] ?? null,
            ]);

            // 2. Encontrar o crear Remitente
            $remitente_id = $remitenteModel->findOrCreate([
                'nombre' => $formData['remitente_nombre'],
                'domicilio' => $formData['remitente_domicilio'],
                'pais_origen' => $formData['remitente_pais_origen'] ?? null,
            ]);

            // 3. Crear Registro de Bitácora
            $registroData = [
                'fecha_ingreso' => $formData['fecha_ingreso'],
                'tipo_operacion' => 'Entrada', // Por ahora, es solo de entrada
                'num_conocimiento_embarque' => $formData['num_conocimiento_embarque'],
                'num_registro_buque_vuelo_contenedor' => $formData['num_registro_buque_vuelo_contenedor'],
                'dimension_tipo_sellos_candados' => $formData['dimension_sellos_candados'] ?? null,
                'primer_puerto_terminal' => $formData['primer_puerto_terminal'],
                'descripcion_mercancia' => $formData['descripcion_mercancia'],
                'peso_unidad_medida' => $formData['peso_unidad_medida'],
                'num_bultos' => $formData['num_bultos'],
                'valor_comercial' => $formData['valor_comercial'],
                'fecha_conclusion_descarga' => !empty($formData['fecha_conclusion_descarga']) ? $formData['fecha_conclusion_descarga'] : null,
                'consignatario_id' => $consignatario_id,
                'remitente_id' => $remitente_id,
                'registrado_por_user_id' => Auth::getUserId(), // Obtener el ID del usuario logueado
            ];

            $newRecordId = $bitacoraModel->createRegistro($registroData);

            if ($newRecordId) {
                $successMessage = "Registro de entrada guardado exitosamente con ID: " . $newRecordId;
                // Limpiar el formulario después de un éxito para un nuevo registro
                $formData = []; 
            } else {
                $errors[] = "Ocurrió un error al guardar el registro en la bitácora. Por favor, inténtelo de nuevo.";
            }

        } catch (Exception $e) {
            error_log("Error al procesar registro de entrada: " . $e->getMessage());
            $errors[] = "Error interno del servidor. Por favor, inténtelo más tarde.";
        }
    }
}

include __DIR__ . '/src/views/header.php';
//include __DIR__ . '/src/views/navbar.php'; // Navbar para navegacion entre bitacora y registro_entrada
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
        <h2>Registro de Entrada de Mercancías</h2>
        <p class="normativa">Normativa 2.3.8 - Campos requeridos</p>

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

        <form action="" method="POST" class="entry-form">
            <h3 class="section-heading">Información General</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de Ingreso *</label>
                    <input type="datetime-local" id="fecha_ingreso" name="fecha_ingreso" 
                           value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? date('Y-m-d\TH:i')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="num_conocimiento_embarque">Número de Conocimiento de Embarque *</label>
                    <input type="text" id="num_conocimiento_embarque" name="num_conocimiento_embarque" 
                           placeholder="EJ: BL123456789" 
                           value="<?php echo htmlspecialchars($formData['num_conocimiento_embarque'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="num_registro_buque_vuelo_contenedor">Número de Registro (Buque/Vuelo/Contenedor) *</label>
                    <input type="text" id="num_registro_buque_vuelo_contenedor" name="num_registro_buque_vuelo_contenedor" 
                           placeholder="EJ: CONT123456" 
                           value="<?php echo htmlspecialchars($formData['num_registro_buque_vuelo_contenedor'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dimension_sellos_candados">Dimensión/Tipo/Sellos/Candados</label>
                    <input type="text" id="dimension_sellos_candados" name="dimension_sellos_candados" 
                           placeholder="Descripción detallada" 
                           value="<?php echo htmlspecialchars($formData['dimension_sellos_candados'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="primer_puerto_terminal">Primer Puerto/Aeropuerto/Terminal *</label>
                    <input type="text" id="primer_puerto_terminal" name="primer_puerto_terminal" 
                           placeholder="EJ: Puerto de Veracruz" 
                           value="<?php echo htmlspecialchars($formData['primer_puerto_terminal'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion_mercancia">Descripción de la Mercancía *</label>
                    <textarea id="descripcion_mercancia" name="descripcion_mercancia" rows="3" 
                              placeholder="Descripción detallada de la mercancía" required><?php echo htmlspecialchars($formData['descripcion_mercancia'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="peso_unidad_medida">Peso y Unidad de Medida *</label>
                    <input type="number" step="0.01" id="peso_unidad_medida" name="peso_unidad_medida" 
                           placeholder="1000.50" 
                           value="<?php echo htmlspecialchars($formData['peso_unidad_medida'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="num_bultos">Número de Bultos *</label>
                    <input type="number" id="num_bultos" name="num_bultos" 
                           placeholder="100" 
                           value="<?php echo htmlspecialchars($formData['num_bultos'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="valor_comercial">Valor Comercial *</label>
                    <input type="number" step="0.01" id="valor_comercial" name="valor_comercial" 
                           placeholder="50000.00" 
                           value="<?php echo htmlspecialchars($formData['valor_comercial'] ?? ''); ?>" required>
                </div>
            </div>

            <h3 class="section-heading">Datos del Consignatario</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="consignatario_nombre">Nombre del Consignatario *</label>
                    <input type="text" id="consignatario_nombre" name="consignatario_nombre" 
                           value="<?php echo htmlspecialchars($formData['consignatario_nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="consignatario_domicilio">Domicilio del Consignatario *</label>
                    <textarea id="consignatario_domicilio" name="consignatario_domicilio" rows="2" required><?php echo htmlspecialchars($formData['consignatario_domicilio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="consignatario_rfc">RFC del Consignatario</label>
                    <input type="text" id="consignatario_rfc" name="consignatario_rfc" 
                           value="<?php echo htmlspecialchars($formData['consignatario_rfc'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="consignatario_email">Email del Consignatario</label>
                    <input type="email" id="consignatario_email" name="consignatario_email" 
                           value="<?php echo htmlspecialchars($formData['consignatario_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="consignatario_telefono">Teléfono del Consignatario</label>
                    <input type="text" id="consignatario_telefono" name="consignatario_telefono" 
                           value="<?php echo htmlspecialchars($formData['consignatario_telefono'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Datos del Remitente</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="remitente_nombre">Nombre de quien envía *</label>
                    <input type="text" id="remitente_nombre" name="remitente_nombre" 
                           value="<?php echo htmlspecialchars($formData['remitente_nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="remitente_domicilio">Domicilio de quien envía *</label>
                    <textarea id="remitente_domicilio" name="remitente_domicilio" rows="2" required><?php echo htmlspecialchars($formData['remitente_domicilio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="remitente_pais_origen">País de Origen</label>
                    <input type="text" id="remitente_pais_origen" name="remitente_pais_origen" 
                           value="<?php echo htmlspecialchars($formData['remitente_pais_origen'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Información de Descarga</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha_conclusion_descarga">Fecha de Conclusión de Descarga</label>
                    <input type="datetime-local" id="fecha_conclusion_descarga" name="fecha_conclusion_descarga" 
                           value="<?php echo htmlspecialchars($formData['fecha_conclusion_descarga'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <a href="/bitacora.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Entrada</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>