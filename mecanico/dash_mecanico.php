<?php
require_once '../autorizacion/auth.php';

// Verificar que tenga rol de mecanico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'mecanico') {
    header("Location: ../acceso_denegado.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

$id_mecanico = $_SESSION['id_usuario']; // id del usaurio mecancio logueado

// Obtener total de de ordens de trabajo pendientes
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado='pendiente' AND id_usuario_asignado=$id_mecanico");
$sql->execute();
$total_ordenes = $sql->fetch(PDO::FETCH_OBJ);

// Obtener total de de ordens de trabajo en proceso
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado='en_proceso' AND id_usuario_asignado=$id_mecanico");
$sql->execute();
$total_ordenes_en_proceso = $sql->fetch(PDO::FETCH_OBJ);

// Obtener total de de ordens de trabajo finalizadas
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado='finalizado' AND id_usuario_asignado=$id_mecanico");
$sql->execute();
$total_ordenes_finalizadas = $sql->fetch(PDO::FETCH_OBJ);

// Obtener las ordenes de trabajo asignadas al mecanico de la base de datos
$sql = $conexion->prepare("SELECT  vehiculos.marca as marca,vehiculos.placa as placa,ordenes_trabajo.id_orden as id_orden,ordenes_trabajo.fecha_creacion as fecha_creacion,ordenes_trabajo.fecha_entrega as fecha_entrega,ordenes_trabajo.observaciones as observaciones,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_mecanico,CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente=clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo=vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado=usuarios.id_usuario WHERE ordenes_trabajo.id_usuario_asignado = :id_mecanico AND ordenes_trabajo.estado='pendiente' ORDER BY ordenes_trabajo.fecha_creacion DESC LIMIT 5");
$sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    :root {
        --primary: #1a3a5f;
        --secondary: #f8b400;
        --success: #28a745;
        --info: #17a2b8;
        --warning: #ffc107;
        --danger: #e63946;
        --light: #f8f9fa;
        --dark: #343a40;
        --gray: #6c757d;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 70px;
        --header-height: 60px;
        --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #f8f9fc;
        color: var(--dark);
        overflow-x: hidden;
    }

    /* Layout Principal */
    .wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--primary) 0%, #0f2a46 100%);
        color: white;
        transition: var(--transition);
        position: fixed;
        height: 100vh;
        z-index: 1000;
        box-shadow: var(--shadow);
    }

    .sidebar-header {
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        height: var(--header-height);
    }

    .sidebar-header .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .sidebar-header .logo i {
        color: var(--secondary);
        font-size: 1.8rem;
    }

    .sidebar-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .sidebar-toggle:hover {
        color: var(--secondary);
    }

    .sidebar-menu {
        padding: 20px 0;
        overflow-y: auto;
        height: calc(100vh - var(--header-height));
    }

    .sidebar-menu ul {
        list-style: none;
    }

    .sidebar-menu li {
        position: relative;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: var(--transition);
        border-left: 3px solid transparent;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        border-left-color: var(--secondary);
    }

    .sidebar-menu a i {
        width: 25px;
        font-size: 1.2rem;
        margin-right: 10px;
        text-align: center;
    }

    .sidebar-menu .menu-text {
        transition: var(--transition);
    }

    .sidebar-menu .badge {
        margin-left: auto;
        background-color: var(--secondary);
        color: var(--dark);
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Main Content */
    .main {
        flex: 1;
        margin-left: var(--sidebar-width);
        transition: var(--transition);
    }

    .main.expanded {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Header */
    .header {
        background-color: white;
        height: var(--header-height);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        position: sticky;
        top: 0;
        z-index: 999;
    }

    .header-left h1 {
        font-size: 1.5rem;
        color: var(--primary);
        font-weight: 600;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* Contenido Principal */
    .content {
        padding: 20px;
    }

    /* Info Cards */
    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-card {
        padding: 25px;
        border-radius: 10px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: var(--transition);
        cursor: pointer;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .info-card i {
        font-size: 3rem;
        opacity: 0.8;
    }

    .info-card-content h3 {
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .info-card-content p {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 0;
    }

    .info-card.primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
    }

    .info-card.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
    }

    .info-card.success {
        background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
    }

    .info-card.danger {
        background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
    }

    /* Cards */
    .card {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        margin-bottom: 25px;
        border: none;
        transition: var(--transition);
    }

    .card:hover {
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
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

    .card-title {
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        font-size: 1.2rem;
    }

    .card-body {
        padding: 20px;
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
        min-width: 800px;
    }

    .table th {
        background-color: #f8f9fc;
        color: var(--primary);
        font-weight: 700;
        padding: 15px;
        border-bottom: 2px solid #e3e6f0;
        text-align: left;
        white-space: nowrap;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fc;
    }

    /* Status */
    .status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        min-width: 100px;
        text-align: center;
    }

    .status.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status.in-progress {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .status.completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status.urgent {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Priority */
    .priority {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .priority.low {
        background-color: #e7f5ff;
        color: #0066cc;
        border: 1px solid #b3d9ff;
    }

    .priority.medium {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .priority.high {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .priority.urgent {
        background-color: #dc3545;
        color: white;
        border: 1px solid #c82333;
    }

    /* Botones de acción */
    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }

    .btn-action-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-action-primary:hover {
        background-color: #0f2a46;
    }

    .btn-action-success {
        background-color: var(--success);
        color: white;
    }

    .btn-action-success:hover {
        background-color: #218838;
    }

    .btn-action-warning {
        background-color: var(--warning);
        color: var(--dark);
    }

    .btn-action-warning:hover {
        background-color: #e0a800;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px 15px;
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        text-decoration: none;
        color: var(--dark);
        transition: var(--transition);
        border: 2px solid transparent;
    }

    .action-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(58, 59, 69, 0.2);
        border-color: var(--primary);
        color: var(--primary);
    }

    .action-btn i {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: var(--primary);
    }

    .action-btn span {
        font-weight: 600;
        text-align: center;
        font-size: 0.95rem;
    }

    /* Calendar Widget */
    .calendar-widget {
        padding: 15px;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        text-align: center;
    }

    .day-header {
        font-weight: 600;
        color: var(--primary);
        padding: 10px 0;
        font-size: 0.9rem;
    }

    .day {
        padding: 12px 0;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
    }

    .day:hover {
        background-color: #f0f0f0;
    }

    .day.today {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
    }

    .day.event {
        background-color: rgba(248, 180, 0, 0.1);
    }

    .day.event::after {
        content: '';
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: var(--secondary);
    }

    /* Progress Bars */
    .progress-section {
        margin-bottom: 25px;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .progress-label {
        font-weight: 600;
        color: var(--dark);
    }

    .progress-value {
        font-weight: 600;
        color: var(--primary);
    }

    .progress {
        height: 12px;
        background-color: #e9ecef;
        border-radius: 6px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 6px;
        background-color: var(--primary);
        transition: width 0.6s ease;
    }

    /* Charts Container */
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        height: 300px;
        position: relative;
    }

    /* User menu */
    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: var(--dark);
    }

    .user-role {
        font-size: 0.85rem;
        color: var(--gray);
    }

    /* Notification */
    .notification-btn {
        background: none;
        border: none;
        font-size: 1.3rem;
        color: var(--gray);
        cursor: pointer;
        position: relative;
        transition: var(--transition);
    }

    .notification-btn:hover {
        color: var(--primary);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: var(--danger);
        color: white;
        font-size: 0.7rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin-bottom: 10px;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .menu-text,
        .sidebar .badge {
            display: none;
        }

        .sidebar .sidebar-header {
            justify-content: center;
        }

        .sidebar .sidebar-header .logo-text {
            display: none;
        }

        .main {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main.expanded {
            margin-left: 0;
        }

        .charts-container {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .info-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        .header-right {
            gap: 10px;
        }

        .user-info {
            display: none;
        }

        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }

        .table-responsive {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .info-cards {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }

        .content {
            padding: 15px;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .calendar-days {
            gap: 4px;
        }

        .day {
            padding: 8px 0;
            font-size: 0.9rem;
        }
    }

    /* Animaciones */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<!-- Info Cards -->
<div class="info-cards">
    <div class="info-card primary">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($total_ordenes->total); ?></h3>
            <p>Órdenes Pendientes</p>
        </div>
        <i class="fas fa-clipboard-list"></i>
    </div>
    <div class="info-card warning">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($total_ordenes_en_proceso->total); ?></h3>
            <p>En Progreso</p>
        </div>
        <i class="fas fa-tools"></i>
    </div>
    <div class="info-card success">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($total_ordenes_finalizadas->total); ?></h3>
            <p>Completadas</p>
        </div>
        <i class="fas fa-check-circle"></i>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="#" class="action-btn">
        <i class="fas fa-plus-circle"></i>
        <span>Nuevo Diagnóstico</span>
    </a>
    <a href="#" class="action-btn">
        <i class="fas fa-clipboard-check"></i>
        <span>Actualizar Estado</span>
    </a>
    <a href="#" class="action-btn">
        <i class="fas fa-box"></i>
        <span>Solicitar Repuestos</span>
    </a>
    <a href="#" class="action-btn">
        <i class="fas fa-file-invoice"></i>
        <span>Generar Reporte</span>
    </a>
    <a href="#" class="action-btn">
        <i class="fas fa-calendar-alt"></i>
        <span>Ver Calendario</span>
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 30px;">
    <!-- Tabla de órdenes asignadas -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Órdenes Asignadas</h2>
            <button class="btn-action btn-action-primary">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Entrega</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordenes)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list"></i>
                                        <p>No tienes órdenes de trabajo pendientes en este momento.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ordenes as $item): ?>
                                <tr>
                                    <td><strong>ORD-<?php echo date('Y'); ?>-<?php echo $item->id_orden; ?></strong></td>
                                    <td><?php echo htmlspecialchars($item->nombre_cliente); ?></td>
                                    <td><?php echo htmlspecialchars($item->marca); ?> <?php echo htmlspecialchars($item->placa); ?></td>
                                    <td><?php echo htmlspecialchars($item->fecha_creacion); ?></td>
                                    <td><?php echo htmlspecialchars($item->fecha_entrega); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Calendario y estadísticas -->
    <div>
        <!-- Calendario -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="card-header">
                <h2 class="card-title">Calendario</h2>
            </div>
            <div class="card-body">
                <div class="calendar-widget">
                    <div class="calendar-header">
                        <span style="font-weight: 600; color: var(--primary);">Marzo 2024</span>
                        <div>
                            <button class="btn-action" style="padding: 5px 10px; margin-right: 5px;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn-action" style="padding: 5px 10px;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="calendar-days">
                        <div class="day-header">Lun</div>
                        <div class="day-header">Mar</div>
                        <div class="day-header">Mié</div>
                        <div class="day-header">Jue</div>
                        <div class="day-header">Vie</div>
                        <div class="day-header">Sáb</div>
                        <div class="day-header">Dom</div>

                        <!-- Días del mes -->
                        <div class="day">26</div>
                        <div class="day">27</div>
                        <div class="day">28</div>
                        <div class="day">29</div>
                        <div class="day">1</div>
                        <div class="day">2</div>
                        <div class="day">3</div>
                        <div class="day">4</div>
                        <div class="day">5</div>
                        <div class="day">6</div>
                        <div class="day">7</div>
                        <div class="day">8</div>
                        <div class="day">9</div>
                        <div class="day">10</div>
                        <div class="day">11</div>
                        <div class="day">12</div>
                        <div class="day">13</div>
                        <div class="day">14</div>
                        <div class="day today">15</div>
                        <div class="day event">16</div>
                        <div class="day">17</div>
                        <div class="day">18</div>
                        <div class="day event">19</div>
                        <div class="day">20</div>
                        <div class="day">21</div>
                        <div class="day">22</div>
                        <div class="day">23</div>
                        <div class="day">24</div>
                        <div class="day">25</div>
                        <div class="day">26</div>
                        <div class="day">27</div>
                        <div class="day">28</div>
                        <div class="day">29</div>
                        <div class="day">30</div>
                        <div class="day">31</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Estado de Órdenes -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Estado de Órdenes</h2>
            </div>
            <div class="card-body" style="height: 300px; position: relative;">
                <canvas id="ordenesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Repuestos necesarios y notificaciones -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Repuestos necesarios -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Repuestos Necesarios</h2>
            <button class="btn-action btn-action-primary">
                <i class="fas fa-shopping-cart"></i> Solicitar
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Repuesto</th>
                            <th>Cantidad</th>
                            <th>Orden</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pastillas de freno</td>
                            <td>2 pares</td>
                            <td>ORD-0123</td>
                            <td><span class="status pending">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td>Aceite sintético 5W-30</td>
                            <td>4 litros</td>
                            <td>ORD-0125</td>
                            <td><span class="status in-progress">En camino</span></td>
                        </tr>
                        <tr>
                            <td>Filtro de aire</td>
                            <td>1 unidad</td>
                            <td>ORD-0125</td>
                            <td><span class="status in-progress">En camino</span></td>
                        </tr>
                        <tr>
                            <td>Batería 12V</td>
                            <td>1 unidad</td>
                            <td>ORD-0126</td>
                            <td><span class="status pending">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td>Amortiguadores</td>
                            <td>2 unidades</td>
                            <td>ORD-0128</td>
                            <td><span class="status completed">Disponible</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notificaciones recientes -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Notificaciones</h2>
            <button class="btn-action">
                <i class="fas fa-check-double"></i> Marcar todo
            </button>
        </div>
        <div class="card-body">
            <div style="max-height: 300px; overflow-y: auto;">
                <div style="padding: 12px; border-bottom: 1px solid #e3e6f0; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fas fa-exclamation-circle" style="color: var(--danger); margin-top: 3px;"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">Nueva orden urgente asignada</div>
                        <div style="font-size: 0.9rem; color: var(--gray);">ORD-0126 - Diagnóstico eléctrico</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Hace 15 minutos</div>
                    </div>
                </div>

                <div style="padding: 12px; border-bottom: 1px solid #e3e6f0; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success); margin-top: 3px;"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">Repuestos recibidos</div>
                        <div style="font-size: 0.9rem; color: var(--gray);">Pastillas de freno y aceite</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Hace 2 horas</div>
                    </div>
                </div>

                <div style="padding: 12px; border-bottom: 1px solid #e3e6f0; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fas fa-calendar-alt" style="color: var(--primary); margin-top: 3px;"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">Reunión de equipo</div>
                        <div style="font-size: 0.9rem; color: var(--gray);">Hoy a las 4:00 PM en taller principal</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Hace 3 horas</div>
                    </div>
                </div>

                <div style="padding: 12px; border-bottom: 1px solid #e3e6f0; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fas fa-tools" style="color: var(--warning); margin-top: 3px;"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">Mantenimiento de herramienta</div>
                        <div style="font-size: 0.9rem; color: var(--gray);">Gato hidráulico necesita revisión</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Hace 5 horas</div>
                    </div>
                </div>

                <div style="padding: 12px; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="fas fa-chart-line" style="color: var(--info); margin-top: 3px;"></i>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 3px;">Reporte mensual disponible</div>
                        <div style="font-size: 0.9rem; color: var(--gray);">Ver estadísticas de marzo</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">Ayer a las 6:00 PM</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        // Notificaciones
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', function() {
                // La lógica de mostrar/ocultar ya está en el sidebar.php
            });
        }

        // Acciones de botones en la tabla
        const startButtons = document.querySelectorAll('.btn-action-success');
        const pauseButtons = document.querySelectorAll('.btn-action-warning');
        const viewButtons = document.querySelectorAll('.btn-action-primary');

        startButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orderNumber = row.querySelector('td:first-child strong').textContent;
                alert(`Iniciando trabajo en ${orderNumber}`);
            });
        });

        pauseButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orderNumber = row.querySelector('td:first-child strong').textContent;
                alert(`Pausando trabajo en ${orderNumber}`);
            });
        });

        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orderNumber = row.querySelector('td:first-child strong').textContent;
                alert(`Ver detalles de ${orderNumber}`);
            });
        });

        // Botones de acción rápida
        const quickActions = document.querySelectorAll('.action-btn');
        quickActions.forEach(action => {
            action.addEventListener('click', function(e) {
                const actionText = this.querySelector('span').textContent;
                // alert(`Acción: ${actionText}`);
            });
        });

        // Interacción con días del calendario
        const calendarDays = document.querySelectorAll('.calendar-days .day');
        calendarDays.forEach(day => {
            day.addEventListener('click', function() {
                const dayNumber = this.textContent;
                alert(`Ver eventos del día ${dayNumber} de Marzo`);
            });
        });

        // Botón solicitar repuestos
        const requestPartsBtn = document.querySelector('.btn-action[class*="shopping-cart"]');
        if (requestPartsBtn) {
            requestPartsBtn.addEventListener('click', function() {
                alert('Solicitar repuestos del inventario');
            });
        }

        // Gráfico de Estado de Órdenes
        const ctx = document.getElementById('ordenesChart').getContext('2d');
        const ordenesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'En Proceso', 'Finalizadas'],
                datasets: [{
                    data: [
                        <?php echo $total_ordenes->total; ?>,
                        <?php echo $total_ordenes_en_proceso->total; ?>,
                        <?php echo $total_ordenes_finalizadas->total; ?>
                    ],
                    backgroundColor: [
                        '#e63946', // Pendientes (Danger)
                        '#ffc107', // En Proceso (Warning)
                        '#28a745' // Finalizadas (Success)
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Simular actualización de datos cada 30 segundos
        setInterval(() => {
            console.log('Actualizando datos del dashboard...');
        }, 30000);
    });
</script>
<?php include '../templates/footer.php'; ?>