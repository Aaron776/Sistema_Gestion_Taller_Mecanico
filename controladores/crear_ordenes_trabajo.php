<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cliente']) && isset($_POST['id_vehiculo']) && isset($_POST['fecha_entrega'])  && isset($_POST['observaciones']) && isset($_POST['id_mecanico'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_cliente = trim($_POST['id_cliente']);
    $id_vehiculo = trim($_POST['id_vehiculo']);
    $fecha_entrega = trim($_POST['fecha_entrega']);
    $observaciones = trim($_POST['observaciones']);
    $id_mecanico = trim($_POST['id_mecanico']);
    $errores = [];

    // Validaciones Y Sanitizacion
    if (empty($id_cliente)) {
        $errores[] = "El cliente no es valido";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El cliente debe ser un numero";
    } elseif ($id_cliente < 1) {
        $errores[] = "El cliente debe ser mayor a 0";
    }


    if (empty($id_vehiculo)) {
        $errores[] = "El vehiculo no es valido";
    } elseif (!is_numeric($id_vehiculo)) {
        $errores[] = "El vehiculo debe ser un numero";
    } elseif ($id_vehiculo < 1) {
        $errores[] = "El vehiculo debe ser mayor a 0";
    }

    if (empty($id_mecanico)) {
        $errores[] = "El mecanico no es valido";
    } elseif (!is_numeric($id_mecanico)) {
        $errores[] = "El mecanico debe ser un numero";
    } elseif ($id_mecanico < 1) {
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

    // Persistencia de datos en caso de errores
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_orden_trabajo_form'] = $_POST;
        header("Location: ../recepcionista/crear_ordenes_trabajo.php");
        exit();
    }

    // En caso de que no haya errores procede a registrar la orden de trabajo
    try {
        $sql = $conexion->prepare("INSERT INTO ordenes_trabajo (id_cliente, id_vehiculo, id_usuario_asignado, fecha_entrega, observaciones, estado) VALUES (:id_cliente, :id_vehiculo, :id_usuario_asignado, :fecha_entrega, :observaciones, 'pendiente')");
        $sql->bindParam(':id_cliente', $id_cliente);
        $sql->bindParam(':id_vehiculo', $id_vehiculo);
        $sql->bindParam(':id_usuario_asignado', $id_mecanico);
        $fecha_entrega_db = date('Y-m-d H:i:s', strtotime($fecha_entrega));
        $sql->bindParam(':fecha_entrega', $fecha_entrega_db);
        $sql->bindParam(':observaciones', $observaciones);
        $sql->execute();

        $id_orden = $conexion->lastInsertId();

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
            $titulo = "Nueva Orden de Trabajo";
            $mensaje = "El recepcionista " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha creado la orden OT-" . date('Y') . "-" . $id_orden;
            $tipo_notif = 'success';
            $id_usuario = $_SESSION['id_usuario'];
            $rol = $_SESSION['rol'];

            $stmt_notif = $conexion->prepare("INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario_origen, rol_origen) VALUES (:titulo, :mensaje, :tipo, :id_usuario, :rol)");
            $stmt_notif->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt_notif->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
            $stmt_notif->bindParam(':tipo', $tipo_notif, PDO::PARAM_STR);
            $stmt_notif->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt_notif->bindParam(':rol', $rol, PDO::PARAM_STR);
            $stmt_notif->execute();
        }

        $_SESSION['exito'] = "Orden de trabajo creada correctamente";
        header("Location: ../recepcionista/crear_ordenes_trabajo.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error al registrar orden de trabajo: " . $e->getMessage());
        $_SESSION['errores'] = ["Error al registrar la orden de trabajo. Intente nuevamente."];
        $_SESSION['datos_orden_trabajo_form'] = $_POST;
        header("Location: ../recepcionista/crear_ordenes_trabajo.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ["No se ha enviado ningun dato"];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
