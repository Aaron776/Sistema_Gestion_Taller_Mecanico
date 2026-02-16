<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_vehiculo"]) && isset($_POST["id_cliente"])) {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_vehiculo = trim($_POST["id_vehiculo"]);
    $id_cliente = trim($_POST["id_cliente"]);
    $errores = [];


    // Validaciones y Sanitizacion
    if (empty($id_vehiculo)) {
        $errores[] = "El id del vehiculo es obligatorio";
    } elseif (!is_numeric($id_vehiculo)) {
        $errores[] = "El id del vehiculo debe ser un numero";
    } elseif ($id_vehiculo <= 0) {
        $errores[] = "El id del vehiculo debe ser mayor a 0";
    }

    if (empty($id_cliente)) {
        $errores[] = "El id del cliente es obligatorio";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente <= 0) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    // Verificar que el vehiculo exista
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT * FROM vehiculos WHERE id_vehiculo=:id_vehiculo AND id_cliente=:id_cliente");
            $sql->bindParam("id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $sql->bindParam("id_cliente", $id_cliente, PDO::PARAM_INT);
            $sql->execute();
            $vehiculo = $sql->fetch(PDO::FETCH_OBJ);
            if (!$vehiculo) {
                $errores[] = "El vehiculo no existe";
            }
        } catch (PDOException $e) {
            error_log("Error al verificar vehiculo: " . $e->getMessage());
            $errores[] = "Hubo un error al verificar el usuario";
        }
    }

    // Si no hay errores procedemos a eliminar el vehiculo
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("DELETE FROM vehiculos WHERE id_vehiculo=:id_vehiculo AND id_cliente=:id_cliente");
            $sql->bindParam("id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $sql->bindParam("id_cliente", $id_cliente, PDO::PARAM_INT);
            $sql->execute();

            // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Vehículo Eliminado";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha eliminado el vehiculo con placa: " . $vehiculo->placa;
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

            $_SESSION["exito"] = "Vehiculo eliminado correctamente";
            header("Location: ../recepcionista/vehiculos_cliente.php?id_cliente=" . base64_encode($id_cliente));
            exit();
        } catch (PDOException $e) {
            error_log("Error al eliminar vehiculo: " . $e->getMessage());
            $_SESSION["errores"] = ["Error al eliminar el vehículo. Intente nuevamente."];
            header("Location: ../recepcionista/vehiculos_cliente.php?id_cliente=" . base64_encode($id_cliente));
            exit();
        }
    } else {
        $_SESSION["errores"] = $errores;
        header("Location: ../recepcionista/vehiculos_cliente.php?id_cliente=" . base64_encode($id_cliente));
        exit();
    }
} else {
    $_SESSION["errores"] = ["Hubo un error al eliminar el vehiculo"];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
