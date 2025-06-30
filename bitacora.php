<?php
// public/bitacora.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Bitacora.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php'); // Redirigir al login si no está autenticado
    exit;
}

$pageTitle = "Consulta de Bitácora - Gtek Logistics";

$bitacoraModel = new Bitacora();

// --- Lógica de Paginación y Filtros ---
$recordsPerPage = isset($_GET['records_per_page']) ? (int)$_GET['records_per_page'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

$filters = [];
if (!empty($_GET['fecha_inicio'])) {
    $filters['fecha_inicio'] = $_GET['fecha_inicio'];
}
if (!empty($_GET['fecha_fin'])) {
    $filters['fecha_fin'] = $_GET['fecha_fin'];
}
if (!empty($_GET['tipo_operacion'])) {
    $filters['tipo_operacion'] = $_GET['tipo_operacion'];
}
if (!empty($_GET['search'])) {
    $filters['search_query'] = $_GET['search'];
}

$registros = $bitacoraModel->getAllRegistros($filters, $recordsPerPage, $offset);
$totalRegistros = $bitacoraModel->countAllRegistros($filters);
$totalPages = ceil($totalRegistros / $recordsPerPage);

// Obtener el total de registros para la paginación
$totalRecords = $bitacoraModel->countAllRegistros($filters);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Obtener los registros de bitácora
$registros = $bitacoraModel->getAllRegistros($filters, $recordsPerPage, $offset);

// Mensajes de estado (éxito/error) desde redirecciones
$status_message = '';
if (isset($_GET['status']) && isset($_GET['message'])) {
    $status_type = $_GET['status'];
    $status_message = htmlspecialchars(urldecode($_GET['message']));
}

// --- Incluir Vistas ---
include __DIR__ . '/src/views/header.php';
?>

<link rel="stylesheet" href="/assets/css/estilo_interfaces.css">

<div class="logbook-container">
    <div class="logbook-header">
        <h1 class="logbook-title">Merchandise Movement Logbook</h1>
    </div>

    <div class="filters-container">
        <form action="" method="GET">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label" for="fecha_inicio">Start Date</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="filter-input" 
                           value="<?php echo htmlspecialchars($filters['fecha_inicio'] ?? ''); ?>" 
                           placeholder="Select start date">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label" for="fecha_fin">End Date</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="filter-input" 
                           value="<?php echo htmlspecialchars($filters['fecha_fin'] ?? ''); ?>" 
                           placeholder="Select end date">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label" for="tipo_operacion">Operation Type</label>
                    <select id="tipo_operacion" name="tipo_operacion" class="filter-select">
                        <option value="" <?php echo (empty($filters['tipo_operacion']) ? 'selected' : ''); ?>>All Types</option>
                        <option value="Entrada" <?php echo ((($filters['tipo_operacion'] ?? '') === 'Entrada') ? 'selected' : ''); ?>>Entry</option>
                        <option value="Salida" <?php echo ((($filters['tipo_operacion'] ?? '') === 'Salida') ? 'selected' : ''); ?>>Exit</option>
                    </select>
                </div>
            </div>
            
            <div class="search-container">
                <input type="text" id="search" name="search" class="search-input" 
                       placeholder="Search by document, merchandise, consignee..." 
                       value="<?php echo htmlspecialchars($filters['search_query'] ?? ''); ?>">
            </div>
            
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-export">Apply Filters</button>
                <a href="/bitacora.php" class="btn-clear-filters">Clear Filters</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-header">
            <span class="records-count"><?php echo $totalRegistros; ?> records found</span>
            <div class="table-actions">
                <button class="btn-export">Export</button>
                <div class="records-per-page">
                    <span>Adjust Records Per Page</span>
                    <form method="GET" style="display: inline;">
                        <?php foreach ($_GET as $key => $value): ?>
                            <?php if ($key !== 'records_per_page'): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="records_per_page" class="records-select" onchange="this.form.submit()">
                            <option value="10" <?php echo ($recordsPerPage == 10 ? 'selected' : ''); ?>>10</option>
                            <option value="25" <?php echo ($recordsPerPage == 25 ? 'selected' : ''); ?>>25</option>
                            <option value="50" <?php echo ($recordsPerPage == 50 ? 'selected' : ''); ?>>50</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Type</th>
                    <th>Document</th>
                    <th>Merchandise</th>
                    <th>Consignee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="6" class="no-records">No records found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($registro['fecha_ingreso']))); ?></td>
                            <td>
                                <span class="operation-tag <?php echo ($registro['tipo_operacion'] === 'Entrada' ? 'tag-entry' : 'tag-exit'); ?>">
                                    <?php echo ($registro['tipo_operacion'] === 'Entrada' ? 'Entry' : 'Exit'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?></td>
                            <td><?php echo htmlspecialchars($registro['descripcion_mercancia'] . ', ' . $registro['peso_unidad_medida'] . 'KG, ' . $registro['num_bultos'] . ' bultos'); ?></td>
                            <td><?php echo htmlspecialchars($registro['consignatario_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="/detalle_registro.php?id=<?php echo htmlspecialchars($registro['id']); ?>" class="btn-view">View</a>
                                <a href="/eliminar_registro.php?id=<?php echo htmlspecialchars($registro['id']); ?>"
                                   class="btn-icon"
                                   title="Eliminar Registro"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar este registro de bitácora (ID: <?php echo htmlspecialchars($registro['id']); ?>)? Esta acción no se puede deshacer.');">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&records_per_page=<?php echo $recordsPerPage; ?>&<?php echo http_build_query($filters); ?>" 
                   class="pagination-btn <?php echo ($i == $currentPage ? 'active' : ''); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/src/views/footer.php'; ?>