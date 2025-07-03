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
if (!empty($_GET['fecha_conclusion_descarga'])) {
    $filters['fecha_conclusion_descarga'] = $_GET['fecha_conclusion_descarga'];
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

// Determinar si el usuario actual es 'admin'
$isAdmin = (Auth::getUserRole() === 'admin');

// --- Incluir Vistas ---
include __DIR__ . '/src/views/header.php';
?>

<style>
    .logbook-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.logbook-header {
    text-align: center;
    margin-bottom: 30px;
}

.logbook-title {
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

.filters-row {
    display: grid;
    grid-template-columns: 1fr 1fr 200px;
    gap: 20px;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 8px;
}

.filter-input {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.filter-input:focus {
    outline: none;
    border-color: #007bff;
}

.filter-select {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 45px;
}

.search-container {
    position: relative;
    grid-column: 1 / -1;
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

.operation-tag {
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.tag-entry {
    background: #d4edda;
    color: #155724;
}

.tag-exit {
    background: #f8d7da;
    color: #721c24;
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

/* Contenedor para los botones de acción */
.actions-container {
    display: flex;
    gap: 8px; /* Espacio consistente entre botones */
    align-items: center; /* Alineación vertical */
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

.no-records {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-style: italic;
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

/* === ESTILOS PARA IMPRESIÓN === */
@media print {
    /* Ocultar elementos que no deben aparecer en el PDF */
    .filters-container,
    .table-actions,
    .pagination-container,
    .no-print,
    .btn-view,
    .btn-delete,
    .actions-container,
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
    
    .logbook-container {
        max-width: none;
        margin: 0;
        padding: 0;
        background: white;
        box-shadow: none;
    }
    
    .logbook-header {
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }
    
    .logbook-title {
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
    
    .operation-tag {
        border: 1px solid #333;
        color: #000 !important;
        background: #f0f0f0 !important;
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
    .filters-row {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .table-actions {
        justify-content: space-between;
    }
}
</style>

<div class="logbook-container">
    <div class="logbook-header">
        <h1 class="logbook-title">Merchandise Movement Logbook</h1>
        <div class="print-info" style="font-size: 14px; color: #666; margin-top: 10px;">
            Generated on: <?php echo date('d/m/Y H:i A'); ?> | 
            User: <?php echo htmlspecialchars(Auth::getUsername()); ?>
            <?php if (!empty($filters)): ?>
                <br>Filters applied: 
                <?php if (!empty($filters['fecha_inicio'])): ?>Start: <?php echo $filters['fecha_inicio']; ?><?php endif; ?>
                <?php if (!empty($filters['fecha_conclusion_descarga'])): ?> | End: <?php echo $filters['fecha_conclusion_descarga']; ?><?php endif; ?>
                <?php if (!empty($filters['tipo_operacion'])): ?> | Type: <?php echo $filters['tipo_operacion']; ?><?php endif; ?>
                <?php if (!empty($filters['search_query'])): ?> | Search: "<?php echo htmlspecialchars($filters['search_query']); ?>"<?php endif; ?>
            <?php endif; ?>
        </div>
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
                    <label class="filter-label" for="fecha_conclusion_descarga">End Date</label>
                    <input type="date" id="fecha_conclusion_descarga" name="fecha_conclusion_descarga" class="filter-input" 
                           value="<?php echo htmlspecialchars($filters['fecha_conclusion_descarga'] ?? ''); ?>" 
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
                       placeholder="Search by Numero de Registro" 
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
                <button class="btn-export" id="btn-print" onclick="printLogbook()">Export</button>
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
                    <th class="no-print">Actions</th>
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
                            <td>
                                <?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($registro['fecha_ingreso']))); ?>
                            </td>
                            <td><?php echo htmlspecialchars($registro['tipo_operacion']); ?></td>
                            <td><?php echo htmlspecialchars($registro['num_conocimiento_embarque']); ?></td>
                            <td><?php echo htmlspecialchars($registro['descripcion_mercancia'] . ', ' . $registro['peso_unidad_medida'] . 'KG, ' . $registro['num_bultos'] . ' bultos'); ?></td>
                            <td><?php echo htmlspecialchars($registro['consignatario_nombre'] ?? 'N/A'); ?></td>
                            <td class="no-print">
                                <div class="actions-container">
                                    <a href="/detalle_registro.php?id=<?php echo htmlspecialchars($registro['id']); ?>" 
                                    class="btn-view">
                                        View
                                    </a>
                                    <?php if ($isAdmin): // Mostrar botón de eliminar solo si es admin ?>
                                    <a href="/editar_entrada.php?id=<?php echo htmlspecialchars($registro['id']); ?>" 
                                    class="btn-view" 
                                    title="Editar Vehiculo">
                                        Edit
                                    </a>
                                    <a href="/eliminar_registro.php?id=<?php echo htmlspecialchars($registro['id']); ?>"
                                    class="btn-delete"
                                    title="Eliminar Registro"
                                    onclick="return confirm('¿Estás seguro de que quieres eliminar este registro de bitácora (ID: <?php echo htmlspecialchars($registro['id']); ?>)? Esta acción no se puede deshacer.');">
                                        Delete
                                    </a>
                                    <?php endif; ?>
                                </div>
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
        <?php endif; ?>
    </div>
</div>

<!-- Pie de página para impresión -->
<div class="print-footer" style="display: none;">
    <p>Gtek Logistics - Merchandise Movement Logbook | Page <span id="page-number"></span></p>
</div>

<script>
function printLogbook() {
    // Mostrar el pie de página solo al imprimir
    const printFooter = document.querySelector('.print-footer');
    if (printFooter) {
        printFooter.style.display = 'block';
    }
    
    // Cambiar el título del documento temporalmente
    const originalTitle = document.title;
    document.title = 'Bitacora_Mercancia_' + new Date().toISOString().split('T')[0];
    
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
        printLogbook();
    }
});
</script>

<?php include __DIR__ . '/src/views/footer.php'; ?>