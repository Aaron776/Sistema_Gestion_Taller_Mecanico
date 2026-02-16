<?php
require_once '../conexion/session.php';
require_once "../conexion/bd.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_servicio']) && isset($_POST['descripcion']) && isset($_POST['precio'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $nombre_servicio = trim($_POST['nombre_servicio']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio']);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($nombre_servicio)) {
        $errores[] = "El nombre del servicio es obligatorio";
    } elseif (strlen($nombre_servicio) < 3) {
        $errores[] = "El nombre del servicio debe tener al menos 3 caracteres";
    } elseif (strlen($nombre_servicio) > 100) {
        $errores[] = "El nombre del servicio debe tener menos de 100 caracteres";
    }

    if (empty($descripcion)) {
        $errores[] = "La descripcion es obligatoria";
    } elseif (strlen($descripcion) < 10) {
        $errores[] = "La descripcion debe tener al menos 10 caracteres";
    } elseif (strlen($descripcion) > 500) {
        $errores[] = "La descripcion debe tener menos de 500 caracteres";
    }

    if (empty($precio)) {
        $errores[] = "El precio es obligatorio";
    } elseif (!is_numeric($precio)) {
        $errores[] = "El precio debe ser un numero";
    } elseif ($precio < 0) {
        $errores[] = "El precio debe ser mayor a 0";
    }

    // Sino hay errores se procede a insertar el servicio en la base de datos
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("INSERT INTO servicios (nombre_servicio, descripcion, precio_base) VALUES (:nombre_servicio, :descripcion, :precio_base)");
            $sql->bindParam(':nombre_servicio', $nombre_servicio, PDO::PARAM_STR);
            $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $sql->bindParam(':precio_base', $precio, PDO::PARAM_STR);
            $sql->execute();

            $_SESSION['exito'] = "Servicio agregado correctamente";
            header("Location: ../admin/agregar_servicio.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al agregar servicio: " . $e->getMessage());
            $_SESSION['errores'] = ["Error de base de datos al agregar el servicio"];
            header("Location: ../admin/agregar_servicio.php");
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_servicio_form'] = $_POST;
        header("Location: ../admin/agregar_servicio.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ["Error al enviar el formulario"];
    header("Location: ../admin/agregar_servicio.php");
    exit();
}
