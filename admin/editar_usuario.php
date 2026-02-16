<?php
require_once '../autorizacion/auth.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../acceso_denegado.php"); // si no lo mandamos al login
    exit();
}

include_once '../conexion/bd.php';

$id_usuario = $_GET['id_usuario']; // obtenemos el id del usuario a editar


// Obtener datos de ese usuario para editar
$sql = $conexion->prepare("SELECT id_usuario, nombre, apellido, email, telefono, rol FROM usuarios WHERE id_usuario = :id_usuario");
$sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_OBJ);

if (!$usuario) { // si no existe el usuario redirigimos a la gestion de usuarios
    $_SESSION['errores'] = ["Usuario no encontrado"];
    header("Location: ../admin/gestion_usuarios.php");
    exit();
}

include_once '../templates/header.php';

// Mantener Persistencia de datos del formulario
$datos_usuario = isset($_SESSION['datos_usuario_edit_form']) ? $_SESSION['datos_usuario_edit_form'] : [];
unset($_SESSION['datos_usuario_edit_form']);
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

    .user-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 700;
        box-shadow: var(--shadow);
    }

    .user-info-header h2 {
        color: var(--primary);
        margin-bottom: 5px;
        font-size: 1.8rem;
    }

    .user-info-header p {
        color: var(--gray);
        margin-bottom: 10px;
    }

    .user-id {
        display: inline-block;
        background-color: #f8f9fc;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
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

    .form-control:disabled {
        background-color: #f8f9fc;
        cursor: not-allowed;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
        padding-right: 40px;
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

    /* Badges para roles */
    .role-badge {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-right: 10px;
    }

    .role-admin {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
    }

    .role-recepcionista {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .role-mecanico {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }


    /* Footer Style Override (if needed, else remove) */

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

        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
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

        .card-body {
            padding: 20px;
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

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .user-avatar-large {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
        }
    }
</style>

<!-- Card del Formulario -->
<div class="card">
    <div class="card-body">
        <div class="form-container">
            <!-- Encabezado del formulario -->
            <div class="form-header">
                <div class="user-avatar-large" id="userAvatar"><?= htmlspecialchars($usuario->nombre[0]) . htmlspecialchars($usuario->apellido[0]) ?></div>
                <div class="user-info-header">
                    <h2 id="userFullName"><?= htmlspecialchars($usuario->nombre) . ' ' . htmlspecialchars($usuario->apellido) ?></h2>
                    <p id="userEmail"><?= htmlspecialchars($usuario->email) ?></p>
                    <div>
                        <span class="role-badge role-admin" id="currentRoleBadge"><?= htmlspecialchars(ucfirst($usuario->rol)) ?></span>
                        <span class="user-id">ID: <span id="userId"><?= htmlspecialchars($usuario->id_usuario) ?></span></span>
                    </div>
                </div>
            </div>

            <!-- Formulario de edición -->
            <form id="editUserForm" action="../controladores/editar_usuario.php" method="post">
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
                <input type="hidden" name="id_usuario" id="userId" value="<?= htmlspecialchars($usuario->id_usuario) ?>">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <!-- Fila 1: Nombre y Apellido -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName" class="required">Nombre</label>
                        <input type="text" name="nombre" id="firstName" class="form-control" required
                            placeholder="Ingrese el nombre" value="<?= isset($datos_usuario['nombre']) ? htmlspecialchars($datos_usuario['nombre']) : htmlspecialchars($usuario->nombre) ?>">
                        <span class="form-helper">Nombre legal del usuario</span>
                    </div>
                    <div class="form-group">
                        <label for="lastName" class="required">Apellido</label>
                        <input type="text" name="apellido" id="lastName" class="form-control" required
                            placeholder="Ingrese el apellido" value="<?= isset($datos_usuario['apellido']) ? htmlspecialchars($datos_usuario['apellido']) : htmlspecialchars($usuario->apellido) ?>">
                        <span class="form-helper">Apellido legal del usuario</span>
                    </div>
                </div>

                <!-- Fila 2: Email y Teléfono -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required
                            placeholder="usuario@ejemplo.com" value="<?= isset($datos_usuario['email']) ? htmlspecialchars($datos_usuario['email']) : htmlspecialchars($usuario->email) ?>">
                        <span class="form-helper">Correo electrónico para inicio de sesión</span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="text" name="telefono" id="phone" class="form-control"
                            placeholder="+1 234 567 890" value="<?= isset($datos_usuario['telefono']) ? htmlspecialchars($datos_usuario['telefono']) : htmlspecialchars($usuario->telefono) ?>">
                        <span class="form-helper">Número de contacto opcional</span>
                    </div>
                </div>

                <!-- Rol (Select) -->
                <div class="form-group full-width">
                    <label for="role" class="required">Rol del Usuario</label>
                    <select name="rol" id="role" class="form-control" required>
                        <option value="">Seleccione un rol</option>
                        <option value="admin" <?= (isset($datos_usuario['rol']) ? $datos_usuario['rol'] : $usuario->rol) === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="recepcionista" <?= (isset($datos_usuario['rol']) ? $datos_usuario['rol'] : $usuario->rol) === 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                        <option value="mecanico" <?= (isset($datos_usuario['rol']) ? $datos_usuario['rol'] : $usuario->rol) === 'mecanico' ? 'selected' : '' ?>>Mecánico</option>
                    </select>
                    <span class="form-helper">
                        <strong>Administrador:</strong> Acceso completo al sistema<br>
                        <strong>Recepcionista:</strong> Gestión de clientes y citas<br>
                        <strong>Mecánico:</strong> Gestión de servicios y vehículos
                    </span>
                </div>

                <!-- Acciones del formulario -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn" onclick="history.back()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="reset" class="btn btn-danger" id="resetBtn">
                        <i class="fas fa-redo"></i> Restablecer
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>

<?php include_once "../templates/footer.php"; ?>