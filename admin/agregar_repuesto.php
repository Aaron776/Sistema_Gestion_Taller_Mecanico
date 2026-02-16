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
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_repuesto)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_repuesto = isset($_SESSION['datos_repuesto_form']) ? $_SESSION['datos_repuesto_form'] : [];
unset($_SESSION['datos_repuesto_form']);
?>
<style>
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

    .new-part-icon {
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
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

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        padding-right: 40px;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
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

    /* Info adicional */
    .additional-info {
        background-color: #f8f9fc;
        padding: 25px;
        border-radius: 8px;
        margin-top: 30px;
        border-left: 4px solid var(--primary);
    }

    .additional-info h4 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .additional-info p {
        color: var(--gray);
        margin-bottom: 10px;
        font-size: 0.95rem;
        line-height: 1.6;
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

        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
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
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .new-part-icon {
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
                <div class="new-part-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="form-header-info">
                    <h2>Nuevo Repuesto</h2>
                    <p>Complete todos los campos para agregar un nuevo repuesto al inventario</p>
                </div>
            </div>

            <!-- Formulario -->
            <form id="partForm" action="../controladores/agregar_repuesto.php" method="POST">
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
                <!-- Fila 1: Nombre y Categoría -->
                <div class="form-group">
                    <label for="partName" class="required">Nombre del Repuesto</label>
                    <input type="text" id="partName" name="nombre_repuesto" class="form-control" required
                        placeholder="Ej: Filtro de aceite" value="<?= isset($datos_repuesto['nombre_repuesto']) ? htmlspecialchars($datos_repuesto['nombre_repuesto']) : '' ?>">
                    <span class="form-helper">Ingrese un nombre claro y descriptivo para el repuesto</span>
                </div>

                <!-- Campo: Descripción -->
                <div class="form-group full-width">
                    <label for="partDescription" class="required">Descripción</label>
                    <textarea id="partDescription" name="descripcion" class="form-control" required
                        placeholder="Describa el repuesto, incluyendo especificaciones técnicas, compatibilidad, etc..."><?= isset($datos_repuesto['descripcion']) ? htmlspecialchars($datos_repuesto['descripcion']) : '' ?></textarea>
                    <span class="form-helper">Proporcione una descripción completa del repuesto</span>
                </div>

                <!-- Fila 2: Precio y Stock -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="partPrice" class="required">Precio Unitario ($)</label>
                        <input type="number" id="partPrice" name="precio" class="form-control" required
                            min="0" step="0.01" placeholder="0.00" value="<?= isset($datos_repuesto['precio']) ? htmlspecialchars($datos_repuesto['precio']) : '' ?>">
                        <span class="form-helper">Precio de compra del repuesto en dólares</span>
                    </div>
                    <div class="form-group">
                        <label for="partStock" class="required">Stock Inicial</label>
                        <input type="number" id="partStock" name="stock" class="form-control" required
                            min="0" placeholder="0" value="<?= isset($datos_repuesto['stock']) ? htmlspecialchars($datos_repuesto['stock']) : '' ?>">
                        <span class="form-helper">Cantidad inicial en inventario</span>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="additional-info">
                    <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
                    <p><strong>Stock Mínimo:</strong> Se recomienda establecer un stock mínimo para recibir alertas cuando el inventario sea bajo.</p>
                    <p><strong>Categorías:</strong> Seleccionar la categoría correcta ayuda a organizar mejor el inventario.</p>
                    <p><strong>Código de Parte:</strong> Mantener un código único para cada repuesto facilita la búsqueda y gestión.</p>
                </div>

                <!-- Acciones del formulario -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Guardar Repuesto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
<?php include_once "../templates/footer.php"; ?>