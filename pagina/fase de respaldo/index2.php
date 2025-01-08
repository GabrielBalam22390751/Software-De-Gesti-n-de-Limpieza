<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
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
        /* Estilo adicional para las ventanas emergentes (modales) */
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
            <!-- Botón para la Gestión de Limpieza -->
            <div class="col-md-6 mb-4">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalLimpieza">
                    Gestión de Limpieza
                </button>
            </div>

            <!-- Botón para la Gestión de Inventarios y Empleados -->
            <div class="col-md-6 mb-4">
                <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalInventarioEmpleados">
                    Gestión de Inventarios y Empleados
                </button>
            </div>
        </div>

        <!-- Modal para la Gestión de Limpieza -->
        <div class="modal fade" id="modalLimpieza" tabindex="-1" aria-labelledby="modalLimpiezaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLimpiezaLabel">Gestión de Limpieza</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-primary w-100 mb-3" onclick="window.location.href='asignacionLimpieza.php'">Asignación de Limpieza</button>
                        <button class="btn btn-primary w-100 mb-3" onclick="window.location.href='inventario.php'">inventario</button>
                        <button class="btn btn-success w-100 mb-3" onclick="window.location.href='limpiezaAnteriores.php'">Registro de Limpiezas Anteriores</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para la Gestión de Inventarios y Empleados -->
        <div class="modal fade" id="modalInventarioEmpleados" tabindex="-1" aria-labelledby="modalInventarioEmpleadosLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInventarioEmpleadosLabel">Gestión de Inventarios y Empleados</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-info w-100 mb-2" onclick="window.location.href='inventario.php'">Inventario</button>
                        <button class="btn btn-warning w-100 mb-2" onclick="window.location.href='empleados.php'">Registro de Empleados</button>
                        <button class="btn btn-danger w-100" onclick="window.location.href='gestionempleados.php'">Gestión de Empleados</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cerrar sesión -->
        <form action="logout.php" method="POST" class="mt-3">
            <button type="submit" class="btn btn-primary position-fixed top-0 end-0 m-3">Cerrar Sesión</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>