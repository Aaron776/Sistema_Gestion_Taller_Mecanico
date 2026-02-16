<?php
require_once '../conexion/session.php';
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_vehiculo']) && isset($_POST['id_cliente']) && isset($_POST['marca']) && isset($_POST['modelo']) && isset($_POST['anio']) && isset($_POST['placa']) && isset($_POST['tipo'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_vehiculo = trim($_POST['id_vehiculo']);
    $id_cliente = trim($_POST['id_cliente']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $anio = trim($_POST['anio']);
    $placa = trim($_POST['placa']);
    $tipo = trim($_POST['tipo']);
    $errores = [];

    // Validaciones Y Sanitizacion
    if (empty($id_vehiculo)) {
        $errores[] = "El id del vehiculo no es valido";
    } elseif (!is_numeric($id_vehiculo)) {
        $errores[] = "El id del vehiculo debe ser un numero";
    } elseif ($id_vehiculo < 1) {
        $errores[] = "El id del vehiculo debe ser mayor a 0";
    }

    if (empty($id_cliente)) {
        $errores[] = "El id del cliente no es valido";
    } elseif (!is_numeric($id_cliente)) {
        $errores[] = "El id del cliente debe ser un numero";
    } elseif ($id_cliente < 1) {
        $errores[] = "El id del cliente debe ser mayor a 0";
    }

    if (empty($marca)) {
        $errores[] = "La marca no es valida";
    } elseif (strlen($marca) < 3) {
        $errores[] = "La marca debe tener al menos 3 caracteres";
    } elseif (strlen($marca) > 100) {
        $errores[] = "La marca debe tener menos de 100 caracteres";
    }

    if (empty($modelo)) {
        $errores[] = "El modelo no es valido";
    } elseif (strlen($modelo) < 3) {
        $errores[] = "El modelo debe tener al menos 3 caracteres";
    } elseif (strlen($modelo) > 100) {
        $errores[] = "El modelo debe tener menos de 100 caracteres";
    }

    if (empty($anio)) {
        $errores[] = "El anio no es valido";
    } elseif (!is_numeric($anio)) {
        $errores[] = "El anio debe ser un numero";
    } elseif ($anio < 1900) {
        $errores[] = "El anio debe ser mayor a 1900";
    } elseif ($anio > (date('Y') + 1)) {
        $errores[] = "El anio debe ser menor o igual a " . (date('Y') + 1);
    }

    if (empty($placa)) {
        $errores[] = "La placa no es valida";
    } elseif (strlen($placa) < 3) {
        $errores[] = "La placa debe tener al menos 3 caracteres";
    } elseif (strlen($placa) > 20) {
        $errores[] = "La placa debe tener menos de 20 caracteres";
    }

    if (empty($tipo)) {
        $errores[] = "El tipo no es valido";
    } elseif (strlen($tipo) < 3) {
        $errores[] = "El tipo debe tener al menos 3 caracteres";
    } elseif (strlen($tipo) > 50) {
        $errores[] = "El tipo debe tener menos de 50 caracteres";
    }

    // Verificar que exita ese vehiculo y que le pertenece al cliente
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM vehiculos WHERE id_vehiculo=:id_vehiculo AND id_cliente=:id_cliente");
        $sql->bindParam(':id_vehiculo', $id_vehiculo, PDO::PARAM_INT);
        $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $sql->execute();
        $vehiculo = $sql->fetch(PDO::FETCH_OBJ);
        if (!$vehiculo) {
            $errores[] = "El vehiculo no existe o no pertenece al cliente";
        }
    }

    // Persistencia de datos en caso de errores
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_vehiculo_form'] = $_POST;
        header("Location: ../recepcionista/editar_vehiculos_cliente.php?id_vehiculo=" . base64_encode($id_vehiculo) . "&id_cliente=" . base64_encode($id_cliente));
        exit();
    }

    // En caso de que no haya errores procede a actualizar el vehiculo
    try {
        $sql = $conexion->prepare("UPDATE vehiculos SET marca=:marca, modelo=:modelo, anio=:anio, placa=:placa, tipo=:tipo WHERE id_vehiculo=:id_vehiculo AND id_cliente=:id_cliente");
        $sql->bindParam(':marca', $marca);
        $sql->bindParam(':modelo', $modelo);
        $sql->bindParam(':anio', $anio);
        $sql->bindParam(':placa', $placa);
        $sql->bindParam(':tipo', $tipo);
        $sql->bindParam(':id_vehiculo', $id_vehiculo);
        $sql->bindParam(':id_cliente', $id_cliente);
        $sql->execute();

        // 🔔 INSERTAR NOTIFICACIÓN PARA EL ADMIN
            if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['recepcionista', 'mecanico'])) {
                $titulo = "Vehículo Editado";
                $mensaje = "El usuario " . $_SESSION['nombre'] . " " . $_SESSION['apellido'] . " ha editado el vehiculo con placa: " . $placa;
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

        $_SESSION['exito'] = "Vehículo actualizado correctamente";
        header("Location: ../recepcionista/editar_vehiculos_cliente.php?id_vehiculo=" . base64_encode($id_vehiculo) . "&id_cliente=" . base64_encode($id_cliente));
        exit();
    } catch (PDOException $e) {
        error_log("Error al actualizar vehiculo: " . $e->getMessage());
        $_SESSION['errores'] = ["Error al actualizar el vehículo. Intente nuevamente."];
        $_SESSION['datos_vehiculo_form'] = $_POST;
        header("Location: ../recepcionista/editar_vehiculos_cliente.php?id_vehiculo=" . base64_encode($id_vehiculo) . "&id_cliente=" . base64_encode($id_cliente));
        exit();
    }
} else {
    $_SESSION['errores'] = ["No se ha enviado ningun dato"];
    header("Location: ../recepcionista/gestion_clientes.php");
    exit();
}
