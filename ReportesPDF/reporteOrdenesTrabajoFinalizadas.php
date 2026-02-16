<?php
ob_start();

require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Seguridad: Permitir admin, recepcionista y mecanico
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'recepcionista' && $_SESSION['rol'] !== 'mecanico')) {
    exit("Acceso denegado");
}

$id_mecanico = $_SESSION['id_usuario'];

$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden, vehiculos.marca, vehiculos.placa, CONCAT(clientes.nombre, ' ', clientes.apellido) as nombre_cliente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_mecanico, ordenes_trabajo.fecha_creacion, ordenes_trabajo.fecha_entrega, ordenes_trabajo.estado FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo = vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado = usuarios.id_usuario WHERE ordenes_trabajo.id_usuario_asignado = :id_mecanico AND lower(ordenes_trabajo.estado) IN ('finalizada', 'finalizado', 'completado') ORDER BY ordenes_trabajo.id_orden DESC");
$sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

if (empty($ordenes)) {
    $_SESSION['errores'] = ["No hay registros de órdenes finalizadas para generar el reporte."];
    header("Location: ../mecanico/gestion_ordenes_trabajo_finalizadas.php");
    exit();
}

$fechaActual = date('d/m/Y H:i A');
$generadoPor = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #333; margin: 0; padding: 0; }
        .header { background-color: #1a3a5f; color: white; padding: 30px; border-bottom: 5px solid #f8b400; }
        .header table { width: 100%; border-collapse: collapse; }
        .logo-text { font-size: 28px; font-weight: bold; color: #f8b400; }
        .report-title { font-size: 20px; text-transform: uppercase; margin-top: 5px; }
        .meta-info { font-size: 11px; margin-top: 15px; color: #cbd5e0; }
        
        .content { padding: 30px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.data-table th { background-color: #f8f9fc; color: #1a3a5f; text-align: left; padding: 12px; font-size: 12px; border-bottom: 2px solid #e2e8f0; }
        table.data-table td { padding: 12px; font-size: 11px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        table.data-table tr:nth-child(even) { background-color: #fdfdfd; }
        
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-finalizada { background-color: #d4edda; color: #155724; }
        
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
                    <div class="report-title">Reporte de Órdenes Finalizadas</div>
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
                    <th>ID ORDEN</th>
                    <th>VEHÍCULO</th>
                    <th>CLIENTE</th>
                    <th>FECHA ENTREGA</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>';

foreach ($ordenes as $o) {
    $html .= '
                <tr>
                    <td><strong>OT-' . date('Y') . '-' . htmlspecialchars($o['id_orden']) . '</strong></td>
                    <td>' . htmlspecialchars($o['marca']) . '<br><small style="color:#718096">' . htmlspecialchars($o['placa']) . '</small></td>
                    <td>' . htmlspecialchars($o['nombre_cliente']) . '</td>
                    <td>' . date('d/m/Y', strtotime($o['fecha_entrega'])) . '</td>
                    <td><span class="status-badge status-finalizada">' . strtoupper(htmlspecialchars($o['estado'])) . '</span></td>
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

$dompdf->stream("reporte_ordenes_finalizadas.pdf", ["Attachment" => true]);
