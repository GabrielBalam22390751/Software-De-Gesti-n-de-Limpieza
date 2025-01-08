<?php
include('conexion.php');
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Determinar la página de inicio según el rol del usuario
$inicio = 'login.php'; // Valor predeterminado
if (isset($_SESSION['idPuesto'])) {
    if ($_SESSION['idPuesto'] == 1) { // 1 para Encargado (Administrador)
        $inicio = 'index_admin.php';
    } elseif ($_SESSION['idPuesto'] == 2) { // 2 para Personal de Limpieza (Empleado)
        $inicio = 'index_empleado.php';
    }
}

// Inicializar mensaje
$mensaje = "";

// Procesar finalización de limpieza
if (isset($_POST['finalizar'])) {
    $numAsignacion = $_POST['numAsignacion'];

    // Cambiar estado del empleado a 'Disponible'
    $sqlUpdateEstado = "UPDATE Personal p
        JOIN AsignacionLimpieza al ON p.RFC = al.RFC
        SET p.EstadoLimpieza = 'Disponible'
        WHERE al.NumAsignacion = ?";
    $stmtUpdateEstado = $conexion->prepare($sqlUpdateEstado);
    $stmtUpdateEstado->bind_param("i", $numAsignacion);
    $stmtUpdateEstado->execute();

    // Regresar materiales a inventario
    $sqlUpdateMateriales = "
        UPDATE MaterialLimpieza m
        JOIN AsignacionLimpieza al ON m.IdMaterial = al.IdMaterial
        SET m.CantidadDisponible = m.CantidadDisponible + al.CantidadUsada
        WHERE al.NumAsignacion = ?";
    $stmtUpdateMateriales = $conexion->prepare($sqlUpdateMateriales);
    $stmtUpdateMateriales->bind_param("i", $numAsignacion);
    $stmtUpdateMateriales->execute();

    // Confirmar acción
    if ($stmtUpdateEstado->affected_rows > 0 || $stmtUpdateMateriales->affected_rows > 0) {
        $mensaje = "Limpieza finalizada y materiales actualizados correctamente.";
    } else {
        $mensaje = "Error al finalizar la limpieza.";
    }

    $stmtUpdateEstado->close();
    $stmtUpdateMateriales->close();
}

// Obtener historial de limpiezas finalizadas
if ($_SESSION['idPuesto'] == 2) { // Si el usuario es un empleado
    $rfcEmpleado = $_SESSION['usuario']; // Asumiendo que el RFC está guardado en la sesión
    $sqlHistorial = "
        SELECT 
            al.NumAsignacion,
            p.RFC,
            CONCAT(p.Nombre, ' ', p.PrimerApellido, ' ', IFNULL(p.SegundoApellido, '')) AS Empleado,
            z.NombreZona AS Zona,
            al.FechaAsignacion,
            al.HoraInicio,
            al.HoraFin,
            GROUP_CONCAT(DISTINCT m.NombreMaterial SEPARATOR ', ') AS Materiales
        FROM 
            AsignacionLimpieza al
            JOIN Personal p ON al.RFC = p.RFC
            JOIN Zona z ON al.IdZona = z.IdZona
            JOIN MaterialLimpieza m ON al.IdMaterial = m.IdMaterial
        WHERE 
            al.HoraFin IS NOT NULL AND p.RFC = ?
        GROUP BY 
            al.NumAsignacion";
    $stmtHistorial = $conexion->prepare($sqlHistorial);
    $stmtHistorial->bind_param("s", $rfcEmpleado);
    $stmtHistorial->execute();
    $resultadoHistorial = $stmtHistorial->get_result();
} else { // Si el usuario es un administrador
    $sqlHistorial = "
        SELECT 
            al.NumAsignacion,
            p.RFC,
            CONCAT(p.Nombre, ' ', p.PrimerApellido, ' ', IFNULL(p.SegundoApellido, '')) AS Empleado,
            z.NombreZona AS Zona,
            al.FechaAsignacion,
            al.HoraInicio,
            al.HoraFin,
            GROUP_CONCAT(DISTINCT m.NombreMaterial SEPARATOR ', ') AS Materiales
        FROM 
            AsignacionLimpieza al
            JOIN Personal p ON al.RFC = p.RFC
            JOIN Zona z ON al.IdZona = z.IdZona
            JOIN MaterialLimpieza m ON al.IdMaterial = m.IdMaterial
        WHERE 
            al.HoraFin IS NOT NULL
        GROUP BY 
            al.NumAsignacion";
    $resultadoHistorial = $conexion->query($sqlHistorial);
}

// Buscar limpiezas realizadas por un empleado
if (isset($_POST['buscar'])) {
    $rfcBusqueda = $_POST['rfcBusqueda'];

    // Llamar al procedimiento almacenado
    $sqlBuscar = "CALL sp_buscarLimpiezasPorEmpleado(?)";
    $stmtBuscar = $conexion->prepare($sqlBuscar);
    $stmtBuscar->bind_param("s", $rfcBusqueda);
    $stmtBuscar->execute();
    $resultadoBusqueda = $stmtBuscar->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Limpiezas</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center text-primary">Historial de Limpiezas</h2>

    <!-- Mostrar mensajes -->
    <?php if (!empty($mensaje)) { ?>
        <div class="alert alert-success"><?= $mensaje; ?></div>
    <?php } ?>

    <?php if ($_SESSION['idPuesto'] == 1) { // Mostrar el formulario de búsqueda solo para administradores ?>
    <!-- Formulario de búsqueda -->
    <form method="POST" class="mb-3">
        <div class="input-group">
            <input type="text" name="rfcBusqueda" class="form-control" placeholder="Ingrese el RFC del empleado" required>
            <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
        </div>
    </form>
    <?php } ?>

    <!-- Tabla de resultados -->
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Asignación</th>
                <th>RFC</th>
                <th>Empleado</th>
                <th>Zona</th>
                <th>Fecha</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Materiales</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $resultados = isset($resultadoBusqueda) ? $resultadoBusqueda : $resultadoHistorial;
            if ($resultados->num_rows > 0) {
                while ($row = $resultados->fetch_assoc()) { ?>
                    <tr id="row-<?= htmlspecialchars($row['NumAsignacion']) ?>">
                        <td><?= htmlspecialchars($row['NumAsignacion']) ?></td>
                        <td><?= htmlspecialchars($row['RFC']) ?></td>
                        <td><?= htmlspecialchars($row['Empleado']) ?></td>
                        <td><?= htmlspecialchars($row['Zona']) ?></td>
                        <td><?= htmlspecialchars($row['FechaAsignacion']) ?></td>
                        <td><?= htmlspecialchars($row['HoraInicio']) ?></td>
                        <td><?= htmlspecialchars($row['HoraFin']) ?></td>
                        <td><?= htmlspecialchars($row['Materiales']) ?></td>
                        <td>
                            <!-- Botón de finalizar limpieza -->
                            <form method="POST" class="d-inline" onsubmit="finalizarLimpieza(this, 'row-<?= $row['NumAsignacion'] ?>')">
                                <input type="hidden" name="numAsignacion" value="<?= $row['NumAsignacion'] ?>">
                                <button type="submit" name="finalizar" class="btn btn-success">Finalizar Limpieza</button>
                            </form>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="9" class="text-center">No hay registros disponibles.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Botón de regreso al inicio -->
<a href="<?= $inicio; ?>" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>

<script>
    // Función para eliminar el botón y fila dinámicamente
    function finalizarLimpieza(form, rowId) {
        const row = document.getElementById(rowId);
        const button = form.querySelector('button');
        button.innerText = "Procesando...";
        button.disabled = true;

        // Después de enviar el formulario, elimina la fila
        setTimeout(() => {
            row.remove();
        }, 500); // Simulación de un pequeño retraso
    }
</script>
</body>
</html>
