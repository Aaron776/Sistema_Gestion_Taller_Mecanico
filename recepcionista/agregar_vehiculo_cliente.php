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

$id_cliente = base64_decode($_GET['id_cliente']);
if (empty($id_cliente) || !is_numeric($id_cliente) || $id_cliente < 1) {
    header("Location: gestion_clientes.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';


// Obtener datos del cliente
$sql = $conexion->prepare("SELECT CONCAT(nombre,' ',apellido) as nombre_cliente,cedula_ruc,telefono FROM clientes WHERE id_cliente=:id_cliente");
$sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$sql->execute();
$cliente = $sql->fetch(PDO::FETCH_OBJ);

if (!$cliente) {
    header("Location: gestion_clientes.php");
    exit();
}

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_vehiculo)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_vehiculo = isset($_SESSION['datos_vehiculo_form']) ? $_SESSION['datos_vehiculo_form'] : []; // Recuperar datos del formulario 
unset($_SESSION['datos_vehiculo_form']); // Limpiar datos del formulario
?>
<style>
    /* Estilos específicos para agregar vehículo */

    /* Información del cliente */
    .client-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 5px solid var(--primary);
    }

    .client-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.9rem;
        color: var(--gray);
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .client-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e3e6f0;
    }

    /* Formularios específicos */
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

    .section-description {
        color: var(--gray);
        font-size: 0.95rem;
        margin-top: -10px;
        margin-bottom: 20px;
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

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 0.85rem;
        color: var(--gray);
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .input-with-icon input {
        padding-left: 40px;
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

    .form-actions-left,
    .form-actions-right {
        display: flex;
        gap: 15px;
    }

    /* Wizard steps */
    .wizard-steps {
        display: flex;
        margin-bottom: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .wizard-step {
        flex: 1;
        padding: 20px;
        text-align: center;
        position: relative;
        transition: var(--transition);
    }

    .wizard-step.active {
        background-color: var(--primary);
        color: white;
    }

    .wizard-step-title {
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* Botón outline (si no está en header) */
    .btn-outline {
        background-color: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: white;
    }

    /* Alert info (si no está en header) */
    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid var(--info);
    }

    /* Responsive específico */
    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .client-info {
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

        .wizard-steps {
            flex-direction: column;
        }

        .wizard-step {
            text-align: left;
        }
    }

    @media (max-width: 576px) {
        .form-section {
            padding-bottom: 20px;
        }
    }
</style>
<!-- Información del cliente -->
<div class="client-card">
    <div class="client-info">
        <div class="info-item">
            <span class="info-label">Cliente</span>
            <span class="info-value"><?= htmlspecialchars($cliente->nombre_cliente) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Cédula/RUC</span>
            <span class="info-value"><?= htmlspecialchars($cliente->cedula_ruc) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Teléfono</span>
            <span class="info-value"><?= htmlspecialchars($cliente->telefono) ?></span>
        </div>
    </div>
    <div class="client-actions">
        <button class="btn btn-outline" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i> Volver a Vehículos
        </button>
    </div>
</div>

<!-- Formulario de nuevo vehículo -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Registrar Nuevo Vehículo</h2>
    </div>
    <div class="card-body">
        <form id="nuevoVehiculoForm" class="form-container" method="POST" action="../controladores/agregar_vehiculo_cliente.php">
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
            <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($id_cliente); ?>">
            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-section" id="step1Content">
                <h3 class="section-title">Información Básica del Vehículo</h3>
                <p class="section-description">Ingrese la información principal del vehículo</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="placa" class="required">Placa *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-car"></i>
                            <input type="text" class="form-control" id="placa" name="placa" required
                                placeholder="Ej: ABC-1234" maxlength="10" value="<?= isset($datos_vehiculo['placa']) ? htmlspecialchars($datos_vehiculo['placa']) : '' ?>">
                        </div>
                        <span class="form-text">Formato: Tres letras, guión y tres números</span>
                    </div>
                    <div class="form-group">
                        <label for="marca" class="required">Marca *</label>
                        <input type="text" class="form-control" id="marca" name="marca" required
                            placeholder="Ej: Toyota" value="<?= isset($datos_vehiculo['marca']) ? htmlspecialchars($datos_vehiculo['marca']) : '' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="modelo" class="required">Modelo *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-car-side"></i>
                            <input type="text" class="form-control" id="modelo" name="modelo" required
                                placeholder="Ej: Corolla, Tucson, Spark" value="<?= isset($datos_vehiculo['modelo']) ? htmlspecialchars($datos_vehiculo['modelo']) : '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ano" class="required">Año *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="number" class="form-control" id="ano" name="anio" required
                                min="1900" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>" value="<?= isset($datos_vehiculo['anio']) ? htmlspecialchars($datos_vehiculo['anio']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo" class="required">Tipo de Vehículo *</label>
                        <input type="text" class="form-control" id="tipo" name="tipo" required
                            placeholder="Ej: Sedán, SUV, Pickup, Hatchback, Coupé" value="<?= isset($datos_vehiculo['tipo']) ? htmlspecialchars($datos_vehiculo['tipo']) : '' ?>">
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
                        <i class="fas fa-save"></i> Registrar Vehículo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</main>
</div>
<?php include '../templates/footer.php'; ?>