 <?php
    require_once '../autorizacion/auth.php';

    // Verificar que tenga rol de admin
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header("Location: ../acceso_denegado.php"); // si no lo mandamos al login
        exit();
    }

    include '../templates/header.php';
    include_once '../conexion/bd.php';

    // Obtener total de repuestos con stock bajo (menor a 10) 
    $sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos WHERE stock < 10");
    $sql->execute();
    $totalBajoStock = $sql->fetch(PDO::FETCH_OBJ)->total;

    // Obtener total de servicios
    $sql = $conexion->prepare("SELECT COUNT(*) as total FROM servicios");
    $sql->execute();
    $totalServicios = $sql->fetch(PDO::FETCH_OBJ)->total;

    // Obtener total de clientes
    $sql = $conexion->prepare("SELECT COUNT(*) as total FROM clientes");
    $sql->execute();
    $totalClientes = $sql->fetch(PDO::FETCH_OBJ)->total;

    // Obtener total de ordenes de trabajo
    $sql = $conexion->prepare("SELECT COUNT(*) as total FROM ordenes_trabajo WHERE estado = 'finalizado'");
    $sql->execute();
    $totalOrdenes = $sql->fetch(PDO::FETCH_OBJ)->total;

    // Obtener total de repuestos (para cálculo de stock)
    $sql = $conexion->prepare("SELECT COUNT(*) as total FROM repuestos");
    $sql->execute();
    $totalRepuestos = $sql->fetch(PDO::FETCH_OBJ)->total;

    // Obtener conteo de ordenes por estado
    $sql = $conexion->prepare("SELECT estado, COUNT(*) as total FROM ordenes_trabajo GROUP BY estado");
    $sql->execute();
    $ordenesPorEstado = $sql->fetchAll(PDO::FETCH_OBJ);

    // Preparar datos para gráfico de ordenes
    $estadosOrdenes = [];
    $cantidadesOrdenes = [];
    foreach ($ordenesPorEstado as $orden) {
        $estadosOrdenes[] = ucfirst($orden->estado);
        $cantidadesOrdenes[] = $orden->total;
    }
    ?>
 <!-- Cards de Información -->
 <div class="info-cards">
     <div class="info-card primary">
         <div class="info-card-content">
             <h3><?php echo htmlspecialchars($totalClientes); ?></h3>
             <p>Clientes Registrados</p>
         </div>
         <i class="fas fa-user"></i>
     </div>
     <div class="info-card success">
         <div class="info-card-content">
             <h3><?php echo htmlspecialchars($totalServicios); ?></h3>
             <p>Servicios Totales</p>
         </div>
         <i class="fas fa-tools"></i>
     </div>
     <div class="info-card warning">
         <div class="info-card-content">
             <h3><?php echo htmlspecialchars($totalOrdenes); ?></h3>
             <p>Ordenes Finalizadas</p>
         </div>
         <i class="fas fa-calendar-check"></i>
     </div>
     <div class="info-card danger">
         <div class="info-card-content">
             <h3><?php echo htmlspecialchars($totalBajoStock); ?></h3>
             <p>Repuestos Bajo Stock</p>
         </div>
         <i class="fas fa-exclamation-triangle"></i>
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
     <div class="card">
         <div class="card-header">
             <div class="card-title">Estado de Inventario</div>
         </div>
         <div class="card-body">
             <div class="chart-container">
                 <canvas id="inventoryChart"></canvas>
             </div>
         </div>
     </div>
 </div>

 <!-- Tabla de Servicios Recientes -->
 <div class="card">
     <div class="card-header">
         <div class="card-title">Servicios Recientes</div>
         <button class="btn btn-primary" id="addServiceBtn">
             <i class="fas fa-plus"></i> Nuevo Servicio
         </button>
     </div>
     <div class="card-body">
         <div class="table-responsive">
             <table class="table">
                 <thead>
                     <tr>
                         <th>Vehículo</th>
                         <th>Cliente</th>
                         <th>Servicio</th>
                         <th>Fecha</th>
                         <th>Estado</th>
                         <th>Acciones</th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr>
                         <td>Toyota Corolla</td>
                         <td>Carlos Rodríguez</td>
                         <td>Cambio de Aceite</td>
                         <td>15/06/2023</td>
                         <td><span class="status completed">Completado</span></td>
                         <td>
                             <button class="btn btn-secondary btn-sm">
                                 <i class="fas fa-eye"></i>
                             </button>
                         </td>
                     </tr>
                     <tr>
                         <td>Honda Civic</td>
                         <td>María González</td>
                         <td>Alineación y Balanceo</td>
                         <td>16/06/2023</td>
                         <td><span class="status in-progress">En Proceso</span></td>
                         <td>
                             <button class="btn btn-secondary btn-sm">
                                 <i class="fas fa-eye"></i>
                             </button>
                         </td>
                     </tr>
                     <tr>
                         <td>Ford F-150</td>
                         <td>Roberto Sánchez</td>
                         <td>Reparación Motor</td>
                         <td>17/06/2023</td>
                         <td><span class="status pending">Pendiente</span></td>
                         <td>
                             <button class="btn btn-secondary btn-sm">
                                 <i class="fas fa-eye"></i>
                             </button>
                         </td>
                     </tr>
                     <tr>
                         <td>Chevrolet Spark</td>
                         <td>Ana Martínez</td>
                         <td>Cambio de Frenos</td>
                         <td>18/06/2023</td>
                         <td><span class="status in-progress">En Proceso</span></td>
                         <td>
                             <button class="btn btn-secondary btn-sm">
                                 <i class="fas fa-eye"></i>
                             </button>
                         </td>
                     </tr>
                     <tr>
                         <td>Nissan Sentra</td>
                         <td>Luis Pérez</td>
                         <td>Diagnóstico Electrónico</td>
                         <td>19/06/2023</td>
                         <td><span class="status completed">Completado</span></td>
                         <td>
                             <button class="btn btn-secondary btn-sm">
                                 <i class="fas fa-eye"></i>
                             </button>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </div>
     </div>
 </div>

 <!-- Acciones Rápidas y Calendario -->
 <div class="charts-row">
     <div class="card">
         <div class="card-header">
             <div class="card-title">Acciones Rápidas</div>
         </div>
         <div class="card-body">
             <div class="quick-actions">
                 <a href="#" class="action-btn">
                     <i class="fas fa-car"></i>
                     <span>Registrar Vehículo</span>
                 </a>
                 <a href="#" class="action-btn">
                     <i class="fas fa-calendar-plus"></i>
                     <span>Nueva Cita</span>
                 </a>
                 <a href="#" class="action-btn">
                     <i class="fas fa-file-invoice"></i>
                     <span>Generar Factura</span>
                 </a>
                 <a href="#" class="action-btn">
                     <i class="fas fa-box"></i>
                     <span>Control de Inventario</span>
                 </a>
                 <a href="#" class="action-btn">
                     <i class="fas fa-user-plus"></i>
                     <span>Agregar Cliente</span>
                 </a>
                 <a href="#" class="action-btn">
                     <i class="fas fa-chart-bar"></i>
                     <span>Generar Reporte</span>
                 </a>
             </div>
         </div>
     </div>
     <div class="card">
         <div class="card-header">
             <div class="card-title">Calendario</div>
         </div>
         <div class="card-body">
             <div class="calendar-widget">
                 <div class="calendar-header">
                     <button class="btn btn-secondary btn-sm">
                         <i class="fas fa-chevron-left"></i>
                     </button>
                     <h4>Junio 2023</h4>
                     <button class="btn btn-secondary btn-sm">
                         <i class="fas fa-chevron-right"></i>
                     </button>
                 </div>
                 <div class="calendar-days">
                     <div class="day-header">Lun</div>
                     <div class="day-header">Mar</div>
                     <div class="day-header">Mié</div>
                     <div class="day-header">Jue</div>
                     <div class="day-header">Vie</div>
                     <div class="day-header">Sáb</div>
                     <div class="day-header">Dom</div>

                     <!-- Días del calendario -->
                     <div class="day">28</div>
                     <div class="day">29</div>
                     <div class="day">30</div>
                     <div class="day">31</div>
                     <div class="day">1</div>
                     <div class="day">2</div>
                     <div class="day">3</div>

                     <div class="day">4</div>
                     <div class="day">5</div>
                     <div class="day event">6</div>
                     <div class="day">7</div>
                     <div class="day">8</div>
                     <div class="day event">9</div>
                     <div class="day">10</div>

                     <div class="day">11</div>
                     <div class="day event">12</div>
                     <div class="day">13</div>
                     <div class="day">14</div>
                     <div class="day today">15</div>
                     <div class="day event">16</div>
                     <div class="day">17</div>

                     <div class="day">18</div>
                     <div class="day">19</div>
                     <div class="day event">20</div>
                     <div class="day">21</div>
                     <div class="day">22</div>
                     <div class="day">23</div>
                     <div class="day">24</div>

                     <div class="day">25</div>
                     <div class="day">26</div>
                     <div class="day">27</div>
                     <div class="day">28</div>
                     <div class="day">29</div>
                     <div class="day">30</div>
                     <div class="day">1</div>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <!-- Chart.js CDN -->
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

 <script>
     // Gráfico de Estado de Ordenes
     // Gráfico de Estado de Ordenes
     const ctxOrders = document.getElementById('ordersChart').getContext('2d');
     const labelsOrders = <?php echo !empty($estadosOrdenes) ? json_encode($estadosOrdenes) : json_encode(['Sin Datos']); ?>;
     const dataOrders = <?php echo !empty($cantidadesOrdenes) ? json_encode($cantidadesOrdenes, JSON_NUMERIC_CHECK) : json_encode([1]); ?>;
     const backgroundColorsOrders = <?php echo !empty($cantidadesOrdenes) ? "['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)']" : "['#e9ecef']"; ?>;

     const ordersChart = new Chart(ctxOrders, {
         type: 'pie',
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

     // Gráfico de Estado de Inventario
     const ctxInventory = document.getElementById('inventoryChart').getContext('2d');
     const inventoryChart = new Chart(ctxInventory, {
         type: 'doughnut',
         data: {
             labels: ['Stock Normal', 'Bajo Stock'],
             datasets: [{
                 label: 'Inventario',
                 data: [<?php echo $totalRepuestos - $totalBajoStock; ?>, <?php echo $totalBajoStock; ?>],
                 backgroundColor: [
                     'rgba(40, 167, 69, 0.7)',
                     'rgba(220, 53, 69, 0.7)'
                 ],
                 borderColor: [
                     'rgba(40, 167, 69, 1)',
                     'rgba(220, 53, 69, 1)'
                 ],
                 borderWidth: 1
             }]
         },
         options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 legend: {
                     position: 'bottom',
                 }
             }
         }
     });
 </script>
 <?php include '../templates/footer.php'; ?>