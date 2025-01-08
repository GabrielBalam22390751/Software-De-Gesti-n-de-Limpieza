<?php
include('conexion.php');
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener todos los materiales
function obtenerMateriales($conexion) {
    $sql = "SELECT * FROM materiallimpieza";
    return $conexion->query($sql);
}

// Variables para mensajes
$mensaje = "";
$tipoAlerta = "";

// Procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Agregar material
    if ($action === 'agregar') {
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $stock = $_POST['stock'] ?? null;

        if (!empty($nombre) && !empty($descripcion) && is_numeric($stock)) {
            $sql = "INSERT INTO materiallimpieza (NombreMaterial, DescripcionMaterial, Stock) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ssi', $nombre, $descripcion, $stock);

            if ($stmt->execute()) {
                $mensaje = "Material agregado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al agregar el material: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "Todos los campos son obligatorios y el stock debe ser un número.";
            $tipoAlerta = "danger";
        }
    }

    // Editar material
    if ($action === 'editar') {
        $idMaterial = $_POST['idMaterial'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $stock = $_POST['stock'] ?? null;

        if (!empty($idMaterial) && !empty($nombre) && !empty($descripcion) && is_numeric($stock)) {
            $sql = "UPDATE materiallimpieza SET NombreMaterial = ?, DescripcionMaterial = ?, Stock = ? WHERE IdMaterial = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ssii', $nombre, $descripcion, $stock, $idMaterial);

            if ($stmt->execute()) {
                $mensaje = "Material actualizado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al actualizar el material: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "Todos los campos son obligatorios y el stock debe ser un número.";
            $tipoAlerta = "danger";
        }
    }

    // Eliminar material
    if ($action === 'eliminar') {
        $idMaterial = $_POST['idMaterial'] ?? null;

        if (!empty($idMaterial)) {
            $sql = "DELETE FROM materiallimpieza WHERE IdMaterial = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $idMaterial);

            if ($stmt->execute()) {
                $mensaje = "Material eliminado exitosamente.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Error al eliminar el material: " . $stmt->error;
                $tipoAlerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "El ID del material es obligatorio para eliminar.";
            $tipoAlerta = "danger";
        }
    }
}

// Obtener lista de materiales
$materiales = obtenerMateriales($conexion);

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
    <title>Gestión de Materiales de Limpieza</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Materiales de Limpieza</h2>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($mensaje)) { ?>
            <div class="alert alert-<?= $tipoAlerta; ?> text-center">
                <?= $mensaje; ?>
            </div>
        <?php } ?>

        <!-- Botón para abrir el modal de agregar material -->
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Material</button>

        <!-- Tabla de materiales -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($material = $materiales->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $material['IdMaterial']; ?></td>
                        <td><?= htmlspecialchars($material['NombreMaterial']); ?></td>
                        <td><?= htmlspecialchars($material['DescripcionMaterial']); ?></td>
                        <td><?= $material['Stock']; ?></td>
                        <td>
                            <!-- Formulario para editar material -->
                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="editar">
                                <input type="hidden" name="idMaterial" value="<?= $material['IdMaterial']; ?>">
                                <div class="mb-2">
                                    <input type="text" class="form-control mb-1" name="nombre" value="<?= htmlspecialchars($material['NombreMaterial']); ?>" required>
                                    <textarea class="form-control mb-1" name="descripcion" required><?= htmlspecialchars($material['DescripcionMaterial']); ?></textarea>
                                    <input type="number" class="form-control mb-1" name="stock" value="<?= $material['Stock']; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm">Guardar Cambios</button>
                            </form>
                            <!-- Botón para eliminar material -->
                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="idMaterial" value="<?= $material['IdMaterial']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para agregar material -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarLabel">Agregar Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="agregar">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="stock" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Botón de regreso al inicio -->
    <a href="<?= $inicio; ?>" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>

    <script src="js/bootstrap.bundle.min[_{{{CITATION{{{_1{](https://github.com/samir04m/VotacionesUnimagWeb2018/tree/068daee4db1ec8bf1c791fd3d4bb4fba28af7862/view%2FnoFound.php)[_{{{CITATION{{{_2{](https://github.com/samir04m/SistemaVotacionWeb2018_NoDB/tree/075ab38aafabc453bdfafc1b5e9ca54354cb54d9/views%2Ftemplate.php)[_{{{CITATION{{{_3{](https://github.com/sebahernandez/Marvel-api-DuocUC/tree/1dfbf4f42bfe162710d15f96de6c7f2e7ed1bbff/admin.php)[_{{{CITATION{{{_4{](https://github.com/faynald/Forecasting-Penjualan-Telor/tree/f1cf147c83fb23ba70810e512619ff8548ef59f0/page%2Ftransaksi.php)[_{{{CITATION{{{_5{](https://github.com/faynald/Forecasting-Penjualan-Telor/tree/f1cf147c83fb23ba70810e512619ff8548ef59f0/page%2Fedit-data.php)[_{{{CITATION{{{_6{](https://github.com/lolxcrib/vallendar/tree/24a1a6433871f0ef95b712b369693fc911699378/vava%2Fordtable.php)[_{{{CITATION{{{_7{](https://github.com/ginogalarzac/form-xhr-javascript/tree/ec28abfcac3b63ddb36e64a1824dfcb5859005d0/index.php)[_{{{CITATION{{{_8{](https://github.com/riskyamaliaharis/intro_php1/tree/927f69082b316924682c3d18aec7b29a32aecc56/hw2%2Fview%2FinsertProduct.php)[_{{{CITATION{{{_9{](https://github.com/anandafarhan/Dumbways-TechnicalTest/tree/e35f46b477fb17520aeaf539eecce3e05415253f/4B%2Fmanage_data.php)[_{{{CITATION{{{_10{](https://github.com/platinum-place/zohoportal/tree/868d8800b4fd4f5954ba097fa86e117bfe0f6b39/app%2FViews%2Fmodals%2Fgenerar_reporte.php)[_{{{CITATION{{{_11{](https://github.com/ramo1005/BolsaDeEmpleo/tree/80991028a74eb55f9398e19bb0432a0b6ed57c1e/Layout%2Flayout.php)