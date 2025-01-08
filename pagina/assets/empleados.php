<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

// Conexión a la base de datos
include('conexion.php');

// Obtener los puestos disponibles
$puestos = $conexion->query("SELECT * FROM Puesto");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Registro de Empleados</h2>
        
        <!-- Formulario de registro de empleados -->
        <form action="registroEmpleado.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="puesto" class="form-label">Puesto</label>
                <select name="puesto" class="form-control" required>
                    <option value="">Seleccione un Puesto</option>
                    <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                        <option value="<?= $puesto['IdPuesto']; ?>"><?= $puesto['nombrePuesto']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-info w-100 py-3 fs-5">Registrar Empleado</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
