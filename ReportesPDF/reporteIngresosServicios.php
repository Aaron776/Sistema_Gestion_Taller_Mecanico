<?php
ob_start();

require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Seguridad: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado");
}

// Ventas/Ingresos por servicios de órdenes finalizadas
$sql = $conexion->prepare("
    SELECT s.nombre_servicio, 
           SUM(ds.cantidad) as total_servicios, 
           SUM(ds.cantidad * ds.precio_unitario) as ingresos_totales 
    FROM orden_servicios ds 
    INNER JOIN ordenes_trabajo ot ON ds.id_orden = ot.id_orden 
    INNER JOIN servicios s ON ds.id_servicio = s.id_servicio 
    WHERE lower(ot.estado) IN ('finalizado', 'entregado', 'finalizada') 
    GROUP BY s.id_servicio, s.nombre_servicio 
    ORDER BY ingresos_totales DESC
");
$sql->execute();
$ingresos = $sql->fetchAll(PDO::FETCH_ASSOC);

$fechaActual = date('d/m/Y H:i A');
$generadoPor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
$totalGeneral = 0;

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #333; margin: 0; padding: 0; }
        .header { background-color: #1a3a5f; color: white; padding: 30px; border-bottom: 5px solid #28a745; }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-text { font-size: 28px; font-weight: bold; color: #28a745; }
        .report-title { font-size: 20px; text-transform: uppercase; margin-top: 5px; }
        .meta-info { font-size: 11px; margin-top: 15px; color: #cbd5e0; }
        
        .content { padding: 30px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.data-table th { background-color: #f8f9fc; color: #1a3a5f; text-align: left; padding: 12px; font-size: 12px; border-bottom: 2px solid #e2e8f0; }
        table.data-table td { padding: 12px; font-size: 11px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        table.data-table tr:nth-child(even) { background-color: #fdfdfd; }
        
        .total-row { background-color: #e2e8f0 !important; font-weight: bold; font-size: 13px !important; color: #1a3a5f; }
        
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 10px; text-align: center; color: #a0aec0; padding: 20px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-text">AUTOTECH</div>
                    <div class="report-title">Ingresos por Servicios</div>
                </td>
                <td style="text-align: right;">
                    <div class="meta-info">
                        Generado el: ' . $fechaActual . '<br>
                        Generado por: ' . htmlspecialchars($generadoPor) . '
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p style="font-size: 12px; color: #718096; margin-bottom: 15px;">Este reporte muestra los ingresos generados únicamente por servicios en órdenes de trabajo con estado <strong>Finalizado</strong> o <strong>Entregado</strong>.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>SERVICIO REALIZADO</th>
                    <th style="text-align: center;">CANTIDAD VENDIDA</th>
                    <th style="text-align: right;">INGRESOS TOTALES</th>
                </tr>
            </thead>
            <tbody>';

if (empty($ingresos)) {
    $html .= '
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px; color: #718096; font-size: 14px;">No se encontraron ingresos registrados para generar el reporte.</td>
                </tr>';
} else {
    foreach ($ingresos as $i) {
        $subtotal = (float)$i['ingresos_totales'];
        $totalGeneral += $subtotal;
        $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($i['nombre_servicio']) . '</strong></td>
                    <td style="text-align: center;">' . $i['total_servicios'] . '</td>
                    <td style="text-align: right; color: #28a745;"><strong>$' . number_format($subtotal, 2) . '</strong></td>
                </tr>';
    }

    // Fila del total general
    $html .= '
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">TOTAL INGRESOS POR SERVICIOS:</td>
                    <td style="text-align: right; color: #155724;">$' . number_format($totalGeneral, 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="footer">
        AutoTech Workshop Management System - Página <span class="page-number"></span>
    </div>
</body>
</html>';

ob_end_clean();

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("reporte_ingresos_servicios.pdf", ["Attachment" => true]);
