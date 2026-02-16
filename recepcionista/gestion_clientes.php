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
include_once '../conexion/bd.php';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener total de registros para la paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM clientes");
$sql_count->execute();
$total_registros = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener los clientes con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT * FROM clientes ORDER BY fecha_registro DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$clientes = $sql->fetchAll(PDO::FETCH_OBJ);


// Obtener cantidad de clientes
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM clientes");
$sql->execute();
$totalClientes = $sql->fetchColumn();

?>
<style>
    /* Acciones en tabla */
    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .btn-icon {
        width: 35px;
        height: 35px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background-color: rgba(26, 58, 95, 0.1);
        color: #1a3a5f;
    }

    .btn-edit:hover {
        background-color: #1a3a5f;
        color: white;
    }

    .btn-view {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .btn-view:hover {
        background-color: #17a2b8;
        color: white;
    }

    .btn-delete {
        background-color: rgba(230, 57, 70, 0.1);
        color: #e63946;
    }

    .btn-delete:hover {
        background-color: #e63946;
        color: white;
    }

    /* Badges - Not globally in header */
    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Filtros y búsqueda */
    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #ddd;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: #1a3a5f;
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    /* Alerts */
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

    /* Formularios */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #343a40;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a3a5f;
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    /* Paginación */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
        padding: 20px 0;
    }

    .page-item {
        list-style: none;
    }

    .page-link {
        display: block;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        color: #1a3a5f;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background-color: #f8f9fc;
    }

    .page-item.active .page-link {
        background-color: #1a3a5f;
        color: white;
        border-color: #1a3a5f;
    }

    @media (max-width: 992px) {
        .filters {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- Filtros y búsqueda -->
<div class="filters">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar cliente...">
    </div>
    <div class="filter-group">
        <select class="form-control" id="filterStatus" style="width: 200px;">
            <option value="">Todos los estados</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
        </select>
    </div>
    <a href="agregar_cliente.php" type="button" class="btn btn-primary" id="btnNuevoCliente">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </a>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lista de Clientes</h2>
        <div class="card-actions">
            <span class="badge badge-success">Mostrando <?php echo count($clientes); ?> de <?php echo $total_registros; ?> clientes</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="clientesTable">
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
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Cédula/RUC</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <?php if (count($clientes) > 0): ?>
                    <?php foreach ($clientes as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->nombre); ?></td>
                            <td><?php echo htmlspecialchars($item->apellido); ?></td>
                            <td><?php echo htmlspecialchars($item->cedula_ruc); ?></td>
                            <td><?php echo htmlspecialchars($item->telefono); ?></td>
                            <td><?php echo htmlspecialchars($item->email); ?></td>
                            <td><?php echo htmlspecialchars($item->direccion); ?></td>
                            <td><?php echo date('d/m/Y h:i A', strtotime($item->fecha_registro)); ?></td>
                            <td>
                                <div class="actions">
                                    <a type="button" href="vehiculos_cliente.php?id_cliente=<?php echo base64_encode($item->id_cliente); ?>" class="btn-icon btn-view" title="Ver Vehículos">
                                        <i class="fas fa-car"></i>
                                    </a>
                                    <a type="button" href="editar_cliente.php?id_cliente=<?php echo base64_encode($item->id_cliente); ?>" class="btn-icon btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../controladores/eliminar_cliente.php" method="post" class="formEliminar">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id_cliente" value="<?php echo $item->id_cliente; ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 60px 20px;">
                            <div style="color: #6c757d;">
                                <i class="fas fa-users" style="font-size: 4rem; opacity: 0.3; margin-bottom: 20px;"></i>
                                <h3 style="color: #343a40; margin-bottom: 10px;">No hay clientes registrados</h3>
                                <p style="margin-bottom: 25px;">Aún no tienes clientes en el sistema.</p>
                                <a href="agregar_cliente.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Primer Cliente
                                </a>
                            </div>
                        </td>
                    </tr>
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
</main>
</div>
<script>
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar este cliente?',
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
<?php
include '../templates/footer.php';
?>
<script src="../js/recepcionista/gestionClientes.js"></script>