<?php
include('conexion.php');

// Verificar si se ha pasado el RFC
if (isset($_GET['rfc'])) {
    $rfc = $_GET['rfc'];

    // Obtener los datos del empleado
    $sql = "SELECT * FROM personal WHERE RFC = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $rfc);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $empleado = $resultado->fetch_assoc();
    } else {
        echo "Empleado no encontrado.";
        exit;
    }
} else {
    echo "RFC no proporcionado.";
    exit;
}

// Obtener los puestos disponibles
$puestos = $conexion->query("SELECT * FROM puestos");

// Obtener los horarios disponibles
$horarios = $conexion->query("SELECT * FROM horarios");

// Manejar la actualización del empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $primerApellido = $_POST['primerApellido'];
    $segundoApellido = $_POST['segundoApellido'];
    $telefono = $_POST['telefono'];
    $noCasa = $_POST['noCasa'];
    $idHorario = $_POST['horario'];
    $idPuesto = $_POST['puesto'];
    $rfc = $_POST['rfc'];

    // Llamar al procedimiento almacenado para actualizar el empleado
    $sql = "CALL actualizarEmpleado(?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sssssiis', $rfc, $nombre, $primerApellido, $segundoApellido, $telefono, $noCasa, $idHorario, $idPuesto);

    if ($stmt->execute()) {
        header("Location: gestionarEmpleados.php?mensaje=Empleado actualizado exitosamente");
        exit;
    } else {
        echo "Error al actualizar el empleado: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Empleado</h2>
        <form action="" method="POST">
            <!-- Campo para el Nombre -->
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($empleado['Nombre']); ?>" required>
            </div>

            <!-- Campo para el Primer Apellido -->
            <div class="mb-3">
                <label for="primerApellido" class="form-label">Primer Apellido</label>
                <input type="text" class="form-control" id="primerApellido" name="primerApellido" value="<?= htmlspecialchars($empleado['PrimerApellido']); ?>" required>
            </div>

            <!-- Campo para el Segundo Apellido -->
            <div class="mb-3">
                <label for="segundoApellido" class="form-label">Segundo Apellido</label>
                <input type="text" class="form-control" id="segundoApellido" name="segundoApellido" value="<?= htmlspecialchars($empleado['SegundoApellido']); ?>" required>
            </div>

            <!-- Campo para el Teléfono -->
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($empleado['Telefono']); ?>" required>
            </div>

            <!-- Campo para el Número de Casa -->
            <div class="mb-3">
                <label for="noCasa" class="form-label">Número de Casa</label>
                <input type="text" class="form-control" id="noCasa" name="noCasa" value="<?= htmlspecialchars($empleado['NoCasa']); ?>" pattern="[A-Za-z0-9\s]+" title="Solo se permiten números, letras y espacios (por ejemplo: 123, 45A)" maxlength="10" required>
            </div>

            <!-- Campo para seleccionar el Puesto del empleado -->
            <div class="mb-3">
                <label for="puesto" class="form-label">Puesto</label>
                <select name="puesto" class="form-control" required>
                    <option value="">Seleccione un Puesto</option>
                    <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                        <option value="<?= $puesto['IdPuesto']; ?>" <?= $empleado['IdPuesto'] == $puesto['IdPuesto'] ? 'selected' : ''; ?>>
                            <?= $puesto['NombrePuesto']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Campo para seleccionar el Horario del empleado -->
            <div class="mb-3">
                <label for="horario" class="form-label">Horario</label>
                <select name="horario" class="form-control" required>
                    <option value="">Seleccione un Horario</option>
                    <?php while ($horario = $horarios->fetch_assoc()) { ?>
                        <option value="<?= $horario['IdHorario']; ?>" <?= $empleado['IdHorario'] == $horario['IdHorario'] ? 'selected' : ''; ?>>
                            <?= $horario['HoraEntrada'] . ' - ' . $horario['HoraSalida']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Campo para el RFC -->
            <input type="hidden" name="rfc" value="<?= htmlspecialchars($empleado['RFC']); ?>">

            <!-- Botones de enviar o cancelar -->
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="gestionarEmpleados.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
