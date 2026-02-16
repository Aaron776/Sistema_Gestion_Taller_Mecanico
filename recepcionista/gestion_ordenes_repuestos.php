<?php
require_once '../autorizacion/auth.php';

// Verificar que tenga rol de recepcionista
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'recepcionista') {
    header("Location: ../acceso_denegado.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el total de registros para la paginación (considerando el estado en_proceso como el filtro actual)
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM orden_repuestos INNER JOIN ordenes_trabajo ON orden_repuestos.id_orden = ordenes_trabajo.id_orden WHERE ordenes_trabajo.estado = 'en_proceso'");
$sql_count->execute();
$total_repuestos_count = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_repuestos_count / $registros_por_pagina);

// Obtener ordenes de repuesto con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT orden_repuestos.id_detalle as id_orden_repuesto, orden_repuestos.cantidad as cantidad, orden_repuestos.precio_unitario as precio_unitario,repuestos.id_repuesto as id_repuesto, repuestos.nombre as nombre_repuesto, ordenes_trabajo.id_orden as id_orden, CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente FROM orden_repuestos INNER JOIN repuestos ON orden_repuestos.id_repuesto = repuestos.id_repuesto INNER JOIN ordenes_trabajo ON orden_repuestos.id_orden = ordenes_trabajo.id_orden INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente WHERE ordenes_trabajo.estado = 'en_proceso' ORDER BY orden_repuestos.id_detalle DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_OBJ);

// 1. Obtener cantidad total de ordenes de repuestos
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM orden_repuestos");
$sql->execute();
$total_ordenes = $sql->fetch(PDO::FETCH_OBJ);

// 2. Obtener el total facturado (suma de cantidad * precio_unitario)
$sql = $conexion->prepare("SELECT SUM(cantidad * precio_unitario) as total_facturado FROM orden_repuestos");
$sql->execute();
$total_facturado = $sql->fetch(PDO::FETCH_OBJ);

// 3. Obtener promedio de precio unitario por repuesto
$sql = $conexion->prepare("SELECT AVG(precio_unitario) as promedio_precio FROM orden_repuestos");
$sql->execute();
$promedio_precio = $sql->fetch(PDO::FETCH_OBJ);

// 4. Obtener total de cantidad de repuestos
$sql = $conexion->prepare("SELECT SUM(cantidad) as total_cantidad FROM orden_repuestos");
$sql->execute();
$total_cantidad = $sql->fetch(PDO::FETCH_OBJ);
?>
<style>
    /* Contenido Principal Override */
    .content {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    /* Cards Override */
    .card {
        margin-bottom: 25px;
    }

    .card-body {
        padding: 25px;
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



    /* Paginación */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
        padding: 20px 0;
    }

    .page-item {
        list-style: none;
    }

    .page-link {
        display: block;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }

    .page-link:hover {
        background-color: #f8f9fc;
    }

    .page-item.active .page-link {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Responsive Page Specific */
    @media (max-width: 992px) {
        .filters {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {

        .table th,
        .table td {
            padding: 10px;
        }
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

    /* Estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
        width: 100%;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
    }

    .stat-card i {
        font-size: 2.5rem;
        opacity: 0.9;
    }

    .stat-info h3 {
        font-size: 1.8rem;
        margin-bottom: 5px;
        margin-top: 0;
    }

    .stat-info p {
        opacity: 0.9;
        margin: 0;
        font-size: 0.9rem;
    }

    .stat-card.success {
        background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
    }

    .stat-card.info {
        background: linear-gradient(135deg, var(--info) 0%, #5bc0de 100%);
    }

    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
    }
</style>


<!-- Estadísticas -->
<div class="stats-container">
    <div class="stat-card">
        <i class="fas fa-clipboard-list"></i>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_ordenes->total ?? 0); ?></h3>
            <p>Total Órdenes</p>
        </div>
    </div>
    <div class="stat-card success">
        <i class="fas fa-cogs"></i>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_cantidad->total_cantidad ?? 0); ?></h3>
            <p>Total Repuestos</p>
        </div>
    </div>
    <div class="stat-card warning">
        <i class="fas fa-dollar-sign"></i>
        <div class="stat-info">
            <h3>$<?php echo number_format($promedio_precio->promedio_precio ?? 0, 2, '.', ','); ?></h3>
            <p>Precio Promedio</p>
        </div>
    </div>
    <div class="stat-card info">
        <i class="fas fa-money-bill-wave"></i>
        <div class="stat-info">
            <h3>$<?php echo number_format($total_facturado->total_facturado ?? 0, 2, '.', ','); ?></h3>
            <p>Total Facturado</p>
        </div>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filters">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por ID de orden, repuesto...">
    </div>
    <div class="actions" style="display: flex; gap: 10px;">
        <a href="../ReportesPDF/reporteOrdenesRepuestos.php" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="../ReportesExcel/reporteOrdenesRepuestos.php" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Excel
        </a>
    </div>
</div>

<!-- Tabla de órdenes de repuestos -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lista de Órdenes de Repuestos</h2>
        <span class="badge" style="background-color: var(--primary); color: white; padding: 5px 10px; border-radius: 20px;">
            Mostrando <?php echo count($ordenes); ?> de <?php echo $total_repuestos_count; ?> órdenes
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="ordenesRepuestosTable">
                <thead>
                    <tr>
                        <th>ID Orden Repuesto</th>
                        <th>Orden de Trabajo</th>
                        <th>Repuesto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)) { ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No se encontraron órdenes de repuestos registradas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($ordenes as $item) { ?>
                            <tr>
                                <td>
                                    <strong>ORD-REP-<?php echo date('Y') . '-' . htmlspecialchars($item->id_orden_repuesto); ?></strong>
                                </td>
                                <td>
                                    <strong>ORD-<?php echo htmlspecialchars($item->id_orden); ?></strong>
                                    <br>
                                    <small>Cliente: <?php echo htmlspecialchars($item->nombre_cliente); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->nombre_repuesto); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($item->cantidad); ?></td>
                                <td>
                                    <strong>$<?php echo number_format($item->precio_unitario, 2); ?></strong>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($item->precio_unitario * $item->cantidad, 2); ?></strong>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <ul class="pagination">
                <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                </li>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </div>
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

        // Funcionalidad de búsqueda
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#ordenesRepuestosTable tbody tr');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
</script>
<?php include '../templates/footer.php'; ?>