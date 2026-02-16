<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden']) && isset($_POST['id_cliente']) && isset($_POST['estado'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_orden = trim($_POST['id_orden']);
    $id_cliente = trim($_POST['id_cliente']);
    $estado = trim($_POST['estado']);
    $errores = [];

    //Validaciones Y sanitizacion
    if (empty($id_orden)) {
        $errores[] = "El id de la orden es obligatorio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El id de la orden debe ser un numero";
    } elseif ($id_orden <= 0) {
        $errores[] = "El id de la orden debe ser mayor a 0";
    }

    if (empty($id_cliente)) {
        $errores[] = "El id del cliente es obligatorio";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente <= 0) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    $tipos_estado_permitidos = ['en_proceso'];
    if (empty($estado)) {
        $errores[] = "El estado es obligatorio";
    } elseif (!in_array($estado, $tipos_estado_permitidos)) {
        $errores[] = "El estado debe ser 'en proceso'";
    }

    // Verficiar si esa orden de trabajo existe antes de editarla su estado
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM ordenes_trabajo WHERE id_orden = :id_orden AND id_cliente = :id_cliente");
            $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->execute();
            $orden = $sql->fetch(PDO::FETCH_OBJ);
            if (!$orden) {
                $errores[] = "La orden de trabajo no existe";
            }
        } catch (Exception $e) {
            error_log("Error al verificar la orden de trabajo: " . $e->getMessage());
            $errores[] = "Error al verificar la orden de trabajo. Intente nuevamente.";
        }
    }

    // Si no hay errores, editar la orden de trabajo
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE ordenes_trabajo SET estado = :estado WHERE id_orden = :id_orden AND id_cliente = :id_cliente");
            $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->bindParam(':estado', $estado, PDO::PARAM_STR);
            $sql->execute();

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Estado de la Orden de Trabajo Editada";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha editado el estado de la orden de trabajo con ID: " . $id_orden;
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

            $_SESSION['exito'] = 'Estado de la orden editada correctamente';
            header("Location: ../mecanico/gestion_ordenes_trabajo_asignados.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar orden de trabajo: " . $e->getMessage());
            $errores[] = "Error al editar el estado de la orden. Intente nuevamente.";
        }
    }else{
        $_SESSION['errores'] = $errores;
        header("Location: ../mecanico/editar_estado_orden_trabajo_asignada.php?id_orden=" . base64_encode($id_orden) . "&id_cliente=" . base64_encode($id_cliente));
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../mecanico/gestion_ordenes_trabajo_asignados.php");
    exit();
}
