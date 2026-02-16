<?php
$host = 'localhost';
$dbname = 'sistema_gestion_taller_mecanico';
$user = 'root';
$pass = '';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "✅ Conexión a MySQL exitosa!";
} catch (PDOException $e) {
    die("❌ Error al conectar a MySQL: " . $e->getMessage());
}
