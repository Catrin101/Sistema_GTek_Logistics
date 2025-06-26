<?php
// public/bitacora.php

require_once __DIR__ . '/../src/core/Auth.php';
require_once __DIR__ . '/../src/models/Bitacora.php';

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

// --- Incluir Vistas ---
include __DIR__ . '/../src/views/header.php';
include __DIR__ . '/../src/views/navbar.php'; 
?>
<div class="page-content">
    <h2 class="page-title">Consulta de Bitácora</h2>

    <div class="filters-section">
        <form action="" method="GET" class="filter-form">
            <div class="filter-group">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($filters['fecha_inicio'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($filters['fecha_fin'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="tipo_operacion">Tipo de Operación:</label>
                <select id="tipo_operacion" name="tipo_operacion">
                    <option value="" <?php echo (empty($filters['tipo_operacion']) ? 'selected' : ''); ?>>Todos</option>
                    <option value="Entrada" <?php echo ((($filters['tipo_operacion'] ?? '') === 'Entrada') ? 'selected' : ''); ?>>Entrada</option>
                    <option value="Salida" <?php echo ((($filters['tipo_operacion'] ?? '') === 'Salida') ? 'selected' : ''); ?>>Salida</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <input type="text" id="search" name="search" placeholder="Conocimiento, Pedimento..." value="<?php echo htmlspecialchars($filters['search_query'] ?? ''); ?>">
                <button type="submit" class="btn btn-search">Q Buscar</button>
                <a href="/bitacora.php" class="btn btn-clear">X</a> </div>
            <div class="filter-active-indicator">
                <span>Filtros activos</span> <span class="filter-icon"></span>
            </div>
        </form>
    </div>

    <div class="records-section">
        <h3 class="section-title">Registros de Bitácora</h3>
        <div class="records-header-actions">
            <span class="record-count"><?php echo $totalRegistros; ?> registros encontrados</span>
            <button class="btn btn-export">Exportar</button>
            <div class="view-options">
                <label for="records_per_page">Ver:</label>
                <select id="records_per_page" name="records_per_page" onchange="this.form.submit()">
                    <option value="10" <?php echo ($recordsPerPage == 10 ? 'selected' : ''); ?>>10</option>
                    <option value="25" <?php echo ($recordsPerPage == 25 ? 'selected' : ''); ?>>25</option>
                    <option value="50" <?php echo ($recordsPerPage == 50 ? 'selected' : ''); ?>>50</option>
                </select>
            </div>
        </div>
        
        <table class="bitacora-table">
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Mercancía</th>
                    <th>Consignatario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No se encontraron registros.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($registro['fecha_ingreso']))); ?></td>
                            <td><span class="tag <?php echo ($registro['tipo_operacion'] === 'Entrada' ? 'entry-tag' : 'exit-tag'); ?>"><?php echo htmlspecialchars($registro['tipo_operacion']); ?></span></td>
                            <td><?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?></td>
                            <td><?php echo htmlspecialchars($registro['descripcion_mercancia'] . ', ' . $registro['peso_unidad_medida'] . 'KG, ' . $registro['num_bultos'] . ' bultos'); ?></td>
                            <td><?php echo htmlspecialchars($registro['consignatario_nombre'] ?? 'N/A'); ?></td>
                            <td><a href="/detalle_registro.php?id=<?php echo htmlspecialchars($registro['id']); ?>" class="btn-icon"><img src="/assets/img/icon_eye.svg" alt="Ver detalle"></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&records_per_page=<?php echo $recordsPerPage; ?>&<?php echo http_build_query($filters); ?>" 
                   class="btn <?php echo ($i == $currentPage ? 'btn-primary' : 'btn-clear'); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../src/views/footer.php'; ?>