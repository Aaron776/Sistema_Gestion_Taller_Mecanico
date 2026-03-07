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
?>

<style>
    .reportes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        padding: 20px 0;
    }

    .reporte-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid #e3e6f0;
    }

    .reporte-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
    }

    .reporte-card-header {
        padding: 25px 20px 15px;
        text-align: center;
    }

    .reporte-card-header i {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .reporte-card-header h3 {
        color: var(--primary);
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .reporte-card-body {
        padding: 0 20px 20px;
        text-align: center;
    }

    .reporte-card-body p {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .reporte-card-footer {
        padding: 15px 20px;
        background-color: #f8f9fc;
        border-top: 1px solid #e3e6f0;
    }

    .btn-generar {
        width: 100%;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        transition: var(--transition);
        cursor: pointer;
    }

    .reporte-card:nth-child(1) .reporte-card-header i {
        color: var(--success);
    }

    .reporte-card:nth-child(1) .btn-generar {
        background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
        color: white;
    }

    .reporte-card:nth-child(2) .reporte-card-header i {
        color: var(--danger);
    }

    .reporte-card:nth-child(2) .btn-generar {
        background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
        color: white;
    }

    .reporte-card:nth-child(3) .reporte-card-header i {
        color: var(--info);
    }

    .reporte-card:nth-child(3) .btn-generar {
        background: linear-gradient(135deg, var(--info) 0%, #3abaf4 100%);
        color: white;
    }

    .reporte-card:nth-child(4) .reporte-card-header i {
        color: var(--warning);
    }

    .reporte-card:nth-child(4) .btn-generar {
        background: linear-gradient(135deg, var(--warning) 0%, #ffd761 100%);
        color: var(--dark);
    }

    .reporte-card:nth-child(5) .reporte-card-header i {
        color: var(--primary);
    }

    .reporte-card:nth-child(5) .btn-generar {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5a8c 100%);
        color: white;
    }

    .reporte-card:nth-child(6) .reporte-card-header i {
        color: #20c997;
    }

    .reporte-card:nth-child(6) .btn-generar {
        background: linear-gradient(135deg, #20c997 0%, #38dfaf 100%);
        color: white;
    }

    /* Diseño Premium para Tarjeta de Ingresos por Repuestos */
    .reporte-card:nth-child(7) {
        border: 2px solid #e9d8fd;
        box-shadow: 0 8px 20px rgba(107, 70, 193, 0.1);
        position: relative;
    }

    .reporte-card:nth-child(7):hover {
        border-color: #d6bcfa;
        box-shadow: 0 12px 25px rgba(107, 70, 193, 0.2);
    }

    .reporte-card:nth-child(7)::before {
        content: "NUEVO ESTILO";
        position: absolute;
        top: 10px;
        right: -30px;
        background: #9f7aea;
        color: white;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 4px 30px;
        transform: rotate(45deg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .reporte-card:nth-child(7) .reporte-card-header i {
        color: #6b46c1;
        text-shadow: 0 4px 12px rgba(107, 70, 193, 0.25);
    }

    .reporte-card:nth-child(7) .reporte-card-header h3 {
        color: #44337a;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .reporte-card:nth-child(7) .btn-generar {
        background: linear-gradient(135deg, #6b46c1 0%, #9f7aea 100%);
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(107, 70, 193, 0.3);
    }

    .reporte-card:nth-child(7) .btn-generar:hover {
        background: linear-gradient(135deg, #553c9a 0%, #805ad5 100%);
        box-shadow: 0 6px 20px rgba(107, 70, 193, 0.4);
        transform: translateY(-2px);
    }

    .btn-generar:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e3e6f0;
    }

    .page-header h2 {
        color: var(--primary);
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header h2 i {
        color: var(--secondary);
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-chart-bar"></i> Reportes del Sistema</h2>
</div>

<div class="reportes-grid">
    <!-- Reporte 1: Ordenes de Trabajo Finalizadas -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-check-circle"></i>
            <h3>Ordenes Finalizadas</h3>
        </div>
        <div class="reporte-card-body">
            <p>Genera un reporte detallado de todas las ordenes de trabajo que han sido finalizadas exitosamente.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteOrdenesTrabajoFinalizadas.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 2: Ordenes de Trabajo Rechazadas -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-times-circle"></i>
            <h3>Ordenes Rechazadas</h3>
        </div>
        <div class="reporte-card-body">
            <p>Genera un reporte de todas las ordenes de trabajo que fueron rechazadas por los mecánicos.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteOrdenesTrabajoRechazadas.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 3: Servicios del Taller -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-tools"></i>
            <h3>Servicios del Taller</h3>
        </div>
        <div class="reporte-card-body">
            <p>Reporte completo de todos los servicios disponibles en el taller con sus precios.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteServicios.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 4: Inventario de Repuestos -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-boxes"></i>
            <h3>Inventario Repuestos</h3>
        </div>
        <div class="reporte-card-body">
            <p>Reporte del inventario actual de repuestos disponibles en el taller.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteRespuestos.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 5: Clientes Registrados -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-users"></i>
            <h3>Clientes Registrados</h3>
        </div>
        <div class="reporte-card-body">
            <p>Lista completa de todos los clientes registrados en el sistema.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteClientes.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 6: Resumen General -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-chart-line"></i>
            <h3>Ingresos por Servicios</h3>
        </div>
        <div class="reporte-card-body">
            <p>Reporte de los ingresos generados por los servicios del taller.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteIngresosServicios.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>

    <!-- Reporte 7: Ingresos por Repuestos -->
    <div class="reporte-card">
        <div class="reporte-card-header">
            <i class="fas fa-chart-line"></i>
            <h3>Ingresos por Repuestos</h3>
        </div>
        <div class="reporte-card-body">
            <p>Reporte de los ingresos generados por los repuestos del taller.</p>
        </div>
        <div class="reporte-card-footer">
            <a href="../ReportesPDF/reporteIngresosRepuestos.php" target="_blank" class="btn-generar">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
        </div>
    </div>
</div>

<?php include_once '../templates/footer.php'; ?>