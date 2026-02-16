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

// Obtener los usuarios por rol de mecanico
$sql = $conexion->prepare("SELECT id_usuario as id_mecanico,CONCAT(nombre,' ',apellido) as nombre_mecanico FROM usuarios WHERE rol = 'mecanico'");
$sql->execute();
$mecanicos = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener todos los clientes
$sql = $conexion->prepare("SELECT id_cliente,CONCAT(nombre,' ',apellido) as nombre_cliente,cedula_ruc FROM clientes");
$sql->execute();
$clientes = $sql->fetchAll(PDO::FETCH_OBJ);

// El dropdown de vehículos se cargará dinámicamente mediante AJAX cuando se seleccione un cliente.

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_orden_trabajo)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_orden_trabajo = isset($_SESSION['datos_orden_trabajo_form']) ? $_SESSION['datos_orden_trabajo_form'] : []; // Recuperar datos del formulario 
unset($_SESSION['datos_orden_trabajo_form']); // Limpiar datos del formulario
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

    /* Formularios - Específicos para el layout de formularios complejos */
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

    /* Tabla de servicios - Estilos adicionales para tablas dentro de tarjetas */
    .services-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .services-table thead {
        background-color: #f8f9fc;
    }

    .services-table th {
        color: var(--primary);
        font-weight: 700;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #e3e6f0;
    }

    .services-table td {
        padding: 15px;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: middle;
    }

    .services-table tbody tr:last-child td {
        border-bottom: none;
    }

    .services-table tbody tr:hover {
        background-color: #f8f9fc;
    }

    .actions-cell {
        width: 120px;
    }

    .btn-icon {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        margin: 0 3px;
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

    /* Botones específicos no presentes en header */
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

    /* Resumen de costos */
    .cost-summary {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .cost-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e3e6f0;
    }

    .cost-row:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary);
    }

    .cost-label {
        color: var(--dark);
    }

    .cost-value {
        font-weight: 600;
    }

    /* Mensajes de alerta - No presentes en snippet de header */
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

    /* Responsive Específico */
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

        .btn {
            width: 100%;
            justify-content: center;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .order-info {
            flex-direction: column;
            text-align: center;
        }

        .order-date {
            text-align: center;
        }

        .services-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 20px;
        }

        .form-section {
            padding-bottom: 20px;
        }

        .section-title {
            font-size: 1rem;
        }
    }

    /* Animaciones Específicas */
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
        <i class="fas fa-file-invoice"></i>
        <div class="order-number-info">
            <h3>ORD-<?php echo date('Y'); ?>-00123</h3>
            <p>Nueva Orden de Trabajo</p>
        </div>
    </div>
    <div class="order-date">
        <div class="date"><?php echo date('d/m/Y'); ?></div>
        <div class="time"><?php echo date('H:i'); ?></div>
    </div>
</div>

<!-- Formulario de la orden -->
<form id="ordenTrabajoForm" method="POST" action="../controladores/crear_ordenes_trabajo.php">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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
                            <select class="form-control" id="cliente" name="id_cliente" required>
                                <option value="">Seleccionar cliente</option>
                                <?php foreach ($clientes as $item) { ?>
                                    <option value="<?php echo $item->id_cliente; ?>"><?php echo $item->nombre_cliente; ?> - <?php echo $item->cedula_ruc; ?></option>
                                <?php } ?>
                            </select>
                            <span class="form-text">Busque y seleccione un cliente</span>
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
                            <select class="form-control" id="vehiculo" name="id_vehiculo" required disabled>
                                <option value="">Primero seleccione un cliente</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Información Adicional -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información Adicional</h2>
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
                            <select class="form-control" id="mecanico" name="id_mecanico" required>
                                <option value="">Seleccionar mecánico</option>
                                <?php foreach ($mecanicos as $item) { ?>
                                    <option value="<?php echo $item->id_mecanico; ?>"><?php echo $item->nombre_mecanico; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_entrega">Fecha Estimada de Entrega</label>
                            <input type="datetime-local" class="form-control" id="fecha_entrega" min="<?= date('Y-m-d\TH:i') ?>" name="fecha_entrega" required value="<?= isset($datos_orden_trabajo['fecha_entrega']) ? htmlspecialchars($datos_orden_trabajo['fecha_entrega']) : '' ?>">
                            <span class="form-text">Fecha estimada para la entrega del vehículo</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones y Notas</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Describa el problema reportado por el cliente, observaciones iniciales, o cualquier información adicional importante..."><?= isset($datos_orden_trabajo['observaciones']) ? htmlspecialchars($datos_orden_trabajo['observaciones']) : '' ?></textarea>
                        <span class="form-text">Información adicional sobre la orden de trabajo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones del formulario -->
    <div class="form-actions">
        <div class="form-actions-right">
            <button type="submit" class="btn btn-success" id="btnCrearOrden">
                <i class="fas fa-check-circle"></i> Crear Orden
            </button>
        </div>
    </div>
</form>
</div>
</main>
</div>
<script>
    // Escuchar cambios en el selector de cliente
    document.getElementById('cliente').addEventListener('change', function() {
        const idCliente = this.value;
        const vehiculoSelect = document.getElementById('vehiculo');

        // Resetear el selector de vehículos y deshabilitarlo mientras carga
        vehiculoSelect.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        vehiculoSelect.disabled = true;

        // Si no hay cliente seleccionado (opción vacía), terminar
        if (!idCliente) {
            return;
        }

        // Mostrar mensaje de carga mientras se hace la petición
        vehiculoSelect.innerHTML = '<option value="">Cargando vehículos...</option>';

        // Petición AJAX al controlador para obtener los vehículos del cliente
        fetch(`../controladores/obtener_vehiculos_por_cliente.php?id_cliente=${idCliente}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                // Si el cliente no tiene vehículos registrados
                if (data.length === 0) {
                    vehiculoSelect.innerHTML = '<option value="">El cliente no tiene vehículos registrados</option>';
                    vehiculoSelect.disabled = true;
                } else {
                    // Llenar el dropdown con los vehículos devueltos por la base de datos
                    vehiculoSelect.innerHTML = '<option value="">Seleccionar vehículo</option>';
                    data.forEach(v => {
                        const option = document.createElement('option');
                        option.value = v.id_vehiculo;
                        option.textContent = `${v.placa} - ${v.marca} - ${v.modelo}`;
                        vehiculoSelect.appendChild(option);
                    });
                    // Habilitar el campo una vez que hay datos
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
<?php include '../templates/footer.php'; ?>