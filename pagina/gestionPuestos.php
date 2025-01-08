<?php
include('conexion.php');
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener todos los puestos
function obtenerPuestos($conexion) {
    $sql = "SELECT * FROM puesto";
    return $conexion->query($sql);
}

// Variables para mensajes
$mensaje = "";
$tipoAlerta = "";

// Procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Agregar puesto
    if ($action === 'agregar') {
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;

        if (!empty($nombre) && !empty($descripcion)) {
            $sql = "INSERT INTO puesto (nombrePuesto, DescripcionPuesto) VALUES (?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ss', $nombre, $descripcion);

            if ($stmt->execute()) {
                $mensaje = "Puesto agregado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al agregar el puesto: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "Todos los campos son obligatorios.";
            $tipoAlerta = "danger";
        }
    }

    // Editar puesto
    if ($action === 'editar') {
        $idPuesto = $_POST['idPuesto'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;

        if (!empty($idPuesto) && !empty($nombre) && !empty($descripcion)) {
            $sql = "UPDATE puesto SET nombrePuesto = ?, DescripcionPuesto = ? WHERE IdPuesto = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ssi', $nombre, $descripcion, $idPuesto);

            if ($stmt->execute()) {
                $mensaje = "Puesto actualizado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al actualizar el puesto: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "Todos los campos son obligatorios.";
            $tipoAlerta = "danger";
        }
    }

    // Eliminar puesto
    if ($action === 'eliminar') {
        $idPuesto = $_POST['idPuesto'] ?? null;

        if (!empty($idPuesto)) {
            $sql = "DELETE FROM puesto WHERE IdPuesto = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $idPuesto);

            if ($stmt->execute()) {
                $mensaje = "Puesto eliminado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al eliminar el puesto: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "El ID del puesto es obligatorio para eliminar.";
            $tipoAlerta = "danger";
        }
    }
}

// Obtener lista de puestos
$puestos = obtenerPuestos($conexion);

// Determinar la página de inicio según el rol del usuario
$inicio = 'login.php'; // Valor predeterminado
if (isset($_SESSION['idPuesto'])) {
    if ($_SESSION['idPuesto'] == 1) { // Encargado (Administrador)
        $inicio = 'index_admin.php';
    } elseif ($_SESSION['idPuesto'] == 2) { // Personal (Empleado)
        $inicio = 'index_empleado.php';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Puestos</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Puestos</h2>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($mensaje)) { ?>
            <div class="alert alert-<?= $tipoAlerta; ?> text-center">
                <?= $mensaje; ?>
            </div>
        <?php } ?>

        <!-- Botón para abrir el modal de agregar puesto -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Puesto</button>

        <!-- Tabla de puestos -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $puesto['IdPuesto']; ?></td>
                        <td><?= htmlspecialchars($puesto['nombrePuesto']); ?></td>
                        <td><?= htmlspecialchars($puesto['DescripcionPuesto']); ?></td>
                        <td>
                            <!-- Botón para abrir el modal de editar -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $puesto['IdPuesto']; ?>">Editar Puesto</button>

                            <!-- Modal para editar puesto -->
                            <div class="modal fade" id="modalEditar<?= $puesto['IdPuesto']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $puesto['IdPuesto']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalEditarLabel<?= $puesto['IdPuesto']; ?>">Editar Puesto</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="editar">
                                                <input type="hidden" name="idPuesto" value="<?= $puesto['IdPuesto']; ?>">
                                                <div class="mb-3">
                                                    <label for="nombre" class="form-label">Nombre</label>
                                                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($puesto['nombrePuesto']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <textarea class="form-control" name="descripcion" required><?= htmlspecialchars($puesto['DescripcionPuesto']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón para confirmar eliminación -->
                            <button class="btn btn-danger btn-sm" onclick="confirmarEliminacion(<?= $puesto['IdPuesto']; ?>)">Eliminar Puesto</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Modal para agregar puesto -->
        <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">Agregar Puesto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="agregar">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Agregar Puesto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Botón de regreso al inicio -->
    <a href="<?= $inicio; ?>" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>
    <!-- Script para confirmación de eliminación -->
    <script>
        function confirmarEliminacion(idPuesto) {
            if (confirm('¿Estás seguro de que deseas eliminar este puesto?')) {
                // Crear formulario para eliminar
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                var inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'eliminar';
                form.appendChild(inputAction);

                var inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'idPuesto';
                inputId.value = idPuesto;
                form.appendChild(inputId);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
