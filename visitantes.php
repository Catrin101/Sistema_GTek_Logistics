<?php
// public/visitantes.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Visitante.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php'); // Redirigir al login si no está autenticado
    exit;
}

$pageTitle = "Consulta de Visitantes - Gtek Logistics";

$visitanteModel = new Visitante();

// --- Lógica de Paginación y Filtros ---
$recordsPerPage = isset($_GET['records_per_page']) ? (int)$_GET['records_per_page'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

$filters = [];
if (!empty($_GET['search'])) {
    $filters['search_query'] = $_GET['search'];
}

// Obtener el total de registros para la paginación
$totalRecords = $visitanteModel->countAllVisitantes($filters);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Obtener los registros de visitantes
$visitantes = $visitanteModel->getAllVisitantes($filters, $recordsPerPage, $offset);

include __DIR__ . '/src/views/header.php';
?>

<div class="page-content">
    <div class="table-container">
        <h2>Consulta de Visitantes</h2>

        <div class="filters-section">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group records-per-page-group">
                    <label for="records_per_page">Mostrar:</label>
                    <select id="records_per_page" name="records_per_page" onchange="this.form.submit()">
                        <option value="5" <?php echo ($recordsPerPage == 5) ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo ($recordsPerPage == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo ($recordsPerPage == 20) ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo ($recordsPerPage == 50) ? 'selected' : ''; ?>>50</option>
                    </select>
                </div>

                <div class="filter-group search-group">
                    <input type="text" id="search" name="search" placeholder="Nombre, Número de Verificación..." value="<?php echo htmlspecialchars($filters['search_query'] ?? ''); ?>">
                    <button type="submit" class="btn btn-search">Q Buscar</button>
                    <a href="/visitantes.php" class="btn btn-clear">X</a>
                </div>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Número de Verificación</th>
                    <th>Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitantes)): ?>
                    <tr>
                        <td colspan="5" class="no-records">No hay visitantes registrados que coincidan con los filtros.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($visitantes as $visitante): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visitante['id']); ?></td>
                            <td><?php echo htmlspecialchars($visitante['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($visitante['numero_verificacion'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($visitante['fecha_creacion']))); ?></td>
                            <td>
                                <a >Eliminar</a>
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