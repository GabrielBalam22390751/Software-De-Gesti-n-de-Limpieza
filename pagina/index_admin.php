<?php
session_start();

// Verificar si el usuario está autenticado y es un Administrador
if (!isset($_SESSION['usuario']) || $_SESSION['idPuesto'] != 1) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Limpieza - Cine</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <style>
        .modal-content {
            border-radius: 15px;
        }
        .btn {
            font-size: 1.1rem;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="iconos/imagen.png" class="img-fluid" width="150">
            <h2 class="text-center w-100">Administración de Gestión de Limpieza de Cine</h2>
            <span>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']); ?></span>
        </div>

        <!-- Botones para abrir los modales -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalLimpieza">
                    Gestión de Limpieza e Inventario
                </button>
            </div>

            <div class="col-md-6 mb-4">
                <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalInventarioEmpleados">
                    Gestión de Empleados
                </button>
            </div>
        </div>

        <!-- Botón nuevo de gestion de puestos -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <button class="btn btn-primary w-100" onclick="window.location.href='gestionPuestos.php'">
                    Gestión de Puestos
                </button>
            </div>

        <div class="modal fade" id="modalLimpieza" tabindex="-1" aria-labelledby="modalLimpiezaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLimpiezaLabel">Gestión de Limpieza e Inventario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-primary w-100 mb-3" onclick="window.location.href='asignacionLimpieza.php'">Asignación de Limpieza</button>
                        <button class="btn btn-info w-100 mb-2" onclick="window.location.href='inventario.php'">Inventario</button>
                        <button class="btn btn-success w-100 mb-3" onclick="window.location.href='limpiezaAnteriores.php'">Bitácora de Limpieza</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalInventarioEmpleados" tabindex="-1" aria-labelledby="modalInventarioEmpleadosLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInventarioEmpleadosLabel">Gestión de Empleados</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-warning w-100 mb-2" onclick="window.location.href='empleados.php'">Registro de Empleados</button>
                        <button class="btn btn-danger w-100" onclick="window.location.href='gestionempleados.php'">Lista de Empleados</button>
                    </div>
                </div>
            </div>
        </div>

        <form action="logout.php" method="POST" class="mt-3">
            <button type="submit" class="btn btn-primary position-fixed top-0 end-0 m-3">Cerrar Sesión</button>
        </form>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
