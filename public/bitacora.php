<?php
// public/bitacora.php
// Esta será la página principal después del login

session_start();
// Lógica para verificar sesión (se desarrollará en Día 2)
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php'); // Redirigir al login si no está autenticado
    exit;
}

$pageTitle = "Consulta de Bitácora - Gtek Logistics";
include __DIR__ . '/../src/views/header.php';
// Incluimos la navbar aquí o si va dentro del header, se omite
include __DIR__ . '/../src/views/navbar.php'; 
?>
<div class="page-content">
    <h2 class="page-title">Consulta de Bitácora</h2>

    <div class="filters-section">
        <form action="" method="GET" class="filter-form">
            <div class="filter-group">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="2024-01-01">
            </div>
            <div class="filter-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="2024-01-31">
            </div>
            <div class="filter-group">
                <label for="tipo_operacion">Tipo de Operación:</label>
                <select id="tipo_operacion" name="tipo_operacion">
                    <option value="">Todos</option>
                    <option value="Entrada">Entrada</option>
                    <option value="Salida">Salida</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <input type="text" id="search" name="search" placeholder="Conocimiento, Pedimento...">
                <button type="submit" class="btn btn-search">Q Buscar</button>
                <button type="button" class="btn btn-clear">X</button>
            </div>
            <div class="filter-active-indicator">
                <span>Filtros activos</span> <span class="filter-icon"></span>
            </div>
        </form>
    </div>

    <div class="records-section">
        <h3 class="section-title">Registros de Bitácora</h3>
        <div class="records-header-actions">
            <span class="record-count">0 registros encontrados</span>
            <button class="btn btn-export">Exportar</button>
            <div class="view-options">
                <label for="records_per_page">Ver:</label>
                <select id="records_per_page" name="records_per_page">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
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
                <tr>
                    <td>2024-06-26 10:30:00</td>
                    <td><span class="tag entry-tag">Entrada</span></td>
                    <td>BL123456789</td>
                    <td>Cajas de componentes electrónicos, 500KG, 10 bultos</td>
                    <td>Tech Solutions S.A.</td>
                    <td><a href="/detalle_registro.php?id=123" class="btn-icon"><img src="/assets/img/icon_eye.svg" alt="Ver detalle"></a></td>
                </tr>
                </tbody>
        </table>
        
        <div class="pagination">
            </div>
    </div>
</div>
<?php include __DIR__ . '/../src/views/footer.php'; ?>