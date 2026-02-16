<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden']) && isset($_POST['id_cliente']) && isset($_POST['estado']) && isset($_POST['mecanico'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_orden = trim($_POST['id_orden']);
    $id_cliente = trim($_POST['id_cliente']);
    $estado = trim($_POST['estado']);
    $id_mecanico = trim($_POST['mecanico']);
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

    if (empty($id_mecanico)) {
        $errores[] = "El id del mecanico es obligatorio";
    } elseif (!is_numeric($id_mecanico)) {
        $errores[] = "El id del mecanico debe ser un numero";
    } elseif ($id_mecanico <= 0) {
        $errores[] = "El id del mecanico debe ser mayor a 0";
    }

    $tipos_estado_permitidos = ['pendiente'];
    if (empty($estado)) {
        $errores[] = "El estado es obligatorio";
    } elseif (!in_array($estado, $tipos_estado_permitidos)) {
        $errores[] = "El estado debe ser 'pendiente'";
    }

    // Verficiar si esa orden de trabajo que le pertenece al cliente existe antes de editarla su estado
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM ordenes_trabajo WHERE id_orden = :id_orden AND id_cliente = :id_cliente AND estado='rechazada'");
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
            $sql = $conexion->prepare("UPDATE ordenes_trabajo SET estado = :estado,id_usuario_asignado=:id_mecanico WHERE id_orden = :id_orden AND id_cliente = :id_cliente");
            $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->bindParam(':estado', $estado, PDO::PARAM_STR);
            $sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
            $sql->execute();

            $_SESSION['exito'] = 'La orden de trabajo ha sido editada correctamente';
            header("Location: ../admin/listado_ordenes_trabajo_rechazadas.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar orden de trabajo: " . $e->getMessage());
            $errores[] = "Error al editar el estado de la orden. Intente nuevamente.";
        }
    }else{
        $_SESSION['errores'] = $errores;
        header("Location: ../admin/editar_orden_trabajo_rechazada.php?id_orden=" . base64_encode($id_orden) . "&id_cliente=" . base64_encode($id_cliente));
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../admin/listado_ordenes_trabajo_rechazadas.php");
    exit();
}
