<?php
include('conexion.php');

// Verificar si se ha enviado el RFC
if (isset($_GET['rfc'])) {
    $rfcEliminar = $_GET['rfc'];

    // Ejecutar el procedimiento almacenado
    $sql = "CALL sp_eliminarEmpleado(?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $rfcEliminar);

    if ($stmt->execute()) {
        $mensaje = "Empleado eliminado exitosamente.";
        $tipoAlerta = "success";
    } else {
        $mensaje = "Error al eliminar el empleado: " . $stmt->error;
        $tipoAlerta = "danger";
    }

    $stmt->close();

    // Redirigir de nuevo a la página de gestión de empleados con un mensaje
    header("Location: gestionarEmpleados.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipoAlerta));
    exit();
} else {
    // Si no se envió el RFC, redirigir con un mensaje de error
    $mensaje = "No se recibió ningún RFC para eliminar.";
    $tipoAlerta = "danger";
    header("Location: gestionarEmpleados.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipoAlerta));
    exit();
}
?>
