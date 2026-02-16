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

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener cantidad total de vehiculos del cliente para la paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) FROM vehiculos WHERE id_cliente = :id_cliente");
$sql_count->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$sql_count->execute();
$total_vehiculos_count = $sql_count->fetchColumn();
$total_paginas = ceil($total_vehiculos_count / $registros_por_pagina);

// Obtener los vehiculos del cliente con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT vehiculos.id_vehiculo as id_vehiculo, vehiculos.placa as placa,vehiculos.modelo as modelo,vehiculos.marca as marca, vehiculos.tipo as tipo, vehiculos.anio as anio FROM vehiculos INNER JOIN clientes ON vehiculos.id_cliente = clientes.id_cliente WHERE vehiculos.id_cliente = :id_cliente ORDER BY vehiculos.id_vehiculo DESC LIMIT :limit OFFSET :offset");
$sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$vehiculos = $sql->fetchAll(PDO::FETCH_OBJ);


// Obtener cantidad de vehiculos del cliente
$sql = $conexion->prepare("SELECT COUNT(*) FROM vehiculos WHERE id_cliente = :id_cliente");
$sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
$sql->execute();
$cantidad_vehiculos = $sql->fetchColumn();
?>

<style>
    /* Layout styles removed (duplicates) */

    /* Breadcrumb */
    .breadcrumb {
        background-color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .breadcrumb ul {
        display: flex;
        list-style: none;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb a:hover {
        color: var(--secondary);
    }

    .breadcrumb .separator {
        color: var(--gray);
    }

    .breadcrumb .current {
        color: var(--dark);
        font-weight: 600;
    }

    /* Información del cliente */
    .client-header {
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

    /* Card and Table styles removed (duplicates) */

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
        transition: var(--transition);
    }

    .btn-edit {
        background-color: rgba(26, 58, 95, 0.1);
        color: var(--primary);
    }

    .btn-edit:hover {
        background-color: var(--primary);
        color: white;
    }

    .btn-delete {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
    }

    .btn-delete:hover {
        background-color: var(--danger);
        color: white;
    }

    /* Button styles removed (duplicates) */

    /* Badges */
    .badge {
        display: inline-block;
        padding: 5px 12px;
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

    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Modal styles removed (duplicates) */

    /* Formularios */
    .form-group {
        margin-bottom: 20px;
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Contador de vehículos */
    .vehicle-count {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .vehicle-count i {
        font-size: 2.5rem;
        opacity: 0.9;
    }

    .vehicle-count-info h3 {
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .vehicle-count-info p {
        opacity: 0.9;
        margin: 0;
    }

    /* Filtros */
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
        padding: 12px 15px 12px 45px;
        border: 1px solid #ddd;
        border-radius: 6px;
        transition: var(--transition);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    /* User menu */
    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: var(--dark);
    }

    .user-role {
        font-size: 0.85rem;
        color: var(--gray);
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
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }

    .page-link:hover {
        background-color: #f8f9fc;
    }

    .page-item.active .page-link {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Responsive */
    /* Responsive sidebar styles removed (duplicates) */

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .modal-content {
            margin: 20px;
        }

        .client-info {
            grid-template-columns: 1fr;
        }

        .filters {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .vehicle-count {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }

        .vehicle-count i {
            font-size: 2rem;
        }

        .actions {
            flex-direction: column;
            gap: 5px;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }
    }

    /* Animaciones */
    /* Modal animation removed (duplicate) */

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
<!-- Contador de vehículos -->
<div class="vehicle-count">
    <i class="fas fa-car"></i>
    <div class="vehicle-count-info">
        <h3><?php echo htmlspecialchars($cantidad_vehiculos); ?> Vehículos</h3>
        <p>Registrados para este cliente</p>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filters">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por placa, marca o modelo...">
    </div>
    <div class="filter-group">
        <select class="form-control" id="filterTipo" style="width: 200px;">
            <option value="">Todos los tipos</option>
            <option value="sedan">Sedán</option>
            <option value="suv">SUV</option>
            <option value="pickup">Pickup</option>
            <option value="hatchback">Hatchback</option>
        </select>
    </div>
    <a href="agregar_vehiculo_cliente.php?id_cliente=<?php echo base64_encode(htmlspecialchars($id_cliente)); ?>" type="button" class="btn btn-primary" id="btnNuevoVehiculo">
        <i class="fas fa-plus"></i> Nuevo Vehículo
    </a>
</div>

<!-- Tabla de vehículos -->
<div class="card">
    <div class="card-header">
        <button class="btn btn-outline" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="card-title">Lista de Vehículos</h2>
        <div class="card-actions">
            <span class="badge badge-success">Mostrando <?php echo count($vehiculos); ?> de <?php echo $total_vehiculos_count; ?> vehículos</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="vehiculosTable">
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
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Tipo</th>
                        <th>Año</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vehiculos) > 0): ?>
                        <?php foreach ($vehiculos as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->placa); ?></strong>
                                    <br>
                                    <small class="badge badge-success">Activo</small>
                                </td>
                                <td><?php echo htmlspecialchars($item->marca); ?></td>
                                <td><?php echo htmlspecialchars($item->modelo); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($item->tipo); ?></span></td>
                                <td><?php echo htmlspecialchars($item->anio); ?></td>
                                <td>
                                    <div class="actions">
                                        <a type="button" href="editar_vehiculos_cliente.php?id_vehiculo=<?php echo base64_encode(htmlspecialchars($item->id_vehiculo)); ?>&id_cliente=<?php echo base64_encode(htmlspecialchars($id_cliente)); ?>" class="btn-icon btn-edit" title="Editar vehículo">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="../controladores/eliminar_vehiculo_cliente.php" method="post">
                                            <input type="hidden" name="id_vehiculo" value="<?php echo htmlspecialchars($item->id_vehiculo); ?>">
                                            <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($id_cliente); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Eliminar vehículo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px 20px;">
                                <div style="color: var(--gray);">
                                    <i class="fas fa-car" style="font-size: 4rem; opacity: 0.3; margin-bottom: 20px;"></i>
                                    <h3 style="color: var(--dark); margin-bottom: 10px;">No hay vehículos registrados</h3>
                                    <p style="margin-bottom: 25px;">Este cliente aún no tiene vehículos asociados a su cuenta.</p>
                                    <button class="btn btn-primary" id="btnNuevoVehiculoEmpty">
                                        <i class="fas fa-plus"></i> Agregar Primer Vehículo
                                    </button>
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
                    <a class="page-link" href="?id_cliente=<?php echo urlencode(base64_encode($id_cliente)); ?>&pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                </li>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                        <a class="page-link" href="?id_cliente=<?php echo urlencode(base64_encode($id_cliente)); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?id_cliente=<?php echo urlencode(base64_encode($id_cliente)); ?>&pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </div>
    </div>
</div>
</div>
</main>
</div>

<?php include_once '../templates/footer.php'; ?>