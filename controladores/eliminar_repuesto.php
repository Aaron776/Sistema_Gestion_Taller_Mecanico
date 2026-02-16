<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_repuesto"])) {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_repuesto = trim($_POST["id_repuesto"]);
    $errores = [];


    // Validaciones y Sanitizacion
    if (empty($id_repuesto)) {
        $errores[] = "El id del repuesto es obligatorio";
    } elseif (!is_numeric($id_repuesto)) {
        $errores[] = "El id del repuesto debe ser un numero";
    } elseif ($id_repuesto <= 0) {
        $errores[] = "El id del repuesto debe ser mayor a 0";
    }

    // Verificar que el repuesto exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM repuestos WHERE id_repuesto=:id_repuesto");
            $sql->bindParam("id_repuesto", $id_repuesto, PDO::PARAM_INT);
            $sql->execute();
            $repuesto = $sql->fetch(PDO::FETCH_OBJ);
            if (!$repuesto) {
                $errores[] = "El repuesto no existe";
            }
        } catch (PDOException $e) {
            error_log("Error al verificar repuesto: " . $e->getMessage());
            $errores[] = "Hubo un error al verificar el repuesto";
        }
    }

    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("DELETE FROM repuestos WHERE id_repuesto=:id_repuesto");
            $sql->bindParam("id_repuesto", $id_repuesto, PDO::PARAM_INT);
            $sql->execute();
            $_SESSION["exito"] = "Repuesto eliminado correctamente";
            header("Location: ../admin/gestion_repuestos.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar repuesto: " . $e->getMessage());
            $_SESSION["errores"] = ["Error al eliminar el repuesto. Intente nuevamente."];
            header("Location: ../admin/gestion_repuestos.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../admin/gestion_repuestos.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar el repuesto"];
    header("Location: ../admin/gestion_repuestos.php");
    exit();
}
