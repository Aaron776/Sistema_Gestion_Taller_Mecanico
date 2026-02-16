<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["password"]) && isset($_POST["confirmPassword"]) && isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["email"]) && isset($_POST["telefono"]) && isset($_POST["rol"])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    // Obtener datos del formulario
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);
    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $email = trim($_POST["email"]);
    $telefono = trim($_POST["telefono"]);
    $rol = trim($_POST["rol"]);
    $errores = [];

    // Validaciones Y sanitizacion

    if (empty($password)) {
        $errores[] = "El password es obligatorio";
    } elseif ($password !== $confirmPassword) {
        $errores[] = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 5) {
        $errores[] = "La contraseña debe tener al menos 5 caracteres";
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


    if (empty($errores)) {
        try {
            $password_hasheada = password_hash($password, PASSWORD_DEFAULT);
            $sql = $conexion->prepare("INSERT INTO usuarios ( nombre, apellido, email, password, telefono, rol) VALUES (:nombre, :apellido, :email, :password, :telefono, :rol)");
            $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':password', $password_hasheada, PDO::PARAM_STR);
            $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $sql->bindParam(':rol', $rol, PDO::PARAM_STR);
            $sql->execute();

            $_SESSION["exito"] = "Usuario agregado correctamente";
            header("Location: ../admin/agregar_usuario.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al agregar usuario: " . $e->getMessage());
            $_SESSION["errores"] = ["Error de base de datos al agregar el usuario"];
            header("Location: ../admin/agregar_usuario.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        $_SESSION['datos_usuario_form'] = $_POST;
        header("Location: ../admin/agregar_usuario.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Solicitud inválida o faltan datos"];
    header("Location: ../admin/agregar_usuario.php");
    exit();
}
