<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

include('conexion.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $material = $conexion->query("SELECT * FROM MaterialLimpieza WHERE id = $id")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conexion->prepare("UPDATE MaterialLimpieza SET NombreMaterial = ?, DescripcionMaterial = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);

    if ($stmt->execute()) {
        echo "Material modificado exitosamente.";
    } else {
        echo "Error al modificar material: " . $conexion->error;
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
    <title>Modificar Material de Limpieza</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Modificar Material de Limpieza</h2>
        <form method="post" action="">
            <input type="hidden" name="id" value="<?= $material['id']; ?>">
            <div class="form-group">
                <label for="nombre">Nombre del Material</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $material['NombreMaterial']; ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required><?= $material['DescripcionMaterial']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-warning mt-3">Modificar Material</button>
        </form>
        <a href="index.php" class="btn btn-primary mt-3">Regresar al Inicio</a>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
