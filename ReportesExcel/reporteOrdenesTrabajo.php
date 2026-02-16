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

// Datos de órdenes de trabajo
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden, vehiculos.marca, vehiculos.placa, CONCAT(clientes.nombre, ' ', clientes.apellido) as nombre_cliente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_mecanico, ordenes_trabajo.fecha_creacion, ordenes_trabajo.estado FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo = vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado = usuarios.id_usuario ORDER BY ordenes_trabajo.id_orden DESC");
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes Trabajo');

// Cabeceras
$sheet->fromArray(
    ['ID Orden', 'Vehículo', 'Placa', 'Cliente', 'Mecánico', 'Fecha Creación', 'Estado'],
    NULL,
    'A1'
);

// Datos
$fila = 2;
if (empty($ordenes)) {
    $sheet->setCellValue("A2", 'No se encontraron registros para generar el reporte.');
} else {
    foreach ($ordenes as $o) {
        $sheet->setCellValue("A$fila", 'OT-' . date('Y') . '-' . $o['id_orden']);
        $sheet->setCellValue("B$fila", $o['marca']);
        $sheet->setCellValue("C$fila", $o['placa']);
        $sheet->setCellValue("D$fila", $o['nombre_cliente']);
        $sheet->setCellValue("E$fila", $o['nombre_mecanico']);
        $sheet->setCellValue("F$fila", date('d/m/Y', strtotime($o['fecha_creacion'])));
        $sheet->setCellValue("G$fila", ucfirst($o['estado']));
        $fila++;
    }
}

// Descargar
ob_clean(); // Limpiar el buffer de salida
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_trabajo.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
