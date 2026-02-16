<?php
session_start();
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_orden_servicio']) && isset($_POST['cantidad']) && isset($_POST['precio_unitario']) && isset($_POST['id_orden_trabajo']) && isset($_POST['id_servicio']) && isset($_POST['id_orden'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_orden_servicio_editar = trim($_POST['id_orden_servicio']);
    $cantidad = trim($_POST['cantidad']);
    $precio_unitario = trim($_POST['precio_unitario']);
    $id_orden_trabajo_editar = trim($_POST['id_orden_trabajo']);
    $id_servicio = trim($_POST['id_servicio']);
    $id_orden = trim($_POST['id_orden']);
    $errores = [];

    // Validaciones y Sanitizacion
    if (empty($id_orden_servicio_editar)) {
        $errores[] = "El id de la orden de servicio es obligatorio";
    } elseif (!is_numeric($id_orden_servicio_editar)) {
        $errores[] = "El id de la orden de servicio debe ser un numero";
    } elseif ($id_orden_servicio_editar <= 0) {
        $errores[] = "El id de la orden de servicio debe ser mayor a 0";
    }

    if (empty($cantidad)) {
        $errores[] = "La cantidad es obligatoria";
    } elseif (!is_numeric($cantidad)) {
        $errores[] = "La cantidad debe ser un numero";
    } elseif ($cantidad <= 0) {
        $errores[] = "La cantidad debe ser mayor a 0";
    }

    if (empty($precio_unitario)) {
        $errores[] = "El precio unitario es obligatorio";
    } elseif (!is_numeric($precio_unitario)) {
        $errores[] = "El precio unitario debe ser un numero";
    } elseif ($precio_unitario <= 0) {
        $errores[] = "El precio unitario debe ser mayor a 0";
    }

    if (empty($id_orden_trabajo_editar)) {
        $errores[] = "El id de la orden de trabajo es obligatorio";
    } elseif (!is_numeric($id_orden_trabajo_editar)) {
        $errores[] = "El id de la orden de trabajo debe ser un numero";
    } elseif ($id_orden_trabajo_editar <= 0) {
        $errores[] = "El id de la orden de trabajo debe ser mayor a 0";
    }

    if (empty($id_servicio)) {
        $errores[] = "El id del servicio es obligatorio";
    } elseif (!is_numeric($id_servicio)) {
        $errores[] = "El id del servicio debe ser un numero";
    } elseif ($id_servicio <= 0) {
        $errores[] = "El id del servicio debe ser mayor a 0";
    }

    if (empty($id_orden)) {
        $errores[] = "El id de la orden es obligatorio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El id de la orden debe ser un numero";
    } elseif ($id_orden <= 0) {
        $errores[] = "El id de la orden debe ser mayor a 0";
    }

    // Verificar si existe la orden de servicio que se va a editar
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM orden_servicios WHERE id_detalle = :id_orden_servicio AND id_orden = :id_orden");
            $sql->bindParam(':id_orden_servicio', $id_orden_servicio_editar, PDO::PARAM_INT);
            $sql->bindParam(':id_orden', $id_orden_trabajo_editar, PDO::PARAM_INT);
            $sql->execute();
            $orden_servicio = $sql->fetch(PDO::FETCH_OBJ);
            if (!$orden_servicio) {
                $errores[] = "La orden de servicio no existe";
            }
        } catch (Exception $e) {
            error_log("Error al verificar la orden de servicio: " . $e->getMessage());
            $errores[] = "Error al verificar la orden de servicio. Intente nuevamente.";
        }
    }

    // Si no hay errores procedemos a editar
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE orden_servicios SET cantidad = :cantidad, precio_unitario = :precio_unitario, id_servicio = :id_servicio WHERE id_detalle = :id_orden_servicio AND id_orden = :id_orden");
            $sql->bindParam(':id_orden_servicio', $id_orden_servicio_editar, PDO::PARAM_INT);
            $sql->bindParam(':id_orden', $id_orden_trabajo_editar, PDO::PARAM_INT);
            $sql->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $sql->bindParam(':precio_unitario', $precio_unitario, PDO::PARAM_STR);
            $sql->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
            $sql->execute();

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Orden de Servicio Editada";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha editado la orden de servicio con ID: " . $id_orden_servicio_editar;
                $tipo = 'warning';
                $id_usuario = $_SESSION['id_usuario'];
                $rol = $_SESSION['rol'];

                $stmt_notif = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario_origen, rol_origen) VALUES (:titulo, :mensaje, :tipo, :id_usuario, :rol)");
                $stmt_notif->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt_notif->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
                $stmt_notif->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                $stmt_notif->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt_notif->bindParam(':rol', $rol, PDO::PARAM_STR);
                $stmt_notif->execute();
            }

            $_SESSION['exito'] = 'Orden de servicio editada correctamente';
            header('Location: ../recepcionista/editar_orden_servicio.php?id_orden_servicio=' . base64_encode($id_orden_servicio_editar) . '&id_orden_trabajo=' . base64_encode($id_orden_trabajo_editar));
            exit();
        } catch (Exception $e) {
            error_log("Error al editar la orden de servicio: " . $e->getMessage());
            $errores[] = "Error al editar la orden de servicio. Intente nuevamente.";
        }
    } else {
        $_SESSION['errores'] = $errores;
        header('Location: ../recepcionista/editar_orden_servicio.php?id_orden_servicio=' . base64_encode($id_orden_servicio_editar) . '&id_orden_trabajo=' . base64_encode($id_orden_trabajo_editar));
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar los datos del formulario'];
    header('Location: ../recepcionista/gestion_ordenes_servicios.php');
    exit();
}
