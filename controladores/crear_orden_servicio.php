<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_servicio']) && isset($_POST['id_orden']) && isset($_POST['cantidad'])  && isset($_POST['precio_unitario'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_servicio = trim($_POST['id_servicio']);
    $id_orden = trim($_POST['id_orden']);
    $cantidad = trim($_POST['cantidad']);
    $precio_unitario = trim($_POST['precio_unitario']);
    $errores = [];

    // Validaciones Y Sanitizacion
    if (empty($id_servicio)) {
        $errores[] = "El servicio no puede estar vacio";
    } elseif (!is_numeric($id_servicio)) {
        $errores[] = "El servicio debe ser un numero";
    } elseif ($id_servicio < 1) {
        $errores[] = "El servicio debe ser mayor a 0";
    }


    if (empty($id_orden)) {
        $errores[] = "El orden no puede estar vacio";
    } elseif (!is_numeric($id_orden)) {
        $errores[] = "El orden debe ser un numero";
    } elseif ($id_orden < 1) {
        $errores[] = "El orden debe ser mayor a 0";
    }

    if (empty($cantidad)) {
        $errores[] = "La cantidad no puede estar vacia";
    } elseif (!is_numeric($cantidad)) {
        $errores[] = "La cantidad debe ser un numero";
    } elseif ($cantidad < 1) {
        $errores[] = "La cantidad debe ser mayor a 0";
    }

    if (empty($precio_unitario)) {
        $errores[] = "El precio unitario no puede estar vacio";
    } elseif (!is_numeric($precio_unitario)) {
        $errores[] = "El precio unitario debe ser un numero";
    } elseif ($precio_unitario < 0) {
        $errores[] = "El precio unitario debe ser mayor a 0";
    }



    // Persistencia de datos en caso de errores
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_orden_servicio_form'] = $_POST;
        header("Location: ../recepcionista/crear_orden_servicio.php");
        exit();
    }

    // En caso de que no haya errores procede a registrar la orden de trabajo
    try {
        $sql = $conexion->prepare("INSERT INTO orden_servicios (id_orden, id_servicio, cantidad, precio_unitario) VALUES (:id_orden, :id_servicio, :cantidad, :precio_unitario)");
        $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
        $sql->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $sql->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $sql->bindParam(':precio_unitario', $precio_unitario, PDO::PARAM_STR);
        $sql->execute();

        $id_orden_servicio = $conexion->lastInsertId();

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
            $titulo = "Nueva Orden de Servicio";
            $mensaje = "El recepcionista " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha creado la orden OS-" . date('Y') . "-" . $id_orden_servicio;
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

        $_SESSION['exito'] = "Orden de servicio creada correctamente";
        header("Location: ../recepcionista/crear_orden_servicio.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error al registrar orden de servicio: " . $e->getMessage());
        $_SESSION['errores'] = ["Error al registrar la orden de servicio. Intente nuevamente."];
        $_SESSION['datos_orden_servicio_form'] = $_POST;
        header("Location: ../recepcionista/crear_orden_servicio.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ["No se ha enviado ningun dato"];
    header("Location: ../recepcionista/gestion_ordenes_servicios.php");
    exit();
}
