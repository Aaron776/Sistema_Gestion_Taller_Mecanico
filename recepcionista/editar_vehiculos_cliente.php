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

$id_cliente = base64_decode($_GET['id_cliente']); // Obtengo el id del cliente de ese vehiculo que voy a editar
if (empty($id_cliente) || !is_numeric($id_cliente) || $id_cliente < 1) {
    header("Location: vehiculos_cliente.php?id_cliente=" . base64_encode(htmlspecialchars($id_cliente)));
    exit();
}

$id_vehiculo = base64_decode($_GET['id_vehiculo']); // Obtengo el id del vehiculo que voy a editar
if (empty($id_vehiculo) || !is_numeric($id_vehiculo) || $id_vehiculo < 1) {
    header("Location: vehiculos_cliente.php?id_cliente=" . base64_encode(htmlspecialchars($id_cliente)));
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

// Obtener datos del vehiculo
$sql = $conexion->prepare("SELECT marca,modelo,anio,placa,tipo FROM vehiculos WHERE id_vehiculo = :id_vehiculo AND id_cliente = :id_cliente");
$sql->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
$sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$sql->execute();
$vehiculo = $sql->fetch(PDO::FETCH_OBJ);

if (!$vehiculo) {
    header("Location: vehiculos_cliente.php?id_cliente=" . base64_encode(htmlspecialchars($id_cliente)));
    exit();
}

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_vehiculo)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_vehiculo = isset($_SESSION['datos_vehiculo_form']) ? $_SESSION['datos_vehiculo_form'] : [];
unset($_SESSION['datos_vehiculo_form']);
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
        max-width: 1200px;
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

    /* Información del vehículo */
    .vehicle-header {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 5px solid var(--primary);
    }

    .vehicle-info-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        margin-bottom: 25px;
        align-items: stretch;
    }

    .info-card {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid var(--primary);
        height: auto;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        flex: 1;
        min-width: 280px;
    }

    .info-card h3 {
        color: var(--primary);
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e3e6f0;
    }

    .info-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--dark);
    }

    .info-value {
        color: var(--gray);
    }

    .info-value.badge {
        padding: 4px 10px;
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

    /* Cards */
    .card {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        margin-bottom: 30px;
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

    /* Formularios */
    .form-container {
        display: grid;
        gap: 30px;
    }

    .form-section {
        padding-bottom: 25px;
        border-bottom: 1px solid #e3e6f0;
    }

    .form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .section-title {
        color: var(--primary);
        font-size: 1.1rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e3e6f0;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 20px;
    }

    .form-row:last-child {
        margin-bottom: 0;
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

    .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
        color: var(--gray);
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

    .btn-danger {
        background-color: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-outline {
        background-color: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: white;
    }

    /* Acciones del formulario */
    .form-actions {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
    }

    .form-actions-left {
        display: flex;
        gap: 15px;
    }

    .form-actions-right {
        display: flex;
        gap: 15px;
    }

    /* Historial de servicios */
    .history-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .history-table th {
        background-color: #f8f9fc;
        color: var(--primary);
        font-weight: 700;
        padding: 12px 15px;
        border-bottom: 2px solid #e3e6f0;
        text-align: left;
    }

    .history-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: middle;
    }

    .history-table tbody tr:hover {
        background-color: #f8f9fc;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.completed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-badge.in-progress {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    /* Color preview */
    .color-preview {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .color-box {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #ddd;
        cursor: pointer;
        transition: var(--transition);
    }

    .color-box:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .color-presets {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .color-preset {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: var(--transition);
    }

    .color-preset:hover {
        transform: scale(1.1);
    }

    .color-preset.active {
        border-color: var(--dark);
        transform: scale(1.1);
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
        .edit-layout {
            grid-template-columns: 1fr;
        }

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

        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions-left,
        .form-actions-right {
            width: 100%;
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 20px;
        }

        .form-section {
            padding-bottom: 20px;
        }

        .color-preview {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
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


<!-- Información del vehículo -->
<div class="edit-layout">
    <!-- Sidebar con Información -->
    <aside class="info-sidebar">
        <div class="info-card">
            <h3>Información del Vehículo</h3>
            <div class="info-item">
                <span class="info-label">Placa: </span>
                <span class="info-value"><?php echo htmlspecialchars($vehiculo->placa); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Marca: </span>
                <span class="info-value"><?php echo htmlspecialchars($vehiculo->marca); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Modelo: </span>
                <span class="info-value"><?php echo htmlspecialchars($vehiculo->modelo); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Año: </span>
                <span class="info-value"><?php echo htmlspecialchars($vehiculo->anio); ?></span>
            </div>
        </div>

        <button class="btn btn-outline" onclick="window.history.back()" style="align-self: flex-start;">
            <i class="fas fa-arrow-left"></i> Volver a Vehículos
        </button>
    </aside>

    <!-- Formulario de edición -->
    <div class="main-form">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Editar Información del Vehículo</h2>
                <button class="btn btn-outline" id="btnResetForm">
                    <i class="fas fa-redo"></i> Restablecer
                </button>
            </div>
            <div class="card-body">
                <form id="editVehiculoForm" class="form-container" method="post" action="../controladores/editar_vehiculo_cliente.php">
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
                    <input type="hidden" id="id_vehiculo" name="id_vehiculo" value="<?php echo htmlspecialchars($id_vehiculo); ?>">
                    <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($id_cliente); ?>">
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-section">
                        <h3 class="section-title">Información Básica</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="placa" class="required">Placa *</label>
                                <input type="text" class="form-control" id="placa" name="placa" value="<?= isset($datos_vehiculo['placa']) ? htmlspecialchars($datos_vehiculo['placa']) : htmlspecialchars($vehiculo->placa); ?>" required maxlength="20">
                                <span class="form-text">Ejemplo: ABC-1234, XYZ-567</span>
                            </div>
                            <div class="form-group">
                                <label for="marca" class="required">Marca *</label>
                                <input type="text" class="form-control" id="marca" name="marca" value="<?= isset($datos_vehiculo['marca']) ? htmlspecialchars($datos_vehiculo['marca']) : htmlspecialchars($vehiculo->marca); ?>" required maxlength="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="modelo" class="required">Modelo *</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" value="<?= isset($datos_vehiculo['modelo']) ? htmlspecialchars($datos_vehiculo['modelo']) : htmlspecialchars($vehiculo->modelo); ?>" required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="anio" class="required">Año *</label>
                                <input type="number" class="form-control" id="anio" name="anio" value="<?= isset($datos_vehiculo['anio']) ? htmlspecialchars($datos_vehiculo['anio']) : htmlspecialchars($vehiculo->anio); ?>" required min="1900" max="<?= date('Y') + 1 ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo" class="required">Tipo de Vehículo *</label>
                                <input type="text" class="form-control" id="tipo" name="tipo" maxlength="50" value="<?= isset($datos_vehiculo['tipo']) ? htmlspecialchars($datos_vehiculo['tipo']) : htmlspecialchars($vehiculo->tipo); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones del formulario -->
                    <div class="form-actions">
                        <div class="form-actions-left">
                            <button type="button" class="btn btn-secondary" id="btnCancelar">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                        <div class="form-actions-right">
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</main>
</div>

<?php include '../templates/footer.php'; ?>