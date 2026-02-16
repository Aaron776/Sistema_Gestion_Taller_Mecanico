<?php
require_once '../autorizacion/auth.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Obtener el total de registros para la paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM orden_servicios");
$sql_count->execute();
$total_servicios_count = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_servicios_count / $registros_por_pagina);

// Obtener las ordenes de servicio de la base de datos con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT orden_servicios.cantidad as cantidad,orden_servicios.precio_unitario as precio_unitario,orden_servicios.id_detalle as id_orden_servicio, servicios.nombre_servicio as nombre_servicio, CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente, ordenes_trabajo.id_orden as id_orden FROM orden_servicios INNER JOIN servicios ON orden_servicios.id_servicio = servicios.id_servicio INNER JOIN ordenes_trabajo ON orden_servicios.id_orden = ordenes_trabajo.id_orden INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente  ORDER BY orden_servicios.id_detalle DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener cantidad total de ordenes de servicio
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM orden_servicios");
$sql->execute();
$total_ordenes = $sql->fetch(PDO::FETCH_OBJ);

// Obtener el total facturado (suma de cantidad * precio_unitario)
$sql = $conexion->prepare("SELECT SUM(cantidad * precio_unitario) as total_facturado FROM orden_servicios");
$sql->execute();
$total_facturado = $sql->fetch(PDO::FETCH_OBJ);

// Obtener promedio de precio por servicio
$sql = $conexion->prepare("SELECT AVG(precio_unitario) as promedio_precio FROM orden_servicios");
$sql->execute();
$promedio_precio = $sql->fetch(PDO::FETCH_OBJ);

// Obtener total de cantidad de servicios
$sql = $conexion->prepare("SELECT SUM(cantidad) as total_cantidad FROM orden_servicios");
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

    /* Cards Override */
    .card {
        margin-bottom: 25px;
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid #e3e6f0;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-body {
        padding: 20px;
    }

    /* Estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-card i {
        font-size: 2.5rem;
        opacity: 0.9;
    }

    .stat-info h3 {
        font-size: 1.8rem;
        margin-bottom: 5px;
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

    /* Tablas Override */
    .table {
        min-width: 1000px;
    }

    .table th,
    .table td {
        padding: 15px;
    }

    /* Acciones en tabla */
    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .btn-icon {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }

    .btn-edit {
        background-color: rgba(26, 58, 95, 0.1);
        color: var(--primary);
    }

    .btn-edit:hover {
        background-color: var(--primary);
        color: white;
    }

    .btn-delete {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
    }

    .btn-delete:hover {
        background-color: var(--danger);
        color: white;
    }



    /* Badges */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
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



    /* Formularios */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
    }

    .form-group .required::after {
        content: " *";
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        transition: var(--transition);
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
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

    .alert i {
        font-size: 1.2rem;
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

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .actions {
            flex-direction: column;
            gap: 5px;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }
    }

    /* Animaciones Page Specific */
    @keyframes slideIn {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
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
        <i class="fas fa-box"></i>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_cantidad->total_cantidad ?? 0); ?></h3>
            <p>Cantidad Total de Servicios</p>
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
        <input type="text" id="searchInput" placeholder="Buscar por ID, orden de trabajo o servicio...">
    </div>
    <div class="filter-group">
        <select class="form-control" id="filterEstado" style="width: 200px;">
            <option value="">Todos los estados</option>
            <option value="completado">Completado</option>
            <option value="proceso">En Proceso</option>
            <option value="pendiente">Pendiente</option>
        </select>
    </div>
    <a href="../ReportesPDF/reporteOrdenesServicios.php" class="btn btn-danger">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    <a href="../ReportesExcel/reporteOrdenesServicios.php" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Excel
    </a>
    <a type="button" href="crear_orden_servicio.php" class="btn btn-primary" id="btnNuevaOrdenServicio">
        <i class="fas fa-plus"></i> Nueva Orden de Servicio
    </a>
</div>

<!-- Tabla de órdenes de servicio -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lista de Órdenes de Servicio</h2>
        <div class="card-actions">
            <span class="badge badge-success">Mostrando <?php echo count($ordenes); ?> de <?php echo $total_servicios_count; ?> órdenes</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="ordenesServicioTable">
                <?php if (isset($_SESSION['errores'])) : ?>
                    <div class=" alert alert-danger">
                        <ul>
                            <?php foreach ($_SESSION['errores'] as $error) : ?>
                                <li><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errores']); ?>
                <?php endif; ?>


                <?php if (isset($_SESSION['exito'])) : ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['exito']; ?>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>
                <thead>
                    <tr>
                        <th>ID Orden Servicio</th>
                        <th>Orden de Trabajo</th>
                        <th>Servicio</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)) { ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No se encontraron órdenes de servicio registradas.</p>
                                    <a href="crear_orden_servicio.php" class="btn btn-primary btn-sm" style="padding: 8px 16px; font-size: 0.9rem;">
                                        <i class="fas fa-plus"></i> Crear Primera Orden de Servicio
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($ordenes as $item) { ?>
                            <tr>
                                <td>
                                    <strong>OS-<?php echo date('Y') . '-' . htmlspecialchars($item->id_orden_servicio); ?></strong>
                                    <br>
                                </td>
                                <td>
                                    <strong>ORD-<?php echo htmlspecialchars($item->id_orden); ?></strong>
                                    <br>
                                    <small>Cliente: <?php echo htmlspecialchars($item->nombre_cliente); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($item->nombre_servicio); ?></td>
                                <td><?php echo htmlspecialchars($item->cantidad); ?></td>
                                <td>$<?php echo number_format($item->precio_unitario, 2, '.', ','); ?></td>
                                <td>$<?php echo number_format($item->precio_unitario * $item->cantidad, 2, '.', ','); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="editar_orden_servicio.php?id_orden_servicio=<?php echo htmlspecialchars(base64_encode($item->id_orden_servicio)); ?>&id_orden_trabajo=<?php echo htmlspecialchars(base64_encode($item->id_orden)); ?>" type="button" class="btn-icon btn-edit" title="Editar orden de servicio">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="../controladores/eliminar_orden_servicio.php" method="POST" class="formEliminar">
                                            <input type="hidden" name="id_orden_servicio" value="<?php echo htmlspecialchars($item->id_orden_servicio); ?>">
                                            <input type="hidden" name="id_orden" value="<?php echo htmlspecialchars($item->id_orden); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Eliminar orden de servicio">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar esta orden de servicio?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // enviar el formulario si confirma
                }
            });
        });
    });
</script>
<?php include '../templates/footer.php'; ?>