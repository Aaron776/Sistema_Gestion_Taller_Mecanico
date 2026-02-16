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

// Datos de órdenes de trabajo finalizadas
$sql = $conexion->prepare("SELECT ordenes_trabajo.id_orden, vehiculos.marca, vehiculos.placa, CONCAT(clientes.nombre, ' ', clientes.apellido) as nombre_cliente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_mecanico, ordenes_trabajo.fecha_creacion, ordenes_trabajo.fecha_entrega, ordenes_trabajo.estado FROM ordenes_trabajo INNER JOIN clientes ON ordenes_trabajo.id_cliente = clientes.id_cliente INNER JOIN vehiculos ON ordenes_trabajo.id_vehiculo = vehiculos.id_vehiculo INNER JOIN usuarios ON ordenes_trabajo.id_usuario_asignado = usuarios.id_usuario WHERE ordenes_trabajo.id_usuario_asignado = :id_mecanico AND lower(ordenes_trabajo.estado) IN ('finalizada', 'finalizado', 'completado') ORDER BY ordenes_trabajo.id_orden DESC");
$sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
$sql->execute();
$ordenes = $sql->fetchAll(PDO::FETCH_ASSOC);

if (empty($ordenes)) {
    $_SESSION['errores'] = ["No hay registros de órdenes finalizadas para generar el reporte."];
    header("Location: ../mecanico/gestion_ordenes_trabajo_finalizadas.php");
    exit();
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ordenes Finalizadas');

// Cabeceras
$sheet->fromArray(
    ['ID Orden', 'Vehículo', 'Placa', 'Cliente', 'Mecánico', 'Fecha Creación', 'Fecha Entrega', 'Estado'],
    NULL,
    'A1'
);

// Estilos de cabecera
$sheet->getStyle('A1:H1')->getFont()->setBold(true);
$sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1A3A5F');
$sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

// Datos
$fila = 2;
foreach ($ordenes as $o) {
    $sheet->setCellValue("A$fila", 'OT-' . date('Y') . '-' . $o['id_orden']);
    $sheet->setCellValue("B$fila", $o['marca']);
    $sheet->setCellValue("C$fila", $o['placa']);
    $sheet->setCellValue("D$fila", $o['nombre_cliente']);
    $sheet->setCellValue("E$fila", $o['nombre_mecanico']);
    $sheet->setCellValue("F$fila", date('d/m/Y', strtotime($o['fecha_creacion'])));
    $sheet->setCellValue("G$fila", date('d/m/Y', strtotime($o['fecha_entrega'])));
    $sheet->setCellValue("H$fila", ucfirst($o['estado']));
    $fila++;
}

// Autoajustar columnas
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar
if (ob_get_length()) ob_clean(); // Limpiar el buffer de salida si hay algo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_ordenes_finalizadas.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
