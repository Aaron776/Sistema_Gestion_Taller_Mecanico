<?php
require_once '../conexion/bd.php';
require_once '../conexion/session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_orden"])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_orden = trim($_POST["id_orden"]);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($id_orden)) {
        $errores[] = "El id de la orden de trabajo es obligatorio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El id de la orden de trabajo debe ser un numero";
    } elseif ($id_orden <= 0) {
        $errores[] = "El id de la orden de trabajo debe ser mayor a 0";
    }

    // Verificar que la orden de trabajo exista y esté rechazada (opcional para mayor seguridad)
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM ordenes_trabajo WHERE id_orden = :id_orden AND estado = 'rechazada'");
            $sql->bindParam(':id_orden', $id_orden);
            $sql->execute();
            $orden = $sql->fetch(PDO::FETCH_OBJ);
            if (!$orden) {
                $errores[] = "La orden de trabajo no existe o no está rechazada";
            }
        } catch (PDOException $e) {
            error_log("Error al verificar orden de trabajo: " . $e->getMessage());
            $errores[] = "Hubo un error al verificar la orden de trabajo";
        }
    }

    // Si no hay errores, eliminar la orden de trabajo
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("DELETE FROM ordenes_trabajo WHERE id_orden = :id_orden");
            $sql->bindParam(':id_orden', $id_orden);
            $sql->execute();

            $_SESSION["exito"] = "Orden de trabajo rechazada eliminada correctamente.";
            header("Location: ../admin/listado_ordenes_trabajo_rechazadas.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar orden de trabajo: " . $e->getMessage());
            $errores[] = "Hubo un error al eliminar la orden de trabajo";
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../admin/listado_ordenes_trabajo_rechazadas.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar la orden de trabajo."];
    header("Location: ../admin/listado_ordenes_trabajo_rechazadas.php");
    exit();
}
