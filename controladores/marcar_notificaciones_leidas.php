<?php
session_start();
require_once "../conexion/bd.php";

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['rol'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    // Si es administrador, marcamos todas las notificaciones como leídas
    // Si hubiera un sistema multidestinatario, aquí filtraríamos por id_usuario
    if ($_SESSION['rol'] === 'admin') {
        $sql = "UPDATE notificaciones SET leido = 'Si' WHERE leido = 'No'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Notificaciones marcadas como leídas']);
    } else {
        // Por ahora solo implementado para admin según sidebar.php
        echo json_encode(['success' => true, 'message' => 'Rol no configurado para notificaciones']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
