<?php
// public/registro_visitante.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Visitante.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$pageTitle = "Registrar Nuevo Visitante - Gtek Logistics";
$success_message = '';
$error_message = '';
$visitanteModel = new Visitante();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $numero_verificacion = trim($_POST['numero_verificacion'] ?? '');

    if (empty($nombre)) {
        $error_message = "El nombre del visitante es obligatorio.";
    } else {
        $data = [
            'nombre' => $nombre,
            'numero_verificacion' => !empty($numero_verificacion) ? $numero_verificacion : null, // Guardar NULL si está vacío
        ];

        $visitante_id = $visitanteModel->createVisitante($data);

        if ($visitante_id) {
            $success_message = "Visitante registrado exitosamente con ID: " . $visitante_id;
            // Limpiar los campos del formulario después de un registro exitoso
            $nombre = '';
            $numero_verificacion = '';
        } else {
            $error_message = "Error al registrar el visitante. Por favor, intente de nuevo.";
        }
    }
}

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="form-container">
        <h2>Registrar Nuevo Visitante</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="/registro_visitante.php" method="POST" class="register-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre del Visitante <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="numero_verificacion">Número de Verificación</label>
                    <input type="text" id="numero_verificacion" name="numero_verificacion" value="<?php echo htmlspecialchars($numero_verificacion ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar Visitante</button>
                <a href="/visitantes.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>