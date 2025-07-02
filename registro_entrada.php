<?php
// public/registrar_entrada.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Bitacora.php';
require_once __DIR__ . '/src/models/Consignatario.php';
require_once __DIR__ . '/src/models/Remitente.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Registrar Nueva Entrada - Gtek Logistics";

// Variables para almacenar los valores del formulario
$formData = [];
$errors = [];

// Instanciar modelos
$bitacoraModel = new Bitacora();
$consignatarioModel = new Consignatario();
$remitenteModel = new Remitente();

// Si es la primera carga, inicializar con valores por defecto o vacíos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $formData = [
        'fecha_ingreso' => date('Y-m-d\TH:i'), // Fecha y hora actuales por defecto
        'tipo_operacion' => 'Entrada', // Por defecto, es una entrada
        'num_conocimiento_embarque' => '',
        'numero_pedimento' => '',
        'fraccion_arancelaria' => '',
        'num_registro_buque_vuelo_contenedor' => '',
        'dimension_sellos_candados' => '',
        'primer_puerto_terminal' => '',
        'descripcion_mercancia' => '',
        'peso_unidad_medida' => '',
        'regimen' => '',
        'patente' => '',
        'piezas' => '',
        'num_bultos' => '',
        'valor_comercial' => '',
        'fecha_conclusion_descarga' => '',
        'consignatario_nombre' => '',
        'consignatario_domicilio' => '',
        'consignatario_rfc' => '',
        'consignatario_email' => '',
        'consignatario_telefono' => '',
        'remitente_nombre' => '',
        'remitente_domicilio' => '',
        'remitente_pais_origen' => '',
    ];
} else {
    // Si se envió el formulario, usar los datos del POST
    $formData = $_POST;
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Asegurarse de que tipo_operacion esté presente y limpiar espacios
    $formData['tipo_operacion'] = trim($formData['tipo_operacion'] ?? '');

    // --- Validaciones (las mismas que en editar_entrada.php) ---
    // General
    if (empty($formData['fecha_ingreso'])) $errors[] = "La Fecha de Ingreso es obligatoria.";
    if (empty($formData['num_conocimiento_embarque'])) $errors[] = "El Número de Conocimiento de Embarque es obligatorio.";
    if (empty($formData['num_registro_buque_vuelo_contenedor'])) $errors[] = "El Número de Registro (Buque/Vuelo/Contenedor) es obligatorio.";
    if (empty($formData['primer_puerto_terminal'])) $errors[] = "El Primer Puerto/Aeropuerto/Terminal es obligatorio.";
    if (empty($formData['descripcion_mercancia'])) $errors[] = "La Descripción de la Mercancía es obligatoria.";
    if (!isset($formData['peso_unidad_medida']) || !is_numeric($formData['peso_unidad_medida'])) $errors[] = "El Peso y Unidad de Medida es obligatorio y debe ser un número.";
    if (!isset($formData['num_bultos']) || !is_numeric($formData['num_bultos']) || $formData['num_bultos'] < 0) $errors[] = "El Número de Bultos es obligatorio y debe ser un número entero positivo.";
    if (!isset($formData['valor_comercial']) || !is_numeric($formData['valor_comercial']) || $formData['valor_comercial'] < 0) $errors[] = "El Valor Comercial es obligatorio y debe ser un número positivo.";
    
    // Validación para numero_pedimento (int)
    if (!empty($formData['numero_pedimento']) && !filter_var($formData['numero_pedimento'], FILTER_VALIDATE_INT)) {
        $errors[] = "El Número de Pedimento debe ser un número entero válido.";
    }

    // Validación para fraccion_arancelaria (decimal)
    if (!empty($formData['fraccion_arancelaria']) && !filter_var($formData['fraccion_arancelaria'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) {
        $errors[] = "La Fracción Arancelaria debe ser un número decimal válido.";
    }

    // Consignatario
    if (empty($formData['consignatario_nombre'])) $errors[] = "El Nombre del Consignatario es obligatorio.";
    if (empty($formData['consignatario_domicilio'])) $errors[] = "El Domicilio del Consignatario es obligatorio.";

    // Remitente
    if (empty($formData['remitente_nombre'])) $errors[] = "El Nombre de quien envía (Remitente) es obligatorio.";
    if (empty($formData['remitente_domicilio'])) $errors[] = "El Domicilio de quien envía (Remitente) es obligatorio.";

    // Validar tipo_operacion estrictamente
    if (!in_array($formData['tipo_operacion'], ['Entrada', 'Salida'])) {
        $errors[] = "El Tipo de Operación es obligatorio y debe ser 'Entrada' o 'Salida'.";
    }

    if (!empty($formData['patente']) && !filter_var($formData['patente'], FILTER_VALIDATE_INT)) {
        $errors[] = "La Patente debe ser un número entero válido.";
    }
    if (!empty($formData['piezas']) && (!filter_var($formData['piezas'], FILTER_VALIDATE_INT) || $formData['piezas'] < 0)) {
        $errors[] = "El número de Piezas debe ser un número entero positivo válido.";
    }

    // NUEVA VALIDACIÓN: Verificar si num_conocimiento_embarque ya existe
    if (empty($errors) && !empty($formData['num_conocimiento_embarque'])) {
        $existingRecord = $bitacoraModel->getRegistroByNumConocimiento($formData['num_conocimiento_embarque']);
        if ($existingRecord) {
            $errors[] = "Ya existe un registro con el Número de Conocimiento de Embarque proporcionado. Por favor, use uno diferente.";
        }
    }

    // Si no hay errores de validación, proceder a crear el registro
    if (empty($errors)) {
        try {
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

            // 3. Preparar datos para la creación
            $createData = [
                'fecha_ingreso' => $formData['fecha_ingreso'],
                'tipo_operacion' => $formData['tipo_operacion'],
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
                'registrado_por_user_id' => Auth::getUserId(), // Asignar el ID del usuario logueado
                'numero_pedimento' => $formData['numero_pedimento'] ?? null,
                'fraccion_arancelaria' => $formData['fraccion_arancelaria'] ?? null,
                'regimen' => $formData['regimen'] ?? null,
                'patente' => $formData['patente'] ?? null,
                'piezas' => $formData['piezas'] ?? null,
            ];

            $newRecordId = $bitacoraModel->createRegistro($createData);

            if ($newRecordId) {
                header('Location: /bitacora.php?status=success&message=' . urlencode('Registro creado exitosamente con ID: ' . $newRecordId));
                exit;
            } else {
                $errors[] = "Error al crear el registro. Por favor, intente de nuevo.";
            }

        } catch (Exception $e) {
            error_log("Error al crear registro de entrada: " . $e->getMessage());
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
    margin: 0 0 20px 0;
    text-align: center;
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
    border-color: #007bff; /* Changed from warning to primary color for new entry */
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1); /* Changed from warning to primary color */
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

.btn-primary { /* Changed from btn-warning */
    background: #007bff;
    color: white;
}

.btn-primary:hover { /* Changed from btn-warning:hover */
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
        <h2>Registrar Nueva Entrada de Bitácora</h2>

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
                    <label for="fecha_ingreso">Fecha de Ingreso <span class="required">*</span></label>
                    <input type="datetime-local" id="fecha_ingreso" name="fecha_ingreso" 
                           value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tipo_operacion">Tipo de Operación <span class="required">*</span></label>
                    <select id="tipo_operacion" name="tipo_operacion" required>
                        <option value="">Seleccione...</option>
                        <option value="Entrada" <?php echo (isset($formData['tipo_operacion']) && $formData['tipo_operacion'] == 'Entrada') ? 'selected' : ''; ?>>Entrada</option>
                        <option value="Salida" <?php echo (isset($formData['tipo_operacion']) && $formData['tipo_operacion'] == 'Salida') ? 'selected' : ''; ?>>Salida</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="num_conocimiento_embarque">Número de Conocimiento de Embarque <span class="required">*</span></label>
                    <input type="text" id="num_conocimiento_embarque" name="num_conocimiento_embarque" 
                           placeholder="EJ: BL123456789" 
                           value="<?php echo htmlspecialchars($formData['num_conocimiento_embarque'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="numero_pedimento">Número de Pedimento</label>
                    <input type="number" id="numero_pedimento" name="numero_pedimento"
                        value="<?php echo htmlspecialchars($formData['numero_pedimento'] ?? ''); ?>"
                        placeholder="Ingrese el número de pedimento">
                </div>
                <div class="form-group">
                    <label for="fraccion_arancelaria">Fracción Arancelaria</label>
                    <input type="number" id="fraccion_arancelaria" name="fraccion_arancelaria" step="0.0001"
                        value="<?php echo htmlspecialchars($formData['fraccion_arancelaria'] ?? ''); ?>"
                        placeholder="Ej. 9802.00.00.00">
                </div>
                <div class="form-group">
                    <label for="num_registro_buque_vuelo_contenedor">Número de Registro (Buque/Vuelo/Contenedor) <span class="required">*</span></label>
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
                    <label for="primer_puerto_terminal">Primer Puerto/Aeropuerto/Terminal <span class="required">*</span></label>
                    <input type="text" id="primer_puerto_terminal" name="primer_puerto_terminal" 
                           placeholder="EJ: Puerto de Veracruz" 
                           value="<?php echo htmlspecialchars($formData['primer_puerto_terminal'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion_mercancia">Descripción de la Mercancía <span class="required">*</span></label>
                    <textarea id="descripcion_mercancia" name="descripcion_mercancia" rows="3" 
                              placeholder="Descripción detallada de la mercancía" required><?php echo htmlspecialchars($formData['descripcion_mercancia'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="peso_unidad_medida">Peso y Unidad de Medida <span class="required">*</span></label>
                    <input type="number" step="0.01" id="peso_unidad_medida" name="peso_unidad_medida" 
                           placeholder="1000.50" 
                           value="<?php echo htmlspecialchars($formData['peso_unidad_medida'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="regimen">Régimen</label>
                    <input type="text" id="regimen" name="regimen"
                           value="<?php echo htmlspecialchars($formData['regimen'] ?? ''); ?>"
                           placeholder="Ej. Importación definitiva">
                </div>
                <div class="form-group">
                    <label for="patente">Patente</label>
                    <input type="number" id="patente" name="patente"
                           value="<?php echo htmlspecialchars($formData['patente'] ?? ''); ?>"
                           placeholder="Ingrese el número de patente">
                </div>
                <div class="form-group">
                    <label for="piezas">Piezas</label>
                    <input type="number" id="piezas" name="piezas"
                           value="<?php echo htmlspecialchars($formData['piezas'] ?? ''); ?>"
                           placeholder="Cantidad de piezas">
                </div>
                <div class="form-group">
                    <label for="num_bultos">Número de Bultos <span class="required">*</span></label>
                    <input type="number" id="num_bultos" name="num_bultos" 
                           placeholder="100" 
                           value="<?php echo htmlspecialchars($formData['num_bultos'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="valor_comercial">Valor Comercial <span class="required">*</span></label>
                    <input type="number" step="0.01" id="valor_comercial" name="valor_comercial" 
                           placeholder="50000.00" 
                           value="<?php echo htmlspecialchars($formData['valor_comercial'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fecha_conclusion_descarga">Fecha de Conclusión de Descarga</label>
                    <input type="datetime-local" id="fecha_conclusion_descarga" name="fecha_conclusion_descarga" 
                           value="<?php echo htmlspecialchars($formData['fecha_conclusion_descarga'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Datos del Consignatario</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="consignatario_nombre">Nombre del Consignatario <span class="required">*</span></label>
                    <input type="text" id="consignatario_nombre" name="consignatario_nombre" 
                           placeholder="Nombre completo o razón social"
                           value="<?php echo htmlspecialchars($formData['consignatario_nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="consignatario_domicilio">Domicilio del Consignatario <span class="required">*</span></label>
                    <textarea id="consignatario_domicilio" name="consignatario_domicilio" rows="2" 
                              placeholder="Dirección completa" required><?php echo htmlspecialchars($formData['consignatario_domicilio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="consignatario_rfc">RFC del Consignatario</label>
                    <input type="text" id="consignatario_rfc" name="consignatario_rfc" 
                           placeholder="RFC-XXXXXX-XXX"
                           value="<?php echo htmlspecialchars($formData['consignatario_rfc'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="consignatario_email">Email del Consignatario</label>
                    <input type="email" id="consignatario_email" name="consignatario_email" 
                           placeholder="correo@empresa.com"
                           value="<?php echo htmlspecialchars($formData['consignatario_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="consignatario_telefono">Teléfono del Consignatario</label>
                    <input type="text" id="consignatario_telefono" name="consignatario_telefono" 
                           placeholder="+52 (xxx) xxx-xxxx"
                           value="<?php htmlspecialchars($formData['consignatario_telefono'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Datos del Remitente</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="remitente_nombre">Nombre de quien envía <span class="required">*</span></label>
                    <input type="text" id="remitente_nombre" name="remitente_nombre" 
                           placeholder="Nombre completo o razón social"
                           value="<?php echo htmlspecialchars($formData['remitente_nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="remitente_domicilio">Domicilio de quien envía <span class="required">*</span></label>
                    <textarea id="remitente_domicilio" name="remitente_domicilio" rows="2" 
                              placeholder="Dirección completa" required><?php echo htmlspecialchars($formData['remitente_domicilio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="remitente_pais_origen">País de Origen</label>
                    <input type="text" id="remitente_pais_origen" name="remitente_pais_origen" 
                           placeholder="País de origen de la mercancía"
                           value="<?php echo htmlspecialchars($formData['remitente_pais_origen'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Información del Registro</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registrado por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(Auth::getUsername()); ?></span>
                </div>
                <div class="form-group">
                    <label>Fecha de Registro</label>
                    <span class="read-only-field"><?php echo date('d/m/Y H:i A'); ?></span>
                </div>
            </div>

            <div class="form-actions">
                <a href="/bitacora.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Nueva Entrada</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>