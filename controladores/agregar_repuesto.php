<?php
require_once '../conexion/session.php';
require_once "../conexion/bd.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nombre_repuesto"]) && isset($_POST["descripcion"]) && isset($_POST["precio"]) && isset($_POST["stock"])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $nombre_repuesto = trim($_POST["nombre_repuesto"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = trim($_POST["precio"]);
    $stock = trim($_POST["stock"]);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($nombre_repuesto)) {
        $errores[] = "El nombre del repuesto es obligatorio.";
    } else if (strlen($nombre_repuesto) < 3) {
        $errores[] = "El nombre del repuesto debe tener al menos 3 caracteres.";
    } else if (strlen($nombre_repuesto) > 100) {
        $errores[] = "El nombre del repuesto debe tener menos de 100 caracteres.";
    }

    if (empty($descripcion)) {
        $errores[] = "La descripción del repuesto es obligatoria.";
    } else if (strlen($descripcion) < 3) {
        $errores[] = "La descripción del repuesto debe tener al menos 3 caracteres.";
    } else if (strlen($descripcion) > 255) {
        $errores[] = "La descripción del repuesto debe tener menos de 255 caracteres.";
    }

    if (empty($precio)) {
        $errores[] = "El precio del repuesto es obligatorio.";
    } else if (!is_numeric($precio)) {
        $errores[] = "El precio del repuesto debe ser un número.";
    } elseif ($precio < 0) {
        $errores[] = "El precio del repuesto debe ser mayor a 0.";
    } elseif ($precio > 1000000) {
        $errores[] = "El precio del repuesto debe ser menor a 1000000.";
    }

    if (empty($stock)) {
        $errores[] = "El stock del repuesto es obligatorio.";
    } else if (!is_numeric($stock)) {
        $errores[] = "El stock del repuesto debe ser un número.";
    } elseif ($stock < 0) {
        $errores[] = "El stock del repuesto debe ser mayor a 0.";
    } elseif ($stock > 1000000) {
        $errores[] = "El stock del repuesto debe ser menor a 1000000.";
    }

    // Si hay errores procedemos a guardar en la base de datos
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("INSERT INTO repuestos (nombre, descripcion, precio, stock) VALUES (:nombre, :descripcion, :precio, :stock);");
            $sql->bindParam(":nombre", $nombre_repuesto, PDO::PARAM_STR);
            $sql->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $sql->bindParam(":precio", $precio, PDO::PARAM_STR);
            $sql->bindParam(":stock", $stock, PDO::PARAM_STR);
            $sql->execute();

            $_SESSION["exito"] = "Repuesto agregado correctamente";
            header("Location: ../admin/agregar_repuesto.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al agregar repuesto: " . $e->getMessage());
            $errores[] = "Error al agregar el repuesto. Intente nuevamente.";
            $_SESSION["errores"] = $errores;
            header("Location: ../admin/agregar_repuesto.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        $_SESSION['datos_repuesto_form'] = $_POST;
        header("Location: ../admin/agregar_repuesto.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["No se pudo enviar el formulario"];
    header("Location: ../admin/agregar_repuesto.php");
    exit();
}
