<?php
require_once '../conexion/session.php';
require_once "../conexion/bd.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && isset($_POST["password"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $errores = [];

    // Validaciones y sanitizacion
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es valido";
    }
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    } elseif (strlen($password) < 5) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }

    // Validar intentos fallidos
    if ($_SESSION['intentos'] >= 5) {
        if (!isset($_SESSION['bloqueado_tiempo'])) {
            $_SESSION['bloqueado_tiempo'] = time();
        }
        $tiempo_transcurrido = time() - $_SESSION['bloqueado_tiempo'];
        $tiempo_espera = 60;

        if ($tiempo_transcurrido < $tiempo_espera) {
            $restante = $tiempo_espera - $tiempo_transcurrido;
            $_SESSION['errores'] = ["Demasiados intentos. Intenta en $restante segundos."];
            header("Location: ../login.php");
            exit();
        } else {
            $_SESSION['intentos'] = 0;
            unset($_SESSION['bloqueado_tiempo']);
        }
    }

    // Si todo esta bien procede a loguearse
    if (empty($errores)) {
        try {
            $sql = $conexion->prepare("SELECT id_usuario,nombre,apellido,rol,password FROM usuarios WHERE email = :email");
            $sql->bindParam("email", $email, PDO::PARAM_STR);
            $sql->execute();
            $usuario = $sql->fetch(PDO::FETCH_OBJ);

            if ($usuario && password_verify($password, $usuario->password)) {
                $_SESSION["id_usuario"] = $usuario->id_usuario;
                $_SESSION["nombre"] = $usuario->nombre;
                $_SESSION["apellido"] = $usuario->apellido;
                $_SESSION["rol"] = $usuario->rol;
                $_SESSION["email"] = $email;
                $_SESSION["logueado"] = true;

                switch ($usuario->rol) {
                    case "admin":
                        header("Location: ../admin/dash_admin.php");
                        exit();
                    case "mecanico":
                        header("Location: ../mecanico/dash_mecanico.php");
                        exit();
                    case "recepcionista":
                        header("Location: ../recepcionista/dash_recepcionista.php");
                        exit();
                }
            } else {
                // Credenciales incorrectas
                $_SESSION['intentos']++;
                if ($_SESSION['intentos'] >= 5) {
                    $_SESSION['bloqueado_tiempo'] = time();
                    $errores[] = "Demasiados intentos fallidos. Intenta nuevamente en 1 minuto.";
                } else {
                    $errores[] = "Email o contraseña incorrectos.";
                }
                $_SESSION["errores"] = $errores;
                header("Location: ../login.php");
                exit();
            }
        } catch (Exception $e) {
            error_log("Error en login.php: " . $e->getMessage());
            $_SESSION["errores"] = ["Error en el inicio de sesión"];
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION["errores"] = ["Error en el inicio de sesión"];
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION["errores"] = ["Error en el inicio de sesión"];
    header("Location: ../login.php");
    exit();
}
