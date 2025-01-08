<?php 
include('conexion.php');

// Cambiar estado del empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiarEstado') {
    $rfcEmpleado = $_POST['rfcEmpleado'];
    $nuevoEstado = $_POST['nuevoEstado'];

    if (!empty($rfcEmpleado) && in_array($nuevoEstado, ['Activo', 'Baja Temporal'])) {
        $sql = "UPDATE personal SET Estado = ? WHERE RFC = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $nuevoEstado, $rfcEmpleado);

        if ($stmt->execute()) {
            $mensaje = "El estado del empleado se actualizó a '$nuevoEstado'.";
            $tipoAlerta = "success";
        } else {
            $mensaje = "Error al actualizar el estado del empleado: " . $stmt->error;
            $tipoAlerta = "danger";
        }
        $stmt->close();
    } else {
        $mensaje = "Datos inválidos para cambiar el estado del empleado.";
        $tipoAlerta = "danger";
    }
}

// Editar empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $rfcEditar = $_POST['rfcEditar'];
    $nombre = strtoupper($_POST['nombre']);
    $primerApellido = strtoupper($_POST['primerApellido']);
    $segundoApellido = isset($_POST['segundoApellido']) ? strtoupper($_POST['segundoApellido']) : null;
    $telefono = $_POST['telefono'];
    $idHorario = $_POST['idHorario'];
    $idPuesto = $_POST['idPuesto'];

    if (!empty($rfcEditar)) {
        $sql = "CALL sp_actualizarEmpleado(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssssiii', $rfcEditar, $nombre, $primerApellido, $segundoApellido, $telefono, $idHorario, $idPuesto);

        if ($stmt->execute()) {
            $mensaje = "Empleado actualizado exitosamente.";
            $tipoAlerta = "success";
        } else {
            $mensaje = "Error al actualizar el empleado: " . $stmt->error;
            $tipoAlerta = "danger";
        }
        $stmt->close();
    } else {
        $mensaje = "El RFC no puede estar vacío.";
        $tipoAlerta = "danger";
    }
}

// Obtener todos los empleados
function obtenerEmpleados($conexion) {
    $sql = "
        SELECT p.*, 
               CONCAT(h.HoraEntrada, ' - ', h.HoraSalida) AS HorarioCompleto, 
               puesto.nombrePuesto AS Puesto
        FROM personal p
        LEFT JOIN horario h ON p.IdHorario = h.IdHorario
        LEFT JOIN puesto ON p.IdPuesto = puesto.IdPuesto";
    return $conexion->query($sql);
}

$empleados = obtenerEmpleados($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Empleados</h2>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-<?= $tipoAlerta; ?> text-center">
                <?= $mensaje; ?>
            </div>
        <?php } ?>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>RFC</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Horario</th>
                    <th>Puesto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($empleado = $empleados->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($empleado['RFC']); ?></td>
                        <td><?= htmlspecialchars($empleado['Nombre'] . ' ' . $empleado['PrimerApellido'] . ' ' . $empleado['SegundoApellido']); ?></td>
                        <td><?= htmlspecialchars($empleado['Telefono']); ?></td>
                        <td><?= htmlspecialchars($empleado['HorarioCompleto']); ?></td>
                        <td><?= htmlspecialchars($empleado['Puesto']); ?></td>
                        <td><?= htmlspecialchars($empleado['Estado']); ?></td>
                        <td>
                            <!-- Botón de Editar -->
                            <button 
                                class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editarEmpleado" 
                                data-rfc="<?= htmlspecialchars($empleado['RFC']); ?>"
                                data-nombre="<?= htmlspecialchars($empleado['Nombre']); ?>"
                                data-primerapellido="<?= htmlspecialchars($empleado['PrimerApellido']); ?>"
                                data-segundoapellido="<?= htmlspecialchars($empleado['SegundoApellido']); ?>"
                                data-telefono="<?= htmlspecialchars($empleado['Telefono']); ?>"
                                data-idhorario="<?= htmlspecialchars($empleado['IdHorario']); ?>"
                                data-idpuesto="<?= htmlspecialchars($empleado['IdPuesto']); ?>"
                                <?= $empleado['Estado'] !== 'Activo' ? 'disabled' : ''; ?>>
                                Editar
                            </button>

                            <!-- Botón de Cambiar Estado -->
                            <?php if ($empleado['Estado'] === 'Activo') { ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="rfcEmpleado" value="<?= htmlspecialchars($empleado['RFC']); ?>">
                                    <input type="hidden" name="nuevoEstado" value="Baja Temporal">
                                    <button type="submit" name="action" value="cambiarEstado" class="btn btn-warning btn-sm">Baja Temporal</button>
                                </form>
                            <?php } else { ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="rfcEmpleado" value="<?= htmlspecialchars($empleado['RFC']); ?>">
                                    <input type="hidden" name="nuevoEstado" value="Activo">
                                    <button type="submit" name="action" value="cambiarEstado" class="btn btn-success btn-sm">Reactivar</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mt-3">Regresar</a>
    </div>

    <!-- Modal de editar -->
    <div class="modal fade" id="editarEmpleado" tabindex="-1" aria-labelledby="editarEmpleadoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editarEmpleadoLabel">Editar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="rfcEditar" id="rfcEditar">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="primerApellido" class="form-label">Primer Apellido</label>
                            <input type="text" class="form-control" name="primerApellido" id="primerApellido" required>
                        </div>
                        <div class="mb-3">
                            <label for="segundoApellido" class="form-label">Segundo Apellido</label>
                            <input type="text" class="form-control" name="segundoApellido" id="segundoApellido">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label for="idHorario" class="form-label">Horario</label>
                            <select class="form-control" name="idHorario" id="idHorario" required>
                                <option value="">Seleccione un Horario</option>
                                <?php while ($horario = $horarios->fetch_assoc()) { ?>
                                    <option value="<?= $horario['IdHorario']; ?>"><?= $horario['HoraEntrada'] . ' - ' . $horario['HoraSalida']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="idPuesto" class="form-label">Puesto</label>
                            <select class="form-control" name="idPuesto" id="idPuesto" required>
                                <option value="">Seleccione un Puesto</option>
                                <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                                    <option value="<?= $puesto['IdPuesto']; ?>"><?= htmlspecialchars($puesto['nombrePuesto']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" name="action" value="editar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        var editarModal = document.getElementById('editarEmpleado');
        editarModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var rfc = button.getAttribute('data-rfc');
            var nombre = button.getAttribute('data-nombre');
            var primerApellido = button.getAttribute('data-primerapellido');
            var segundoApellido = button.getAttribute('data-segundoapellido');
            var telefono = button.getAttribute('data-telefono');
            var idHorario = button.getAttribute('data-idhorario');
            var idPuesto = button.getAttribute('data-idpuesto');

            document.getElementById('rfcEditar').value = rfc;
            document.getElementById('nombre').value = nombre;
            document.getElementById('primerApellido').value = primerApellido;
            document.getElementById('segundoApellido').value = segundoApellido;
            document.getElementById('telefono').value = telefono;
            document.getElementById('idHorario').value = idHorario;
            document.getElementById('idPuesto').value = idPuesto;
        });
    </script>
</body>
</html>
