-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-01-2025 a las 12:49:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cine`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarEmpleado` (IN `criterio` VARCHAR(255))   BEGIN
    SET @criterio = CONCAT('%', UPPER(criterio), '%');
    SELECT * 
    FROM personal
    WHERE UPPER(RFC) LIKE @criterio
       OR UPPER(Nombre) LIKE @criterio
       OR UPPER(PrimerApellido) LIKE @criterio
       OR UPPER(SegundoApellido) LIKE @criterio;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizarEmpleado` (IN `in_RFC` VARCHAR(13), IN `in_Nombre` VARCHAR(100), IN `in_PrimerApellido` VARCHAR(50), IN `in_SegundoApellido` VARCHAR(50), IN `in_Telefono` VARCHAR(15), IN `in_IdHorario` INT, IN `in_IdPuesto` INT)   BEGIN
    -- Actualizar los datos del empleado
    UPDATE personal
    SET 
        Nombre = in_Nombre,
        PrimerApellido = in_PrimerApellido,
        SegundoApellido = in_SegundoApellido,
        Telefono = in_Telefono,
        IdHorario = in_IdHorario,
        IdPuesto = in_IdPuesto
    WHERE RFC = in_RFC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_asignarLimpieza` (IN `p_RFC` VARCHAR(20), IN `p_IdTipoLimpieza` INT, IN `p_IdZona` INT, IN `p_IdMaterial` INT, IN `p_HoraInicio` TIME, IN `p_HoraFin` TIME)   BEGIN
    DECLARE emp_count INT;

    -- Verifica si el empleado ya está asignado a la zona y material en el mismo horario
    SELECT COUNT(*) INTO emp_count
    FROM asignacionlimpieza 
    WHERE RFC = p_RFC 
    AND IdZona = p_IdZona 
    AND IdMaterial = p_IdMaterial
    AND ((p_HoraInicio BETWEEN HoraInicio AND HoraFin) OR (p_HoraFin BETWEEN HoraInicio AND HoraFin));

    IF emp_count = 0 THEN
        -- Si no está asignado en el mismo horario, realiza la asignación
        INSERT INTO asignacionlimpieza (RFC, IdTipoLimpieza, IdZona, IdMaterial, HoraInicio, HoraFin) 
        VALUES (p_RFC, p_IdTipoLimpieza, p_IdZona, p_IdMaterial, p_HoraInicio, p_HoraFin);
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El empleado ya está asignado a esta zona y material en las mismas horas.';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_buscarLimpiezasPorEmpleado` (IN `empleadoRFC` VARCHAR(13))   BEGIN
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
        JOIN Personal p ON LOWER(al.RFC) = LOWER(p.RFC) -- Comparar sin importar mayúsculas/minúsculas
        JOIN Zona z ON al.IdZona = z.IdZona
        JOIN MaterialLimpieza m ON al.IdMaterial = m.IdMaterial
    WHERE 
        LOWER(al.RFC) = LOWER(empleadoRFC) -- Validar RFC ignorando mayúsculas/minúsculas
        AND al.HoraFin IS NOT NULL
    GROUP BY 
        al.NumAsignacion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminarMaterial` (IN `p_IdMaterial` INT)   BEGIN
    -- Eliminar registros relacionados en la tabla AsignacionLimpieza
    DELETE FROM AsignacionLimpieza
    WHERE IdMaterial = p_IdMaterial;

    -- Eliminar el material de la tabla MaterialLimpieza
    DELETE FROM MaterialLimpieza
    WHERE IdMaterial = p_IdMaterial;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacionlimpieza`
--

CREATE TABLE `asignacionlimpieza` (
  `NumAsignacion` int(3) NOT NULL,
  `RFC` varchar(15) NOT NULL,
  `IdTipoLimpieza` int(11) NOT NULL,
  `IdZona` int(11) NOT NULL,
  `IdMaterial` int(11) NOT NULL,
  `FechaAsignacion` date DEFAULT NULL,
  `HoraInicio` time NOT NULL,
  `HoraFin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacionlimpieza`
--

INSERT INTO `asignacionlimpieza` (`NumAsignacion`, `RFC`, `IdTipoLimpieza`, `IdZona`, `IdMaterial`, `FechaAsignacion`, `HoraInicio`, `HoraFin`) VALUES
(11, 'AWMV360713BJ5', 2, 1, 1, '2024-12-04', '00:00:00', '00:00:00'),
(12, 'AWMV360713BJ5', 2, 1, 2, '2024-12-04', '00:00:00', '00:00:00'),
(13, 'AWMV360713BJ5', 1, 1, 1, '2024-12-05', '01:17:00', '02:18:00'),
(14, 'AWMV360713BJ5', 1, 1, 2, '2024-12-05', '01:17:00', '02:18:00'),
(15, 'AWMV360713BJ5', 1, 1, 3, '2024-12-05', '01:17:00', '02:18:00'),
(16, 'AWMV360713BJ5', 2, 1, 1, '2024-12-05', '10:18:00', '14:18:00'),
(17, 'AWMV360713BJ5', 2, 1, 2, '2024-12-05', '10:18:00', '14:18:00'),
(18, 'AWMV360713BJ5', 2, 1, 3, '2024-12-05', '10:18:00', '14:18:00'),
(19, 'AWMV360713BJ5', 2, 1, 4, '2024-12-05', '10:18:00', '14:18:00'),
(20, 'AWMV360713BJ5', 2, 1, 5, '2024-12-05', '10:18:00', '14:18:00'),
(21, 'AWMV360713BJ5', 1, 1, 4, '2024-12-05', '01:00:00', '02:00:00'),
(22, 'AWMV360713BJ5', 1, 2, 1, NULL, '01:52:00', '02:53:00'),
(23, 'AWMV360713BJ5', 1, 2, 2, NULL, '01:52:00', '02:53:00'),
(24, 'AWMV360713BJ5', 1, 2, 3, NULL, '01:52:00', '02:53:00'),
(25, 'AWMV360713BJ5', 1, 2, 4, NULL, '01:52:00', '02:53:00'),
(26, 'AWMV360713BJ5', 1, 2, 1, NULL, '14:35:00', '15:36:00'),
(27, 'AWMV360713BJ5', 1, 2, 2, NULL, '14:35:00', '15:36:00'),
(28, 'AWMV360713BJ5', 1, 2, 3, NULL, '14:35:00', '15:36:00'),
(29, 'IIOQ750314FP9', 1, 1, 1, NULL, '03:04:00', '03:04:00'),
(30, 'IIOQ750314FP9', 1, 1, 2, NULL, '03:04:00', '03:04:00'),
(31, 'IIOQ750314FP9', 1, 1, 3, NULL, '03:04:00', '03:04:00'),
(32, 'IIOQ750314FP9', 1, 1, 4, NULL, '03:04:00', '03:04:00'),
(33, 'IIOQ750314FP9', 1, 1, 5, NULL, '03:04:00', '03:04:00'),
(34, 'AWMV360713BJ5', 1, 1, 4, NULL, '02:18:00', '03:17:00'),
(35, 'POPV890228TB7', 3, 2, 1, NULL, '02:10:00', '03:26:00'),
(36, 'POPV890228TB7', 3, 2, 2, NULL, '02:10:00', '03:26:00'),
(37, 'IIOQ978314FP9', 1, 2, 7, NULL, '13:24:00', '14:24:00'),
(38, 'IIOQ978314FP9', 1, 2, 8, NULL, '13:24:00', '14:24:00'),
(39, 'YWUG838626VJ9', 1, 2, 1, NULL, '16:30:00', '17:30:00'),
(40, 'YWUG838626VJ9', 1, 2, 4, NULL, '16:30:00', '17:30:00'),
(41, 'YWUG838626VJ9', 1, 2, 7, NULL, '16:30:00', '17:30:00'),
(42, 'YWUG838626VJ9', 1, 2, 10, NULL, '16:30:00', '17:30:00'),
(43, 'YWUG838626VJ9', 1, 2, 12, NULL, '16:30:00', '17:30:00'),
(44, 'YWUG838626VJ9', 2, 3, 1, NULL, '13:38:00', '14:39:00'),
(45, 'YWUG838626VJ9', 2, 3, 2, NULL, '13:38:00', '14:39:00'),
(46, 'POPV890228TB7', 2, 2, 1, NULL, '13:00:00', '02:00:00'),
(47, 'POPV890228TB7', 2, 2, 10, NULL, '13:00:00', '02:00:00'),
(48, 'POPV890228TB7', 2, 2, 12, NULL, '13:00:00', '02:00:00'),
(49, 'ABCD188897789', 2, 1, 1, NULL, '17:42:00', '18:42:00'),
(50, 'ABCD188897789', 2, 1, 7, NULL, '17:42:00', '18:42:00'),
(51, 'ABCD188897789', 2, 1, 8, NULL, '17:42:00', '18:42:00'),
(52, 'ABCD188897789', 2, 1, 12, NULL, '17:42:00', '18:42:00'),
(53, 'ABCD873456789', 1, 3, 1, NULL, '22:00:00', '23:00:00'),
(54, 'ABCD873456789', 1, 3, 2, NULL, '22:00:00', '23:00:00'),
(55, 'ABCD873456789', 1, 3, 3, NULL, '22:00:00', '23:00:00'),
(56, 'ABCD873456789', 1, 3, 4, NULL, '22:00:00', '23:00:00'),
(57, 'ABCD873456789', 1, 3, 5, NULL, '22:00:00', '23:00:00'),
(58, 'ABCD873456789', 1, 3, 6, NULL, '22:00:00', '23:00:00'),
(59, 'ABCD873456789', 1, 3, 7, NULL, '22:00:00', '23:00:00'),
(60, 'ABCD873456789', 1, 3, 8, NULL, '22:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horario`
--

CREATE TABLE `horario` (
  `IdHorario` int(11) NOT NULL,
  `HoraEntrada` time NOT NULL,
  `HoraSalida` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horario`
--

INSERT INTO `horario` (`IdHorario`, `HoraEntrada`, `HoraSalida`) VALUES
(1, '08:00:00', '12:00:00'),
(2, '12:00:00', '16:00:00'),
(3, '16:00:00', '20:00:00'),
(4, '20:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `IdInventario` int(11) NOT NULL,
  `NumAsignacion` int(3) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `FechaIngreso` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiallimpieza`
--

CREATE TABLE `materiallimpieza` (
  `IdMaterial` int(11) NOT NULL,
  `NombreMaterial` varchar(40) NOT NULL,
  `DescripcionMaterial` varchar(100) NOT NULL,
  `Stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materiallimpieza`
--

INSERT INTO `materiallimpieza` (`IdMaterial`, `NombreMaterial`, `DescripcionMaterial`, `Stock`) VALUES
(1, 'Detergente', 'Producto limpiador de superficies y pisosy', 1),
(2, 'Desinfectante', 'Desinfectante para baños y superficies de contacto frecuente', 4),
(3, 'Escoba', 'Herramienta para barrer', 2),
(4, 'Mopa', 'Herramienta para limpiar pisos mojados', 4),
(5, 'Guantes', 'Guantes de protección para la limpieza', 3),
(6, 'escobas', 'pp', 5),
(7, 'escobasu72', 'es de prueba', 5),
(8, 'escobasu72', 'es de prueba', 7),
(10, 'angel', '86', 0),
(12, 'gttgtggt', 'gg', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mobiliario`
--

CREATE TABLE `mobiliario` (
  `IdMobiliario` int(11) NOT NULL,
  `NombreMobiliario` varchar(40) NOT NULL,
  `DescripcionMobiliario` varchar(70) NOT NULL,
  `IdZona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `RFC` varchar(15) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `PrimerApellido` varchar(30) NOT NULL,
  `SegundoApellido` varchar(30) DEFAULT NULL,
  `Telefono` varchar(15) NOT NULL,
  `NoCasa` int(11) NOT NULL,
  `IdHorario` int(11) NOT NULL,
  `IdPuesto` int(11) NOT NULL,
  `EstadoLimpieza` enum('Disponible','Asignado') DEFAULT 'Disponible',
  `Estado` enum('Activo','Baja Temporal') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`RFC`, `Nombre`, `PrimerApellido`, `SegundoApellido`, `Telefono`, `NoCasa`, `IdHorario`, `IdPuesto`, `EstadoLimpieza`, `Estado`) VALUES
('ABCD188897789', 'MANUELFASDAS', 'IS', 'HERNSDO', '9838978673', 19, 4, 2, 'Asignado', 'Baja Temporal'),
('ABCD873456789', 'GYUUYYYS', 'SOLISDF', NULL, '9842342342', 0, 2, 2, 'Asignado', 'Baja Temporal'),
('AWMV360713BJ5', 'JUAN', 'PÉÉRE', NULL, '9875413123', 87, 4, 2, 'Asignado', 'Activo'),
('BLOK050220XV2', 'MARIO', 'ALBRETO', '', '9812321634', 0, 1, 2, 'Disponible', 'Activo'),
('IIOQ750314FP9', 'José ', 'Molina', 'Rodriguez', '9855413123', 121, 1, 1, 'Disponible', 'Activo'),
('IIOQ978314FP9', 'Julieta', 'Escalante', 'Escalante', '9879437263', 121, 3, 1, 'Asignado', 'Activo'),
('POPV890228TB7', 'Juan', 'Garcia', 'Rodriguez', '9875413123', 214, 2, 1, 'Asignado', 'Activo'),
('YWRE123345455', 'MIGEL EDUARDO', 'ALFREDO', 'MOLINA', '8934879234', 0, 2, 1, 'Disponible', 'Activo'),
('YWUG838626VJ9', 'angel', 'martin', 'miranda', '9831265321', 1, 2, 2, 'Disponible', 'Activo'),
('YWUG901026VJ9', 'OAYUDASD', 'MANUEL', '', '9871235421', 0, 1, 1, 'Disponible', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puesto`
--

CREATE TABLE `puesto` (
  `IdPuesto` int(11) NOT NULL,
  `nombrePuesto` varchar(30) NOT NULL,
  `DescripcionPuesto` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puesto`
--

INSERT INTO `puesto` (`IdPuesto`, `nombrePuesto`, `DescripcionPuesto`) VALUES
(1, 'Encargado de Limpieza', 'Responsable de coordinar las tareas de limpieza del cine'),
(2, 'Personal de Limpieza', 'Empleado que realiza las tareas de limpieza en las areas del cine');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipolimpieza`
--

CREATE TABLE `tipolimpieza` (
  `IdTipoLimpieza` int(11) NOT NULL,
  `ClaseLimpieza` varchar(20) NOT NULL,
  `DescripcionLimpieza` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipolimpieza`
--

INSERT INTO `tipolimpieza` (`IdTipoLimpieza`, `ClaseLimpieza`, `DescripcionLimpieza`) VALUES
(1, 'General', 'Limpieza básica de todas las zonas'),
(2, 'Profunda', 'Limpieza profunda de áreas específicas como baños o pasillos'),
(3, 'Rápida', 'Limpieza ligera y rápida en zonas de alto tránsito');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rfcEmpleado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `usuario`, `contraseña`, `email`, `rfcEmpleado`) VALUES
(15, 'raquel', '$2y$10$9e/F6aDw1yMPeIr8QPCvy.EbbJVoqKB2ID1vbo7.Lgxs1eUPnRh/S', 'raquelivet@gmail.com', 'IIOQ750314FP9'),
(16, 'Gab', '$2y$10$eu21o9z1nKsFrsAR8s2nHuE8ZJYHqPJC5St3KK72A5yA2ioPtcw1i', 'anaquelo1551@gmail.com', 'ABCD873456789');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zona`
--

CREATE TABLE `zona` (
  `IdZona` int(11) NOT NULL,
  `NombreZona` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `zona`
--

INSERT INTO `zona` (`IdZona`, `NombreZona`) VALUES
(1, 'Lobby'),
(2, 'Pasillos'),
(3, 'Salas de Cine 1'),
(4, 'Salas de Cine 2'),
(5, 'Baños'),
(6, 'Cafetería');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacionlimpieza`
--
ALTER TABLE `asignacionlimpieza`
  ADD PRIMARY KEY (`NumAsignacion`),
  ADD KEY `RFC` (`RFC`),
  ADD KEY `IdTipoLimpieza` (`IdTipoLimpieza`),
  ADD KEY `IdZona` (`IdZona`),
  ADD KEY `IdMaterial` (`IdMaterial`);

--
-- Indices de la tabla `horario`
--
ALTER TABLE `horario`
  ADD PRIMARY KEY (`IdHorario`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`IdInventario`),
  ADD KEY `NumAsignacion` (`NumAsignacion`);

--
-- Indices de la tabla `materiallimpieza`
--
ALTER TABLE `materiallimpieza`
  ADD PRIMARY KEY (`IdMaterial`);

--
-- Indices de la tabla `mobiliario`
--
ALTER TABLE `mobiliario`
  ADD PRIMARY KEY (`IdMobiliario`),
  ADD KEY `mobiliario_ibfk_1` (`IdZona`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`RFC`),
  ADD KEY `IdHorario` (`IdHorario`),
  ADD KEY `IdPuesto` (`IdPuesto`);

--
-- Indices de la tabla `puesto`
--
ALTER TABLE `puesto`
  ADD PRIMARY KEY (`IdPuesto`);

--
-- Indices de la tabla `tipolimpieza`
--
ALTER TABLE `tipolimpieza`
  ADD PRIMARY KEY (`IdTipoLimpieza`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `fk_rfcEmpleado` (`rfcEmpleado`);

--
-- Indices de la tabla `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`IdZona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacionlimpieza`
--
ALTER TABLE `asignacionlimpieza`
  MODIFY `NumAsignacion` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `horario`
--
ALTER TABLE `horario`
  MODIFY `IdHorario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `IdInventario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materiallimpieza`
--
ALTER TABLE `materiallimpieza`
  MODIFY `IdMaterial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `mobiliario`
--
ALTER TABLE `mobiliario`
  MODIFY `IdMobiliario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puesto`
--
ALTER TABLE `puesto`
  MODIFY `IdPuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipolimpieza`
--
ALTER TABLE `tipolimpieza`
  MODIFY `IdTipoLimpieza` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `zona`
--
ALTER TABLE `zona`
  MODIFY `IdZona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacionlimpieza`
--
ALTER TABLE `asignacionlimpieza`
  ADD CONSTRAINT `asignacionlimpieza_ibfk_1` FOREIGN KEY (`RFC`) REFERENCES `personal` (`RFC`),
  ADD CONSTRAINT `asignacionlimpieza_ibfk_2` FOREIGN KEY (`IdTipoLimpieza`) REFERENCES `tipolimpieza` (`IdTipoLimpieza`),
  ADD CONSTRAINT `asignacionlimpieza_ibfk_3` FOREIGN KEY (`IdZona`) REFERENCES `zona` (`IdZona`),
  ADD CONSTRAINT `asignacionlimpieza_ibfk_4` FOREIGN KEY (`IdMaterial`) REFERENCES `materiallimpieza` (`IdMaterial`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`NumAsignacion`) REFERENCES `asignacionlimpieza` (`NumAsignacion`);

--
-- Filtros para la tabla `mobiliario`
--
ALTER TABLE `mobiliario`
  ADD CONSTRAINT `mobiliario_ibfk_1` FOREIGN KEY (`IdZona`) REFERENCES `zona` (`IdZona`);

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`IdHorario`) REFERENCES `horario` (`IdHorario`),
  ADD CONSTRAINT `personal_ibfk_2` FOREIGN KEY (`IdPuesto`) REFERENCES `puesto` (`IdPuesto`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_rfcEmpleado` FOREIGN KEY (`rfcEmpleado`) REFERENCES `personal` (`RFC`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
