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
$success_message = '';
$error_message = '';
$vehiculoModel = new Vehiculo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_conductor = trim($_POST['nombre_conductor'] ?? '');
    $placas = trim(strtoupper($_POST['placas'] ?? '')); // Convertir placas a mayúsculas
    $empresa = trim($_POST['empresa'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $usuario_del_sistema_id = Auth::getUserId(); // Obtener el ID del usuario logueado

    if (empty($nombre_conductor)) {
        $error_message = "El nombre del conductor es obligatorio.";
    } elseif (empty($placas)) {
        $error_message = "Las placas son obligatorias.";
    } else {
        $data = [
            'nombre_conductor' => $nombre_conductor,
            'usuario_del_sistema_id' => $usuario_del_sistema_id,
            'placas' => $placas,
            'empresa' => !empty($empresa) ? $empresa : null, // Guardar NULL si está vacío
            'modelo' => !empty($modelo) ? $modelo : null, // Guardar NULL si está vacío
        ];

        $vehiculo_id = $vehiculoModel->createVehiculo($data);

        if ($vehiculo_id) {
            $success_message = "Vehículo registrado exitosamente con ID: " . $vehiculo_id;
            // Opcional: Redirigir a la página de detalle o listado de vehículos
            // header('Location: /vehiculos.php');
            // exit;
            
            // Limpiar los campos del formulario después de un registro exitoso
            $nombre_conductor = '';
            $placas = '';
            $empresa = '';
            $modelo = '';

        } else {
            $error_message = "Error al registrar el vehículo. Por favor, intente de nuevo.";
            // Puedes agregar más detalles de error si el método createVehiculo los devuelve
        }
    }
}

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="form-container">
        <h2>Registrar Nuevo Vehículo</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="/registro_vehiculo.php" method="POST" class="register-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_conductor">Nombre del Conductor <span class="required">*</span></label>
                    <input type="text" id="nombre_conductor" name="nombre_conductor" value="<?php echo htmlspecialchars($nombre_conductor ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="placas">Placas <span class="required">*</span></label>
                    <input type="text" id="placas" name="placas" value="<?php echo htmlspecialchars($placas ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="empresa">Empresa</label>
                    <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($empresa ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo del Vehículo</label>
                    <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($modelo ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Registrado por</label>
                    <span class="read-only-field"><?php echo htmlspecialchars(Auth::getUsername()); ?></span>
                    <input type="hidden" name="usuario_del_sistema_id" value="<?php echo htmlspecialchars(Auth::getUserId()); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar Vehículo</button>
                <a href="/vehiculos.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>