<?php
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfc = $_POST['rfcEditar'];
    $nombre = strtoupper($_POST['nombre']);
    $primerApellido = strtoupper($_POST['primerApellido']);
    $segundoApellido = strtoupper($_POST['segundoApellido']);
    $telefono = $_POST['telefono'];
    $idHorario = $_POST['idHorario'];
    $idPuesto = $_POST['idPuesto'];

    $sql = "CALL sp_actualizarEmpleado(?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sssssss', $rfc, $nombre, $primerApellido, $segundoApellido, $telefono, $idHorario, $idPuesto);

    if ($stmt->execute()) {
        header("Location: index.php?mensaje=Empleado actualizado correctamente&tipo=success");
    } else {
        header("Location: index.php?mensaje=Error al actualizar empleado: " . $stmt->error . "&tipo=danger");
    }

    $stmt->close();
    $conexion->close();
}
