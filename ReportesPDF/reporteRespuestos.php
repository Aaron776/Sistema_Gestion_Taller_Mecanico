<?php
ob_start(); // Iniciar buffer de salida

require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado");
}

// Obtener datos
$sql = $conexion->prepare("SELECT nombre, descripcion, precio, stock FROM repuestos ORDER BY nombre");
$sql->execute();
$repuestos = $sql->fetchAll(PDO::FETCH_ASSOC);

// HTML del PDF
$html = '
<h2 style="text-align:center">Reporte de Repuestos</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="6">
<thead>
<tr>
    <th>Nombre</th>
    <th>Descripción</th>
    <th>Precio</th>
    <th>Stock</th>
</tr>
</thead>
<tbody>';

if (empty($repuestos)) {
    $html .= '
    <tr>
        <td colspan="4" style="text-align: center; padding: 40px; color: #718096; font-size: 14px;">No se encontraron registros para generar el reporte.</td>
    </tr>';
} else {
    foreach ($repuestos as $r) {
        $html .= '
    <tr>
        <td>' . htmlspecialchars($r['nombre']) . '</td>
        <td>' . htmlspecialchars($r['descripcion']) . '</td>
        <td>$' . number_format($r['precio'], 2) . '</td>
        <td>' . $r['stock'] . '</td>
    </tr>';
    }
}

$html .= '</tbody></table>';

// Limpiar cualquier salida anterior (espacios, warnings, etc.)
ob_end_clean();

// Dompdf config
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true); // Permitir cargar imágenes remotas si es necesario

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar
$dompdf->stream("reporte_repuestos.pdf", ["Attachment" => true]);
