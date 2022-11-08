-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 08-11-2022 a las 01:38:47
-- Versión del servidor: 10.4.21-MariaDB
-- Versión de PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `php_login_datebase3`
--

DELIMITER $$
--
-- Funciones
--
CREATE FUNCTION `BIN_TO_UUID` (`b` BINARY(16), `f` BOOLEAN) RETURNS CHAR(36) CHARSET utf8mb4 DETERMINISTIC BEGIN
   DECLARE hexStr CHAR(32);
   SET hexStr = HEX(b);
   RETURN LOWER(CONCAT(
        IF(f,SUBSTR(hexStr, 9, 8),SUBSTR(hexStr, 1, 8)), '-',
        IF(f,SUBSTR(hexStr, 5, 4),SUBSTR(hexStr, 9, 4)), '-',
        IF(f,SUBSTR(hexStr, 1, 4),SUBSTR(hexStr, 13, 4)), '-',
        SUBSTR(hexStr, 17, 4), '-',
        SUBSTR(hexStr, 21)
    ));
END$$

CREATE FUNCTION `UUID_TO_BIN` (`uuid` CHAR(36), `f` BOOLEAN) RETURNS BINARY(16) DETERMINISTIC BEGIN
  RETURN UNHEX(CONCAT(
  IF(f,SUBSTRING(uuid, 15, 4),SUBSTRING(uuid, 1, 8)),
  SUBSTRING(uuid, 10, 4),
  IF(f,SUBSTRING(uuid, 1, 8),SUBSTRING(uuid, 15, 4)),
  SUBSTRING(uuid, 20, 4),
  SUBSTRING(uuid, 25))
  );
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_usuarios`
--

CREATE TABLE `intentos_usuarios` (
  `id_usuario` varchar(200) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `intentos` int(11) NOT NULL,
  `timer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peticiones_ip`
--

CREATE TABLE `peticiones_ip` (
  `ip` varchar(255) NOT NULL,
  `timer` int(11) NOT NULL,
  `intentos` int(11) NOT NULL,
  `ultima_peticion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `pass`) VALUES
('11ED5C3F232848D29F8EACDE48001122', 'admin@admin.com', '$2y$10$Vj5sur/VNz4S1T0V2rQ3V.5hG5LzF78gUlZ0YWzFszUUltxBnmVgm'),
('11ED5C3F309282C69F8EACDE48001122', 'test@test.com', '$2y$10$gJ8nZGu8IAKZ8l.QnSpSsu1XZ8Mp3Cz84QF.gSYOdJg4hfxmcyysO'),
('11ED5C3F4272A69C9F8EACDE48001122', 'tini@tini.com', '$2y$10$/HZyPAhtNMs9ZB5MrSwYEur24ECvGkjMWt3BtkSa8m.oS1TUr7e6G');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
