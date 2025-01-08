<?php
session_start(); // Inicia la sesión
if (!isset($_SESSION['usuario'])) { 
    header('Location: index.php');
    exit();
}

// Conexión a la base de datos
include('conexion.php');

// Obtener los puestos disponibles
$puestos = $conexion->query("SELECT * FROM Puesto");

// Obtener los horarios disponibles
$horarios = $conexion->query("SELECT * FROM Horario");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Empleados</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Registro de Empleados</h2>
        
        <!-- Formulario de registro de empleados -->
        <form action="registroEmpleado.php" method="POST">
            <!-- Campo para ingresar el RFC del empleado -->
            <div class="mb-3">
                <label for="rfc" class="form-label">RFC</label>
                <small class="form-text text-muted">Ejemplo: ABCD123456XYZ</small>
                <input 
                    type="text" 
                    name="rfc" 
                    class="form-control" 
                    maxlength="13" 
                    pattern="[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}" 
                    title="Debe ingresar un RFC válido, por ejemplo: ABCD123456XYZ" 
                    required 
                    oninput="this.value = this.value.toUpperCase()">
            </div>

            <!-- Campo para ingresar el nombre completo del empleado -->
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input 
                    type="text" 
                    name="nombre" 
                    class="form-control" 
                    pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+" 
                    title="Solo se permiten letras y espacios" 
                    required 
                    oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ\s]/g, '').toUpperCase()">
            </div>

            <!-- Campo para ingresar el apellido paterno del empleado -->
            <div class="mb-3">
                <label for="primerApellido" class="form-label">Apellido Paterno</label>
                <input 
                    type="text" 
                    name="PrimerApellido" 
                    class="form-control" 
                    pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+" 
                    title="Solo se permiten letras y espacios" 
                    required 
                    oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ\s]/g, '').toUpperCase()">
            </div>

            <!-- Campo para ingresar el apellido materno del empleado (opcional) -->
            <div class="mb-3">
                <label for="segundoApellido" class="form-label">Apellido Materno (Opcional)</label>
                <input 
                    type="text" 
                    name="SegundoApellido" 
                    class="form-control" 
                    pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ\s]*" 
                    title="Solo se permiten letras y espacios" 
                    oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ\s]/g, '').toUpperCase()">
            </div>

            <!-- Campo para ingresar el número de teléfono del empleado -->
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input 
                    type="tel" 
                    name="telefono" 
                    class="form-control" 
                    pattern="\d{10}" 
                    title="Debe ingresar un número de 10 dígitos sin espacios ni guiones" 
                    maxlength="10" 
                    required 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

            <!-- Campo para seleccionar el puesto del empleado -->
 <div class="mb-3">
                <label for="puesto" class="form-label">Puesto</label>
                <select name="puesto" class="form-control" required>
                    <option value="">Seleccione un Puesto</option>
                    <?php while ($puesto = $puestos->fetch_assoc()) { ?>
                        <option value="<?= $puesto['IdPuesto']; ?>"><?= $puesto['nombrePuesto']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- Campo para seleccionar el horario del empleado -->
            <div class="mb-3">
                <label for="horario" class="form-label">Horario</label>
                <select id="horario" name="horario" class="form-control" required>
                    <option value="">Seleccione un Horario</option>
                    <?php while ($horario = $horarios->fetch_assoc()) { ?>
                        <option value="<?= $horario['IdHorario']; ?>"><?= $horario['HoraEntrada'] . ' - ' . $horario['HoraSalida']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- Botón para enviar el formulario y registrar al empleado -->
            <div class="mb-3">
                <button type="submit" class="btn btn-info w-100 py-3 fs-5">Registrar Empleado</button>
            </div>
        </form>
    </div>
    <a href="index.php" class="btn btn-primary position-fixed top-0 end-0 m-3">Regresar al Inicio</a>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>