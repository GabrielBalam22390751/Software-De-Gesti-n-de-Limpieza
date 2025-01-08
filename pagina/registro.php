<?php
// Conexión a la base de datos
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Cifrar la contraseña
    $email = $_POST['email'];
    $rfcEmpleado = $_POST['rfcEmpleado'];

    // Verificar que el RFC exista en la tabla `personal`
    $stmt = $conexion->prepare("SELECT RFC, IdPuesto FROM personal WHERE RFC = ?");
    $stmt->bind_param("s", $rfcEmpleado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // RFC existe, proceder con el registro
        $row = $resultado->fetch_assoc();
        $idPuesto = $row['IdPuesto'];

        // Comprobar si el usuario ya existe
        $stmt2 = $conexion->prepare("SELECT * FROM Usuarios WHERE usuario = ?");
        $stmt2->bind_param("s", $usuario);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();

        if ($resultado2->num_rows > 0) {
            echo "<script>alert('El usuario ya está registrado');</script>";
        } else {
            // Insertar nuevo registro en la base de datos
            $stmt3 = $conexion->prepare("INSERT INTO Usuarios (usuario, contraseña, email, rfcEmpleado) VALUES (?, ?, ?, ?)");
            $stmt3->bind_param("ssss", $usuario, $contraseña, $email, $rfcEmpleado);

            if ($stmt3->execute()) {
                echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('Hubo un error al registrar al usuario');</script>";
            }

            $stmt3->close();
        }

        $stmt2->close();
    } else {
        echo "<script>alert('RFC no encontrado en la tabla de personal');</script>";
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Cine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Registrar Nueva Cuenta</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="contraseña" id="contraseña" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="rfcEmpleado" class="form-label">RFC del Empleado</label>
                <input type="text" class="form-control" name="rfcEmpleado" id="rfcEmpleado" required>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-success w-100">Registrar Cuenta</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
