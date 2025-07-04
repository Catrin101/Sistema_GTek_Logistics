-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql102.infinityfree.com
-- Tiempo de generación: 04-07-2025 a las 15:02:19
-- Versión del servidor: 11.4.7-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_39332004_gtek`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_registros`
--

CREATE TABLE `bitacora_registros` (
  `id` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `tipo_operacion` enum('Entrada','Salida') NOT NULL,
  `num_conocimiento_embarque` varchar(100) NOT NULL,
  `num_registro_buque_vuelo_contenedor` varchar(100) NOT NULL,
  `dimension_tipo_sellos_candados` text DEFAULT NULL,
  `primer_puerto_terminal` varchar(255) NOT NULL,
  `descripcion_mercancia` text NOT NULL,
  `peso_unidad_medida` decimal(10,2) NOT NULL,
  `num_bultos` int(11) NOT NULL,
  `valor_comercial` decimal(15,2) NOT NULL,
  `fecha_conclusion_descarga` datetime DEFAULT NULL,
  `consignatario_id` int(11) DEFAULT NULL,
  `remitente_id` int(11) DEFAULT NULL,
  `registrado_por_user_id` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `ultima_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `numero_pedimento` int(11) DEFAULT NULL,
  `fraccion_arancelaria` decimal(10,4) DEFAULT NULL,
  `regimen` text DEFAULT NULL,
  `patente` int(11) DEFAULT NULL,
  `piezas` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consignatarios`
--

CREATE TABLE `consignatarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `domicilio` text DEFAULT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `remitentes`
--

CREATE TABLE `remitentes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `domicilio` text DEFAULT NULL,
  `pais_origen` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `nombre_conductor` varchar(255) NOT NULL,
  `usuario_del_sistema_id` int(11) DEFAULT NULL,
  `placas` varchar(50) NOT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitantes`
--

CREATE TABLE `visitantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `numero_verificacion` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_entrada` datetime DEFAULT current_timestamp(),
  `fecha_salida` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora_registros`
--
ALTER TABLE `bitacora_registros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_conocimiento_embarque` (`num_conocimiento_embarque`),
  ADD KEY `consignatario_id` (`consignatario_id`),
  ADD KEY `remitente_id` (`remitente_id`),
  ADD KEY `registrado_por_user_id` (`registrado_por_user_id`);

--
-- Indices de la tabla `consignatarios`
--
ALTER TABLE `consignatarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `remitentes`
--
ALTER TABLE `remitentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placas` (`placas`),
  ADD KEY `fk_vehiculos_usuario` (`usuario_del_sistema_id`),
  ADD KEY `idx_vehiculos_conductor` (`nombre_conductor`),
  ADD KEY `idx_vehiculos_placas` (`placas`),
  ADD KEY `idx_vehiculos_empresa` (`empresa`);

--
-- Indices de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_verificacion` (`numero_verificacion`),
  ADD KEY `idx_visitantes_nombre` (`nombre`),
  ADD KEY `idx_visitantes_num_verificacion` (`numero_verificacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora_registros`
--
ALTER TABLE `bitacora_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consignatarios`
--
ALTER TABLE `consignatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `remitentes`
--
ALTER TABLE `remitentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
