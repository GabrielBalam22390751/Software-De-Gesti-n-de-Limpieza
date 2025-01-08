<?php
include('conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Cifrar la contraseña
    $email = $_POST['email'];
    $rfcEmpleado = $_POST['rfcEmpleado'];

    // Verificar si el RFC ya está registrado en la tabla `personal`
    $stmt = $conexion->prepare("SELECT RFC FROM personal WHERE RFC = ?");
    $stmt->bind_param("s", $rfcEmpleado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "<script>alert('El RFC ya está registrado en la tabla de personal.');</script>";
    } else {
        // RFC no existe, proceder con el registro
        $nombre = $_POST['nombre'];
        $primerApellido = $_POST['primerApellido'];
        $segundoApellido = $_POST['segundoApellido'];
        $telefono = $_POST['telefono'];
        $noCasa = $_POST['noCasa'];
        $idHorario = $_POST['idHorario'];
        $idPuesto = $_POST['idPuesto'];

        // Insertar en la tabla de usuarios
        $stmt = $conexion->prepare("INSERT INTO Usuarios (usuario, contraseña, email, rfcEmpleado) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $usuario, $contraseña, $email, $rfcEmpleado);

        if ($stmt->execute()) {
            // Insertar en la tabla de personal
            $stmt2 = $conexion->prepare("INSERT INTO personal (RFC, Nombre, PrimerApellido, SegundoApellido, Telefono, NoCasa, IdHorario, IdPuesto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssssii", $rfcEmpleado, $nombre, $primerApellido, $segundoApellido, $telefono, $noCasa, $idHorario, $idPuesto);

            if ($stmt2->execute()) {
                echo "Registro exitoso.";
            } else {
                echo "Error al registrar en la tabla de personal: " . $conexion->error;
            }

            $stmt2->close();
        } else {
            echo "Error al registrar en la tabla de usuarios: " . $conexion->error;
        }

        $stmt->close();
    }

    $resultado->close();
    $conexion->close();
}

// Obtener puestos para el formulario
$puestos = $conexion->query("SELECT idPuesto, nombrePuesto FROM puesto");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empleados</title>
</head>
<body>
    <form action="registro_empleado.php" method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="contraseña" placeholder="Contraseña" required>
        <input type="email" name="email" placeholder="Email" required><br>

        <input type="text" name="rfcEmpleado" placeholder="RFC del Empleado" required>
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="primerApellido" placeholder="Primer Apellido" required>
        <input type="text" name="segundoApellido" placeholder="Segundo Apellido" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="text" name="noCasa" placeholder="Número de Casa" required>
        <input type="number" name="idHorario" placeholder="ID Horario" required>

        <!-- Selección de Puesto -->
        <label for="idPuesto">Puesto:</label>
        <select name="idPuesto" required>
            <?php while ($row = $puestos->fetch_assoc()) { ?>
                <option value="<?= $row['idPuesto']; ?>"><?= $row['nombrePuesto']; ?></option>
            <?php } ?>
        </select><br>

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
