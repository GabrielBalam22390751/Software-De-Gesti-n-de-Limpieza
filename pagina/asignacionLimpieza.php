<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Conexión a la base de datos
include('conexion.php');

// Inicializar mensajes
$mensaje = '';
$errores = [];

// Obtener datos para los selectores
$zonas = $conexion->query("SELECT * FROM Zona");
$tiposLimpieza = $conexion->query("SELECT * FROM TipoLimpieza");

// Procesar búsqueda de empleados
$empleados = $conexion->query("
    SELECT * 
    FROM Personal 
    WHERE Estado = 'Activo' AND EstadoLimpieza = 'Disponible'
");

// Procesar formulario de asignación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $zona = $_POST['zona'];
    $materialesSeleccionados = $_POST['materiales'] ?? []; // Validar selección de materiales
    $tipoLimpieza = $_POST['tipoLimpieza'];
    $empleado = $_POST['empleado'];
    $horaInicio = $_POST['horaInicio'];
    $horaFin = $_POST['horaFin'];

    // Validar que se hayan seleccionado materiales
    if (empty($materialesSeleccionados)) {
        $errores[] = "Debe seleccionar al menos un material con stock disponible.";
    }

    // Validar asignaciones existentes y realizar la asignación
    if (empty($errores)) {
        foreach ($materialesSeleccionados as $material) {
            $sqlDescuento = "UPDATE MaterialLimpieza SET Stock = Stock - 1 WHERE IdMaterial = ? AND Stock > 0";
            $stmtDescuento = $conexion->prepare($sqlDescuento);
            $stmtDescuento->bind_param("i", $material);

            if (!$stmtDescuento->execute()) {
                $errores[] = "Error al actualizar el inventario del material ID $material: " . $stmtDescuento->error;
            }
            $stmtDescuento->close();

            if (empty($errores)) {
                $sql = "CALL sp_asignarLimpieza(?, ?, ?, ?, ?, ?)";
                if ($stmt = $conexion->prepare($sql)) {
                    $stmt->bind_param("siiiss", $empleado, $tipoLimpieza, $zona, $material, $horaInicio, $horaFin);
                    if (!$stmt->execute()) {
                        $errores[] = "Error en material ID $material: " . $stmt->error;
                    } else {
                        $mensaje = "Asignación de limpieza realizada correctamente.";
                        
                        $sqlUpdate = "UPDATE Personal SET EstadoLimpieza = 'Asignado' WHERE RFC = ?";
                        $stmtUpdate = $conexion->prepare($sqlUpdate);
                        $stmtUpdate->bind_param("s", $empleado);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    }
                    $stmt->close();
                } else {
                    $errores[] = "Error al preparar la consulta SQL: " . $conexion->error;
                }
            }
        }
    }
}

// Consultar materiales actualizados
$materiales = $conexion->query("SELECT * FROM MaterialLimpieza WHERE Stock > 0");

// Determinar la página de inicio según el rol del usuario
$inicio = 'login.php'; // Valor predeterminado
if (isset($_SESSION['idPuesto'])) {
    if ($_SESSION['idPuesto'] == 1) { // 1 para Encargado (Administrador)
        $inicio = 'index_admin.php';
    } elseif ($_SESSION['idPuesto'] == 2) { // 2 para Personal de Limpieza (Empleado)
        $inicio = 'index_empleado.php';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Limpieza</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Asignación de Limpieza</h2>
    <?php if (!empty($mensaje)) { echo "<div class='alert alert-success'>$mensaje</div>"; } ?>
    <?php if (!empty($errores)) { echo "<div class='alert alert-danger'>".implode("<br>", $errores)."</div>"; } ?>
    <form method="POST">
        <!-- Zona -->
        <label>Zona</label>
        <select name="zona" class="form-control" required>
            <option value="">Seleccione</option>
            <?php while ($row = $zonas->fetch_assoc()) {
                echo "<option value='{$row['IdZona']}'>{$row['NombreZona']}</option>";
            } ?>
        </select>

        <!-- Materiales -->
        <label>Materiales</label>
        <?php while ($row = $materiales->fetch_assoc()) { ?>
            <div>
                <input type="checkbox" name="materiales[]" value="<?= $row['IdMaterial'] ?>"> <?= $row['NombreMaterial'] ?> (Stock: <?= $row['Stock'] ?>)
            </div>
        <?php } ?>

        <!-- Tipo de Limpieza -->
        <label>Tipo de Limpieza</label>
        <select name="tipoLimpieza" class="form-control" required>
            <option value="">Seleccione</option>
            <?php while ($row = $tiposLimpieza->fetch_assoc()) {
                echo "<option value='{$row['IdTipoLimpieza']}'>{$row['ClaseLimpieza']}</option>";
            } ?>
        </select>

        <!-- Empleado -->
        <label>Empleado</label>
        <select name="empleado" class="form-control" required>
            <option value="">Seleccione</option>
            <?php while ($row = $empleados->fetch_assoc()) {
                echo "<option value='{$row['RFC']}'>{$row['Nombre']} {$row['PrimerApellido']}</option>";
            } ?>
        </select>

        <!-- Horario -->
        <label>Hora Inicio</label>
        <input type="time" name="horaInicio" class="form-control" required>
        <label>Hora Fin</label>
        <input type="time" name="horaFin" class="form-control" required>

        <button type="submit" class="btn btn-primary mt-3">Asignar Limpieza</button>
    </form>
</div>

<!-- Botón de regreso al inicio -->
<a href="<?= $inicio; ?>" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>
</body>
</html>
