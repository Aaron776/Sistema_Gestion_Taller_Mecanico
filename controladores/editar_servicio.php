<?php
require_once '../conexion/session.php';
require_once "../conexion/bd.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_servicio']) && isset($_POST['nombre_servicio']) && isset($_POST['descripcion']) && isset($_POST['precio_base'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $nombre_servicio = trim($_POST['nombre_servicio']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio_base']);
    $id_servicio = trim($_POST['id_servicio']);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($nombre_servicio)) {
        $errores[] = "El nombre del servicio es obligatorio";
    } elseif (strlen($nombre_servicio) < 3) {
        $errores[] = "El nombre del servicio debe tener al menos 3 caracteres";
    } elseif (strlen($nombre_servicio) > 100) {
        $errores[] = "El nombre del servicio debe tener menos de 100 caracteres";
    }

    if (empty($id_servicio)) {
        $errores[] = "El id del servicio es obligatorio";
    } elseif (!is_numeric($id_servicio)) {
        $errores[] = "El id del servicio debe ser un numero";
    } elseif ($id_servicio < 0) {
        $errores[] = "El id del servicio debe ser mayor a 0";
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

    // Validar si ese servicio existe
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio = :id_servicio");
            $sql->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $sql->execute();
            $servicio = $sql->fetch(PDO::FETCH_OBJ);

            if (!$servicio) {
                $errores[] = "El servicio no existe";
            }
        } catch (PDOException $e) {
            error_log("Error al buscar el servicio: " . $e->getMessage());
            $errores[] = "Error de base de datos al buscar el servicio";
        }
    }

    // Sino hay errores se procede a insertar el servicio en la base de datos
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE servicios SET nombre_servicio = :nombre_servicio, descripcion = :descripcion, precio_base = :precio_base WHERE id_servicio = :id_servicio");
            $sql->bindParam(':nombre_servicio', $nombre_servicio, PDO::PARAM_STR);
            $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $sql->bindParam(':precio_base', $precio, PDO::PARAM_STR);
            $sql->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $sql->execute();

            $_SESSION['exito'] = "Servicio editado correctamente";
            header("Location: ../admin/editar_servicio.php?id_servicio=" . $id_servicio);
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar servicio: " . $e->getMessage());
            $_SESSION['errores'] = ["Error de base de datos al editar el servicio"];
            $_SESSION['datos_servicio_edit_form'] = $_POST;
            header("Location: ../admin/editar_servicio.php?id_servicio=" . $id_servicio);
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_servicio_edit_form'] = $_POST;
        header("Location: ../admin/editar_servicio.php?id_servicio=" . $id_servicio);
        exit();
    }
} else {
    $_SESSION['errores'] = ["Error al enviar el formulario"];
    header("Location: ../admin/gestion_servicios.php");
    exit();
}
