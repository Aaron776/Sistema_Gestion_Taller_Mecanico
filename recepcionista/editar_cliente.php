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

include_once '../conexion/bd.php';

if (isset($_GET['id_cliente'])) {
    $id_cliente = base64_decode($_GET['id_cliente']);
} else {
    $id_cliente = null;
}

if (!$id_cliente) {
    header("Location: gestion_clientes.php");
    exit();
}

// Obtener información del cliente que se va a editar
$stmt = $conexion->prepare("SELECT clientes.id_cliente as id_cliente, clientes.nombre as nombre_cliente, clientes.apellido as apellido_cliente, clientes.cedula_ruc as cedula_ruc_cliente, clientes.telefono as telefono_cliente, clientes.email as email_cliente, clientes.direccion as direccion_cliente, clientes.fecha_registro as fecha_registro_cliente, vehiculos.id_vehiculo as id_vehiculo, vehiculos.marca as marca_vehiculo, vehiculos.modelo as modelo_vehiculo, vehiculos.placa as placa_vehiculo, vehiculos.tipo as tipo_vehiculo FROM clientes INNER JOIN vehiculos ON clientes.id_cliente = vehiculos.id_cliente WHERE clientes.id_cliente = :id_cliente");
$stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_OBJ);

if (!$cliente) {
    header("Location: gestion_clientes.php");
    exit();
}

include '../templates/header.php';

// Mantener Persistencia de datos del formulario
$datos_cliente = isset($_SESSION['datos_cliente_edit_form']) ? $_SESSION['datos_cliente_edit_form'] : [];
unset($_SESSION['datos_cliente_edit_form']);
?>
<style>
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
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

    /* Información del cliente */
    .client-info {
        background-color: #f8f9fc;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary);
    }

    .client-info h3 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.85rem;
        color: var(--gray);
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 600;
        color: var(--dark);
    }



    /* Acciones del formulario */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
    }

    /* Pestañas */
    .tabs {
        display: flex;
        border-bottom: 1px solid #e3e6f0;
        margin-bottom: 25px;
        gap: 5px;
    }

    .tab {
        padding: 12px 24px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-weight: 600;
        color: var(--gray);
        transition: var(--transition);
    }

    .tab:hover {
        color: var(--primary);
    }

    .tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    /* Historial de servicios */
    .history-table {
        width: 100%;
        border-collapse: collapse;
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
        padding: 5px 10px;
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



        .tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
        }

        .tab {
            white-space: nowrap;
            padding: 10px 15px;
        }
    }

    @media (max-width: 576px) {


        .info-grid {
            grid-template-columns: 1fr;
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

<!-- Información del cliente -->
<div class="client-info">
    <h3>Información del Cliente</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">ID Cliente:</span>
            <span class="info-value">CLI-<?php echo htmlspecialchars($cliente->id_cliente); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Fecha de Registro:</span>
            <span class="info-value"><?php echo htmlspecialchars(date('d/m/Y H:i A', strtotime($cliente->fecha_registro_cliente))); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Vehiculo :</span>
            <span class="info-value"><?php echo htmlspecialchars($cliente->marca_vehiculo . ' ' . $cliente->modelo_vehiculo . ' ' . $cliente->placa_vehiculo); ?></span>
        </div>
    </div>
</div>

<!-- Pestañas -->
<div class="tabs">
    <button class="tab active" data-tab="info">Información Personal</button>
</div>

<!-- Formulario de información personal -->
<div class="tab-content active" id="infoTab">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información Personal</h2>
            <button class="btn btn-outline" id="btnReset">
                <i class="fas fa-redo"></i> Restablecer
            </button>
        </div>
        <div class="card-body">
            <form id="editClienteForm" class="form-container" action="../controladores/editar_cliente.php" method="POST">
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
                <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($cliente->id_cliente); ?>">
                <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre" class="required">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" value="<?= isset($datos_cliente['nombre']) ? htmlspecialchars($datos_cliente['nombre']) : htmlspecialchars($cliente->nombre_cliente); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido" class="required">Apellido</label>
                        <input type="text" class="form-control" name="apellido" id="apellido" value="<?= isset($datos_cliente['apellido']) ? htmlspecialchars($datos_cliente['apellido']) : htmlspecialchars($cliente->apellido_cliente); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cedula" class="required">Cédula/RUC</label>
                        <input type="text" class="form-control" name="cedula" id="cedula" value="<?= isset($datos_cliente['cedula']) ? htmlspecialchars($datos_cliente['cedula']) : htmlspecialchars($cliente->cedula_ruc_cliente); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono" class="required">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" id="telefono" value="<?= isset($datos_cliente['telefono']) ? htmlspecialchars($datos_cliente['telefono']) : htmlspecialchars($cliente->telefono_cliente); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?= isset($datos_cliente['email']) ? htmlspecialchars($datos_cliente['email']) : htmlspecialchars($cliente->email_cliente); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="direccion" class="required">Dirección</label>
                        <textarea class="form-control" name="direccion" id="direccion" rows="3" required><?= isset($datos_cliente['direccion']) ? htmlspecialchars($datos_cliente['direccion']) : htmlspecialchars($cliente->direccion_cliente); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="btnCancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php include_once '../templates/footer.php'; ?>