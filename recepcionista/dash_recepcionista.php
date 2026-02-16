<?php
require_once '../autorizacion/auth.php';

// Verificar que tenga rol de recepcionista
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'recepcionista') {
    header("Location: ../acceso_denegado.php");
    exit();
}

include '../templates/header.php';
include_once '../conexion/bd.php';

// 1. Total Clientes
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM clientes");
$sql->execute();
$totalClientes = $sql->fetch(PDO::FETCH_OBJ)->total;

// 2. Ordenes Pendientes
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado = 'pendiente'");
$sql->execute();
$totalPendientes = $sql->fetch(PDO::FETCH_OBJ)->total;

// 3. Ordenes En Proceso
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado = 'en_proceso'");
$sql->execute();
$totalProceso = $sql->fetch(PDO::FETCH_OBJ)->total;

// 4. Ordenes Finalizadas
$sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado = 'finalizado'");
$sql->execute();
$totalFinalizadas = $sql->fetch(PDO::FETCH_OBJ)->total;

// Datos para el Gráfico (Ordenes por Estado)
$sql = $conexion->prepare("SELECT estado, COUNT(*) as total FROM ordenes_trabajo GROUP BY estado");
$sql->execute();
$ordenesPorEstado = $sql->fetchAll(PDO::FETCH_OBJ);

$estadosOrdenes = [];
$cantidadesOrdenes = [];
foreach ($ordenesPorEstado as $orden) {
    $estadosOrdenes[] = ucfirst($orden->estado);
    $cantidadesOrdenes[] = $orden->total;
}

// NUEVA CONSULTA: Órdenes por mes (últimos 6 meses)
$sql = $conexion->prepare("
    SELECT 
        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
        COUNT(*) as total
    FROM ordenes_trabajo 
    WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
    ORDER BY mes
");
$sql->execute();
$ordenesPorMes = $sql->fetchAll(PDO::FETCH_OBJ);

// Preparar datos para el gráfico de barras
$meses = [];
$totalesPorMes = [];
$mesesEnEspanol = [
    '01' => 'Ene',
    '02' => 'Feb',
    '03' => 'Mar',
    '04' => 'Abr',
    '05' => 'May',
    '06' => 'Jun',
    '07' => 'Jul',
    '08' => 'Ago',
    '09' => 'Sep',
    '10' => 'Oct',
    '11' => 'Nov',
    '12' => 'Dic'
];

foreach ($ordenesPorMes as $mesData) {
    list($anio, $mesNumero) = explode('-', $mesData->mes);
    $mesNombre = $mesesEnEspanol[$mesNumero] . ' ' . substr($anio, 2);
    $meses[] = $mesNombre;
    $totalesPorMes[] = $mesData->total;
}

// Si no hay datos, mostrar un mensaje
$hayDatosMensuales = !empty($ordenesPorMes);
?>
<style>
    .chart-container {
        position: relative;
        height: 300px;
        /* Slightly taller for better readability */
        width: 100%;
    }

    /* Cards responsive adjustments */
    .charts-row .card {
        flex: 1;
        min-width: 0;
        /* Changed from 300px to avoid overflow */
    }

    /* Header responsive adjustments */
    .card-header {
        flex-wrap: wrap;
        gap: 15px;
        justify-content: space-between;
    }

    /* Media queries for specific dashboard refinements */
    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }

    @media (max-width: 576px) {
        .info-cards {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>


<!-- Cards de Información -->
<div class="info-cards">
    <div class="info-card primary">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($totalClientes); ?></h3>
            <p>Clientes Totales</p>
        </div>
        <i class="fas fa-users"></i>
    </div>
    <div class="info-card warning">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($totalPendientes); ?></h3>
            <p>Ordenes Pendientes</p>
        </div>
        <i class="fas fa-clock"></i>
    </div>
    <div class="info-card info">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($totalProceso); ?></h3>
            <p>En Proceso</p>
        </div>
        <i class="fas fa-cogs"></i>
    </div>
    <div class="info-card success">
        <div class="info-card-content">
            <h3><?php echo htmlspecialchars($totalFinalizadas); ?></h3>
            <p>Finalizadas</p>
        </div>
        <i class="fas fa-check-circle"></i>
    </div>
</div>

<!-- Gráficos y Tablas -->
<div class="charts-row">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Estado de Ordenes</div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- NUEVO GRÁFICO DE BARRAS REEMPLAZANDO ACCIONES RÁPIDAS -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Órdenes por Mes (Últimos 6 meses)</div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="monthlyOrdersChart"></canvas>
            </div>
            <?php if (!$hayDatosMensuales): ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                    <p>No hay datos de órdenes en los últimos 6 meses</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabla de Ordenes Pendientes -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Ordenes Recientes (Pendientes)</div>
        <button class="btn btn-primary" id="newOrderBtn">
            <i class="fas fa-plus"></i> Nueva Orden
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filas de ejemplo estáticas -->
                    <tr>
                        <td>#1023</td>
                        <td>Juan Pérez</td>
                        <td>Chevrolet Aveo</td>
                        <td>Mantenimiento General</td>
                        <td>18/06/2023</td>
                        <td><span class="status pending">Pendiente</span></td>
                        <td>
                            <button class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>#1024</td>
                        <td>Ana Gómez</td>
                        <td>Mazda 3</td>
                        <td>Cambio de Aceite</td>
                        <td>18/06/2023</td>
                        <td><span class="status pending">Pendiente</span></td>
                        <td>
                            <button class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>#1025</td>
                        <td>Carlos Ruiz</td>
                        <td>Toyota Hilux</td>
                        <td>Revisión Frenos</td>
                        <td>19/06/2023</td>
                        <td><span class="status in-progress">En Proceso</span></td>
                        <td>
                            <button class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Gráfico de Estado de Ordenes (Doughnut)
    const ctxOrders = document.getElementById('ordersChart').getContext('2d');
    const labelsOrders = <?php echo !empty($estadosOrdenes) ? json_encode($estadosOrdenes) : json_encode(['Sin Datos']); ?>;
    const dataOrders = <?php echo !empty($cantidadesOrdenes) ? json_encode($cantidadesOrdenes, JSON_NUMERIC_CHECK) : json_encode([1]); ?>;
    const backgroundColorsOrders = <?php echo !empty($cantidadesOrdenes) ? "['rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(153, 102, 255, 0.7)']" : "['#e9ecef']"; ?>;

    const ordersChart = new Chart(ctxOrders, {
        type: 'doughnut',
        data: {
            labels: labelsOrders,
            datasets: [{
                label: '<?php echo !empty($cantidadesOrdenes) ? "Ordenes de Trabajo" : "Sin Datos"; ?>',
                data: dataOrders,
                backgroundColor: backgroundColorsOrders,
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    enabled: <?php echo !empty($cantidadesOrdenes) ? 'true' : 'false'; ?>
                }
            }
        }
    });

    // NUEVO GRÁFICO DE BARRAS - Órdenes por Mes
    <?php if ($hayDatosMensuales): ?>
        const ctxMonthly = document.getElementById('monthlyOrdersChart').getContext('2d');
        const monthlyOrdersChart = new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($meses); ?>,
                datasets: [{
                    label: 'Órdenes',
                    data: <?php echo json_encode($totalesPorMes, JSON_NUMERIC_CHECK); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Órdenes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mes'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Órdenes: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });
    <?php endif; ?>
</script>
<?php include '../templates/footer.php'; ?>