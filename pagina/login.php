<?php
session_start();

// Verificar si el usuario está logueado
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('conexion.php');
    
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Preparar la consulta para verificar usuario y RFC
    $stmt = $conexion->prepare("SELECT u.idUsuario, u.contraseña, p.IdPuesto FROM Usuarios u JOIN personal p ON u.rfcEmpleado = p.RFC WHERE u.usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        if (password_verify($contraseña, $row['contraseña'])) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['idUsuario'] = $row['idUsuario'];
            $_SESSION['idPuesto'] = $row['IdPuesto'];

            if ($row['IdPuesto'] == 1) { // 1 para Encargado (Administrador)
                header('Location: index_admin.php');
            } elseif ($row['IdPuesto'] == 2) { // 2 para Personal de Limpieza (Empleado)
                header('Location: index_empleado.php');
            } else {
                echo "<script>alert('Puesto no reconocido.');</script>";
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta.');</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado.');</script>";
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
    <title>Login - Cine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Login</h2>
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
                <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </div>
            <div class="text-center">
                <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
            </div>
        </form>
    </div>
</body>
</html>
