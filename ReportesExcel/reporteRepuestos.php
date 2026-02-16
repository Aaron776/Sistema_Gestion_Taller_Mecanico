<?php
require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado");
}

// Datos
$sql = $conexion->prepare("SELECT nombre, descripcion, precio, stock FROM repuestos ORDER BY nombre");
$sql->execute();
$repuestos = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Repuestos');

// Cabeceras
$sheet->fromArray(
    ['Nombre', 'Descripción', 'Precio', 'Stock'],
    NULL,
    'A1'
);

// Datos
$fila = 2;
if (empty($repuestos)) {
    $sheet->setCellValue("A2", 'No se encontraron registros para generar el reporte.');
} else {
    foreach ($repuestos as $r) {
        $sheet->setCellValue("A$fila", $r['nombre']);
        $sheet->setCellValue("B$fila", $r['descripcion']);
        $sheet->setCellValue("C$fila", $r['precio']);
        $sheet->setCellValue("D$fila", $r['stock']);
        $fila++;
    }
}

// Descargar
ob_clean(); // Limpiar el buffer de salida para evitar corrupción del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_repuestos.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
