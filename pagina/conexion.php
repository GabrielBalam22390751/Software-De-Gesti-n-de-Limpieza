<?php
$host = 'localhost'; // O el servidor de tu base de datos
$usuario = 'root';   // Tu usuario de MySQL
$contraseña = '';    // Tu contraseña de MySQL
$basededatos = 'Cine';

$conexion = new mysqli($host, $usuario, $contraseña, $basededatos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
