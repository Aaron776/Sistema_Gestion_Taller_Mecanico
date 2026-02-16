<?php
require_once '../autorizacion/auth.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que el usuario tenga permiso de administrador para editar servicios
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../acceso_denegado.php");
    exit();
}

include_once '../templates/header.php';
include_once '../conexion/bd.php';

// Mantener Persistencia de datos del formulario
$datos_servicio = isset($_SESSION['datos_servicio_edit_form']) ? $_SESSION['datos_servicio_edit_form'] : [];
unset($_SESSION['datos_servicio_edit_form']);

$id_servicio = $_GET['id_servicio'];

if (!isset($id_servicio) || !is_numeric($id_servicio) || $id_servicio <= 0) {
    header("Location: ../gestion_servicios.php");
    exit();
}

// Obtener datos del servicio que se va a editar
$sql = $conexion->prepare("SELECT id_servicio, nombre_servicio, descripcion, precio_base FROM servicios WHERE id_servicio = :id_servicio");
$sql->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
$sql->execute();
$servicio = $sql->fetch(PDO::FETCH_OBJ);

if (!$servicio) {
    header("Location: ../gestion_servicios.php");
    exit();
}
?>
<style>
    /* Contenido Principal - Ajustes específicos */
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

    .edit-service-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
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

    .service-id {
        display: inline-block;
        background-color: #f8f9fc;
        padding: 5px 12px;
        border-radius: 5px;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
        margin-left: 10px;
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
        justify-content: space-between;
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

    /* Información del servicio */
    .service-info {
        background-color: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid var(--primary);
    }

    .service-info h4 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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

        .info-grid {
            grid-template-columns: 1fr;
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

        .edit-service-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .form-actions {
            gap: 10px;
        }
    }
</style>

<!-- Card del Formulario -->
<div class="card">
    <div class="card-body">
        <div class="form-container">
            <!-- Encabezado del formulario -->
            <div class="form-header">
                <div class="edit-service-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="form-header-info">
                    <h2>Editar Servicio <span class="service-id" id="serviceId">ID: <?php echo htmlspecialchars($servicio->id_servicio); ?> </span></h2>
                    <p>Modifique los campos necesarios para actualizar el servicio</p>
                </div>
            </div>

            <!-- Formulario -->
            <form id="serviceForm" method="POST" action="../controladores/editar_servicio.php">
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
                <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($servicio->id_servicio); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- Campo: Nombre del Servicio -->
                <div class="form-group">
                    <label for="serviceName" class="required">Nombre del Servicio</label>
                    <input type="text" id="serviceName" name="nombre_servicio" value="<?= isset($datos_servicio['nombre_servicio']) ? htmlspecialchars($datos_servicio['nombre_servicio']) : htmlspecialchars($servicio->nombre_servicio); ?>" class="form-control" required
                        placeholder="Ej: Cambio de aceite">
                    <span class="form-helper">Ingrese un nombre claro y descriptivo para el servicio</span>
                </div>

                <!-- Campo: Descripción -->
                <div class="form-group">
                    <label for="serviceDescription" class="required">Descripción</label>
                    <textarea id="serviceDescription" name="descripcion" class="form-control" required
                        placeholder="Describa en detalle en qué consiste el servicio..."><?= isset($datos_servicio['descripcion']) ? htmlspecialchars($datos_servicio['descripcion']) : htmlspecialchars($servicio->descripcion); ?></textarea>
                    <span class="form-helper">Proporcione una descripción completa del servicio</span>
                </div>

                <!-- Campo: Precio -->
                <div class="form-group">
                    <label for="servicePrice" class="required">Precio ($)</label>
                    <input type="number" id="servicePrice" name="precio_base" value="<?= isset($datos_servicio['precio_base']) ? htmlspecialchars($datos_servicio['precio_base']) : htmlspecialchars($servicio->precio_base); ?>" class="form-control" required
                        min="0" step="0.01" placeholder="0.00">
                    <span class="form-helper">Ingrese el precio del servicio en dólares</span>
                </div>

                <!-- Acciones del formulario -->
                <div class="form-actions">
                    <div style="display: flex; gap: 15px;">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
<?php include_once '../templates/footer.php'; ?>