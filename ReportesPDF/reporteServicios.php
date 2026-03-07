<?php
ob_start();

require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado");
}

$sql = $conexion->prepare("SELECT nombre_servicio, descripcion, precio_base FROM servicios ORDER BY nombre_servicio");
$sql->execute();
$servicios = $sql->fetchAll(PDO::FETCH_ASSOC);

$fechaActual = date('d/m/Y H:i A');
$generadoPor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #333; margin: 0; padding: 0; }
        .header { background-color: #1a3a5f; color: white; padding: 30px; border-bottom: 5px solid #3abaf4; }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-text { font-size: 28px; font-weight: bold; color: #3abaf4; }
        .report-title { font-size: 20px; text-transform: uppercase; margin-top: 5px; }
        .meta-info { font-size: 11px; margin-top: 15px; color: #cbd5e0; }
        
        .content { padding: 30px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.data-table th { background-color: #f8f9fc; color: #1a3a5f; text-align: left; padding: 12px; font-size: 12px; border-bottom: 2px solid #e2e8f0; }
        table.data-table td { padding: 12px; font-size: 11px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        table.data-table tr:nth-child(even) { background-color: #fdfdfd; }
        
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
                    <div class="report-title">Reporte de Servicios Oficiales</div>
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
        <table class="data-table">
            <thead>
                <tr>
                    <th>SERVICIO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>PRECIO BASE</th>
                </tr>
            </thead>
            <tbody>';

if (empty($servicios)) {
    $html .= '
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px; color: #718096; font-size: 14px;">No se encontraron registros para generar el reporte.</td>
                </tr>';
} else {
    foreach ($servicios as $s) {
        $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($s['nombre_servicio']) . '</strong></td>
                    <td>' . htmlspecialchars($s['descripcion']) . '</td>
                    <td><strong>$' . number_format($s['precio_base'], 2) . '</strong></td>
                </tr>';
    }
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

$dompdf->stream("reporte_servicios.pdf", ["Attachment" => true]);
