<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conexion->prepare("INSERT INTO MaterialLimpieza (NombreMaterial, DescripcionMaterial) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);

    if ($stmt->execute()) {
        echo "Material agregado exitosamente.";
    } else {
        echo "Error al agregar material: " . $conexion->error;
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
    <title>Agregar Material de Limpieza</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Agregar Material de Limpieza</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre del Material</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
            </div>
            <button type="submit" class="btn btn-success mt-3">Agregar Material</button>
        </form>
        <a href="index.php" class="btn btn-primary mt-3">Regresar al Inicio</a>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
