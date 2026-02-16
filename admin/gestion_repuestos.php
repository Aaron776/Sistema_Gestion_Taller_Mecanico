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
include_once "../conexion/bd.php";

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener total de repuestos para la paginación
$sql_count = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos");
$sql_count->execute();
$total_registros_count = $sql_count->fetch(PDO::FETCH_OBJ)->total;
$total_paginas = ceil($total_registros_count / $registros_por_pagina);

// Obtener lista de repuestos con LIMIT y OFFSET
$sql = $conexion->prepare("SELECT id_repuesto, nombre, descripcion, precio, stock FROM repuestos ORDER BY id_repuesto DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$repuestos = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener total de repuestos
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos");
$sql->execute();
$totalRepuestos = $sql->fetch(PDO::FETCH_OBJ)->total;

// Obtener total de repuestos con stock bajo (menor a 10) 
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos WHERE stock < 10");
$sql->execute();
$totalBajoStock = $sql->fetch(PDO::FETCH_OBJ)->total;

// Obtener total de repuestos con stock agotado
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos WHERE stock = 0");
$sql->execute();
$totalAgotados = $sql->fetch(PDO::FETCH_OBJ)->total;

// Obtener suma total del los precios de los repuestos
$sql = $conexion->prepare("SELECT SUM(precio) as total FROM repuestos");
$sql->execute();
$totalPrecios = $sql->fetch(PDO::FETCH_OBJ)->total;

// Obtener total de repuestos con stock alto
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos WHERE stock > 15");
$sql->execute();
$totalAltoStock = $sql->fetch(PDO::FETCH_OBJ)->total;
?>

<style>
    /* Tabla */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }



    /* Botones */
    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #0f2a46;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 58, 95, 0.2);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    /* Botones de Acción (Iconos) */
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
        text-decoration: none;
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

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    .table {
        min-width: 900px;
    }

    /* Estilos específicos para columnas */
    .part-name {
        font-weight: 600;
        color: var(--primary);
    }

    .part-description {
        color: var(--gray);
        font-size: 0.95rem;
        line-height: 1.5;
        max-width: 300px;
    }

    .part-price {
        font-weight: 700;
        color: var(--success);
        font-size: 1.1rem;
    }

    .part-price::before {
        content: "$ ";
    }

    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .stock-high {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .stock-medium {
        background-color: rgba(255, 193, 7, 0.1);
        color: #b38f00;
    }

    .stock-low {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }

    .stock-out {
        background-color: rgba(108, 117, 125, 0.1);
        color: var(--gray);
    }

    .actions {
        display: flex;
        gap: 15px;
    }

    /* Badges para categorías */
    .category-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 5px;
    }

    .category-motor {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
    }

    .category-frenos {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .category-electrica {
        background-color: rgba(255, 193, 7, 0.1);
        color: #b38f00;
    }

    .category-filtros {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .category-suspension {
        background-color: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }

    /* Estadísticas */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
    }

    .stat-info h3 {
        font-size: 1.8rem;
        color: var(--primary);
        margin-bottom: 5px;
    }

    .stat-info p {
        color: var(--gray);
        font-size: 0.9rem;
        margin: 0;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
    }

    .stat-icon.success {
        background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
    }

    .stat-icon.danger {
        background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
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
    @media (max-width: 992px) {
        .stats-cards {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: 1fr 1fr;
        }

        .actions {
            flex-direction: column;
            gap: 10px;
        }

        .actions .btn-link {
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }
    }
</style>


<!-- Estadísticas -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="totalParts"><?php echo htmlspecialchars($totalRepuestos); ?></h3>
            <p>Repuestos en Inventario</p>
        </div>
        <div class="stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="lowStock"><?php echo htmlspecialchars($totalBajoStock); ?></h3>
            <p>Bajo Stock</p>
        </div>
        <div class="stat-icon danger">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="totalValue">$<?php echo htmlspecialchars(number_format($totalPrecios, 2)); ?></h3>
            <p>Valor Total</p>
        </div>
        <div class="stat-icon success">
            <i class="fas fa-dollar-sign"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3 id="outOfStock"><?php echo htmlspecialchars($totalAgotados); ?></h3>
            <p>Agotados</p>
        </div>
        <div class="stat-icon warning">
            <i class="fas fa-times-circle"></i>
        </div>
    </div>

</div>

<!-- Card de Repuestos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-cogs me-2"></i>Catálogo de Repuestos
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <span class="badge" style="background-color: var(--primary); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem;">
                Mostrando <?php echo count($repuestos); ?> de <?php echo $total_registros_count; ?> repuestos
            </span>
            <a href="../ReportesPDF/reporteRespuestos.php" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="../ReportesExcel/reporteRepuestos.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="agregar_repuesto.php" class="btn btn-primary" id="addPartBtn">
                <i class="fas fa-plus"></i> Nuevo Repuesto
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($repuestos)) {  ?>
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-gray-300 mb-3" style="color: #dddfeb;"></i>
                <h4 class="text-gray-500">No hay repuestos registrados</h4>
                <p class="text-muted">Agrega un nuevo repuesto para comenzar a gestionar el catálogo.</p>
            </div>
        <?php } else { ?>
            <div class="table-container">
                <table class="table" id="partsTable">
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
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio Unitario</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="partsTableBody">
                        <?php foreach ($repuestos as $item) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item->nombre); ?></td>
                                <td><?php echo htmlspecialchars($item->descripcion); ?></td>
                                <td>$<?php echo htmlspecialchars($item->precio); ?></td>
                                <td <?php
                                    if ($item->stock < 10) {
                                        echo 'style="color: #e63946; font-weight: bold;"';
                                    } elseif ($item->stock <= 15) {
                                        echo 'style="color: #ffc107; font-weight: bold;"';
                                    }
                                    ?>>
                                    <?php echo htmlspecialchars($item->stock); ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="editar_repuesto.php?id_repuesto=<?php echo htmlspecialchars(urlencode(base64_encode($item->id_repuesto))); ?>" class="btn-icon btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="../controladores/eliminar_repuesto.php" method="POST" class="formEliminar" style="margin: 0;">
                                            <input type="hidden" name="id_repuesto" value="<?php echo htmlspecialchars($item->id_repuesto); ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                                            <button type="submit" class="btn-icon btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
        <?php } ?>
    </div>
</div>
</div>
</div>
<script>
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar este repuesto?',
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
<?php include_once "../templates/footer.php"; ?>