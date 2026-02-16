<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_cliente = trim($_POST['id_cliente']);
    $errores = [];

    //Validaciones Y sanitizacion
    if (empty($id_cliente)) {
        $errores[] = "El id del cliente es obligatorio";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente <= 0) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    // Verficiar si el cliente existe
    if (empty($errores)) {
        $sql = $conexion->prepare("SELECT * FROM clientes WHERE id_cliente = :id_cliente");
        $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $sql->execute();
        $cliente = $sql->fetch(PDO::FETCH_OBJ);
        if (!$cliente) {
            $errores[] = "El cliente no existe";
        }
    }

    // Si no hay errores, eliminar el cliente
    if (empty($errores)) {
        $sql = $conexion->prepare("DELETE FROM clientes WHERE id_cliente = :id_cliente");
        $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $sql->execute();

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
        if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
            $titulo = "Cliente Eliminado";
            $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha eliminado al cliente con ID: " . $id_cliente;
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

        $_SESSION['exito'] = 'Cliente eliminado correctamente';
        header("Location: ../recepcionista/gestion_clientes.php");
        exit();
    } else {
        $_SESSION['errores'] = $errores;
        header("Location: ../recepcionista/gestion_clientes.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
