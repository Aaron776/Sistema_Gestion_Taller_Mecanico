<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_servicio"])) {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_servicio = trim($_POST["id_servicio"]);
    $errores = [];


    // Validaciones y Sanitizacion
    if (empty($id_servicio)) {
        $errores[] = "El id del servicio es obligatorio";
    } elseif (!is_numeric($id_servicio)) {
        $errores[] = "El id del servicio debe ser un numero";
    } elseif ($id_servicio <= 0) {
        $errores[] = "El id del servicio debe ser mayor a 0";
    }

    // Verificar que el servicio exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio=:id_servicio");
            $sql->bindParam("id_servicio", $id_servicio, PDO::PARAM_INT);
            $sql->execute();
            $servicio = $sql->fetch(PDO::FETCH_OBJ);
            if (!$servicio) {
                $errores[] = "El servicio no existe";
            }
        } catch (PDOException $e) {
            $errores[] = "Hubo un error al verificar el servicio";
        }
    }

    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("DELETE FROM servicios WHERE id_servicio=:id_servicio");
            $sql->bindParam("id_servicio", $id_servicio, PDO::PARAM_INT);
            $sql->execute();
            $_SESSION["exito"] = "Servicio eliminado correctamente";
            header("Location: ../admin/gestion_servicios.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar servicio: " . $e->getMessage());
            $_SESSION["errores"] = ["Error al eliminar el servicio. Intente nuevamente."];
            header("Location: ../admin/gestion_servicios.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../admin/gestion_servicios.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar el servicio"];
    header("Location: ../admin/gestion_servicios.php");
    exit();
}
