<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

include('conexion.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("DELETE FROM MaterialLimpieza WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Material eliminado exitosamente.";
    } else {
        echo "Error al eliminar material: " . $conexion->error;
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
    <title>Eliminar Material de Limpieza</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Eliminar Material de Limpieza</h2>
        <form method="get" action="">
            <div class="form-group">
                <label for="id">ID del Material a Eliminar</label>
                <input type="number" class="form-control" id="id" name="id" required>
            </div>
            <button type="submit" class="btn btn-danger mt-3">Eliminar Material</button>
        </form>
        <a href="index.php" class="btn btn-primary mt-3">Regresar al Inicio</a>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
