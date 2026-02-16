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

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_cliente)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_cliente = isset($_SESSION['datos_cliente_form']) ? $_SESSION['datos_cliente_form'] : []; // Recuperar datos del formulario 
unset($_SESSION['datos_cliente_form']); // Limpiar datos del formulario
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

    /* Acciones del formulario */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
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

    /* Animaciones */
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Nuevo Cliente</h2>
                <a href="gestion_clientes.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Clientes
                </a>
            </div>
            <div class="card-body">
                <form id="addClienteForm" class="form-container" action="../controladores/agregar_cliente.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre" class="required">Nombre(s)</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Juan Carlos" value="<?= isset($datos_cliente['nombre']) ? htmlspecialchars($datos_cliente['nombre']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido" class="required">Apellido(s)</label>
                            <input type="text" class="form-control" name="apellido" id="apellido" placeholder="Ej: Pérez González" value="<?= isset($datos_cliente['apellido']) ? htmlspecialchars($datos_cliente['apellido']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 25px;">
                        <div class="form-group">
                            <label for="cedula" class="required">Cédula/RUC</label>
                            <input type="text" class="form-control" name="cedula" id="cedula" placeholder="Ingrese número de identificación" value="<?= isset($datos_cliente['cedula']) ? htmlspecialchars($datos_cliente['cedula']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono" class="required">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" placeholder="+52 555 123 4567" value="<?= isset($datos_cliente['telefono']) ? htmlspecialchars($datos_cliente['telefono']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 25px;">
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="ejemplo@dominio.com" value="<?= isset($datos_cliente['email']) ? htmlspecialchars($datos_cliente['email']) : '' ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 25px;">
                        <div class="form-group">
                            <label for="direccion" class="required">Dirección</label>
                            <textarea class="form-control" name="direccion" id="direccion" rows="3" placeholder="Dirección completa" required><?= isset($datos_cliente['direccion']) ? htmlspecialchars($datos_cliente['direccion']) : '' ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>