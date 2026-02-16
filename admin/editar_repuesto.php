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
include_once "../conexion/bd.php";

$id_repuesto = base64_decode($_GET['id_repuesto']);
if (!$id_repuesto) {
    header("Location: gestion_repuestos.php");
    exit();
}

// Obtener datos del repuesto para editar
$sql = $conexion->prepare("SELECT id_repuesto, nombre, descripcion, precio, stock FROM repuestos WHERE id_repuesto = :id_repuesto");
$sql->bindValue(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
$sql->execute();
$repuesto = $sql->fetch(PDO::FETCH_OBJ);

if (!$repuesto) {
    header("Location: gestion_repuestos.php");
    exit();
}
include_once "../templates/header.php";

// Mantener Persistencia de datos del formulario
$datos_repuesto = isset($_SESSION['datos_repuesto_edit_form']) ? $_SESSION['datos_repuesto_edit_form'] : [];
unset($_SESSION['datos_repuesto_edit_form']);
?>
<style>
    /* Contenido Principal Override */
    .content {
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
        padding: 30px;
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

    .edit-part-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffc107 0%, #ffd761 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .form-header-info h2 {
        color: #1a3a5f;
        margin-bottom: 5px;
        font-size: 1.8rem;
    }

    .form-header-info p {
        color: #6c757d;
        margin-bottom: 10px;
    }

    .part-id {
        display: inline-block;
        background-color: #f8f9fc;
        padding: 5px 12px;
        border-radius: 5px;
        font-weight: 600;
        color: #1a3a5f;
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
        color: #343a40;
        font-size: 1rem;
    }

    .form-group label.required::after {
        content: " *";
        color: #e63946;
    }

    .form-control {
        width: 100%;
        padding: 14px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a3a5f;
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
        color: #6c757d;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid #e3e6f0;
    }

    /* Información del repuesto */
    .part-info {
        background-color: #f8f9fc;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #1a3a5f;
    }

    .part-info h4 {
        color: #1a3a5f;
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
        color: #6c757d;
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 600;
        color: #343a40;
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
        .edit-part-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .form-actions {
            gap: 10px;
        }
    }
</style>


<!-- Información del repuesto -->
<div class="part-info">
    <h4>Información del Repuesto</h4>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">ID del Repuesto</span>
            <span class="info-value" id="partIdDisplay">#<?php echo htmlspecialchars($repuesto->id_repuesto); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Nombre del Repuesto</span>
            <span class="info-value" id="partNameDisplay"><?php echo htmlspecialchars($repuesto->nombre); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Descripción</span>
            <span class="info-value" id="partDescriptionDisplay"><?php echo htmlspecialchars($repuesto->descripcion); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Precio Unitario ($)</span>
            <span class="info-value" id="partPriceDisplay"><?php echo htmlspecialchars($repuesto->precio); ?></span>
        </div>
    </div>
</div>

<!-- Card del Formulario -->
<div class="card">
    <div class="card-body">
        <div class="form-container">
            <!-- Encabezado del formulario -->
            <div class="form-header">
                <div class="edit-part-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="form-header-info">
                    <h2>Editar Repuesto <span class="part-id" id="partId">ID: <?php echo htmlspecialchars($repuesto->id_repuesto); ?></span></h2>
                    <p>Modifique los campos necesarios para actualizar el repuesto</p>
                </div>
            </div>

            <!-- Formulario -->
            <form id="partForm" method="POST" action="../controladores/editar_repuesto.php">
                <?php if (isset($_SESSION['errores'])) : ?>
                    <div class="alert alert-danger">
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
                <input type="hidden" id="partIdHidden" name="id_repuesto" value="<?php echo htmlspecialchars($repuesto->id_repuesto); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Campo: Nombre del Repuesto -->
                <div class="form-group">
                    <label for="partName" class="required">Nombre del Repuesto</label>
                    <input type="text" id="partName" name="nombre_repuesto" class="form-control" value="<?= isset($datos_repuesto['nombre_repuesto']) ? htmlspecialchars($datos_repuesto['nombre_repuesto']) : htmlspecialchars($repuesto->nombre); ?>" required
                        placeholder="Ej: Filtro de aceite">
                    <span class="form-helper">Ingrese un nombre claro y descriptivo para el repuesto</span>
                </div>

                <!-- Campo: Descripción -->
                <div class="form-group">
                    <label for="partDescription" class="required">Descripción</label>
                    <textarea id="partDescription" name="descripcion" class="form-control" required
                        placeholder="Describa el repuesto..."><?= isset($datos_repuesto['descripcion']) ? htmlspecialchars($datos_repuesto['descripcion']) : htmlspecialchars($repuesto->descripcion); ?></textarea>
                    <span class="form-helper">Proporcione una descripción completa del repuesto</span>
                </div>

                <!-- Campo: Precio Unitario -->
                <div class="form-group">
                    <label for="partPrice" class="required">Precio Unitario ($)</label>
                    <input type="number" id="partPrice" name="precio" class="form-control" value="<?= isset($datos_repuesto['precio']) ? htmlspecialchars($datos_repuesto['precio']) : htmlspecialchars($repuesto->precio); ?>" required
                        min="0" step="0.01" placeholder="0.00">
                    <span class="form-helper">Ingrese el precio del repuesto en dólares</span>
                </div>

                <!-- Campo: Stock -->
                <div class="form-group">
                    <label for="partStock" class="required">Stock</label>
                    <input type="number" id="partStock" name="stock" class="form-control" value="<?= isset($datos_repuesto['stock']) ? htmlspecialchars($datos_repuesto['stock']) : htmlspecialchars($repuesto->stock); ?>" required
                        min="0" placeholder="0">
                    <span class="form-helper">Cantidad actual en inventario</span>
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
<?php include_once "../templates/footer.php"; ?>