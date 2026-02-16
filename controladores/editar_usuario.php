<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_usuario"]) && isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["email"]) && isset($_POST["telefono"]) && isset($_POST["rol"])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_usuario = trim($_POST["id_usuario"]);
    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $email = trim($_POST["email"]);
    $telefono = trim($_POST["telefono"]);
    $rol = trim($_POST["rol"]);
    $errores = [];

    // Validaciones Y sanitizacion

    if (empty($id_usuario)) {
        $errores[] = "El id del usuario es obligatorio";
    } elseif (!is_numeric($id_usuario)) {
        $errores[] = "El id del usuario debe ser un numero";
    } elseif ($id_usuario <= 0) {
        $errores[] = "El id del usuario debe ser mayor a 0";
    }

    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
        $errores[] = "El nombre debe contener solo letras y espacios";
    }

    if (empty($apellido)) {
        $errores[] = "El apellido es obligatorio";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $apellido)) {
        $errores[] = "El apellido debe contener solo letras y espacios";
    }

    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email debe ser un email valido";
    }

    if (empty($telefono)) {
        $errores[] = "El telefono es obligatorio";
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = "El telefono debe tener 10 digitos y solo numeros";
    }

    $roles_permitidos = ["admin", "mecanico", "recepcionista"];
    if (empty($rol)) {
        $errores[] = "El rol es obligatorio";
    } elseif (!in_array($rol, $roles_permitidos)) {
        $errores[] = "El rol debe ser administrador, mecanico o recepcionista";
    }

    // Verificar que no exista otro usuario con el mismo email y telefono 
    if (empty($errores)) {
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE (email = :email OR telefono = :telefono) AND id_usuario != :id_usuario");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->execute();
        $usuario_existente = $sql->fetch(PDO::FETCH_OBJ);
        if ($usuario_existente) {
            $errores[] = "El email o telefono ya pertenece a otro usuario";
        }
    }

    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, rol = :rol WHERE id_usuario = :id_usuario");
            $sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $sql->bindParam(':rol', $rol, PDO::PARAM_STR);
            $sql->execute();

            $_SESSION["exito"] = "Usuario editado correctamente";
            header("Location: ../admin/editar_usuario.php?id_usuario=" . $id_usuario);
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar usuario: " . $e->getMessage());
            $_SESSION["errores"] = ["Error de base de datos al editar el usuario"];
            $_SESSION['datos_usuario_edit_form'] = $_POST;
            header("Location: ../admin/editar_usuario.php?id_usuario=" . $id_usuario);
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        $_SESSION['datos_usuario_edit_form'] = $_POST;
        header("Location: ../admin/editar_usuario.php?id_usuario=" . $id_usuario);
        exit();
    }
} else {
    $_SESSION["errores"] = ["Solicitud inválida o faltan datos"];
    header("Location: ../admin/gestion_usuarios.php");
    exit();
}
