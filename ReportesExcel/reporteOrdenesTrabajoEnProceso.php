<?php
require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Seguridad: Permitir admin, recepcionista y mecanico
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'recepcionista' && $_SESSION['rol'] !== 'mecanico')) {
    exit("Acceso denegado");
}

$id_mecanico = $_SESSION['id_usuario'];

// Datos de órdenes de trabajo en proceso
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden, vehiculos.marca, vehiculos.placa, CONCAT(clientes.nombre, ' ', clientes.apellido) as nombre_cliente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_mecanico, ordenes_trabajo.fecha_creacion, ordenes_trabajo.fecha_entrega, ordenes_trabajo.observaciones, ordenes_trabajo.estado FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo = vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado = usuarios.id_usuario WHERE ordenes_trabajo.id_usuario_asignado = :id_mecanico AND ordenes_trabajo.estado='en_proceso' ORDER BY ordenes_trabajo.id_orden DESC");
$sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes en Proceso');

// Cabeceras
$sheet->fromArray(
    ['ID Orden', 'Vehículo', 'Placa', 'Cliente', 'Mecánico', 'Fecha Creación', 'Fecha Entrega', 'Estado', 'Observaciones'],
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
        $sheet->setCellValue("G$fila", date('d/m/Y', strtotime($o['fecha_entrega'])));
        $sheet->setCellValue("H$fila", ucfirst($o['estado']));
        $sheet->setCellValue("I$fila", $o['observaciones']);
        $fila++;
    }
}

// Descargar
if (ob_get_length()) ob_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_en_proceso.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
