<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente']) && isset($_POST['id_vehiculo']) && isset($_POST['id_mecanico']) && isset($_POST['id_orden']) && isset($_POST['mecanico']) && isset($_POST['fecha_entrega']) && isset($_POST['observaciones'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_cliente = trim($_POST['id_cliente']);
    $id_vehiculo = trim($_POST['id_vehiculo']);
    $id_mecanico = trim($_POST['id_mecanico']);
    $id_orden = trim($_POST['id_orden']);
    $mecanico = trim($_POST['mecanico']);
    $fecha_entrega = trim($_POST['fecha_entrega']);
    $observaciones = trim($_POST['observaciones']);
    $errores = [];

    //Validaciones Y sanitizacion
    if (empty($id_cliente)) {
        $errores[] = "El id del cliente es obligatorio";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente <= 0) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    if (empty($id_vehiculo)) {
        $errores[] = "El id del vehiculo es obligatorio";
    } elseif (!is_numeric($id_vehiculo)) {
        $errores[] = "El id del vehiculo debe ser un numero";
    } elseif ($id_vehiculo <= 0) {
        $errores[] = "El id del vehiculo debe ser mayor a 0";
    }

    if (empty($id_mecanico)) {
        $errores[] = "El id del mecanico es obligatorio";
    } elseif (!is_numeric($id_mecanico)) {
        $errores[] = "El id del mecanico debe ser un numero";
    } elseif ($id_mecanico <= 0) {
        $errores[] = "El id del mecanico debe ser mayor a 0";
    }

    if (empty($id_orden)) {
        $errores[] = "El id de la orden es obligatorio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El id de la orden debe ser un numero";
    } elseif ($id_orden <= 0) {
        $errores[] = "El id de la orden debe ser mayor a 0";
    }

    if (empty($mecanico)) {
        $errores[] = "El mecanico es obligatorio";
    } elseif (!is_numeric($mecanico)) {
        $errores[] = "El mecanico debe ser un numero";
    } elseif ($mecanico <= 0) {
        $errores[] = "El mecanico debe ser mayor a 0";
    }

    if (empty($fecha_entrega)) {
        $errores[] = "La fecha de entrega es obligatoria";
    } elseif (!strtotime($fecha_entrega)) {
        $errores[] = "La fecha de entrega no es valida";
    } elseif (strtotime($fecha_entrega) < time()) {
        $errores[] = "La fecha de entrega no puede ser anterior a la fecha y hora actual";
    }

    if (empty($observaciones)) {
        $errores[] = "La observaciones no es valida";
    } elseif (strlen($observaciones) < 3) {
        $errores[] = "La observaciones debe tener al menos 3 caracteres";
    } elseif (strlen($observaciones) > 200) {
        $errores[] = "La observaciones debe tener menos de 200 caracteres";
    }

    // Verficiar si esa orden de trabajo existe que le pertenece a ese cliente y a ese mecanico y a ese vehiculo
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM ordenes_trabajo WHERE id_orden = :id_orden AND id_cliente = :id_cliente AND id_usuario_asignado = :id_mecanico AND id_vehiculo = :id_vehiculo");
            $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->bindParam(':id_mecanico', $id_mecanico, PDO::PARAM_INT);
            $sql->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
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

    // Persistencia de datos en caso de errores
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_orden_trabajo_form_edit'] = $_POST;
        header("Location: ../recepcionista/editar_orden_trabajo.php?id_orden=" . base64_encode($id_orden) . "&id_cliente=" . base64_encode($id_cliente) . "&id_mecanico=" . base64_encode($id_mecanico) . "&id_vehiculo=" . base64_encode($id_vehiculo));
        exit();
    }

    // Si no hay errores, editar la orden de trabajo
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE ordenes_trabajo SET id_usuario_asignado = :mecanico, fecha_entrega = :fecha_entrega, observaciones = :observaciones WHERE id_orden = :id_orden");
            $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
            $sql->bindParam(':mecanico', $mecanico, PDO::PARAM_INT);
            $sql->bindParam(':fecha_entrega', $fecha_entrega, PDO::PARAM_STR);
            $sql->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $sql->execute();

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Orden de Trabajo Editada";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha editado la orden de trabajo con ID: " . $id_orden;
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

            $_SESSION['exito'] = 'Orden de Trabajo editada correctamente';
            header("Location: ../recepcionista/editar_orden_trabajo.php?id_orden=" . base64_encode($id_orden) . "&id_cliente=" . base64_encode($id_cliente) . "&id_vehiculo=" . base64_encode($id_vehiculo) . "&id_mecanico=" . base64_encode($id_mecanico));
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar orden de trabajo: " . $e->getMessage());
            $errores[] = "Error al editar la orden de trabajo. Intente nuevamente.";
            $_SESSION['datos_orden_trabajo_form_edit'] = $_POST;
        }
    }

    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_orden_trabajo_form_edit'] = $_POST;
        header("Location: ../recepcionista/editar_orden_trabajo.php?id_orden=" . base64_encode($id_orden) . "&id_cliente=" . base64_encode($id_cliente) . "&id_vehiculo=" . base64_encode($id_vehiculo) . "&id_mecanico=" . base64_encode($id_mecanico));
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
