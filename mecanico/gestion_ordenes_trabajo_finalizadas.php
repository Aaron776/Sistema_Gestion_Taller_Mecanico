<?php
require_once '../autorizacion/auth.php';


// Verificar que tenga rol de mecanico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'mecanico') {
    header("Location: ../acceso_denegado.php");
    exit();
}

$id_mecanico = $_SESSION['id_usuario']; // Obtener el id del mecanico que esta logueado

include '../templates/header.php';
include_once '../conexion/bd.php';
include_once '../conexion/session.php';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el total de registros para la paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_usuario_asignado = :id_mecanico AND estado IN ('pendiente', 'en_proceso')");
$sql_count->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql_count->execute();
$total_registros = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener las ordenes de trabajo asignadas al mecanico de la base de datos con paginación
$sql = $conexion->prepare("SELECT ordenes_trabajo.estado as estado, ordenes_trabajo.id_cliente as id_cliente,ordenes_trabajo.id_vehiculo as id_vehiculo, vehiculos.marca as marca,vehiculos.placa as placa,ordenes_trabajo.id_orden as id_orden,ordenes_trabajo.fecha_creacion as fecha_creacion,ordenes_trabajo.fecha_entrega as fecha_entrega,ordenes_trabajo.observaciones as observaciones,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_mecanico,CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente=clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo=vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado=usuarios.id_usuario WHERE ordenes_trabajo.id_usuario_asignado = :id_mecanico AND ordenes_trabajo.estado='finalizado' ORDER BY ordenes_trabajo.fecha_creacion DESC LIMIT :limit OFFSET :offset");
$sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql->bindValue(':limit', (int)$registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_OBJ);


// Contar el numero de ordenes de trabajo asignadas al mecanico
$sql_total = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_usuario_asignado = :id_mecanico");
$sql_total->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql_total->execute();
$total_ordenes = $sql_total->fetch(PDO::FETCH_OBJ);

// Contar ordenes en proceso
$sql_proceso = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_usuario_asignado = :id_mecanico AND estado = 'en_proceso'");
$sql_proceso->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql_proceso->execute();
$ordenes_proceso = $sql_proceso->fetch(PDO::FETCH_OBJ);

// Contar ordenes completadas (hoy)
$sql_completadas = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_usuario_asignado = :id_mecanico AND estado = 'finalizada' AND DATE(fecha_entrega) = CURDATE()");
$sql_completadas->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql_completadas->execute();
$ordenes_completadas = $sql_completadas->fetch(PDO::FETCH_OBJ);
?>
<style>
    /* Contenido Principal */
    .content {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    /* Breadcrumb */
    .breadcrumb {
        background-color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .breadcrumb ul {
        display: flex;
        list-style: none;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb a:hover {
        color: var(--secondary);
    }

    .breadcrumb .separator {
        color: var(--gray);
    }

    .breadcrumb .current {
        color: var(--dark);
        font-weight: 600;
    }

    /* Unique Card Adjustments */
    .card-header {
        padding: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Filtros y búsqueda */
    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid #ddd;
        border-radius: 6px;
        transition: var(--transition);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    /* Tablas */
    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .table th {
        padding: 15px;
        border-bottom: 2px solid #e3e6f0;
        white-space: nowrap;
    }

    .table td {
        padding: 15px;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        min-width: 100px;
        text-align: center;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-in-progress {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-rechazada {
        background-color: #ffd8d8;
        color: #9d1c24;
        border: 1px solid #f5c6cb;
    }

    .status-finalizada,
    .status-finalizado {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* Priority Badges */
    .priority-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .priority-low {
        background-color: #e7f5ff;
        color: #0066cc;
        border: 1px solid #b3d9ff;
    }

    .priority-medium {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .priority-high {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .priority-urgent {
        background-color: #dc3545;
        color: white;
        border: 1px solid #c82333;
    }

    .btn-action {
        padding: 5px 12px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 0.8rem;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, #1a3a5f 0%, #3a608f 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(26, 58, 95, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .btn-action-primary:hover {
        background: linear-gradient(135deg, #3a608f 0%, #1a3a5f 100%);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 7px 14px rgba(26, 58, 95, 0.3), 0 3px 6px rgba(0, 0, 0, 0.1);
        color: white;
    }

    .btn-action-primary:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 2px 4px rgba(26, 58, 95, 0.2);
    }

    .btn-action-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #a82a33 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(230, 57, 70, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .btn-action-danger:hover {
        background: linear-gradient(135deg, #a82a33 0%, var(--danger) 100%);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 7px 14px rgba(230, 57, 70, 0.3), 0 3px 6px rgba(0, 0, 0, 0.1);
        color: white;
    }

    .btn-action-danger:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 2px 4px rgba(230, 57, 70, 0.2);
    }

    .btn-action-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .btn-action-success:hover {
        background: linear-gradient(135deg, #218838 0%, #28a745 100%);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 7px 14px rgba(40, 167, 69, 0.3), 0 3px 6px rgba(0, 0, 0, 0.1);
        color: white;
    }

    .btn-action-success:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
    }

    .btn-action-warning {
        background-color: var(--warning);
        color: var(--dark);
    }

    .btn-action-warning:hover {
        background-color: #e0a800;
    }

    /* Botón de estado */
    .btn-status {
        padding: 6px 12px;
        border-radius: 4px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.85rem;
        min-width: 100px;
    }

    .btn-status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .btn-status-pending:hover {
        background-color: #ffeaa7;
    }

    .btn-status-in-progress {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .btn-status-in-progress:hover {
        background-color: #bee5eb;
    }

    .btn-status-completed {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .btn-status-completed:hover {
        background-color: #c3e6cb;
    }

    /* Resumen de órdenes */
    .orders-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .summary-card {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: var(--shadow);
        text-align: center;
        border-top: 4px solid var(--primary);
        transition: var(--transition);
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .summary-card h3 {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 5px;
    }

    .summary-card p {
        color: var(--gray);
        font-size: 0.9rem;
        margin: 0;
    }

    @media (max-width: 992px) {
        .orders-summary {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .orders-summary {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .summary-card {
            padding: 15px;
        }

        .user-info {
            display: none;
        }
    }

    @media (max-width: 576px) {

        .table td,
        .table th {
            padding: 10px;
        }

        .btn-action {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .btn-status {
            min-width: 80px;
            padding: 4px 8px;
            font-size: 0.8rem;
        }
    }

    /* Mensajes de alerta */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger);
    }

    /* Paginación Ultra-Premium */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 30px;
        list-style: none;
        padding: 0;
    }

    .page-item {
        display: inline-block;
    }

    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 15px;
        border-radius: 8px;
        background: white;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e3e6f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, #3a608f 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 10px rgba(26, 58, 95, 0.3);
    }

    .page-link:hover:not(.active) {
        background-color: #f8f9fc;
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .page-item.disabled .page-link {
        background-color: #f8f9fc;
        color: #b7b9cc;
        cursor: not-allowed;
        border-color: #eaecf4;
        box-shadow: none;
        transform: none;
    }

    /* Estado Vacío */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin: 0;
    }
</style>
<!-- Resumen de órdenes -->
<div class="orders-summary">
    <div class="summary-card">
        <h3><?php echo htmlspecialchars($total_ordenes->total); ?></h3>
        <p>Total de Órdenes</p>
    </div>
    <div class="summary-card" style="border-top-color: var(--info);">
        <h3><?php echo htmlspecialchars($ordenes_proceso->total); ?></h3>
        <p>En Progreso</p>
    </div>
    <div class="summary-card" style="border-top-color: var(--success);">
        <h3><?php echo htmlspecialchars($ordenes_completadas->total); ?></h3>
        <p>Completadas Hoy</p>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filters">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por orden, cliente o vehículo...">
    </div>
    <div class="filter-group">
        <select class="form-control" id="filterStatus" style="padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; width: 200px;">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="in-progress">En Progreso</option>
            <option value="completed">Completado</option>
            <option value="cancelled">Cancelado</option>
            <option value="rechazada">Rechazada</option>
        </select>
    </div>
    <div class="filter-group">
        <select class="form-control" id="filterPriority" style="padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; width: 200px;">
            <option value="">Todas las prioridades</option>
            <option value="low">Baja</option>
            <option value="medium">Media</option>
            <option value="high">Alta</option>
            <option value="urgent">Urgente</option>
        </select>
    </div>
</div>

<!-- Tabla de órdenes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lista de Órdenes de Trabajo Finalizadas</h2>
        <div class="search-container" style="flex-wrap: wrap; gap: 10px;">
            <a href="../ReportesPDF/reporteOrdenesTrabajoFinalizadas.php" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="../ReportesExcel/reporteOrdenesTrabajoFinalizadas.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <button class="btn btn-warning" id="btnRefresh">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="ordenesTable">
                <thead>
                    <tr>
                        <th>ID Orden</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Entrega</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)) : ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No tienes órdenes de trabajo finalizadas en este momento.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($ordenes as $item) { ?>
                            <tr>
                                <td><strong>ORD-<?php echo htmlspecialchars(date('Y')) ?>-<?php echo htmlspecialchars(htmlspecialchars($item->id_orden)); ?></strong></td>
                                <td><?php echo htmlspecialchars(htmlspecialchars($item->nombre_cliente)); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars(htmlspecialchars($item->marca)); ?></div>
                                    <small style="color: var(--gray);"><?php echo htmlspecialchars(htmlspecialchars($item->placa)); ?></small>
                                </td>
                                <td>
                                    <?php if ($item->estado == 'pendiente') : ?>
                                        <span class="status-badge status-pending">Pendiente</span>
                                    <?php elseif ($item->estado == 'en_proceso') : ?>
                                        <span class="status-badge status-in-progress">En Proceso</span>
                                    <?php elseif ($item->estado == 'rechazada') : ?>
                                        <span class="status-badge status-rechazada">Rechazada</span>
                                    <?php elseif (strtolower($item->estado) == 'finalizada' || strtolower($item->estado) == 'finalizado') : ?>
                                        <span class="status-badge status-finalizada">Finalizada</span>
                                    <?php else : ?>
                                        <span class="status-badge"><?php echo htmlspecialchars($item->estado); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime(htmlspecialchars($item->fecha_creacion)))); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime(htmlspecialchars($item->fecha_entrega)))); ?></td>
                                <td><?php echo htmlspecialchars(htmlspecialchars($item->observaciones)); ?></td>
                            </tr>
                        <?php } ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($pagina_actual > 1) ? '?pagina=' . ($pagina_actual - 1) : '#'; ?>" tabindex="-1">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                    <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo ($pagina_actual < $total_paginas) ? '?pagina=' . ($pagina_actual + 1) : '#'; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
</div>
</main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            main.classList.toggle('expanded');
        });

        // Elementos del DOM
        const modalEstado = document.getElementById('modalEstado');
        const modalClose = document.getElementById('modalClose');
        const btnCancelModal = document.getElementById('btnCancelModal');
        const btnGuardarEstado = document.getElementById('btnGuardarEstado');
        const estadoSelect = document.getElementById('estadoOrden');
        const modalOrderInfo = document.getElementById('modalOrderInfo');
        const statusPreview = document.getElementById('statusPreview');
        const previewStatus = document.getElementById('previewStatus');
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const filterPriority = document.getElementById('filterPriority');
        const btnExport = document.getElementById('btnExport');
        const btnRefresh = document.getElementById('btnRefresh');
        const tableRows = document.querySelectorAll('#ordenesTable tbody tr');

        // Funcionalidad de búsqueda
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchTerm) ? '' : 'none';
            });
        });

        // Funcionalidad de filtro por estado
        filterStatus.addEventListener('change', function() {
            const estado = this.value;
            applyFilters();
        });

        // Funcionalidad de filtro por prioridad
        filterPriority.addEventListener('change', function() {
            const prioridad = this.value;
            applyFilters();
        });

        function applyFilters() {
            const estado = filterStatus.value;
            const prioridad = filterPriority.value;

            tableRows.forEach(row => {
                let showRow = true;

                // Filtrar por estado
                if (estado) {
                    const statusBadge = row.querySelector('.status-badge');
                    const rowEstado = getEstadoValue(statusBadge.textContent);
                    if (rowEstado !== estado) {
                        showRow = false;
                    }
                }

                // Filtrar por prioridad
                if (prioridad && showRow) {
                    const priorityBadge = row.querySelector('.priority-badge');
                    if (priorityBadge) {
                        const rowPrioridad = getPrioridadValue(priorityBadge.textContent);
                        if (rowPrioridad !== prioridad) {
                            showRow = false;
                        }
                    }
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        function getEstadoValue(estadoTexto) {
            switch (estadoTexto.toLowerCase()) {
                case 'pendiente':
                    return 'pending';
                case 'en progreso':
                    return 'in-progress';
                case 'completado':
                    return 'completed';
                case 'cancelado':
                    return 'cancelled';
                case 'finalizada':
                    return 'finalizada';
                default:
                    return estadoTexto.toLowerCase();
            }
        }

        function getPrioridadValue(prioridadTexto) {
            switch (prioridadTexto.toLowerCase()) {
                case 'baja':
                    return 'low';
                case 'media':
                    return 'medium';
                case 'alta':
                    return 'high';
                case 'urgente':
                    return 'urgent';
                default:
                    return prioridadTexto.toLowerCase();
            }
        }
    });
</script>
<?php include '../templates/footer.php'; ?>