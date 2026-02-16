<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

header('Content-Type: application/json');

// Verificar que el usuario tenga una sesión activa
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Validar que se haya recibido un ID de cliente válido por GET
if (isset($_GET['id_cliente']) && is_numeric($_GET['id_cliente'])) {
    $id_cliente = $_GET['id_cliente'];

    try {
        // Consultar los vehículos que pertenecen específicamente al cliente seleccionado
        $sql = $conexion->prepare("SELECT id_vehiculo, placa, marca, modelo FROM vehiculos WHERE id_cliente = :id_cliente");
        $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $sql->execute();
        $vehiculos = $sql->fetchAll(PDO::FETCH_ASSOC);

        // Devolver la lista en formato JSON para que el frontend pueda procesarla
        echo json_encode($vehiculos);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cliente no proporcionado o inválido']);
}
