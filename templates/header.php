<?php
// Comprobar el estado actual de la sesión
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../conexion/session.php';
}

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['rol'])) {
    header("Location: ../index.php");
    exit;
}

// Calcular la ruta base relativa hacia la raíz del proyecto
// Obtenemos el archivo que incluye este header
$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
$including_file = isset($backtrace[0]['file']) ? $backtrace[0]['file'] : __FILE__;
$including_dir = dirname($including_file);
$root_dir = dirname(__DIR__); // Directorio raíz del proyecto

// Normalizar las rutas para que funcionen en Windows y Linux
$including_dir = str_replace('\\', '/', $including_dir);
$root_dir = str_replace('\\', '/', $root_dir);

// Calcular la ruta relativa desde el directorio del archivo que incluye el header hacia la raíz
$relative_path = str_replace($root_dir, '', $including_dir);
$relative_path = trim($relative_path, '/');
$depth = !empty($relative_path) ? substr_count($relative_path, '/') + 1 : 0;

// Construir la ruta base: si está en mecanico/ o recepcionista/ o admin/, necesitamos "../", si está en la raíz, ""
$base_url = $depth > 0 ? str_repeat('../', $depth) : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoTech - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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

        .sidebar-submenu {
            display: none;
            background-color: rgba(0, 0, 0, 0.2);
            padding-left: 20px;
        }

        .sidebar-submenu.show {
            display: block;
        }

        .sidebar-submenu a {
            padding: 10px 20px 10px 45px;
            font-size: 0.95rem;
        }

        .sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-collapsed .menu-text,
        .sidebar-collapsed .badge {
            display: none;
        }

        .sidebar-collapsed .sidebar-header {
            justify-content: center;
        }

        .sidebar-collapsed .sidebar-header .logo-text {
            display: none;
        }

        .sidebar-collapsed .sidebar-toggle {
            display: none;
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

        .header-search {
            position: relative;
        }

        .header-search input {
            padding: 8px 15px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 20px;
            width: 250px;
            transition: var(--transition);
        }

        .header-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
        }

        .header-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .header-notifications {
            position: relative;
        }

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

        /* Contenido Principal */
        .content {
            padding: 20px;
        }

        /* Cards */
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            border: none;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
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

        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-card i {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .info-card-content h3 {
            font-size: 1.8rem;
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

        .info-card.success {
            background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
        }

        .info-card.warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
        }

        .info-card.danger {
            background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
        }

        .info-card.info {
            background: linear-gradient(135deg, var(--info) 0%, #3abaf4 100%);
        }

        /* Paginación Global Estandarizada */
        .pagination-container {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 5px;
            align-items: center;
        }

        .pagination .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 5px;
            background-color: white;
            border: 1px solid #ddd;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #f8f9fc;
            border-color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
            background-color: #f8f9fc;
        }

        /* Tablas */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background-color: #f8f9fc;
            color: var(--primary);
            font-weight: 700;
            padding: 12px 15px;
            border-bottom: 1px solid #e3e6f0;
            text-align: left;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e3e6f0;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
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

        .status.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Progress Bars */
        .progress {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 5px;
            background-color: var(--primary);
        }

        /* Charts */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Calendar */
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
            gap: 5px;
            text-align: center;
        }

        .day-header {
            font-weight: 600;
            color: var(--primary);
            padding: 10px 0;
        }

        .day {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .day:hover {
            background-color: #f0f0f0;
        }

        .day.today {
            background-color: var(--primary);
            color: white;
        }

        .day.event {
            background-color: rgba(248, 180, 0, 0.1);
            position: relative;
        }

        .day.event::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: var(--secondary);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
            color: var(--primary);
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .action-btn span {
            font-weight: 600;
            text-align: center;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
            animation: modalFadeIn 0.3s;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e3e6f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Botones */
        .btn {
            padding: 8px 20px;
            border-radius: 5px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #0f2a46;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        /* Footer */
        .footer {
            background-color: white;
            padding: 20px;
            border-top: 1px solid #e3e6f0;
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Submenu Arrow */
        .submenu-arrow {
            margin-left: auto;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .active .submenu-arrow {
            transform: rotate(180deg);
        }

        /* Animaciones */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

            .sidebar .sidebar-toggle {
                display: none;
            }

            .main {
                margin-left: var(--sidebar-collapsed-width);
            }

            .main.expanded {
                margin-left: 0;
            }

            .charts-row {
                grid-template-columns: 1fr;
            }

            .header-search input {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .info-cards {
                grid-template-columns: 1fr 1fr;
            }

            .header-search {
                display: none;
            }

            .user-info {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .info-cards {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }

            .content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-car"></i>
                    <span class="logo-text">AutoTech</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <?php if ($_SESSION['rol'] == 'admin') { ?>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/dash_admin.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/gestion_usuarios.php">
                                <i class="fas fa-users"></i>
                                <span class="menu-text">Gestion Usuarios</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/gestion_servicios.php">
                                <i class="fas fa-tools"></i>
                                <span class="menu-text">Gestion Servicios</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/gestion_repuestos.php">
                                <i class="fas fa-boxes"></i>
                                <span class="menu-text">Gestion Repuestos</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/listado_ordenes_trabajo_rechazadas.php">
                                <i class="fas fa-tools"></i>
                                <span class="menu-text">Listado Ordenes Trabajo Rechazadas</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/bitacora.php">
                                <i class="fas fa-book"></i>
                                <span class="menu-text">Bitacora</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>admin/reportes.php">
                                <i class="fas fa-chart-bar"></i>
                                <span class="menu-text">Reportes</span>
                            </a>
                        </li>
                    <?php } else if ($_SESSION['rol'] == 'recepcionista') { ?>
                        <li>
                            <a href="<?php echo $base_url; ?>recepcionista/dash_recepcionista.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>recepcionista/gestion_clientes.php">
                                <i class="fas fa-users"></i>
                                <span class="menu-text">Clientes</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>recepcionista/gestion_ordenes_trabajo.php">
                                <i class="fas fa-tools"></i>
                                <span class="menu-text">Ordenes de Trabajo</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>recepcionista/gestion_ordenes_servicios.php">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="menu-text">Ordenes de Servicios</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $base_url; ?>recepcionista/gestion_ordenes_repuestos.php">
                                <i class="fas fa-boxes"></i>
                                <span class="menu-text">Ordenes de Repuestos</span>
                            </a>
                        </li>
                    <?php } else if ($_SESSION['rol'] == 'mecanico') { ?>
                        <li>
                            <a href="<?php echo $base_url; ?>mecanico/dash_mecanico.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="servicesMenu">
                                <i class="fas fa-tools"></i>
                                <span class="menu-text">Ordenes de Trabajo</span>
                                <i class="fas fa-chevron-down submenu-arrow"></i>
                            </a>
                            <ul class="sidebar-submenu" id="servicesSubmenu">
                                <li><a href="<?php echo $base_url; ?>mecanico/gestion_ordenes_trabajo_asignados.php"><i class="fas fa-clipboard-list"></i> Asignados</a></li>
                                <li><a href="<?php echo $base_url; ?>mecanico/gestion_ordenes_trabajo_en_proceso.php"><i class="fas fa-spinner"></i> En Proceso</a></li>
                                <li><a href="<?php echo $base_url; ?>mecanico/gestion_ordenes_trabajo_finalizadas.php"><i class="fas fa-check-circle"></i> Finalizados</a></li>
                            </ul>
                        </li>
                    <?php } ?>
                    <li>
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            <span class="menu-text">Configuración</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>controladores/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="menu-text">Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main" id="main">
            <!-- Header -->
            <?php include 'sidebar.php'; ?>

            <!-- Contenido Principal -->
            <div class="content">