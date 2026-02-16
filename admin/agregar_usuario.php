<?php
require_once '../autorizacion/auth.php';

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../acceso_denegado.php"); // si no lo mandamos a la pagina de acceso denegado
    exit();
}

include_once '../templates/header.php';

// Mecanismo de Persistencia de Datos
// Si hay errores de validación, los valores ingresados se recuperan de la sesión ($datos_usuario)
// y se asignan a los atributos 'value' de los inputs para que el usuario no tenga que reescribirlos.
$datos_usuario = isset($_SESSION['datos_usuario_form']) ? $_SESSION['datos_usuario_form'] : [];
unset($_SESSION['datos_usuario_form']);
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

    .new-user-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
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

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
        cursor: pointer;
        transition: var(--transition);
    }

    .input-with-icon i:hover {
        color: var(--primary);
    }

    .form-helper {
        display: block;
        margin-top: 8px;
        font-size: 0.875rem;
        color: var(--gray);
    }

    .password-strength {
        margin-top: 8px;
        height: 5px;
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: var(--transition);
        border-radius: 3px;
    }

    .password-strength-bar.weak {
        width: 33%;
        background-color: var(--danger);
    }

    .password-strength-bar.medium {
        width: 66%;
        background-color: var(--warning);
    }

    .password-strength-bar.strong {
        width: 100%;
        background-color: var(--success);
    }

    .password-requirements {
        margin-top: 10px;
        padding: 15px;
        background-color: #f8f9fc;
        border-radius: 8px;
        border-left: 4px solid var(--primary);
    }

    .password-requirements h5 {
        margin-bottom: 10px;
        color: var(--primary);
        font-size: 0.95rem;
    }

    .password-requirements ul {
        list-style: none;
        padding-left: 0;
    }

    .password-requirements li {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.875rem;
        color: var(--gray);
    }

    .password-requirements li i {
        font-size: 0.8rem;
    }

    .password-requirements li.valid {
        color: var(--success);
    }

    .password-requirements li.invalid {
        color: var(--danger);
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

        .new-user-icon {
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
                <div class="new-user-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="form-header-info">
                    <h2>Nuevo Usuario del Sistema</h2>
                    <p>Complete todos los campos para registrar un nuevo usuario en el sistema</p>
                </div>
            </div>

            <!-- Formulario para nuevo usuario -->
            <form id="newUserForm" method="POST" action="../controladores/agregar_usuario.php">
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
                <!-- Fila 1: Nombre y Apellido -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName" class="required">Nombre</label>
                        <input type="text" id="firstName" name="nombre" class="form-control" required
                            placeholder="Ingrese el nombre" value="<?= isset($datos_usuario['nombre']) ? htmlspecialchars($datos_usuario['nombre']) : '' ?>">
                        <span class="form-helper">Nombre legal del usuario</span>
                    </div>
                    <div class="form-group">
                        <label for="lastName" class="required">Apellido</label>
                        <input type="text" id="lastName" name="apellido" class="form-control" required
                            placeholder="Ingrese el apellido" value="<?= isset($datos_usuario['apellido']) ? htmlspecialchars($datos_usuario['apellido']) : '' ?>">
                        <span class="form-helper">Apellido legal del usuario</span>
                    </div>
                </div>

                <!-- Fila 2: Email y Teléfono -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required
                            placeholder="usuario@ejemplo.com" value="<?= isset($datos_usuario['email']) ? htmlspecialchars($datos_usuario['email']) : '' ?>">
                        <span class="form-helper">Correo electrónico para inicio de sesión</span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="text" id="phone" name="telefono" class="form-control"
                            placeholder="+1 234 567 890" value="<?= isset($datos_usuario['telefono']) ? htmlspecialchars($datos_usuario['telefono']) : '' ?>">
                        <span class="form-helper">Número de contacto opcional</span>
                    </div>
                </div>

                <!-- Rol (Select) -->
                <div class="form-group full-width">
                    <label for="role" class="required">Rol del Usuario</label>
                    <select id="role" name="rol" class="form-control" required>
                        <option value="">Seleccione un rol</option>
                        <option value="admin" <?= (isset($datos_usuario['rol']) && $datos_usuario['rol'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                        <option value="recepcionista" <?= (isset($datos_usuario['rol']) && $datos_usuario['rol'] == 'recepcionista') ? 'selected' : '' ?>>Recepcionista</option>
                        <option value="mecanico" <?= (isset($datos_usuario['rol']) && $datos_usuario['rol'] == 'mecanico') ? 'selected' : '' ?>>Mecánico</option>
                    </select>
                    <span class="form-helper">
                        <strong>Administrador:</strong> Acceso completo al sistema<br>
                        <strong>Recepcionista:</strong> Gestión de clientes y citas<br>
                        <strong>Mecánico:</strong> Gestión de servicios y vehículos
                    </span>
                </div>

                <!-- Contraseña -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Contraseña</label>
                        <div class="input-with-icon">
                            <input type="password" id="password" name="password" class="form-control" required
                                placeholder="Ingrese la contraseña" minlength="5">
                            <i class="fas fa-eye" id="togglePassword1"></i>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>

                        <!-- Indicadores de fortaleza de contraseña -->
                        <div class="password-requirements">
                            <h5>Requisitos de contraseña:</h5>
                            <ul>
                                <li id="reqLength">
                                    <i class="fas fa-circle"></i>
                                    <span>Mínimo 5 caracteres</span>
                                </li>
                                <li id="reqUppercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Al menos una mayúscula</span>
                                </li>
                                <li id="reqLowercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Al menos una minúscula</span>
                                </li>
                                <li id="reqNumber">
                                    <i class="fas fa-circle"></i>
                                    <span>Al menos un número</span>
                                </li>
                                <li id="reqSpecial">
                                    <i class="fas fa-circle"></i>
                                    <span>Al menos un carácter especial</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="required">Confirmar Contraseña</label>
                        <div class="input-with-icon">
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required
                                placeholder="Repita la contraseña">
                            <i class="fas fa-eye" id="togglePassword2"></i>
                        </div>
                        <span class="form-helper" id="passwordMatchText">Las contraseñas deben coincidir</span>
                    </div>
                </div>

                <!-- Acciones del formulario -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn" onclick="history.back()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="reset" class="btn btn-danger" id="resetBtn">
                        <i class="fas fa-redo"></i> Limpiar Formulario
                    </button>
                    <button type="submit" class="btn btn-success" id="saveBtn">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Información de ayuda -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-info-circle"></i> Información Importante
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <h4 style="margin-bottom: 15px; color: var(--primary);">
                    <i class="fas fa-shield-alt"></i> Seguridad
                </h4>
                <ul style="color: var(--gray); padding-left: 20px;">
                    <li>La contraseña debe tener al menos 8 caracteres</li>
                    <li>Se recomienda usar una combinación de letras, números y símbolos</li>
                    <li>El usuario deberá cambiar su contraseña en el primer inicio de sesión</li>
                    <li>Las credenciales se enviarán al correo electrónico proporcionado</li>
                </ul>
            </div>
            <div class="form-group">
                <h4 style="margin-bottom: 15px; color: var(--primary);">
                    <i class="fas fa-user-tag"></i> Roles del Sistema
                </h4>
                <div style="background-color: #f8f9fc; padding: 15px; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <div style="width: 12px; height: 12px; background-color: var(--danger); border-radius: 50%;"></div>
                        <span><strong>Administrador:</strong> Acceso completo a todas las funciones del sistema</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <div style="width: 12px; height: 12px; background-color: var(--info); border-radius: 50%;"></div>
                        <span><strong>Recepcionista:</strong> Gestiona clientes, citas y facturación</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 12px; height: 12px; background-color: var(--success); border-radius: 50%;"></div>
                        <span><strong>Mecánico:</strong> Gestiona servicios, vehículos y reparaciones</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php include_once "../templates/footer.php"; ?>