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
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo");
$sql_count->execute();
$total_registros = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener las ordenes de trabajo de la base de datos con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_cliente as id_cliente,ordenes_trabajo.id_vehiculo as id_vehiculo, ordenes_trabajo.id_usuario_asignado as id_mecanico, vehiculos.marca as marca,vehiculos.placa as placa,ordenes_trabajo.id_orden as id_orden,ordenes_trabajo.fecha_creacion as fecha_creacion,ordenes_trabajo.fecha_entrega as fecha_entrega,ordenes_trabajo.estado as estado,ordenes_trabajo.observaciones as observaciones,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_mecanico,CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente, clientes.telefono as telefono FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente=clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo=vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado=usuarios.id_usuario ORDER BY ordenes_trabajo.fecha_creacion DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener estadísticas reales de toda la base de datos
$sql_stats = $conexion->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
    SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados,
    SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregados
FROM ordenes_trabajo");
$sql_stats->execute();
$stats = $sql_stats->fetch(PDO::FETCH_OBJ);

$total_ordenes_db = $stats->total;
$total_pendientes = $stats->pendientes;
$total_en_proceso = $stats->en_proceso;
$total_finalizados = $stats->finalizados;
$total_entregados = $stats->entregados;

// Obtener los usuarios por rol de mecanico
$sql = $conexion->prepare("SELECT id_usuario,CONCAT(nombre,' ',apellido) as nombre_mecanico FROM usuarios WHERE rol = 'mecanico'");
$sql->execute();
$mecanicos = $sql->fetchAll(PDO::FETCH_OBJ);

?>
<style>
    /* Contenido Principal */
    .content {
        padding: 20px;
        max-width: 1600px;
        margin: 0 auto;
        width: 100%;
    }

    /* Stats Cards */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .stat-icon.pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning);
    }

    .stat-icon.progress {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .stat-icon.completed {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .stat-icon.total {
        background-color: rgba(26, 58, 95, 0.1);
        color: var(--primary);
    }

    .stat-info h3 {
        font-size: 1.8rem;
        margin-bottom: 5px;
        color: var(--dark);
    }

    .stat-info p {
        color: var(--gray);
        font-size: 0.9rem;
        margin: 0;
    }

    /* Filtros */
    .filters-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 20px;
        margin-bottom: 25px;
    }

    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filters-title {
        font-weight: 600;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
    }

    .filter-select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: white;
        color: var(--dark);
        font-size: 0.95rem;
        transition: var(--transition);
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    /* Cards */
    .card {
        margin-bottom: 20px;
        border: none;
        transition: var(--transition);
    }

    .card-header {
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Tablas */
    .table-responsive {
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    .table {
        min-width: 1200px;
    }

    .table th {
        padding: 15px;
        border-bottom: 2px solid #e3e6f0;
        white-space: nowrap;
    }

    .table td {
        padding: 15px;
    }

    /* Estados */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
        min-width: 100px;
    }

    .status-pendiente {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-en-proceso {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .status-finalizado {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-entregado {
        background-color: #c7ead1;
        color: #155724;
        border: 1px solid #b2dba1;
    }

    .status-cancelada {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-rechazada {
        background-color: #ffd8d8;
        color: #9d1c24;
        border: 1px solid #f5c6cb;
    }

    .status-aprobada {
        background-color: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
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

    .btn-view {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .btn-view:hover {
        background-color: var(--success);
        color: white;
    }

    /* Botones */
    .btn {
        padding: 12px 24px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 58, 95, 0.2);
    }

    /* Búsqueda */
    .search-container {
        display: flex;
        gap: 15px;
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

    /* Modal */
    .modal {
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content {
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 1;
    }

    /* Formularios */
    .form-container {
        display: grid;
        gap: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .form-group {
        margin-bottom: 0;
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
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


    /* Responsive */
    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-container {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }

        .stats-cards {
            grid-template-columns: 1fr 1fr;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .modal-content {
            margin: 20px;
        }
    }

    @media (max-width: 576px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
            gap: 5px;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }

        .filters-header {
            flex-direction: column;
            align-items: stretch;
        }
    }

    /* Animaciones */

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
<!-- Stats Cards -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-icon total">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_ordenes_db); ?></h3>
            <p>Total Órdenes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_pendientes); ?></h3>
            <p>Pendientes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon progress">
            <i class="fas fa-tools"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_en_proceso); ?></h3>
            <p>En Proceso</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon completed">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo htmlspecialchars($total_finalizados); ?></h3>
            <p>Finalizados</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-container">
    <div class="filters-header">
        <div class="filters-title">Filtrar Órdenes</div>
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por ID, cliente, vehículo...">
            </div>
            <a href="../ReportesPDF/reporteOrdenesTrabajo.php" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="../ReportesExcel/reporteOrdenesTrabajo.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="crear_ordenes_trabajo.php" type="button" class="btn btn-primary" id="btnNuevaOrden">
                <i class="fas fa-plus"></i> Nueva Orden
            </a>
        </div>
    </div>
    <div class="filters-grid">
        <div class="filter-group">
            <label for="filterEstado">Estado</label>
            <select class="filter-select" id="filterEstado">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en-proceso">En Proceso</option>
                <option value="finalizado">Finalizado</option>
                <option value="entregado">Entregado</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterMecanico">Mecánico</label>
            <select class="filter-select" id="filterMecanico">
                <option value="">Todos los mecánicos</option>
                <?php foreach ($mecanicos as $item): ?>
                    <option value="<?php echo htmlspecialchars($item->id_usuario); ?>"><?php echo htmlspecialchars($item->nombre_mecanico); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterFechaInicio">Desde</label>
            <input type="date" class="filter-select" id="filterFechaInicio">
        </div>
        <div class="filter-group">
            <label for="filterFechaFin">Hasta</label>
            <input type="date" class="filter-select" id="filterFechaFin">
        </div>
    </div>
</div>

<!-- Tabla de órdenes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lista de Órdenes de Trabajo</h2>
        <div class="card-actions">
            <span id="totalOrdenes">Mostrando <?php echo count($ordenes); ?> de <?php echo $total_registros; ?> órdenes</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="ordenesTable">
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
                        <th>ID Orden</th>
                        <th>Vehículo</th>
                        <th>Cliente</th>
                        <th>Mecánico Asignado</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Entrega</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)) { ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>No se encontraron órdenes de trabajo registradas.</p>
                                    <a href="crear_ordenes_trabajo.php" class="btn btn-primary btn-sm" style="padding: 8px 16px; font-size: 0.9rem;">
                                        <i class="fas fa-plus"></i> Crear Primera Orden
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($ordenes as $item) { ?>
                            <tr>
                                <td>
                                    <strong>OT-<?php echo date('Y'); ?>-<?php echo htmlspecialchars($item->id_orden); ?></strong>
                                    <br>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->marca); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($item->placa); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->nombre_cliente); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($item->telefono); ?></small>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 30px; height: 30px; background-color: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo htmlspecialchars(substr($item->nombre_mecanico, 0, 2)); ?></div>
                                        <span><?php echo htmlspecialchars($item->nombre_mecanico); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($item->fecha_creacion)); ?>
                                    <br>
                                    <small><?php echo date('h:i A', strtotime($item->fecha_creacion)); ?></small>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($item->fecha_entrega)); ?>
                                    <br>
                                    <small><?php echo date('h:i A', strtotime($item->fecha_entrega)); ?></small>
                                </td>
                                <td>
                                    <?php if ($item->estado == "pendiente") { ?>
                                        <span class="status-badge status-pendiente">Pendiente</span>
                                    <?php } else if ($item->estado == "en_proceso") { ?>
                                        <span class="status-badge status-en-proceso">En Proceso</span>
                                    <?php } else if ($item->estado == "finalizado") { ?>
                                        <span class="status-badge status-finalizado">Finalizado</span>
                                    <?php } else if ($item->estado == "entregado") { ?>
                                        <span class="status-badge status-entregado">Entregado</span>
                                    <?php } else if ($item->estado == "rechazada") { ?>
                                        <span class="status-badge status-rechazada">Rechazada</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($item->observaciones); ?>">
                                        <?php echo htmlspecialchars($item->observaciones); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($item->estado == "pendiente") { ?>
                                            <a href="editar_orden_trabajo.php?id_orden=<?php echo htmlspecialchars(base64_encode($item->id_orden)); ?> &id_cliente=<?php echo htmlspecialchars(base64_encode($item->id_cliente)); ?> &id_vehiculo=<?php echo htmlspecialchars(base64_encode($item->id_vehiculo)); ?> &id_mecanico=<?php echo htmlspecialchars(base64_encode($item->id_mecanico)); ?>" type="button" class="btn-icon btn-edit" title="Editar orden">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php } ?>
                                        <?php if ($item->estado == "finalizado") { ?>
                                            <a href="cambiar_estado_orden.php?id_orden=<?php echo htmlspecialchars(base64_encode($item->id_orden)); ?>" class="btn-icon btn-view" title="Cambiar Estado">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        <?php } ?>
                                        <?php if ($item->estado !='rechazada') { ?>
                                        <form action="../controladores/eliminar_orden_trabajo.php" method="POST" class="formEliminar">
                                            <input type="hidden" name="id_orden" value="<?php echo htmlspecialchars($item->id_orden); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Eliminar orden">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php } ?>
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
                title: '¿Está seguro que desea eliminar esta orden de trabajo?',
                text: 'Esta acción no se puede deshacer',
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos
        const searchInput = document.getElementById('searchInput');
        const totalOrdenes = document.getElementById('totalOrdenes');
        const filterEstado = document.getElementById('filterEstado');
        const filterMecanico = document.getElementById('filterMecanico');
        const filterFechaInicio = document.getElementById('filterFechaInicio');
        const filterFechaFin = document.getElementById('filterFechaFin');
        const tableRows = document.querySelectorAll('#ordenesTable tbody tr');

        // Aplicar filtros unificados
        function aplicarFiltros() {
            const searchTerm = searchInput.value.toLowerCase();
            const estado = filterEstado.value.toLowerCase();
            const mecanico = filterMecanico.value.toLowerCase();
            const fechaInicio = filterFechaInicio.value ? new Date(filterFechaInicio.value) : null;
            const fechaFin = filterFechaFin.value ? new Date(filterFechaFin.value) : null;

            let visibleCount = 0;

            tableRows.forEach(row => {
                let mostrar = true;
                const text = row.textContent.toLowerCase();

                // 1. Búsqueda por texto (ID, Cliente, Vehículo, Observaciones, etc.)
                if (searchTerm && !text.includes(searchTerm)) {
                    mostrar = false;
                }

                // 2. Filtro por Estado
                if (mostrar && estado) {
                    const badge = row.querySelector('.status-badge');
                    const estadoTexto = badge ? badge.textContent.toLowerCase().trim().replace(' ', '-') : '';

                    // El select tiene valores como "en-proceso" pero el texto puede ser "En Proceso"
                    if (estadoTexto !== estado) {
                        mostrar = false;
                    }
                }

                // 3. Filtro por Mecánico
                if (mostrar && mecanico) {
                    const mecanicoTextoCell = row.querySelector('td:nth-child(4) span');
                    const mecanicoTexto = mecanicoTextoCell ? mecanicoTextoCell.textContent.toLowerCase().trim() : '';

                    // Mapeo simple de nombres a valores de select (opcional si los valores del select son nombres parciales)
                    if (!mecanicoTexto.includes(mecanico)) {
                        mostrar = false;
                    }
                }

                // 4. Filtro por Rango de Fechas (basado en Fecha de Creación)
                if (mostrar && (fechaInicio || fechaFin)) {
                    const fechaCellText = row.querySelector('td:nth-child(5)').textContent.trim();
                    const match = fechaCellText.match(/(\d{2})\/(\d{2})\/(\d{4})/);

                    if (match) {
                        const fechaOrden = new Date(match[3], match[2] - 1, match[1]);

                        if (fechaInicio) {
                            const fInicio = new Date(fechaInicio);
                            fInicio.setHours(0, 0, 0, 0);
                            if (fechaOrden < fInicio) mostrar = false;
                        }

                        if (mostrar && fechaFin) {
                            const fFin = new Date(fechaFin);
                            fFin.setHours(23, 59, 59, 999);
                            if (fechaOrden > fFin) mostrar = false;
                        }
                    }
                }

                row.style.display = mostrar ? '' : 'none';
                if (mostrar) visibleCount++;
            });

            totalOrdenes.textContent = `Mostrando ${visibleCount} de ${tableRows.length} órdenes`;
        }

        // Agregar eventos
        searchInput.addEventListener('input', aplicarFiltros);
        filterEstado.addEventListener('change', aplicarFiltros);
        filterMecanico.addEventListener('change', aplicarFiltros);
        filterFechaInicio.addEventListener('change', aplicarFiltros);
        filterFechaFin.addEventListener('change', aplicarFiltros);

        // Llamada inicial
        aplicarFiltros();
    });
</script>
<?php include '../templates/footer.php';
