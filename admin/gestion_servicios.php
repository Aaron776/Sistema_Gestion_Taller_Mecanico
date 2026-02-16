<?php
require_once "../autorizacion/auth.php";

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../acceso_denegado.php");
    exit();
}
include_once "../templates/header.php";
include_once "../conexion/bd.php";

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener todos los servicios para cálculos de estadísticas (dataset completo)
$sql_all = $conexion->prepare("SELECT nombre_servicio as nombre, precio_base as precio FROM servicios");
$sql_all->execute();
$todos_los_servicios = $sql_all->fetchAll(PDO::FETCH_OBJ);
$total_servicios_count = count($todos_los_servicios);
$total_paginas = ceil($total_servicios_count / $registros_por_pagina);

// Obtener servicios con LIMIT y OFFSET para la tabla
$sql = $conexion->prepare("SELECT id_servicio, descripcion, nombre_servicio as nombre, precio_base as precio FROM servicios ORDER BY id_servicio DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$servicios = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener promedio de precios (del dataset completo)
$sql = $conexion->prepare("SELECT AVG(precio_base) as promedio_precio FROM servicios");
$sql->execute();
$avgPrice = $sql->fetch(PDO::FETCH_OBJ);

// Calcular estadísticas adicionales en PHP para carga inicial
// ------------------------------------------------------------------------------------------------
// Explicación del cálculo de estadísticas:
// 1. Verificamos si existen servicios en la base de datos (count > 0).
// 2. Extraemos todos los precios en un array simple usando array_column($servicios, 'precio').
// 3. Utilizamos las funciones nativas de PHP max() y min() para encontrar el precio más alto y más bajo.
// 4. Se recorre el array original de servicios para encontrar qué servicio corresponde a esos precios
//    y así poder mostrar su nombre (e.g., "$maxPriceService").
// ------------------------------------------------------------------------------------------------
if ($total_servicios_count > 0) {
    $precios = array_column($todos_los_servicios, 'precio');
    $maxPrice = max($precios);
    $minPrice = min($precios);

    // Encontrar nombres de servicios correspondientes
    foreach ($todos_los_servicios as $item) {
        if ($item->precio == $maxPrice) {
            $maxPriceService = $item->nombre;
        }
        if ($item->precio == $minPrice) {
            $minPriceService = $item->nombre;
        }
    }
}
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

    .sidebar-collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar-collapsed .menu-text {
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

    /* Contenido Principal */
    .content {
        padding: 20px;
    }

    /* Card */
    .card {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        border: none;
        transition: var(--transition);
        animation: fadeIn 0.5s ease-out;
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
        font-size: 0.95rem;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #0f2a46;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-success {
        background-color: var(--success);
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-warning {
        background-color: var(--warning);
        color: var(--dark);
    }

    .btn-warning:hover {
        background-color: #e0a800;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-danger {
        background-color: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #545b62;
        transform: translateY(-2px);
    }

    .btn-link {
        background: none;
        border: none;
        color: var(--primary);
        padding: 5px 10px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-link:hover {
        color: var(--secondary);
        text-decoration: underline;
    }

    .btn-link.text-danger {
        color: var(--danger);
    }

    .btn-link.text-danger:hover {
        color: #c82333;
    }

    .btn-link.text-warning {
        color: var(--warning);
    }

    .btn-link.text-warning:hover {
        color: #e0a800;
    }

    /* Tabla */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .table thead {
        background-color: #f8f9fc;
    }

    .table th {
        color: var(--primary);
        font-weight: 700;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #e3e6f0;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: top;
    }

    .table tbody tr:hover {
        background-color: #f8f9fc;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Estilos específicos para columnas */
    .service-name {
        font-weight: 600;
        color: var(--primary);
    }

    .service-description {
        color: var(--gray);
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 400px;
    }

    .service-price {
        font-weight: 700;
        color: var(--success);
        font-size: 1.1rem;
    }

    .service-price::before {
        content: "$ ";
    }

    .actions {
        display: flex;
        gap: 15px;
    }

    /* Badges para categorías */
    .category-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 5px;
    }

    .category-mantenimiento {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .category-reparacion {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }

    .category-diagnostico {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .category-electrica {
        background-color: rgba(255, 193, 7, 0.1);
        color: #b38f00;
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
        padding: 20px;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 10px;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        animation: modalFadeIn 0.3s;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 1;
        border-radius: 10px 10px 0 0;
    }

    .modal-title {
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        font-size: 1.3rem;
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
        position: sticky;
        bottom: 0;
        background-color: white;
        border-radius: 0 0 10px 10px;
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

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: var(--transition);
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-helper {
        display: block;
        margin-top: 8px;
        font-size: 0.875rem;
        color: var(--gray);
    }

    /* Alertas */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    /* Estadísticas */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
    }

    .stat-info h3 {
        font-size: 1.8rem;
        color: var(--primary);
        margin-bottom: 5px;
    }

    .stat-info p {
        color: var(--gray);
        font-size: 0.9rem;
        margin: 0;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
    }

    .stat-icon.success {
        background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
    }

    .stat-icon.info {
        background: linear-gradient(135deg, var(--info) 0%, #5bc0de 100%);
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
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive */
    @media (max-width: 992px) {
        .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .menu-text {
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

        .header-search input {
            width: 200px;
        }

        .stats-cards {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .header-search {
            display: none;
        }

        .user-info {
            display: none;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .stats-cards {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .content {
            padding: 15px;
        }

        .header {
            padding: 0 15px;
        }

        .header-left h1 {
            font-size: 1.3rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
            gap: 10px;
        }

        .actions .btn-link {
            justify-content: flex-start;
        }
    }
</style>

<!-- Estadísticas -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="totalServices"><?php echo htmlspecialchars($total_servicios_count); ?></h3>
            <p>Servicios Activos</p>
        </div>
        <div class="stat-icon primary">
            <i class="fas fa-tools"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="avgPrice">$<?php echo number_format($avgPrice->promedio_precio, 2); ?></h3>
            <p>Precio Promedio</p>
        </div>
        <div class="stat-icon success">
            <i class="fas fa-dollar-sign"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="maxPrice">$<?php echo number_format($maxPrice, 2); ?></h3>
            <p>Servicio Más Caro</p>
        </div>
        <div class="stat-icon warning">
            <i class="fas fa-arrow-up"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="minPrice">$<?php echo number_format($minPrice, 2); ?></h3>
            <p>Servicio Más Barato</p>
        </div>
        <div class="stat-icon info">
            <i class="fas fa-arrow-down"></i>
        </div>
    </div>
</div>

<!-- Card de Servicios -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-list me-2"></i>Catálogo de Servicios
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge" style="background-color: var(--primary); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                Mostrando <?php echo count($servicios); ?> de <?php echo $total_servicios_count; ?> servicios
            </span>
            <a href="agregar_servicio.php" class="btn btn-primary" id="addServiceBtn">
                <i class="fas fa-plus"></i> Nuevo Servicio
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($servicios)) {  ?>
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-gray-300 mb-3" style="color: #dddfeb;"></i>
                <h4 class="text-gray-500">No hay servicios registrados</h4>
                <p class="text-muted">Agrega un nuevo servicio para comenzar a gestionar el catálogo.</p>
            </div>
        <?php } else { ?>
            <div class="table-container">
                <table class="table" id="servicesTable">
                    <?php if (isset($_SESSION['errores'])) : ?>
                        <div class="alert alert-danger">
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
                            <th>Nombre del Servicio</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="servicesTableBody">
                        <?php foreach ($servicios as $item) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item->nombre); ?></td>
                                <td><?php echo htmlspecialchars($item->descripcion); ?></td>
                                <td>$<?php echo htmlspecialchars($item->precio); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="editar_servicio.php?id_servicio=<?php echo htmlspecialchars($item->id_servicio); ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="../controladores/eliminar_servicio.php" method="post" class="formEliminar" style="margin: 0;">
                                            <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($item->id_servicio); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
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
        <?php } ?>
    </div>
</div>
</div>
</div>
<script>
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar este servicio?',
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
<?php include_once "../templates/footer.php"; ?>