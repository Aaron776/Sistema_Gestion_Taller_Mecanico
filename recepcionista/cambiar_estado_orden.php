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


// Obtener el id de la orden de trabajo
$id_orden = base64_decode($_GET['id_orden']);
if (!isset($id_orden) || !is_numeric($id_orden)) {
    header("Location: gestion_ordenes_trabajo.php");
    exit();
}

// Obtener la orden de trabajo

include '../templates/header.php';
include_once '../conexion/bd.php';

// Obtener la orden de trabajo
$sql = $conexion->prepare("SELECT id_orden,estado,fecha_creacion FROM ordenes_trabajo WHERE id_orden = :id_orden");
$sql->bindValue(':id_orden', $id_orden, PDO::PARAM_INT);
$sql->execute();
$orden = $sql->fetch(PDO::FETCH_OBJ);

if (!$orden) {
    header("Location: ../gestion_ordenes_trabajo.php");
    exit();
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
        max-width: 800px;
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
        padding: 30px;
    }

    /* Información de la orden */
    .order-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        text-align: center;
    }

    .order-number {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .order-date {
        opacity: 0.9;
        font-size: 0.95rem;
    }

    /* Estado actual */
    .current-status {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        text-align: center;
        border-left: 4px solid var(--warning);
    }

    .status-label {
        font-size: 0.9rem;
        color: var(--gray);
        margin-bottom: 5px;
    }

    .status-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark);
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 10px;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* Formulario de estado */
    .status-form {
        max-width: 400px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .form-control {
        width: 100%;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: var(--transition);
        font-size: 1rem;
        background-color: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
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
        width: 100%;
        justify-content: center;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #0f2a46;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 58, 95, 0.2);
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

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid var(--info);
    }

    .alert i {
        font-size: 1.2rem;
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

        .main {
            margin-left: var(--sidebar-collapsed-width);
        }

        .main.expanded {
            margin-left: 0;
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

        .card-body {
            padding: 20px;
        }

        .status-form {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .order-header {
            padding: 15px;
        }

        .order-number {
            font-size: 1.5rem;
        }

        .current-status {
            padding: 15px;
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
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>


<!-- Información de la orden -->
<div class="order-header">
    <div class="order-number">ORD-<?php echo htmlspecialchars(date('Y')) ?>-<?php echo htmlspecialchars($orden->id_orden) ?></div>
    <div class="order-date">Fecha de creación: <?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($orden->fecha_creacion))) ?></div>
</div>

<!-- Estado actual -->
<div class="current-status">
    <div class="status-label">Estado Actual</div>
    <span class="status-badge status-success">Finalizado</span>
</div>

<!-- Formulario para editar estado -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Actualizar Estado de la Orden</h2>
    </div>
    <div class="card-body">
        <form id="estadoForm" class="status-form" method="POST" action="../controladores/cambiar_estado_orden_trabajo_recepcionista.php">
            <input type="hidden" name="id_orden" value="<?php echo htmlspecialchars($orden->id_orden) ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="estadoOrden">Seleccionar Nuevo Estado</label>
                <select class="form-control" id="estadoOrden" name="estado" required>
                    <option value="">Seleccionar estado</option>
                    <option value="entregado">ENTREGADO</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="btnCancelar" style="margin-bottom: 10px;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="btnActualizarEstado">
                    <i class="fas fa-save"></i> Actualizar Estado
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</main>
</div>

<?php include '../templates/footer.php'; ?>