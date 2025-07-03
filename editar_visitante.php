<?php
// public/editar_visitante.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Visitante.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Editar Visitante - Gtek Logistics";
$visitanteModel = new Visitante();
$errors = [];
$formData = [];
$visitante_id = null;
$current_image_path = null; // Para guardar la ruta de la imagen actual

// --- Lógica para cargar datos del visitante existente ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visitante_id = (int)$_GET['id'];
    $visitante = $visitanteModel->getVisitanteById($visitante_id);
    
    if (!$visitante) {
        // Visitante no encontrado, redirigir o mostrar error
        header('Location: /visitantes.php?status=error&message=Visitante no encontrado.');
        exit;
    }

    // Pre-llenar el formulario con los datos del visitante
    $formData = $visitante;
    $current_image_path = $visitante['ruta_imagen'] ?? null; // Asegurar que es null si no existe

    // Formatear fechas para los campos datetime-local
    if (isset($formData['fecha_entrada'])) {
        $formData['fecha_entrada'] = date('Y-m-d\TH:i', strtotime($formData['fecha_entrada']));
    }
    if (isset($formData['fecha_salida']) && $formData['fecha_salida']) {
        $formData['fecha_salida'] = date('Y-m-d\TH:i', strtotime($formData['fecha_salida']));
    } else {
        $formData['fecha_salida'] = ''; // Asegurar que sea vacío si es NULL en DB
    }

} else {
    // ID no proporcionado o inválido, redirigir
    header('Location: /visitantes.php?status=error&message=ID de visitante inválido.');
    exit;
}


// --- Lógica para procesar el formulario de edición ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolectar y sanear datos
    $formData['nombre'] = trim($_POST['nombre'] ?? '');
    $formData['numero_verificacion'] = trim($_POST['numero_verificacion'] ?? '');
    $formData['fecha_entrada'] = trim($_POST['fecha_entrada'] ?? ''); // AGREGAR ESTA LÍNEA
    $formData['fecha_salida'] = trim($_POST['fecha_salida'] ?? '');

    // Validaciones
    if (empty($formData['nombre'])) {
        $errors[] = "El nombre del visitante es obligatorio.";
    }
    if (empty($formData['numero_verificacion'])) {
        $errors[] = "El número de verificación es obligatorio.";
    }

    // Manejo de la imagen (opcional: si se carga una nueva imagen)
    $uploadDir = __DIR__ . '/assets/uploads/visitantes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $new_image_path = $current_image_path; // Por defecto, mantener la imagen actual

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName = $_FILES['imagen']['name'];
        $fileSize = $_FILES['imagen']['size'];
        $fileType = $_FILES['imagen']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            $errors[] = "Tipo de archivo de imagen no permitido. Solo JPG, JPEG, PNG, GIF.";
        } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB
            $errors[] = "El archivo de imagen es demasiado grande (máx. 5MB).";
        } else {
            // Generar un nombre único para el archivo
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $new_image_path = '/assets/uploads/visitantes/' . $newFileName;
                // Opcional: Eliminar la imagen antigua si se subió una nueva y no es la predeterminada
                if ($current_image_path && $current_image_path !== $new_image_path && file_exists(__DIR__ . $current_image_path)) {
                     unlink(__DIR__ . $current_image_path);
                }
            } else {
                $errors[] = "Error al mover el archivo de imagen subido.";
            }
        }
    } else if (isset($_POST['remove_image']) && $_POST['remove_image'] === 'true') {
        // Si se marca la casilla para eliminar imagen
        if ($current_image_path && file_exists(__DIR__ . $current_image_path)) {
            unlink(__DIR__ . $current_image_path);
        }
        $new_image_path = null; // Establecer la ruta de la imagen a NULL
    }


    if (empty($errors)) {
        $visitanteData = [
            'nombre' => $formData['nombre'],
            'numero_verificacion' => $formData['numero_verificacion'],
            'ruta_imagen' => $new_image_path, // Usar la nueva ruta o null
            'fecha_entrada' => !empty($formData['fecha_entrada']) ? $formData['fecha_entrada'] : null, // AGREGAR ESTA LÍNEA
            'fecha_salida' => !empty($formData['fecha_salida']) ? $formData['fecha_salida'] : null,   // AGREGAR ESTA LÍNEA
        ];

        $updated = $visitanteModel->updateVisitante($visitante_id, $visitanteData);

        if ($updated) {
            header('Location: /visitantes.php?status=success&message=Visitante actualizado exitosamente.');
            exit;
        } else {
            $errors[] = "Error al actualizar el visitante en la base de datos.";
        }
    }
}

// Incluir vistas
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

.visitor-info {
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

/* Estilos específicos para la imagen */
.image-upload-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.current-image-preview {
    padding: 15px;
    background: #f8f9fa;
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    text-align: center;
}

.current-image-preview img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    object-fit: cover;
}

.current-image-preview p {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}

.remove-image-checkbox {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    padding: 8px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
}

.remove-image-checkbox input[type="checkbox"] {
    width: auto;
    margin: 0;
    padding: 0;
}

.remove-image-checkbox label {
    margin: 0;
    font-size: 13px;
    color: #856404;
    cursor: pointer;
}

.file-input-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.file-input-styled {
    position: relative;
    background: #fff;
    border: 2px dashed #007bff;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}

.file-input-styled:hover {
    border-color: #0056b3;
    background: #f8f9ff;
}

.file-input-styled input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    border: none;
    padding: 0;
}

.file-input-text {
    color: #007bff;
    font-size: 14px;
    font-weight: 500;
}

.file-input-hint {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
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
    
    .current-image-preview img {
        max-width: 150px;
        max-height: 150px;
    }
    
    .remove-image-checkbox {
        flex-direction: column;
        gap: 5px;
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
        <h2>Editar Visitante</h2>
        <div class="visitor-info">
            <strong>ID:</strong> <?php echo htmlspecialchars($visitante['id']); ?> | 
            <strong>Registrado:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($visitante['fecha_creacion']))); ?> | 
            <strong>Por:</strong> <?php echo htmlspecialchars($visitante['usuario_del_sistema_username'] ?? 'Usuario Desconocido'); ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/editar_visitante.php?id=<?php echo htmlspecialchars($visitante_id); ?>" method="POST" enctype="multipart/form-data" class="edit-form">
            <h3 class="section-heading">Información del Visitante</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre del Visitante <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" 
                           placeholder="Nombre completo del visitante"
                           value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="numero_verificacion">Número de Verificación <span class="required">*</span></label>
                    <input type="text" id="numero_verificacion" name="numero_verificacion" 
                           placeholder="Número de identificación o verificación"
                           value="<?php echo htmlspecialchars($formData['numero_verificacion'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="fecha_entrada">Fecha de Entrada</label>
                    <input type="datetime-local" id="fecha_entrada" name="fecha_entrada"
                           value="<?php echo htmlspecialchars($formData['fecha_entrada'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_salida">Fecha de Salida</label>
                    <input type="datetime-local" id="fecha_salida" name="fecha_salida"
                           value="<?php echo htmlspecialchars($formData['fecha_salida'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="section-heading">Imagen del Visitante</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="imagen">Subir Nueva Imagen</label>
                    <div class="image-upload-container">
                        <div class="file-input-wrapper">
                            <div class="file-input-styled">
                                <input type="file" id="imagen" name="imagen" accept="image/*">
                                <div class="file-input-text">Seleccionar archivo de imagen</div>
                                <div class="file-input-hint">JPG, JPEG, PNG, GIF (máx. 5MB)</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($current_image_path)): ?>
                            <div class="current-image-preview">
                                <p>Imagen actual:</p>
                                <img src="<?php echo htmlspecialchars($current_image_path); ?>" alt="Imagen actual del visitante">
                                <div class="remove-image-checkbox">
                                    <input type="checkbox" id="remove_image" name="remove_image" value="true">
                                    <label for="remove_image">Eliminar imagen actual</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <h3 class="section-heading">Información del Registro</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Registrado Originalmente por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars($visitante['usuario_del_sistema_username'] ?? 'Usuario Desconocido'); ?></span>
                </div>
                <div class="form-group">
                    <label>Fecha de Registro Original</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($visitante['fecha_creacion']))); ?></span>
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
                <a href="/visitantes.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Visitante</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>