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

// Ventas/Ingresos por repuestos de órdenes finalizadas
$sql = $conexion->prepare("
    SELECT r.nombre, 
           SUM(dr.cantidad) as total_repuestos, 
           SUM(dr.cantidad * dr.precio_unitario) as ingresos_totales 
    FROM orden_repuestos dr 
    INNER JOIN ordenes_trabajo ot ON dr.id_orden = ot.id_orden 
    INNER JOIN repuestos r ON dr.id_repuesto = r.id_repuesto 
    WHERE lower(ot.estado) IN ('finalizado', 'entregado', 'finalizada') 
    GROUP BY r.id_repuesto, r.nombre 
    ORDER BY ingresos_totales DESC
");
$sql->execute();
$ingresos = $sql->fetchAll(PDO::FETCH_ASSOC);

$fechaActual = date('d/m/Y H:i A');
$generadoPor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

$totalGeneral = 0;
$mejorRepuesto = 'N/A';
$repuestosVendidos = 0;

if (!empty($ingresos)) {
    $mejorRepuesto = $ingresos[0]['nombre'];
    foreach ($ingresos as $i) {
        $totalGeneral += (float)$i['ingresos_totales'];
        $repuestosVendidos += (int)$i['total_repuestos'];
    }
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Helvetica Neue", "DejaVu Sans", sans-serif; color: #2d3748; margin: 0; padding: 0; background-color: #ffffff; }
        
        .header { background-color: #ffffff; padding: 40px 40px 20px; border-bottom: 2px solid #edf2f7; }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-text { font-size: 30px; font-weight: 800; color: #553c9a; letter-spacing: 1px; }
        .report-title { font-size: 14px; text-transform: uppercase; color: #718096; font-weight: 600; letter-spacing: 1.5px; margin-top: 8px; }
        
        .company-info { text-align: right; color: #a0aec0; font-size: 10px; line-height: 1.5; }
        .date-badge { display: inline-block; background-color: #faf5ff; color: #6b46c1; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 10px; border: 1px solid #e9d8fd; }
        
        .content { padding: 30px 40px; }
        
        .intro-text { font-size: 12px; color: #718096; margin-bottom: 25px; line-height: 1.6; border-left: 3px solid #9f7aea; padding-left: 12px; }
        
        .summary-container { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .summary-card { background-color: #faf5ff; padding: 20px; text-align: center; border: 1px solid #e9d8fd; width: 33.33%; }
        .summary-title { font-size: 10px; color: #805ad5; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; margin-bottom: 8px; }
        .summary-value { font-size: 22px; font-weight: 800; color: #44337a; }
        .summary-card.highlight { background-color: #6b46c1; color: white; border-color: #553c9a; }
        .summary-card.highlight .summary-title { color: #d6bcfa; }
        .summary-card.highlight .summary-value { color: #ffffff; }
        
        table.data-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 10px; }
        table.data-table th { background-color: #ffffff; color: #a0aec0; text-align: left; padding: 15px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        table.data-table td { padding: 14px 12px; font-size: 12px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #4a5568; }
        table.data-table tr:nth-child(even) td { background-color: #fcfcfc; }
        
        .item-name { font-weight: 600; color: #2d3748; }
        .money-value { font-weight: 700; color: #553c9a; }
        
        .total-row td { background-color: #faf5ff !important; font-weight: bold; font-size: 13px !important; color: #44337a; border-top: 2px solid #9f7aea; border-bottom: none; }
        
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 10px; text-align: center; color: #cbd5e0; padding: 20px 0; border-top: 1px solid #edf2f7; background-color: white; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-text">AUTOTECH</div>
                    <div class="report-title">Ingresos por Repuestos</div>
                </td>
                <td style="text-align: right;">
                    <div class="company-info">
                        Sistema de Gestión de Taller<br>
                        Generado por: ' . htmlspecialchars($generadoPor) . '
                    </div>
                    <div class="date-badge">Fecha: ' . $fechaActual . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p class="intro-text">
            Este reporte ejecutivo muestra de forma detallada los ingresos generados exclusivamente por la venta de repuestos, correspondientes a órdenes de trabajo comprobadas (<strong>Finalizado</strong> o <strong>Entregado</strong>).
        </p>
        
        <table class="summary-container">
            <tr>
                <td class="summary-card">
                    <div class="summary-title">Repuestos Vendidos</div>
                    <div class="summary-value">' . number_format($repuestosVendidos) . ' unid.</div>
                </td>
                <td class="summary-card highlight">
                    <div class="summary-title">Ingreso Total</div>
                    <div class="summary-value">$' . number_format($totalGeneral, 2) . '</div>
                </td>
                <td class="summary-card">
                    <div class="summary-title">Mejor Repuesto (Ventas)</div>
                    <div class="summary-value" style="font-size: 14px; margin-top: 6px;">' . htmlspecialchars($mejorRepuesto) . '</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>REPUESTO VENDIDO</th>
                    <th style="text-align: center;">CANT. VENDIDA</th>
                    <th style="text-align: right;">INGRESOS TOTALES</th>
                </tr>
            </thead>
            <tbody>';

if (empty($ingresos)) {
    $html .= '
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #a0aec0; font-size: 13px;">No hay registros de ingresos por repuestos en este momento.</td>
                </tr>';
} else {
    $contador = 1;
    foreach ($ingresos as $i) {
        $subtotal = (float)$i['ingresos_totales'];
        $html .= '
                <tr>
                    <td style="color: #a0aec0; font-size: 11px;">' . $contador . '</td>
                    <td class="item-name">' . htmlspecialchars($i['nombre']) . '</td>
                    <td style="text-align: center;">' . $i['total_repuestos'] . '</td>
                    <td style="text-align: right;" class="money-value">$' . number_format($subtotal, 2) . '</td>
                </tr>';
        $contador++;
    }

    // Fila del total general
    $html .= '
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; padding-top: 15px; padding-bottom: 15px;">TOTAL GENERAL:</td>
                    <td style="text-align: right; padding-top: 15px; padding-bottom: 15px;">$' . number_format($totalGeneral, 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="footer">
        AutoTech Workshop Management System &copy; ' . date('Y') . ' | Reporte Ejecutivo - Página <span class="page-number"></span>
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

$dompdf->stream("reporte_ingresos_repuestos.pdf", ["Attachment" => true]);
