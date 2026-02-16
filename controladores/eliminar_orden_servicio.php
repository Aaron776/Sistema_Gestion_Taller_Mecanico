<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden_servicio']) && isset($_POST['id_orden'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_orden_servicio = trim($_POST['id_orden_servicio']);
    $id_orden = trim($_POST['id_orden']);
    $errores = [];

    //Validaciones Y sanitizacion
    if (empty($id_orden_servicio)) {
        $errores[] = "El id del orden de servicio es obligatorio";
    } elseif (!is_numeric($id_orden_servicio)) {
        $errores[] = "El id del orden de servicio debe ser un numero";
    } elseif ($id_orden_servicio <= 0) {
        $errores[] = "El id del orden de servicio debe ser mayor a 0";
    }

    if (empty($id_orden)) {
        $errores[] = "El id del orden es obligatorio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El id del orden debe ser un numero";
    } elseif ($id_orden <= 0) {
        $errores[] = "El id del orden debe ser mayor a 0";
    }

    // Verficiar si el orden de servicio existe y le pertenece a a esa orden de trabajo
    if (empty($errores)) {
        $sql = $conexion->prepare("SELECT * FROM orden_servicios WHERE id_detalle = :id_orden_servicio AND id_orden = :id_orden");
        $sql->bindParam(':id_orden_servicio', $id_orden_servicio, PDO::PARAM_INT);
        $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
        $sql->execute();
        $ordencliente = $sql->fetch(PDO::FETCH_OBJ);
        if (!$ordencliente) {
            $errores[] = "El orden de servicio no existe o no pertenece a esa orden de trabajo";
        }
    }

    // Si no hay errores, eliminar el orden de servicio
    if (empty($errores)) {
        $sql = $conexion->prepare("DELETE FROM orden_servicios WHERE id_detalle = :id_orden_servicio AND id_orden = :id_orden");
        $sql->bindParam(':id_orden_servicio', $id_orden_servicio, PDO::PARAM_INT);
        $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
        $sql->execute();

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
            $titulo = "Orden de Servicio Eliminada";
            $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha eliminado la orden de servicio con ID: " . $id_orden_servicio . " y orden de trabajo con ID: " . $id_orden;
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

        $_SESSION['exito'] = 'Orden de servicio eliminada correctamente';
        header("Location: ../recepcionista/gestion_ordenes_servicios.php");
        exit();
    } else {
        $_SESSION['errores'] = $errores;
        header("Location: ../recepcionista/gestion_ordenes_servicios.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../recepcionista/gestion_ordenes_servicios.php");
    exit();
}
