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

// Datos de órdenes de repuestos
$sql = $conexion->prepare("SELECT orden_repuestos.id_detalle as id_orden_repuesto, repuestos.nombre as nombre_repuesto, orden_repuestos.cantidad, orden_repuestos.precio_unitario, (orden_repuestos.cantidad * orden_repuestos.precio_unitario) as total, ordenes_trabajo.id_orden FROM orden_repuestos INNER JOIN repuestos ON orden_repuestos.id_repuesto = repuestos.id_repuesto INNER JOIN ordenes_trabajo ON orden_repuestos.id_orden = ordenes_trabajo.id_orden ORDER BY orden_repuestos.id_detalle DESC");
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes Repuestos');

// Cabeceras
$sheet->fromArray(
    ['ID Orden Repuesto', 'ID Orden Trabajo', 'Repuesto', 'Cantidad', 'Precio Unitario', 'Total'],
    NULL,
    'A1'
);

// Datos
$fila = 2;
if (empty($ordenes)) {
    $sheet->setCellValue("A2", 'No se encontraron registros para generar el reporte.');
} else {
    foreach ($ordenes as $o) {
        $sheet->setCellValue("A$fila", 'ORD-REP-' . date('Y') . '-' . $o['id_orden_repuesto']);
        $sheet->setCellValue("B$fila", 'ORD-' . $o['id_orden']);
        $sheet->setCellValue("C$fila", $o['nombre_repuesto']);
        $sheet->setCellValue("D$fila", $o['cantidad']);
        $sheet->setCellValue("E$fila", $o['precio_unitario']);
        $sheet->setCellValue("F$fila", $o['total']);
        $fila++;
    }
}

// Descargar
ob_clean(); // Limpiar el buffer de salida
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_repuestos.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
