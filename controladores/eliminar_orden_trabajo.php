<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

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

    // Verificar que la orden de trabajo exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM ordenes_trabajo WHERE id_orden = :id_orden");
            $sql->bindParam(':id_orden', $id_orden);
            $sql->execute();
            $orden = $sql->fetch(PDO::FETCH_OBJ);
            if (!$orden) {
                $errores[] = "La orden de trabajo no existe";
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

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN O MECÁNICO
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Orden de Trabajo Eliminada";
                $mensaje = "El recepcionista " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha eliminado la orden OT-" . date('Y') . "-" . $id_orden;
                $tipo_notif = 'warning';
                $id_usuario = $_SESSION['id_usuario'];
                $rol = $_SESSION['rol'];

                $stmt_notif = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario_origen, rol_origen) VALUES (:titulo, :mensaje, :tipo, :id_usuario, :rol)");
                $stmt_notif->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt_notif->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
                $stmt_notif->bindParam(':tipo', $tipo_notif, PDO::PARAM_STR);
                $stmt_notif->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt_notif->bindParam(':rol', $rol, PDO::PARAM_STR);
                $stmt_notif->execute();
            }

            $_SESSION["exito"] = "Orden de trabajo eliminada correctamente.";
            header("Location: ../recepcionista/gestion_ordenes_trabajo.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar orden de trabajo: " . $e->getMessage());
            $errores[] = "Hubo un error al eliminar la orden de trabajo";
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../recepcionista/gestion_ordenes_trabajo.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar la orden de trabajo."];
    header("Location: ../recepcionista/gestion_ordenes_trabajo.php");
    exit();
}
