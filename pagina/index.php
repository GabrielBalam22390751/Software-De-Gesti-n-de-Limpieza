<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Determinar la página de inicio según el rol del usuario
$inicio = 'login.php'; // Valor predeterminado
if (isset($_SESSION['idPuesto'])) {
    if ($_SESSION['idPuesto'] == 1) { // 1 para Encargado (Administrador)
        $inicio = 'index_admin.php';
    } elseif ($_SESSION['idPuesto'] == 2) { // 2 para Personal de Limpieza (Empleado)
        $inicio = 'index_empleado.php';
    }
}

// Redirigir al inicio correspondiente
header('Location: ' . $inicio);
exit();
?>
