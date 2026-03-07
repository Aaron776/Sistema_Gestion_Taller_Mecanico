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

$sql = $conexion->prepare("SELECT nombre, apellido, cedula_ruc, telefono, email, direccion FROM clientes ORDER BY nombre ASC, apellido ASC");
$sql->execute();
$clientes = $sql->fetchAll(PDO::FETCH_ASSOC);

$fechaActual = date('d/m/Y H:i A');
$generadoPor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #333; margin: 0; padding: 0; }
        .header { background-color: #1a3a5f; color: white; padding: 30px; border-bottom: 5px solid #2a5a8c; }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-text { font-size: 28px; font-weight: bold; color: #2a5a8c; }
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
                    <div class="report-title">Directorio de Clientes</div>
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
                    <th>NOMBRES Y APELLIDOS</th>
                    <th>CÉDULA/RUC</th>
                    <th>TELÉFONO</th>
                    <th>EMAIL</th>
                    <th>DIRECCIÓN</th>
                </tr>
            </thead>
            <tbody>';

if (empty($clientes)) {
    $html .= '
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #718096; font-size: 14px;">No se encontraron registros para generar el reporte.</td>
                </tr>';
} else {
    foreach ($clientes as $c) {
        $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) . '</strong></td>
                    <td>' . htmlspecialchars($c['cedula_ruc']) . '</td>
                    <td>' . htmlspecialchars($c['telefono']) . '</td>
                    <td>' . htmlspecialchars($c['email']) . '</td>
                    <td>' . htmlspecialchars($c['direccion']) . '</td>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("reporte_clientes.pdf", ["Attachment" => true]);
