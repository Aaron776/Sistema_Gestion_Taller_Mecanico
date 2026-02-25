<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_repuesto']) && isset($_POST['id_orden']) && isset($_POST['cantidad'])  && isset($_POST['precio_unitario'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_repuesto = trim($_POST['id_repuesto']);
    $id_orden = trim($_POST['id_orden']);
    $cantidad = trim($_POST['cantidad']);
    $precio_unitario = trim($_POST['precio_unitario']);
    $errores = [];

    // Validaciones Y Sanitizacion
    if (empty($id_repuesto)) {
        $errores[] = "El repuesto no puede estar vacio";
    } elseif (!is_numeric($id_repuesto)) {
        $errores[] = "El repuesto debe ser un numero";
    } elseif ($id_repuesto < 1) {
        $errores[] = "El repuesto debe ser mayor a 0";
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

    // En caso de que no haya errores procede a registrar la orden de trabajo
    try {
        $sql = $conexion->prepare("INSERT INTO orden_repuestos (id_orden, id_repuesto, cantidad, precio_unitario) VALUES (:id_orden, :id_repuesto, :cantidad, :precio_unitario)");
        $sql->bindParam(':id_orden', $id_orden, PDO::PARAM_INT);
        $sql->bindParam(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
        $sql->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $sql->bindParam(':precio_unitario', $precio_unitario, PDO::PARAM_STR);
        $sql->execute();

        $id_orden_repuesto = $conexion->lastInsertId();

        // 🛠️ DESCONTAR STOCK DEL REPUESTO
        $stmt_update_stock = $conexion->prepare("UPDATE repuestos SET stock = stock - :cantidad WHERE id_repuesto = :id_repuesto");
        $stmt_update_stock->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt_update_stock->bindParam(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
        $stmt_update_stock->execute();

        // 🔍 VERIFICAR STOCK BAJO PARA NOTIFICACIÓN
        $stmt_check_stock = $conexion->prepare("SELECT nombre, stock FROM repuestos WHERE id_repuesto = :id_repuesto");
        $stmt_check_stock->bindParam(':id_repuesto', $id_repuesto, PDO::PARAM_INT);
        $stmt_check_stock->execute();
        $repuesto_info = $stmt_check_stock->fetch(PDO::FETCH_OBJ);

        if ($repuesto_info && $repuesto_info->stock < 10) {
            // Verificar si ya existe una notificación de stock bajo para este repuesto que no haya sido leída
            $titulo_notif_stock = "Bajo Stock: " . $repuesto_info->nombre;
            $stmt_check_notif = $conexion->prepare("SELECT id_notificacion FROM notificaciones WHERE titulo = :titulo AND leido = 'No'");
            $stmt_check_notif->bindParam(':titulo', $titulo_notif_stock, PDO::PARAM_STR);
            $stmt_check_notif->execute();

            if (!$stmt_check_notif->fetch()) {
                $mensaje_stock = "El repuesto " . $repuesto_info->nombre . " tiene un stock bajo (" . $repuesto_info->stock . " unidades). Se recomienda reabastecer.";
                $tipo_notif_stock = 'warning';

                // Usamos el ID de usuario de la sesión para el origen
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

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN (Sobre la creación de la orden)
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
            $titulo = "Nueva Orden de Repuesto";
            $mensaje = "El mecanico " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha creado la orden OR-" . date('Y') . "-" . $id_orden_repuesto;
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

        $_SESSION['exito'] = "Orden de repuesto creada correctamente";
        header("Location: ../mecanico/crear_ordenes_repuestos.php?id_orden=" . base64_encode($id_orden));
        exit();
    } catch (PDOException $e) {
        error_log("Error al registrar orden de repuesto: " . $e->getMessage());
        $_SESSION['errores'] = ["Error al registrar la orden de repuesto. Intente nuevamente."];
        header("Location: ../mecanico/crear_ordenes_repuestos.php?id_orden=" . base64_encode($id_orden));
        exit();
    }
} else {
    $_SESSION['errores'] = ["No se ha enviado ningun dato"];
    header("Location: ../mecanico/gestion_ordenes_trabajo_en_proceso.php");
    exit();
}
