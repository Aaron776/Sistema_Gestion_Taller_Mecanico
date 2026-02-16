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

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_servicio)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_servicio = isset($_SESSION['datos_servicio_form']) ? $_SESSION['datos_servicio_form'] : [];
unset($_SESSION['datos_servicio_form']);
?>
<style>
    /* Contenido Principal - Ajustes específicos para el formulario */
    .content {
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
    }

    /* Formulario */
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e3e6f0;
    }

    .new-service-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: var(--shadow);
    }

    .form-header-info h2 {
        color: var(--primary);
        margin-bottom: 5px;
        font-size: 1.8rem;
    }

    .form-header-info p {
        color: var(--gray);
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--dark);
        font-size: 1rem;
    }

    .form-group label.required::after {
        content: " *";
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: 14px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: var(--transition);
        background-color: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 140px;
        line-height: 1.6;
    }

    .form-helper {
        display: block;
        margin-top: 8px;
        font-size: 0.875rem;
        color: var(--gray);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
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

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Animaciones */
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
    @media (max-width: 768px) {
        .content {
            padding: 20px;
        }

        .form-header {
            flex-direction: column;
            text-align: center;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
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

        .new-service-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }
</style>

<!-- Card del Formulario -->
<div class="card">
    <div class="card-body">
        <div class="form-container">
            <!-- Encabezado del formulario -->
            <div class="form-header">
                <div class="new-service-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="form-header-info">
                    <h2>Nuevo Servicio</h2>
                    <p>Complete los campos para agregar un nuevo servicio al catálogo</p>
                </div>
            </div>

            <!-- Formulario -->
            <form id="serviceForm" method="POST" action="../controladores/agregar_servicio.php">
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
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <!-- Campo: Nombre del Servicio -->
                <div class="form-group">
                    <label for="serviceName" class="required">Nombre del Servicio</label>
                    <input type="text" name="nombre_servicio" id="serviceName" class="form-control" required
                        placeholder="Ej: Cambio de aceite" value="<?= isset($datos_servicio['nombre_servicio']) ? htmlspecialchars($datos_servicio['nombre_servicio']) : '' ?>">
                    <span class="form-helper">Ingrese un nombre claro y descriptivo para el servicio</span>
                </div>

                <!-- Campo: Descripción -->
                <div class="form-group">
                    <label for="serviceDescription" class="required">Descripción</label>
                    <textarea name="descripcion" id="serviceDescription" class="form-control" required
                        placeholder="Describa en detalle en qué consiste el servicio..."><?= isset($datos_servicio['descripcion']) ? htmlspecialchars($datos_servicio['descripcion']) : '' ?></textarea>
                    <span class="form-helper">Proporcione una descripción completa del servicio</span>
                </div>

                <!-- Campo: Precio -->
                <div class="form-group">
                    <label for="servicePrice" class="required">Precio ($)</label>
                    <input type="number" name="precio" id="servicePrice" class="form-control" required
                        min="0" step="0.01" placeholder="0.00" value="<?= isset($datos_servicio['precio']) ? htmlspecialchars($datos_servicio['precio']) : '' ?>">
                    <span class="form-helper">Ingrese el precio del servicio en dólares</span>
                </div>

                <!-- Acciones del formulario -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Guardar Servicio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<?php include_once "../templates/footer.php"; ?>