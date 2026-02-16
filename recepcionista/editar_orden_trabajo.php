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

// Obtener ID de la orden
$id_orden = base64_decode($_GET['id_orden']);
$id_cliente = base64_decode($_GET['id_cliente']);
$id_vehiculo = base64_decode($_GET['id_vehiculo']);
$id_mecanico = base64_decode($_GET['id_mecanico']);

if (empty($id_orden) || empty($id_cliente) || empty($id_vehiculo) || empty($id_mecanico) || !is_numeric($id_orden) || !is_numeric($id_cliente) || !is_numeric($id_vehiculo) || !is_numeric($id_mecanico)) {
    header("Location: ../recepcionista/ordenes_trabajo.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

// Obtener las ordenes de trabajo de la base de datos a editar
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_cliente as id_cliente,ordenes_trabajo.id_vehiculo as id_vehiculo, ordenes_trabajo.id_usuario_asignado as id_mecanico, vehiculos.marca as marca,vehiculos.placa as placa,ordenes_trabajo.id_orden as id_orden,ordenes_trabajo.fecha_creacion as fecha_creacion,ordenes_trabajo.fecha_entrega as fecha_entrega,ordenes_trabajo.estado as estado,ordenes_trabajo.observaciones as observaciones,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_mecanico,CONCAT(clientes.nombre,' ',clientes.apellido) as nombre_cliente, clientes.telefono as telefono FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente=clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo=vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado=usuarios.id_usuario WHERE ordenes_trabajo.id_orden = :id_orden");
$sql->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
$sql->execute();
$orden = $sql->fetch(PDO::FETCH_OBJ);

if (!$orden) {
    header("Location: ../recepcionista/ordenes_trabajo.php");
    exit();
}


// Obtener listado de usuarios con rol de mecanico
$sql = $conexion->prepare("SELECT id_usuario,CONCAT(nombre,' ',apellido) as nombre FROM usuarios WHERE rol='mecanico'");
$sql->execute();
$mecanicos = $sql->fetchAll(PDO::FETCH_OBJ);

// Mantener los datos del formulario en la sesión
$datos_orden_trabajo = isset($_SESSION['datos_orden_trabajo_form_edit']) ? $_SESSION['datos_orden_trabajo_form_edit'] : [];
unset($_SESSION['datos_orden_trabajo_form_edit']);
?>

<style>
    /* Breadcrumb - Específico de esta vista */
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

    /* Información de la orden - Específico */
    .order-info {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .order-number {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .order-number i {
        font-size: 2.5rem;
        opacity: 0.9;
    }

    .order-number-info h3 {
        font-size: 1.8rem;
        margin-bottom: 5px;
    }

    .order-number-info p {
        opacity: 0.9;
        margin: 0;
    }

    .order-date {
        text-align: right;
    }

    .order-date .date {
        font-size: 1.2rem;
        font-weight: 600;
    }

    .order-date .time {
        opacity: 0.9;
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

    /* Acciones del formulario */
    .form-actions {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
    }

    .form-actions-right {
        display: flex;
        gap: 15px;
    }

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

    /* Responsive */
    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .form-actions-right {
            width: 100%;
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .order-info {
            flex-direction: column;
            text-align: center;
        }

        .order-date {
            text-align: center;
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
<div class="order-info">
    <div class="order-number">
        <i class="fas fa-edit"></i>
        <div class="order-number-info">
            <h3>Editar Orden</h3>
            <p>Modificando la orden seleccionada</p>
        </div>
    </div>
    <div class="order-date">
        <div class="date"><?php echo date('d/m/Y'); ?></div>
    </div>
</div>

<form id="editarOrdenForm" method="POST" action="../controladores/editar_orden_trabajo.php">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id_cliente" value="<?php echo isset($id_cliente) ? $id_cliente : ''; ?>">
    <input type="hidden" name="id_vehiculo" value="<?php echo isset($id_vehiculo) ? $id_vehiculo : ''; ?>">
    <input type="hidden" name="id_mecanico" value="<?php echo isset($id_mecanico) ? $id_mecanico : ''; ?>">
    <input type="hidden" name="id_orden" value="<?php echo isset($id_orden) ? $id_orden : ''; ?>">

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

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información del Cliente y Vehículo</h2>
        </div>
        <div class="card-body">
            <div class="form-container">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Datos del Cliente
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cliente" class="required">Cliente *</label>
                            <input type="text" class="form-control" id="cliente" value="<?php echo htmlspecialchars($orden->nombre_cliente); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-car"></i> Datos del Vehículo
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehiculo" class="required">Vehículo *</label>
                            <input type="text" class="form-control" id="vehiculo" value="<?php echo htmlspecialchars($orden->marca . ' ' . $orden->placa); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Información Adicional -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información de la Orden</h2>
        </div>
        <div class="card-body">
            <div class="form-container">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-clipboard-check"></i> Detalles de la Orden
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mecanico" class="required">Mecánico Asignado *</label>
                            <select class="form-control" id="mecanico" name="mecanico" required>
                                <option value="">Seleccionar mecánico</option>
                                <?php foreach ($mecanicos as $item) { ?>
                                    <option value="<?php echo $item->id_usuario; ?>" <?php if ($item->id_usuario == $orden->id_mecanico) echo 'selected'; ?>>
                                        <?php echo $item->nombre; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_entrega">Fecha Estimada de Entrega</label>
                            <input type="datetime-local" class="form-control" id="fecha_entrega" name="fecha_entrega"
                                value="<?= $orden->fecha_entrega ?>">
                            <span class="form-text">Fecha estimada para la entrega del vehículo</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones y Notas</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= $orden->observaciones ?></textarea>
                        <span class="form-text">Información adicional sobre la orden de trabajo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones del formulario -->
    <div class="form-actions">
        <div class="form-actions-right">
            <a href="listar_ordenes.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btnEditarOrden">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </div>
</form>

</div>
</main>
</div>

<script>
    // Se mantiene la lógica para actualizar el vehículo si se cambia el cliente
    document.getElementById('cliente').addEventListener('change', function() {
        const idCliente = this.value;
        const vehiculoSelect = document.getElementById('vehiculo');

        vehiculoSelect.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        vehiculoSelect.disabled = true;

        if (!idCliente) {
            return;
        }

        vehiculoSelect.innerHTML = '<option value="">Cargando vehículos...</option>';

        fetch(`../controladores/obtener_vehiculos_por_cliente.php?id_cliente=${idCliente}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                if (data.length === 0) {
                    vehiculoSelect.innerHTML = '<option value="">El cliente no tiene vehículos registrados</option>';
                    vehiculoSelect.disabled = true;
                } else {
                    vehiculoSelect.innerHTML = '<option value="">Seleccionar vehículo</option>';
                    data.forEach(v => {
                        const option = document.createElement('option');
                        option.value = v.id_vehiculo;
                        option.textContent = `${v.placa} - ${v.marca} - ${v.modelo}`;
                        vehiculoSelect.appendChild(option);
                    });
                    vehiculoSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                vehiculoSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
                vehiculoSelect.disabled = true;
            });
    });
</script>

<?php include_once '../templates/footer.php'; ?>