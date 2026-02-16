<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['cedula']) && isset($_POST['telefono']) && isset($_POST['email']) && isset($_POST['direccion'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_cliente = trim($_POST['id_cliente']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cedula = trim($_POST['cedula']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    $errores = [];

    //Validaciones Y sanitizacion
    if (empty($id_cliente)) {
        $errores[] = "El id del cliente es obligatorio";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente <= 0) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    if (empty($nombre)) {
        $errores[] = "El nombre del cliente es obligatorio";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
        $errores[] = "El nombre del cliente debe contener solo letras y espacios";
    }

    if (empty($apellido)) {
        $errores[] = "El apellido del cliente es obligatorio";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $apellido)) {
        $errores[] = "El apellido del cliente debe contener solo letras y espacios";
    }

    if (empty($cedula)) {
        $errores[] = "La cédula/RUC del cliente es obligatoria";
    } elseif (!is_numeric($cedula)) {
        $errores[] = "La cédula/RUC del cliente debe ser un número";
    } elseif (strlen($cedula) < 8 || strlen($cedula) > 10) {
        $errores[] = "La cédula/RUC del cliente debe tener entre 8 y 10 dígitos";
    }

    if (empty($telefono)) {
        $errores[] = "El telefono es obligatorio";
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = "El telefono debe tener 10 digitos y solo numeros";
    }

    if (empty($email)) {
        $errores[] = "El email del cliente es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email del cliente debe ser válido";
    }

    if (empty($direccion)) {
        $errores[] = "La dirección del cliente es obligatoria";
    } elseif (!preg_match('/^[a-zA-Z0-9\s,\.]+$/', $direccion)) {
        $errores[] = "La dirección del cliente debe contener solo letras, números, comas y puntos";
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

    // Si no hay errores, editar el cliente
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("UPDATE clientes SET nombre = :nombre, apellido = :apellido, cedula_ruc = :cedula_ruc, telefono = :telefono, email = :email, direccion = :direccion WHERE id_cliente = :id_cliente");
            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $sql->bindParam(':cedula_ruc', $cedula, PDO::PARAM_STR);
            $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $sql->bindParam(':email', $email, PDO::PARAM_STR);
            $sql->bindParam(':direccion', $direccion, PDO::PARAM_STR);
            $sql->execute();

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Cliente Editado";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha editado al cliente con ID: " . $id_cliente;
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

            $_SESSION['exito'] = 'Cliente editado correctamente';
            header("Location: ../recepcionista/editar_cliente.php?id_cliente=" . base64_encode($id_cliente));
            exit();
        } catch (PDOException $e) {
            error_log("Error al editar cliente: " . $e->getMessage());
            $errores[] = "Error al editar el cliente. Intente nuevamente.";
            $_SESSION['datos_cliente_edit_form'] = $_POST;
        }
    }

    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_cliente_edit_form'] = $_POST;
        header("Location: ../recepcionista/editar_cliente.php?id_cliente=" . base64_encode($id_cliente));
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
