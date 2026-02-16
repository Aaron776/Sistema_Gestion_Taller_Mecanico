<?php
require_once '../autorizacion/auth.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de mecanico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'mecanico') {
    header("Location: ../acceso_denegado.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

$id_orden_trabajo = base64_decode($_GET['id_orden']);

if (!isset($id_orden_trabajo) || empty($id_orden_trabajo) || !is_numeric($id_orden_trabajo)) {
    header("Location: gestion_ordenes_trabajo_en_proceso.php");
    exit();
}

// Obtener repuestos de la base de datos
$sql = $conexion->prepare("SELECT id_repuesto,nombre,precio,stock FROM repuestos");
$sql->execute();
$lista_repuestos = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener Orden de trabajo asignado a esa orden de trabajo
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden as id_orden,CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente,vehiculos.placa as placa_vehiculo FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente=clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo=vehiculos.id_vehiculo WHERE ordenes_trabajo.id_orden=:id_orden_trabajo");
$sql->bindParam(':id_orden_trabajo', $id_orden_trabajo, PDO::PARAM_INT);
$sql->execute();
$orden_trabajo = $sql->fetch(PDO::FETCH_OBJ);
?>
<style>
    /* Contenido Principal Override */
    .content {
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

    /* Cards Override */
    .card {
        margin-bottom: 25px;
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
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--secondary);
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

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Select con búsqueda */
    .select-search {
        position: relative;
    }

    .select-search input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
    }

    .select-search i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    /* Botones Override */
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

    /* Checkbox y Radio personalizados */
    .form-check {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .form-check-input {
        margin-right: 10px;
        width: 18px;
        height: 18px;
    }

    .form-check-label {
        margin-bottom: 0;
        cursor: pointer;
    }

    /* Información de cliente/vehículo */
    .info-box {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        border-left: 4px solid var(--primary);
    }

    .info-item {
        display: flex;
        margin-bottom: 5px;
    }

    .info-label {
        font-weight: 600;
        min-width: 120px;
        color: var(--dark);
    }

    .info-value {
        color: var(--gray);
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



    /* Responsive Page Specific */
    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .form-actions-left,
        .form-actions-right {
            width: 100%;
            flex-direction: column;
        }

        .card-body {
            padding: 20px;
        }
    }

    @media (max-width: 576px) {
        .form-section {
            padding-bottom: 20px;
        }

        .section-title {
            font-size: 1rem;
        }

        .info-item {
            flex-direction: column;
        }

        .info-label {
            min-width: auto;
            margin-bottom: 2px;
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


<!-- Formulario de nueva orden -->
<form id="nuevaOrdenForm" method="POST" action="../controladores/crear_orden_repuestos.php">
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
    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" id="id_orden" name="id_orden" value="<?php echo $id_orden_trabajo; ?>">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información para la Orden de Repuestos</h2>
        </div>
        <div class="card-body">
            <div class="form-container">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-tools"></i> Detalles de la Orden de Repuestos
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo_servicio" class="required">Repuesto *</label>
                            <select class="form-control" id="tipo_servicio" name="id_repuesto" required>
                                <option value="">Seleccionar Repuesto</option>
                                <?php foreach ($lista_repuestos as $item) { ?>
                                    <!-- Se agrega el atributo data-precio para guardar el precio base del repuesto -->
                                    <!-- Se agrega el atributo data-stock para guardar el stock del repuesto -->
                                    <option value="<?php echo $item->id_repuesto; ?>" data-precio="<?php echo $item->precio; ?>" data-stock="<?php echo $item->stock; ?>"><?php echo $item->nombre; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_orden_display" class="required">Orden de Trabajo *</label>
                            <input type="text" class="form-control" id="id_orden_display" name="" value="<?php echo htmlspecialchars($orden_trabajo->id_orden . '-' . $orden_trabajo->nombre_cliente . '-' . $orden_trabajo->placa_vehiculo); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cantidad" class="required">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" min="1" value="1" name="cantidad" required>
                        </div>
                        <div class="form-group">
                            <label for="precio_unitario" class="required">Precio Unitario *</label>
                            <input type="number" class="form-control" step="0.01" id="precio_unitario" min="0" value="0.00" name="precio_unitario" required readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones del formulario -->
    <div class="form-actions">
        <div class="form-actions-left">
            <button type="button" class="btn btn-secondary" id="btnCancelar">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn btn-outline" id="btnLimpiarFormulario">
                <i class="fas fa-broom"></i> Limpiar Formulario
            </button>
        </div>
        <div class="form-actions-right">
            <button type="button" class="btn btn-outline" id="btnGuardarBorrador">
                <i class="fas fa-save"></i> Guardar Borrador
            </button>
            <button type="submit" class="btn btn-success" id="btnCrearOrden">
                <i class="fas fa-check-circle"></i> Crear Orden de Repuesto
            </button>
        </div>
    </div>
</form>
</div>
</main>
</div>

<script src="../js/mecanico/crear_orden_repuestos.js?v=<?php echo time(); ?>"></script>
<?php include '../templates/footer.php'; ?>