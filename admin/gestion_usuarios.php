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

include_once '../templates/header.php';
include_once '../conexion/bd.php';

// Obtener listado de usuarios
// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// Contar total de usuarios activos para paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE estado='Activo'");
$sql_count->execute();
$total_registros_count = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_registros_count / $registros_por_pagina);

if ($pagina_actual > $total_paginas && $total_paginas > 0) {
    $pagina_actual = $total_paginas;
}

$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener listado de usuarios con paginación
$sql = $conexion->prepare("SELECT id_usuario, CONCAT(nombre,' ',apellido) as nombre_usuario, email, telefono, rol FROM usuarios WHERE estado='Activo' ORDER BY id_usuario DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$usuarios = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    /* Estilos específicos de esta página */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    /* Badges para roles */
    .role-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .role-admin {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
    }

    .role-mecanico {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .role-recepcion {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .role-almacen {
        background-color: rgba(255, 193, 7, 0.1);
        color: #b38f00;
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 15px;
        border-radius: 3px;
        font-size: 14px;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .page-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    */
</style>
<!-- Card de Usuarios -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-users me-2"></i>Lista de Usuarios del Sistema
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge" style="background-color: var(--primary); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                Mostrando <?php echo count($usuarios); ?> de <?php echo $total_registros_count; ?> usuarios
            </span>
            <a href="<?php echo $base_url ?>admin/agregar_usuario.php" type="button" class="btn btn-primary" id="addUserBtn">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table" id="usersTable">
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
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted mb-0">No hay usuarios registrados en el sistema.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $item): ?>
                            <tr>
                                <td>USER-#<?php echo htmlspecialchars($item->id_usuario); ?></td>
                                <td><?php echo htmlspecialchars($item->nombre_usuario); ?></td>
                                <td><?php echo htmlspecialchars($item->email); ?></td>
                                <td>
                                    <?php if ($item->rol == 'admin') { ?>
                                        <span class="role-badge role-admin">Administrador</span>
                                    <?php } elseif ($item->rol == 'mecanico') { ?>
                                        <span class="role-badge role-mecanico">Mecánico</span>
                                    <?php } elseif ($item->rol == 'recepcionista') { ?>
                                        <span class="role-badge role-recepcion">Recepcionista</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo $base_url; ?>admin/editar_usuario.php?id_usuario=<?php echo $item->id_usuario; ?>" class="btn btn-primary btn-sm user-edit-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($item->id_usuario != $_SESSION['id_usuario']) { ?>
                                            <form action="<?php echo $base_url; ?>controladores/eliminar_usuario.php" method="post" class="formEliminar" style="display:inline;">
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($item->id_usuario); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm user-delete-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <ul class="pagination">
                <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                </li>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </div>
    </div>
</div>
</div>
</div>
<script>
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // enviar el formulario si confirma
                }
            });
        });
    });
</script>
<?php include '../templates/footer.php'; ?>