<?php
require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Seguridad: Permitir admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    exit("Acceso denegado");
}

// Datos de órdenes de trabajo rechazadas
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden, vehiculos.marca, vehiculos.placa, CONCAT(clientes.nombre, ' ', clientes.apellido) as nombre_cliente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_mecanico, ordenes_trabajo.fecha_creacion, ordenes_trabajo.motivo_rechazo FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo = vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado = usuarios.id_usuario WHERE ordenes_trabajo.estado='rechazada' ORDER BY ordenes_trabajo.id_orden DESC");
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes Rechazadas');

// Cabeceras
$sheet->fromArray(
    ['ID Orden', 'Vehículo', 'Placa', 'Cliente', 'Mecánico', 'Fecha Creación', 'Motivo Rechazo'],
    NULL,
    'A1'
);

// Estilo para las cabeceras
$sheet->getStyle('A1:G1')->getFont()->setBold(true);

// Datos
$fila = 2;
if (empty($ordenes)) {
    $sheet->setCellValue("A2", 'No se encontraron registros para generar el reporte.');
} else {
    foreach ($ordenes as $o) {
        $sheet->setCellValue("A$fila", 'ORD-' . date('Y') . '-' . $o['id_orden']);
        $sheet->setCellValue("B$fila", $o['marca']);
        $sheet->setCellValue("C$fila", $o['placa']);
        $sheet->setCellValue("D$fila", $o['nombre_cliente']);
        $sheet->setCellValue("E$fila", $o['nombre_mecanico']);
        $sheet->setCellValue("F$fila", date('d/m/Y', strtotime($o['fecha_creacion'])));
        $sheet->setCellValue("G$fila", $o['motivo_rechazo']);
        $fila++;
    }
}

// Ajuste automático de columnas
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar
if (ob_get_length()) ob_clean(); // Limpiar el buffer de salida si hay algo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_rechazadas.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
