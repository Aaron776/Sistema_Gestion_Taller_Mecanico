<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_usuario"])) {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_usuario = trim($_POST["id_usuario"]);
    $errores = [];


    // Validaciones y Sanitizacion
    if (empty($id_usuario)) {
        $errores[] = "El id del usuario es obligatorio";
    } elseif (!is_numeric($id_usuario)) {
        $errores[] = "El id del usuario debe ser un numero";
    } elseif ($id_usuario <= 0) {
        $errores[] = "El id del usuario debe ser mayor a 0";
    }

    // Validar que no se elimine a si mismo
    if ($id_usuario == $_SESSION['id_usuario']) {
        $errores[] = "No puedes eliminar tu propio usuario";
    }

    // Verificar que el usuario exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario=:id_usuario");
            $sql->bindParam("id_usuario", $id_usuario, PDO::PARAM_INT);
            $sql->execute();
            $usuario = $sql->fetch(PDO::FETCH_OBJ);
            if (!$usuario) {
                $errores[] = "El usuario no existe";
            }
        } catch (PDOException $e) {
            $errores[] = "Hubo un error al verificar el usuario";
        }
    }

    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE usuarios SET estado='Inactivo' WHERE id_usuario=:id_usuario");
            $sql->bindParam("id_usuario", $id_usuario, PDO::PARAM_INT);
            $sql->execute();
            $_SESSION["exito"] = "Usuario desactivado correctamente";
            header("Location: ../admin/gestion_usuarios.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            $_SESSION["errores"] = ["Error al desactivar el usuario. Intente nuevamente."];
            header("Location: ../admin/gestion_usuarios.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../admin/gestion_usuarios.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar el usuario"];
    header("Location: ../admin/gestion_usuarios.php");
    exit();
}
