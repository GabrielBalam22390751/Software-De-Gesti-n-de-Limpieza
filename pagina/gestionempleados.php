<?php 
include('conexion.php');

// Inicializar mensajes
$mensaje = '';
$tipoAlerta = '';

// Cambiar estado del empleado con AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiarEstado') {
    $rfcEmpleado = $_POST['rfcEmpleado'];
    $nuevoEstado = $_POST['nuevoEstado'];

    if (!empty($rfcEmpleado) && in_array($nuevoEstado, ['Activo', 'Baja Temporal'])) {
        $sql = "UPDATE personal SET Estado = ? WHERE RFC = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ss', $nuevoEstado, $rfcEmpleado);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'mensaje' => "Estado cambiado a '$nuevoEstado'"]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => "Error: " . $stmt->error]);
        }
        exit;
    }
}

// Editar empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $rfcEditar = $_POST['rfcEditar'];
    $nombre = strtoupper(trim($_POST['nombre']));
    $primerApellido = strtoupper(trim($_POST['primerApellido']));
    $segundoApellido = !empty($_POST['segundoApellido']) ? strtoupper(trim($_POST['segundoApellido'])) : null;
    $telefono = trim($_POST['telefono']);
    $idHorario = $_POST['idHorario'];
    $idPuesto = $_POST['idPuesto'];

    // Validaciones adicionales en PHP
    if (!preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/', $nombre)) {
        $mensaje = "El nombre contiene caracteres inválidos.";
        $tipoAlerta = "danger";
    } elseif (!preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/', $primerApellido)) {
        $mensaje = "El primer apellido contiene caracteres inválidos.";
        $tipoAlerta = "danger";
    } elseif ($segundoApellido && !preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/', $segundoApellido)) {
        $mensaje = "El segundo apellido contiene caracteres inválidos.";
        $tipoAlerta = "danger";
    } elseif (!preg_match('/^\d{10}$/', $telefono)) {
        $mensaje = "El teléfono debe contener exactamente 10 números.";
        $tipoAlerta = "danger";
    } else {
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
    }
}

// Buscar empleados
$searchQuery = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $searchTerm = strtoupper($_GET['buscar']);
    $searchQuery = "WHERE UPPER(RFC) LIKE '%$searchTerm%' OR 
                           UPPER(Nombre) LIKE '%$searchTerm%' OR 
                           UPPER(PrimerApellido) LIKE '%$searchTerm%' OR 
                           UPPER(SegundoApellido) LIKE '%$searchTerm%'";
}

// Obtener empleados, horarios y puestos
function obtenerEmpleados($conexion, $searchQuery = '') {
    $sql = "
        SELECT p.*, 
               CONCAT(h.HoraEntrada, ' - ', h.HoraSalida) AS HorarioCompleto, 
               puesto.nombrePuesto AS Puesto
        FROM personal p
        LEFT JOIN horario h ON p.IdHorario = h.IdHorario
        LEFT JOIN puesto ON p.IdPuesto = puesto.IdPuesto
        $searchQuery";
    return $conexion->query($sql);
}

function obtenerHorarios($conexion) {
    $sql = "SELECT * FROM horario";
    return $conexion->query($sql);
}

function obtenerPuestos($conexion) {
    $sql = "SELECT * FROM puesto";
    return $conexion->query($sql);
}

$empleados = obtenerEmpleados($conexion, $searchQuery);
$horarios = obtenerHorarios($conexion);
$puestos = obtenerPuestos($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Empleados</h2>

        <!-- Área de notificaciones -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipoAlerta; ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Barra de búsqueda -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por RFC, Nombre o Apellido" style="text-transform: uppercase;" value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <!-- Tabla de empleados -->
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
                        <td class="text-center">
                            <span class="badge bg-<?= $empleado['Estado'] === 'Activo' ? 'success' : 'warning'; ?>">
                                <?= htmlspecialchars($empleado['Estado']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- Botón Editar -->
                            <button class="btn btn-warning btn-sm editarEmpleado" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editarEmpleado"
                                    data-rfc="<?= htmlspecialchars($empleado['RFC']); ?>"
                                    data-nombre="<?= htmlspecialchars($empleado['Nombre']); ?>"
                                    data-primerapellido="<?= htmlspecialchars($empleado['PrimerApellido']); ?>"
                                    data-segundoapellido="<?= htmlspecialchars($empleado['SegundoApellido']); ?>"
                                    data-telefono="<?= htmlspecialchars($empleado['Telefono']); ?>"
                                    data-idhorario="<?= htmlspecialchars($empleado['IdHorario']); ?>"
                                    data-idpuesto="<?= htmlspecialchars($empleado['IdPuesto']); ?>"
                                    data-estado="<?= htmlspecialchars($empleado['Estado']); ?>">
                                Editar
                            </button>
                            <!-- Botón Estado -->
                            <button class="btn btn-sm btn-<?= $empleado['Estado'] === 'Activo' ? 'danger' : 'success'; ?> cambiarEstado"
                                    data-rfc="<?= htmlspecialchars($empleado['RFC']); ?>"
                                    data-nuevoestado="<?= $empleado['Estado'] === 'Activo' ? 'Baja Temporal' : 'Activo'; ?>">
                                <?= $empleado['Estado'] === 'Activo' ? 'Desactivar' : 'Activar'; ?>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de edición -->
    <div class="modal fade" id="editarEmpleado" tabindex="-1" aria-labelledby="editarEmpleadoLabel" aria-hidden="true">
        <div class ="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="editarEmpleadoLabel">Editar Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="rfcEditar" id="rfcEditar">
                        <div class="mb-3">
                            <label for="nombre">Nombre:</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required pattern="[A-ZÁÉÍÓÚÑ\s]+" title="Solo se permiten letras en mayúsculas" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="primerApellido">Primer Apellido:</label>
                            <input type="text" class="form-control" name="primerApellido" id="primerApellido" required pattern="[A-ZÁÉÍÓÚÑ\s]+" title="Solo se permiten letras en mayúsculas" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="segundoApellido">Segundo Apellido:</label>
                            <input type="text" class="form-control" name="segundoApellido" id="segundoApellido" pattern="[A-ZÁÉÍÓÚÑ\s]+" title="Solo se permiten letras en mayúsculas" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" class="form-control" name="telefono" id="telefono" required pattern="\d{10}" title="Debe ingresar exactamente 10 números" maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label for="idHorario">Horario:</label>
                            <select class="form-control" name="idHorario" id="idHorario" required>
                                <option value="">Seleccione un Horario</option>
                                <?php while ($horario = $horarios->fetch_assoc()) { ?>
                                    <option value="<?= $horario['IdHorario']; ?>"><?= htmlspecialchars($horario['HoraEntrada'] . ' - ' . $horario['HoraSalida']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="idPuesto">Puesto:</label>
                            <select class="form-control" name="idPuesto" id="idPuesto" required>
                                <option value="">Seleccione un Puesto</option>
                                <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                                    <option value="<?= $puesto['IdPuesto']; ?>"><?= htmlspecialchars($puesto['nombrePuesto']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning" name="action" value="editar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.editarEmpleado', function () {
            const estado = $(this).data('estado');
            const modal = $('#editarEmpleado');

            modal.find('input, select, button[type="submit"]').prop('disabled', estado === 'Baja Temporal');

            modal.find('#rfcEditar').val($(this).data('rfc'));
            modal.find('#nombre').val($(this).data('nombre'));
            modal.find('#primerApellido').val($(this).data('primerapellido'));
            modal.find('#segundoApellido').val($(this).data('segundoapellido'));
            modal.find('#telefono').val($(this).data('telefono'));
            modal.find('#idHorario').val($(this).data('idhorario'));
            modal.find('#idPuesto').val($(this).data('idpuesto'));
        });

        $(document).on('click', '.cambiarEstado', function () {
            const rfc = $(this).data('rfc');
            const nuevoEstado = $(this).data('nuevoestado');

            $.post('', { action: 'cambiarEstado', rfcEmpleado: rfc, nuevoEstado: nuevoEstado }, function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    alert(data.mensaje);
                    location.reload();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            });
        });

        // Convert ir a mayúsculas en tiempo real
        document.querySelectorAll('#nombre, #primerApellido, #segundoApellido').forEach(input => {
            input.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
            });
        });

        document.getElementById('telefono').addEventListener('input', function (e) {
            const value = e.target.value.replace(/\D/g, '');
            e.target.value = value.slice(0, 10);
        });
    </script>
    <!-- Botón de regreso al inicio -->
<a href="index.php" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>
</body>
</html>