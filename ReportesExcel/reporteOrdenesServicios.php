<?php
require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Seguridad: Permitir admin y recepcionista
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'recepcionista')) {
    exit("Acceso denegado");
}

// Datos de órdenes de servicios
$sql = $conexion->prepare("SELECT orden_servicios.id_detalle as id_orden_servicio, servicios.nombre_servicio, orden_servicios.cantidad, orden_servicios.precio_unitario, (orden_servicios.cantidad * orden_servicios.precio_unitario) as total, ordenes_trabajo.id_orden FROM orden_servicios INNER JOIN servicios ON orden_servicios.id_servicio = servicios.id_servicio INNER JOIN ordenes_trabajo ON orden_servicios.id_orden = ordenes_trabajo.id_orden ORDER BY orden_servicios.id_detalle DESC");
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes Servicios');

// Cabeceras
$sheet->fromArray(
    ['ID Orden Servicio', 'ID Orden Trabajo', 'Servicio', 'Cantidad', 'Precio Unitario', 'Total'],
    NULL,
    'A1'
);

// Datos
$fila = 2;
if (empty($ordenes)) {
    $sheet->setCellValue("A2", 'No se encontraron registros para generar el reporte.');
} else {
    foreach ($ordenes as $o) {
        $sheet->setCellValue("A$fila", 'OS-' . date('Y') . '-' . $o['id_orden_servicio']);
        $sheet->setCellValue("B$fila", 'ORD-' . $o['id_orden']);
        $sheet->setCellValue("C$fila", $o['nombre_servicio']);
        $sheet->setCellValue("D$fila", $o['cantidad']);
        $sheet->setCellValue("E$fila", $o['precio_unitario']);
        $sheet->setCellValue("F$fila", $o['total']);
        $fila++;
    }
}

// Descargar
ob_clean(); // Limpiar el buffer de salida
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_servicios.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
