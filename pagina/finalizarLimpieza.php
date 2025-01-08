<?php
session_start();
include('conexion.php');

// Verifica si se envió el RFC
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['RFC'])) {
    $RFC = $_POST['RFC'];

    // Cambia el estado del empleado a "Disponible"
    $sql = "UPDATE Personal SET EstadoLimpieza = 'Disponible' WHERE RFC = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $RFC);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Limpieza finalizada correctamente para el empleado con RFC: $RFC.";
    } else {
        $_SESSION['mensaje_error'] = "Error al finalizar la limpieza: " . $stmt->error;
    }

    $stmt->close();
}

// Redirige de nuevo al historial de asignaciones
header('Location: limpiezaAnteriores.php');
exit();
?>
