<?php
// public/vehiculos.php

require_once __DIR__ . '/src/core/Auth.php';
require_once __DIR__ . '/src/models/Vehiculo.php';

// Verificar sesión
if (!Auth::isLoggedIn()) {
    header('Location: /index.php'); // Redirigir al login si no está autenticado
    exit;
}

$pageTitle = "Consulta de Vehículos - Gtek Logistics";

$vehiculoModel = new Vehiculo();

// --- Lógica de Paginación y Filtros ---
$recordsPerPage = isset($_GET['records_per_page']) ? (int)$_GET['records_per_page'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

$filters = [];
if (!empty($_GET['search'])) {
    $filters['search_query'] = $_GET['search'];
}

// Obtener el total de registros para la paginación
$totalRecords = $vehiculoModel->countAllVehiculos($filters);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Obtener los registros de vehículos
$vehiculos = $vehiculoModel->getAllVehiculos($filters, $recordsPerPage, $offset);

// Mensajes de estado (éxito/error) desde redirecciones
$status_message = '';
if (isset($_GET['status']) && isset($_GET['message'])) {
    $status_type = $_GET['status'];
    $status_message = htmlspecialchars(urldecode($_GET['message']));
}

// Determinar si el usuario actual es 'admin'
$isAdmin = (Auth::getUserRole() === 'admin');

// --- Incluir Vistas ---
include __DIR__ . '/src/views/header.php';
?>

<style>
.vehicles-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.vehicles-header {
    text-align: center;
    margin-bottom: 30px;
}

.vehicles-title {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.filters-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.search-container {
    position: relative;
    margin-bottom: 20px;
}

.search-input {
    width: 100%;
    padding: 12px 16px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m21 21-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z'/%3e%3c/svg%3e");
    background-position: 15px center;
    background-repeat: no-repeat;
    background-size: 20px;
}

.search-input:focus {
    outline: none;
    border-color: #007bff;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.btn-search {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-search:hover {
    background: #0056b3;
    text-decoration: none;
    color: white;
}

.btn-clear-filters {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-clear-filters:hover {
    background: #c82333;
    text-decoration: none;
    color: white;
}

.table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
}

.records-count {
    font-size: 14px;
    color: #6c757d;
}

.table-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-export {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-export:hover {
    background: #218838;
}

.records-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6c757d;
}

.records-select {
    padding: 6px 12px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 14px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
    border-bottom: 1px solid #e9ecef;
}

.data-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f3f4;
    font-size: 14px;
    color: #495057;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.no-records {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-style: italic;
}

.pagination-container {
    padding: 20px 25px;
    display: flex;
    justify-content: center;
    gap: 8px;
}

.pagination-btn {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    background: white;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination-btn:hover {
    background: #e9ecef;
    text-decoration: none;
}

.pagination-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-view {
    background: #007bff;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
    vertical-align: middle;
}

.btn-view:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}

.btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
    margin-left: 8px; /* Más espacio entre botones */
    vertical-align: middle; /* Alinear verticalmente */
}

.btn-delete:hover {
    background: #c82333;
    color: white;
    text-decoration: none;
}

.actions-container {
    display: flex;
    gap: 8px; /* Espacio consistente entre botones */
    align-items: center; /* Alineación vertical */
}

/* Estilo para la columna Type */
.type-badge {
    background-color: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    display: inline-block;
}

/* === ESTILOS PARA IMPRESIÓN === */
@media print {
    /* Ocultar elementos que no deben aparecer en el PDF */
    .filters-container,
    .table-actions,
    .pagination-container,
    .no-print,
    .btn-delete,
    header.app-header,
    .user-info,
    nav {
        display: none !important;
    }
    
    /* Ajustes para la página impresa */
    body {
        background: white !important;
        font-size: 12px;
    }
    
    .vehicles-container {
        max-width: none;
        margin: 0;
        padding: 0;
        background: white;
        box-shadow: none;
    }
    
    .vehicles-header {
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }
    
    .vehicles-title {
        font-size: 20px;
        color: #000;
    }
    
    .table-container {
        border-radius: 0;
        box-shadow: none;
        border: 1px solid #333;
    }
    
    .table-header {
        padding: 10px;
        border-bottom: 1px solid #333;
    }
    
    .records-count {
        font-weight: bold;
        color: #000;
    }
    
    .data-table {
        font-size: 11px;
    }
    
    .data-table th {
        background: #f0f0f0 !important;
        color: #000 !important;
        font-weight: bold;
        border: 1px solid #333;
        padding: 8px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .data-table td {
        border: 1px solid #333;
        padding: 8px;
        color: #000 !important;
    }
    
    /* Estilo para type-badge en impresión */
    .type-badge {
        background-color: #f0f0f0 !important;
        color: #000 !important;
        border: 1px solid #333;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Información adicional en el pie de página */
    .print-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #666;
        border-top: 1px solid #333;
        padding: 5px;
        background: white;
    }
    
    /* Forzar salto de página si es necesario */
    .page-break {
        page-break-before: always;
    }
}

@media (max-width: 768px) {
    .table-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .table-actions {
        justify-content: space-between;
    }
    
    .filter-actions {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<div class="vehicles-container">
    <div class="vehicles-header">
        <h1 class="vehicles-title">Vehicle Registry</h1>
        <div class="print-info" style="font-size: 14px; color: #666; margin-top: 10px;">
            Generated on: <?php echo date('d/m/Y H:i A'); ?> | 
            User: <?php echo htmlspecialchars(Auth::getUsername()); ?>
            <?php if (!empty($filters)): ?>
                <br>Filters applied: 
                <?php if (!empty($filters['search_query'])): ?>Search: "<?php echo htmlspecialchars($filters['search_query']); ?>"<?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <span class="records-count"><?php echo $totalRecords; ?> vehicles found</span>
            <div class="table-actions">
                <button class="btn-export" id="btn-print" onclick="printVehicles()">Export</button>
                <div class="records-per-page">
                    <span>Records Per Page</span>
                    <form method="GET" style="display: inline;">
                        <?php foreach ($_GET as $key => $value): ?>
                            <?php if ($key !== 'records_per_page'): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <select name="records_per_page" class="records-select" onchange="this.form.submit()">
                            <option value="5" <?php echo ($recordsPerPage == 5 ? 'selected' : ''); ?>>5</option>
                            <option value="10" <?php echo ($recordsPerPage == 10 ? 'selected' : ''); ?>>10</option>
                            <option value="20" <?php echo ($recordsPerPage == 20 ? 'selected' : ''); ?>>20</option>
                            <option value="50" <?php echo ($recordsPerPage == 50 ? 'selected' : ''); ?>>50</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Driver</th>
                    <th>License Plates</th>
                    <th>Company</th>
                    <th>Model</th>
                    <th>Type</th>
                    <th>Registered by</th>
                    <th>Registration Date</th>
                    <?php if ($isAdmin): // Mostrar columna de acciones solo si es admin ?>
                    <th class="no-print">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehiculos)): ?>
                    <tr>
                        <td colspan="<?php echo ($isAdmin ? '9' : '8'); ?>" class="no-records">No vehicles found matching the search criteria</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vehiculo['id']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['nombre_conductor']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['placas']); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['empresa'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($vehiculo['modelo'] ?? 'N/A'); ?></td>
                            <td><span class="type-badge">Entrada</span></td>
                            <td><?php echo htmlspecialchars($vehiculo['usuario_del_sistema_username'] ?? 'Unknown User'); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($vehiculo['fecha_creacion']))); ?></td>
                            <?php if ($isAdmin): // Mostrar botón de eliminar solo si es admin ?>
                            <td class="no-print">
                                <div class="actions-container">
                                    <a href="/editar_vehiculo.php?id=<?php echo htmlspecialchars($vehiculo['id']); ?>" 
                                    class="btn-view" 
                                    title="Editar Vehiculo">
                                        Edit
                                    </a>
                                    <a href="/eliminar_vehiculo.php?id=<?php echo htmlspecialchars($vehiculo['id']); ?>"
                                    class="btn-delete"
                                    title="Eliminar Vehículo"
                                    onclick="return confirm('¿Estás seguro de que quieres eliminar este vehículo (ID: <?php echo htmlspecialchars($vehiculo['id']); ?> - Placas: <?php echo htmlspecialchars($vehiculo['placas']); ?>)? Esta acción no se puede deshacer.');">
                                        Delete
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
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
        <?php endif; ?>
    </div>
</div>

<!-- Pie de página para impresión -->
<div class="print-footer" style="display: none;">
    <p>Gtek Logistics - Vehicle Registry | Page <span id="page-number"></span></p>
</div>

<script>
function printVehicles() {
    // Mostrar el pie de página solo al imprimir
    const printFooter = document.querySelector('.print-footer');
    if (printFooter) {
        printFooter.style.display = 'block';
    }
    
    // Cambiar el título del documento temporalmente
    const originalTitle = document.title;
    document.title = 'Registro_Vehiculos_' + new Date().toISOString().split('T')[0];
    
    // Llamar a la función de impresión
    window.print();
    
    // Restaurar el título original después de la impresión
    setTimeout(() => {
        document.title = originalTitle;
        if (printFooter) {
            printFooter.style.display = 'none';
        }
    }, 1000);
}

// También puedes agregar un evento para detectar cuando se cancela la impresión
window.addEventListener('afterprint', function() {
    const printFooter = document.querySelector('.print-footer');
    if (printFooter) {
        printFooter.style.display = 'none';
    }
});

// Agregar funcionalidad adicional para el atajo de teclado Ctrl+P
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printVehicles();
    }
});
</script>

<?php include __DIR__ . '/src/views/footer.php'; ?>