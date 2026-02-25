<?php
require_once '../conexion/session.php';
require_once "../conexion/bd.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_repuesto']) && isset($_POST['nombre_repuesto']) && isset($_POST['descripcion']) && isset($_POST['precio']) && isset($_POST['stock'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $nombre_repuesto = trim($_POST['nombre_repuesto']);
    $descripcion = trim($_POST['descripcion']);
    $precio = trim($_POST['precio']);
    $stock = trim($_POST['stock']);
    $id_repuesto = trim($_POST['id_repuesto']);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($nombre_repuesto)) {
        $errores[] = "El nombre del repuesto es obligatorio";
    } elseif (strlen($nombre_repuesto) < 3) {
        $errores[] = "El nombre del repuesto debe tener al menos 3 caracteres";
    } elseif (strlen($nombre_repuesto) > 100) {
        $errores[] = "El nombre del repuesto debe tener menos de 100 caracteres";
    }

    if (empty($id_repuesto)) {
        $errores[] = "El id del repuesto es obligatorio";
    } elseif (!is_numeric($id_repuesto)) {
        $errores[] = "El id del repuesto debe ser un numero";
    } elseif ($id_repuesto < 0) {
        $errores[] = "El id del repuesto debe ser mayor a 0";
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

    if (empty($stock)) {
        $errores[] = "El stock es obligatorio";
    } elseif (!is_numeric($stock)) {
        $errores[] = "El stock debe ser un numero";
    } elseif ($stock < 0) {
        $errores[] = "El stock debe ser mayor a 0";
    }

    // Validar si ese repuesto existe
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM repuestos WHERE id_repuesto = :id_repuesto");
            $sql->bindParam(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
            $sql->execute();
            $repuesto = $sql->fetch(PDO::FETCH_OBJ);

            if (!$repuesto) {
                $errores[] = "El repuesto no existe";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al buscar el repuesto: " . $e->getMessage();
        }
    }

    // Sino hay errores se procede a insertar el repuesto en la base de datos
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE repuestos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock WHERE id_repuesto = :id_repuesto");
            $sql->bindParam(':nombre', $nombre_repuesto, PDO::PARAM_STR);
            $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $sql->bindParam(':precio', $precio, PDO::PARAM_STR);
            $sql->bindParam(':stock', $stock, PDO::PARAM_STR);
            $sql->bindParam(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
            $sql->execute();

            // 🔍 VERIFICAR STOCK BAJO PARA NOTIFICACIÓN
            if ($stock < 10) {
                // Verificar si ya existe una notificación de stock bajo para este repuesto que no haya sido leída
                $titulo_notif_stock = "Bajo Stock: " . $nombre_repuesto;
                $stmt_check_notif = $conexion->prepare("SELECT id_notificacion FROM notificaciones WHERE titulo = :titulo AND leido = 'No'");
                $stmt_check_notif->bindParam(':titulo', $titulo_notif_stock, PDO::PARAM_STR);
                $stmt_check_notif->execute();

                if (!$stmt_check_notif->fetch()) {
                    $mensaje_stock = "El repuesto " . $nombre_repuesto . " tiene un stock bajo (" . $stock . " unidades). Se recomienda reabastecer.";
                    $tipo_notif_stock = 'warning';

                    // Como el admin es quien edita, el origen es el propio admin
                    $id_usuario_origen = $_SESSION['id_usuario'];
                    $rol_origen = $_SESSION['rol'];

                    $stmt_notif_stock = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario_origen, rol_origen) VALUES (:titulo, :mensaje, :tipo, :id_usuario, :rol)");
                    $stmt_notif_stock->bindParam(':titulo', $titulo_notif_stock, PDO::PARAM_STR);
                    $stmt_notif_stock->bindParam(':mensaje', $mensaje_stock, PDO::PARAM_STR);
                    $stmt_notif_stock->bindParam(':tipo', $tipo_notif_stock, PDO::PARAM_STR);
                    $stmt_notif_stock->bindParam(':id_usuario', $id_usuario_origen, PDO::PARAM_INT);
                    $stmt_notif_stock->bindParam(':rol', $rol_origen, PDO::PARAM_STR);
                    $stmt_notif_stock->execute();
                }
            }

            $_SESSION['exito'] = "Repuesto editado correctamente";
            header("Location: ../admin/editar_repuesto.php?id_repuesto=" . urlencode(base64_encode($id_repuesto)));
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar repuesto: " . $e->getMessage());
            $_SESSION['errores'] = ["Error de base de datos al editar el repuesto"];
            $_SESSION['datos_repuesto_edit_form'] = $_POST;
            header("Location: ../admin/editar_repuesto.php?id_repuesto=" . urlencode(base64_encode($id_repuesto)));
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_repuesto_edit_form'] = $_POST;
        header("Location: ../admin/editar_repuesto.php?id_repuesto=" . urlencode(base64_encode($id_repuesto)));
        exit();
    }
} else {
    $_SESSION['errores'] = ["Error al enviar el formulario"];
    header("Location: ../admin/gestion_repuestos.php");
    exit();
}
