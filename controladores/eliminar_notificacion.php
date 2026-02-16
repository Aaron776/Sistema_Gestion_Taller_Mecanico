<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_notificacion"])) {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_notificacion = trim($_POST["id_notificacion"]);
    $errores = [];


    // Validaciones y Sanitizacion
    if (empty($id_notificacion)) {
        $errores[] = "El id de la notificacion es obligatorio";
    } elseif (!is_numeric($id_notificacion)) {
        $errores[] = "El id de la notificacion debe ser un numero";
    } elseif ($id_notificacion <= 0) {
        $errores[] = "El id de la notificacion debe ser mayor a 0";
    }

    // Verificar que la notificacion exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM notificaciones WHERE id_notificacion=:id_notificacion");
            $sql->bindParam("id_notificacion", $id_notificacion, PDO::PARAM_INT);
            $sql->execute();
            $notificacion = $sql->fetch(PDO::FETCH_OBJ);
            if (!$notificacion) {
                $errores[] = "La notificacion no existe";
            }
        } catch (PDOException $e) {
            $errores[] = "Hubo un error al verificar la notificacion";
        }
    }

    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("DELETE FROM notificaciones WHERE id_notificacion=:id_notificacion");
            $sql->bindParam("id_notificacion", $id_notificacion, PDO::PARAM_INT);
            $sql->execute();

            $_SESSION["exito"] = "Notificacion eliminada correctamente";
            header("Location: ../admin/bitacora.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar la notificacion: " . $e->getMessage());
            $_SESSION["errores"] = ["Error al eliminar la notificacion. Intente nuevamente."];
            header("Location: ../admin/bitacora.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../admin/bitacora.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar la notificacion"];
    header("Location: ../admin/bitacora.php");
    exit();
}
