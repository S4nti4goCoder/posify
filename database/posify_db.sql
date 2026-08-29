-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-08-2026 a las 23:55:48
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
-- Base de datos: `posify_db`
--
CREATE DATABASE IF NOT EXISTS `posify_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `posify_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id_admin` int(11) NOT NULL,
  `email_admin` varchar(191) DEFAULT NULL,
  `password_admin` varchar(255) DEFAULT NULL,
  `rol_admin` varchar(30) DEFAULT NULL,
  `permissions_admin` mediumtext DEFAULT '{}',
  `token_admin` mediumtext DEFAULT NULL,
  `token_exp_admin` varchar(500) DEFAULT NULL,
  `status_admin` int(11) DEFAULT 1,
  `title_admin` varchar(500) DEFAULT NULL,
  `symbol_admin` varchar(500) DEFAULT NULL,
  `font_admin` varchar(500) DEFAULT NULL,
  `color_admin` varchar(500) DEFAULT NULL,
  `back_admin` varchar(500) DEFAULT NULL,
  `scode_admin` mediumtext DEFAULT NULL,
  `name_admin` varchar(500) DEFAULT NULL,
  `id_office_admin` int(11) DEFAULT 0,
  `chatgpt_admin` mediumtext DEFAULT NULL,
  `date_created_admin` date DEFAULT NULL,
  `date_updated_admin` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_admin` varchar(64) DEFAULT NULL COMMENT 'sha256 of the recovery code',
  `date_reset_admin` datetime DEFAULT NULL COMMENT 'when the code stops working'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id_admin`, `email_admin`, `password_admin`, `rol_admin`, `permissions_admin`, `token_admin`, `token_exp_admin`, `status_admin`, `title_admin`, `symbol_admin`, `font_admin`, `color_admin`, `back_admin`, `scode_admin`, `name_admin`, `id_office_admin`, `chatgpt_admin`, `date_created_admin`, `date_updated_admin`, `reset_admin`, `date_reset_admin`) VALUES
(1, 'admin@surtihogar.com', '$2y$10$sG4RTClh15y1TBv9VU073O9/5ibS8MKOQoYVC/tpoS2sY3qwYt5yi', 'superadmin', '{\"todo\":\"on\"}', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3ODgwMzg5ODEsImV4cCI6MTc4ODEyNTM4MSwiZGF0YSI6eyJpZCI6MSwiZW1haWwiOiJhZG1pbkBzdXJ0aWhvZ2FyLmNvbSJ9fQ.c54_jn3XUAKOODzICJ4i5nhpCbUOvfrLH83uSa00nt4', '1788125381', 1, 'POSify', 'cart-check-fill', 'Nunito', '#00a6fb', '/views/assets/files/6a9109461c23530.jpg', NULL, 'Camilo Herrera', 0, NULL, '2025-02-14', '2026-08-29 21:29:41', NULL, NULL),
(2, 'gerente@surtihogar.com', '$2y$10$TiSvgenIP9c6Df8S13XiUehXKLkByT0HK217NfeLIAUf7Z9K.ikVy', 'admin', '{\"todo\":\"on\"}', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3ODgwMzkwNDMsImV4cCI6MTc4ODEyNTQ0MywiZGF0YSI6eyJpZCI6MiwiZW1haWwiOiJnZXJlbnRlQHN1cnRpaG9nYXIuY29tIn19.Au-vrnmwh9GjkYzuFo2P8E07oyex94ncaNWKdad2mmY', '1788125443', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Laura Restrepo', 0, NULL, '2026-08-27', '2026-08-29 21:30:43', NULL, NULL),
(3, 'centro@surtihogar.com', '$2y$10$S5PxBIPXy3fG1l7n3kaYrO7GgE0fRQ5qDXc7rmiI39QY2sscPEci6', 'vendedor', '{\"posify\":\"on\",\"ventas\":\"on\",\"caja\":\"on\",\"clientes\":\"on\"}', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3ODgwMzg5MjgsImV4cCI6MTc4ODEyNTMyOCwiZGF0YSI6eyJpZCI6MywiZW1haWwiOiJjZW50cm9Ac3VydGlob2dhci5jb20ifX0.zF6kpmWrAyRsNcrOvaUHsXLoKriEeHKRVCet6Kowjk0', '1788125328', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Andres Gomez', 1, NULL, '2026-08-27', '2026-08-29 21:46:57', NULL, NULL),
(4, 'norte@surtihogar.com', '$2y$10$S5PxBIPXy3fG1l7n3kaYrO7GgE0fRQ5qDXc7rmiI39QY2sscPEci6', 'vendedor', '{\"posify\":\"on\",\"ventas\":\"on\",\"caja\":\"on\",\"clientes\":\"on\"}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Diana Torres', 2, NULL, '2026-08-27', '2026-08-29 21:46:57', NULL, NULL),
(5, 'sur@surtihogar.com', '$2y$10$S5PxBIPXy3fG1l7n3kaYrO7GgE0fRQ5qDXc7rmiI39QY2sscPEci6', 'vendedor', '{\"posify\":\"on\",\"ventas\":\"on\",\"caja\":\"on\",\"clientes\":\"on\"}', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Julian Ramirez', 3, NULL, '2026-08-27', '2026-08-29 21:46:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bills`
--

CREATE TABLE `bills` (
  `id_bill` int(11) NOT NULL,
  `concept_bill` varchar(500) DEFAULT NULL,
  `cost_bill` decimal(14,2) DEFAULT 0.00,
  `date_bill` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_admin_bill` int(11) DEFAULT 0,
  `id_office_bill` int(11) DEFAULT 0,
  `date_created_bill` date DEFAULT NULL,
  `date_updated_bill` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cashs`
--

CREATE TABLE `cashs` (
  `id_cash` int(11) NOT NULL,
  `start_cash` decimal(14,2) DEFAULT 0.00,
  `bills_cash` decimal(14,2) DEFAULT 0.00,
  `money_cash` decimal(14,2) DEFAULT 0.00,
  `diff_cash` decimal(14,2) DEFAULT 0.00,
  `end_cash` decimal(14,2) DEFAULT 0.00,
  `gap_cash` decimal(14,2) DEFAULT 0.00,
  `status_cash` int(11) DEFAULT 1,
  `date_start_cash` datetime DEFAULT NULL,
  `date_end_cash` datetime DEFAULT NULL,
  `id_admin_cash` int(11) DEFAULT 0,
  `id_office_cash` int(11) DEFAULT 0,
  `date_created_cash` date DEFAULT NULL,
  `date_updated_cash` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id_category` int(11) NOT NULL,
  `title_category` varchar(500) DEFAULT NULL,
  `img_category` varchar(500) DEFAULT NULL,
  `order_category` int(11) DEFAULT 0,
  `status_category` int(11) DEFAULT 1,
  `date_created_category` date DEFAULT NULL,
  `date_updated_category` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id_category`, `title_category`, `img_category`, `order_category`, `status_category`, `date_created_category`, `date_updated_category`) VALUES
(1, 'Bebidas', '/views/assets/files/6a91093e5e46d22.jpg', 1, 1, '2026-08-27', '2026-08-28 04:28:38'),
(2, 'Snacks y dulces', '/views/assets/files/6a91093e4768822.jpg', 2, 1, '2026-08-27', '2026-08-28 04:28:38'),
(3, 'Lácteos y huevos', '/views/assets/files/6a91093e6536b22.jpg', 3, 1, '2026-08-27', '2026-08-28 04:28:38'),
(4, 'Abarrotes', '/views/assets/files/6a91093e57b3022.jpg', 4, 1, '2026-08-27', '2026-08-28 04:28:38'),
(5, 'Aseo del hogar', '/views/assets/files/6a91093e4e16622.jpg', 5, 1, '2026-08-27', '2026-08-28 04:28:38'),
(6, 'Panadería', '/views/assets/files/6a91093e6f05a22.jpg', 6, 1, '2026-08-27', '2026-08-28 04:28:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id_client` int(11) NOT NULL,
  `cc_client` varchar(500) DEFAULT NULL,
  `name_client` varchar(500) DEFAULT NULL,
  `surname_client` varchar(500) DEFAULT NULL,
  `email_client` varchar(191) DEFAULT NULL,
  `address_client` varchar(500) DEFAULT NULL,
  `phone_client` varchar(500) DEFAULT NULL,
  `id_office_client` int(11) DEFAULT 0,
  `date_created_client` date DEFAULT NULL,
  `date_updated_client` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`id_client`, `cc_client`, `name_client`, `surname_client`, `email_client`, `address_client`, `phone_client`, `id_office_client`, `date_created_client`, `date_updated_client`) VALUES
(1, '222222222222', 'Consumidor', 'Final', '', '', '', 1, '2026-08-27', '2026-08-28 09:28:38'),
(2, '222222222222', 'Consumidor', 'Final', '', '', '', 2, '2026-08-27', '2026-08-28 09:28:38'),
(3, '222222222222', 'Consumidor', 'Final', '', '', '', 3, '2026-08-27', '2026-08-28 09:28:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `columns`
--

CREATE TABLE `columns` (
  `id_column` int(11) NOT NULL,
  `id_module_column` int(11) DEFAULT 0,
  `title_column` varchar(100) DEFAULT NULL,
  `alias_column` varchar(150) DEFAULT NULL,
  `type_column` varchar(30) DEFAULT NULL,
  `matrix_column` varchar(500) DEFAULT NULL,
  `visible_column` int(11) DEFAULT 1,
  `date_created_column` date DEFAULT NULL,
  `date_updated_column` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `columns`
--

INSERT INTO `columns` (`id_column`, `id_module_column`, `title_column`, `alias_column`, `type_column`, `matrix_column`, `visible_column`, `date_created_column`, `date_updated_column`) VALUES
(1, 2, 'rol_admin', 'rol', 'select', 'superadmin,admin,vendedor', 1, '2025-02-14', '2026-08-29 21:46:57'),
(2, 2, 'permissions_admin', 'permisos', 'object', '', 1, '2025-02-14', '2025-02-14 07:06:16'),
(3, 2, 'email_admin', 'email', 'email', '', 1, '2025-02-14', '2025-02-14 07:06:16'),
(4, 2, 'password_admin', 'pass', 'password', '', 0, '2025-02-14', '2025-02-14 07:06:16'),
(5, 2, 'token_admin', 'token', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:16'),
(6, 2, 'token_exp_admin', 'expiración', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:16'),
(7, 2, 'status_admin', 'estado', 'boolean', '', 1, '2025-02-14', '2025-02-14 07:06:16'),
(8, 2, 'title_admin', 'título', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:16'),
(9, 2, 'symbol_admin', 'simbolo', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:17'),
(10, 2, 'font_admin', 'tipografía', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:17'),
(11, 2, 'color_admin', 'color', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:17'),
(12, 2, 'back_admin', 'fondo', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:17'),
(13, 2, 'scode_admin', 'seguridad', 'text', '', 0, '2025-02-14', '2025-02-14 07:06:17'),
(14, 4, 'title_office', 'Sucursales', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:05:35'),
(15, 4, 'address_office', 'Dirección ', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:05:35'),
(16, 4, 'phone_office', 'Teléfono', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:05:35'),
(17, 6, 'cc_client', 'Documento', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(18, 6, 'name_client', 'Nombre', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(19, 6, 'surname_client', 'Apellido', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(20, 6, 'email_client', 'Email', 'email', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(21, 6, 'address_client', 'Dirección ', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(22, 6, 'phone_client', 'Teléfono ', 'text', NULL, 1, '2025-02-15', '2025-02-15 01:51:48'),
(23, 6, 'id_office_client', 'Sucursal', 'relations', 'offices', 1, '2025-02-15', '2026-08-23 20:13:07'),
(24, 8, 'title_category', 'Categoría ', 'text', NULL, 1, '2025-02-16', '2025-02-16 03:20:10'),
(25, 8, 'img_category', 'Imagen', 'image', NULL, 1, '2025-02-16', '2025-02-16 03:20:10'),
(26, 8, 'order_category', 'Orden', 'order', NULL, 1, '2025-02-16', '2025-02-16 03:20:10'),
(27, 8, 'status_category', 'Estado', 'boolean', NULL, 1, '2025-02-16', '2025-02-16 03:20:10'),
(28, 10, 'title_product', 'Producto', 'text', NULL, 1, '2025-02-18', '2025-02-18 22:46:26'),
(29, 10, 'img_product', 'Imagen', 'image', NULL, 1, '2025-02-18', '2025-02-18 22:46:26'),
(30, 10, 'id_category_product', 'Categoría ', 'relations', 'categories', 1, '2025-02-18', '2025-02-18 22:49:11'),
(31, 10, 'sku_product', 'SKU', 'text', NULL, 1, '2025-02-18', '2025-02-18 22:46:27'),
(32, 10, 'unit_product', 'Medida', 'select', 'unidad,centímetros cúbicos,decibel,pie cúbico,libra,tonelada', 1, '2025-02-18', '2025-02-18 23:23:00'),
(33, 10, 'tax_product', 'Impuesto', 'select', 'IVA_19,INC_4', 1, '2025-02-18', '2025-02-18 22:53:36'),
(34, 10, 'rte_product', 'Retención', 'select', 'Ninguna,RETF_11', 1, '2025-02-18', '2026-08-24 01:38:47'),
(36, 10, 'discount_product', 'Descuento', 'double', NULL, 1, '2025-02-18', '2025-02-18 22:46:27'),
(37, 10, 'status_product', 'Estado', 'boolean', NULL, 1, '2025-02-18', '2025-02-18 22:46:27'),
(39, 12, 'supplier_purchase', 'Proveedor', 'text', NULL, 1, '2025-02-20', '2025-02-20 22:36:21'),
(40, 12, 'id_product_purchase', 'Producto', 'relations', 'products', 1, '2025-02-20', '2025-02-20 22:40:00'),
(41, 12, 'cost_purchase', 'Costo', 'money', NULL, 1, '2025-02-20', '2025-02-20 22:36:21'),
(42, 12, 'utility_purchase', 'Utilidad', 'select', '10%,20%,30%,40%,50%', 1, '2025-02-20', '2025-02-20 23:52:05'),
(43, 12, 'price_purchase', 'Precio', 'money', NULL, 1, '2025-02-20', '2025-02-20 22:36:22'),
(44, 12, 'qty_purchase', 'Cantidad', 'int', NULL, 1, '2025-02-20', '2025-02-20 22:36:22'),
(45, 12, 'invest_purchase', 'Inversión ', 'money', NULL, 1, '2025-02-20', '2025-02-20 22:36:22'),
(46, 12, 'contact_purchase', 'Teléfono ', 'text', NULL, 1, '2025-02-20', '2025-02-20 22:36:22'),
(47, 12, 'id_office_purchase', 'Sucursal', 'relations', 'offices', 1, '2025-02-20', '2025-02-20 22:40:50'),
(48, 14, 'transaction_order', 'Transacción ', 'posify', NULL, 1, '2025-02-21', '2025-04-03 04:43:52'),
(49, 14, 'id_admin_order', 'Vendedor', 'relations', 'admins', 1, '2025-02-21', '2025-02-21 00:51:33'),
(50, 14, 'id_client_order', 'Cliente', 'relations', 'clients', 1, '2025-02-21', '2025-02-21 00:51:39'),
(51, 14, 'subtotal_order', 'Subtotal', 'money', NULL, 1, '2025-02-21', '2025-02-21 00:50:48'),
(52, 14, 'discount_order', 'Decuento', 'money', NULL, 1, '2025-02-21', '2025-02-21 00:50:48'),
(53, 14, 'tax_order', 'Impuesto', 'money', NULL, 1, '2025-02-21', '2025-02-21 00:50:48'),
(54, 14, 'total_order', 'Total', 'money', NULL, 1, '2025-02-21', '2025-02-21 00:50:48'),
(55, 14, 'method_order', 'Método ', 'select', 'efectivo,transferencia,tarjeta', 1, '2025-02-21', '2025-02-21 00:55:57'),
(56, 14, 'transfer_order', 'Transferencia', 'text', NULL, 1, '2025-02-21', '2025-02-21 00:50:49'),
(57, 14, 'status_order', 'Estado', 'select', 'Completada,Pendiente', 1, '2025-02-21', '2025-02-21 00:56:13'),
(58, 14, 'date_order', 'Fecha', 'timestamp', NULL, 1, '2025-02-21', '2025-02-21 00:50:49'),
(59, 14, 'id_office_order', 'Sucursal', 'relations', 'offices', 1, '2025-02-21', '2025-02-21 00:51:51'),
(60, 16, 'id_order_sale', 'Orden', 'relations', 'orders', 1, '2025-02-21', '2025-02-21 03:27:55'),
(61, 16, 'id_product_sale', 'Producto', 'relations', 'products', 1, '2025-02-21', '2025-02-21 03:27:45'),
(62, 16, 'tax_type_sale', 'Tipo Impuesto', 'text', NULL, 1, '2025-02-21', '2025-02-21 03:25:49'),
(63, 16, 'tax_sale', 'Impuesto', 'double', NULL, 1, '2025-02-21', '2026-08-24 01:30:15'),
(64, 16, 'discount_sale', 'Descuento', 'double', NULL, 1, '2025-02-21', '2026-08-24 01:30:15'),
(65, 16, 'qty_sale', 'Cantidad', 'int', NULL, 1, '2025-02-21', '2025-02-21 03:25:49'),
(66, 16, 'subtotal_sale', 'Subtotal', 'money', NULL, 1, '2025-02-21', '2025-02-21 03:25:49'),
(67, 16, 'status_sale', 'Estado', 'select', 'Completada,Pendiente', 1, '2025-02-21', '2025-02-21 03:27:24'),
(68, 16, 'id_admin_sale', 'Vendedor', 'relations', 'admins', 1, '2025-02-21', '2025-02-21 03:27:05'),
(69, 16, 'id_client_sale', 'Cliente', 'relations', 'clients', 1, '2025-02-21', '2025-02-21 03:27:02'),
(70, 16, 'id_office_sale', 'Sucursal', 'relations', 'offices', 1, '2025-02-21', '2025-02-21 03:26:51'),
(71, 18, 'start_cash', 'Dinero Inicial', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:07'),
(72, 18, 'bills_cash', 'Gastos', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:07'),
(73, 18, 'money_cash', 'Ingresos', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:07'),
(74, 18, 'diff_cash', 'Diferencia', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(75, 18, 'end_cash', 'Dinero Final', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(76, 18, 'gap_cash', 'Brecha', 'money', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(77, 18, 'status_cash', 'Estado', 'boolean', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(78, 18, 'date_start_cash', 'Fecha Inicial', 'datetime', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(79, 18, 'date_end_cash', 'Fecha Final', 'datetime', NULL, 1, '2025-02-22', '2025-02-22 04:59:08'),
(80, 18, 'id_admin_cash', 'Administrador', 'relations', 'admins', 1, '2025-02-22', '2025-02-22 04:59:48'),
(81, 18, 'id_office_cash', 'Sucursal', 'relations', 'offices', 1, '2025-02-22', '2025-02-22 04:59:40'),
(82, 20, 'concept_bill', 'Concepto', 'text', NULL, 1, '2025-02-22', '2025-02-22 05:07:19'),
(83, 20, 'cost_bill', 'Costo', 'money', NULL, 1, '2025-02-22', '2025-02-22 05:07:19'),
(84, 20, 'date_bill', 'Fecha', 'timestamp', NULL, 1, '2025-02-22', '2025-02-22 05:07:19'),
(85, 20, 'id_admin_bill', 'Administrador', 'relations', 'admins', 1, '2025-02-22', '2025-02-22 05:07:42'),
(86, 20, 'id_office_bill', 'Sucursal', 'relations', 'offices', 1, '2025-02-22', '2025-02-22 05:07:46'),
(87, 2, 'name_admin', 'Nombre', 'text', NULL, 1, '2025-03-28', '2025-03-28 05:46:21'),
(88, 2, 'id_office_admin', 'Sucursal', 'relations', 'offices', 1, '2025-03-28', '2025-03-28 21:05:38'),
(92, 10, 'qty_stock', 'Stock', 'stock', '', 1, '2026-08-24', '2026-08-24 19:26:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `files`
--

CREATE TABLE `files` (
  `id_file` int(11) NOT NULL,
  `id_folder_file` int(11) DEFAULT 0,
  `name_file` varchar(255) DEFAULT NULL,
  `extension_file` varchar(20) DEFAULT NULL,
  `type_file` varchar(30) DEFAULT NULL,
  `size_file` double DEFAULT 0,
  `link_file` varchar(500) DEFAULT NULL,
  `thumbnail_vimeo_file` varchar(500) DEFAULT NULL,
  `id_mailchimp_file` varchar(100) DEFAULT NULL,
  `date_created_file` date DEFAULT NULL,
  `date_updated_file` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `files`
--

INSERT INTO `files` (`id_file`, `id_folder_file`, `name_file`, `extension_file`, `type_file`, `size_file`, `link_file`, `thumbnail_vimeo_file`, `id_mailchimp_file`, `date_created_file`, `date_updated_file`) VALUES
(1, 1, '2', 'jpg', 'image/jpeg', 436379, '/views/assets/files/6a91092fdb9a57.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(2, 1, '3', 'jpg', 'image/jpeg', 404986, '/views/assets/files/6a91092fe95f37.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(3, 1, '1', 'jpg', 'image/jpeg', 893269, '/views/assets/files/6a91092ff1b587.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(4, 1, '5', 'jpg', 'image/jpeg', 473500, '/views/assets/files/6a9109300a23c8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(5, 1, '4', 'jpg', 'image/jpeg', 1077204, '/views/assets/files/6a910930118558.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(6, 1, '6', 'jpg', 'image/jpeg', 884777, '/views/assets/files/6a9109301a6838.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(7, 1, '7', 'jpg', 'image/jpeg', 609643, '/views/assets/files/6a9109302272a8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(8, 1, '8', 'jpg', 'image/jpeg', 537498, '/views/assets/files/6a9109302d0e68.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(9, 1, '9', 'jpg', 'image/jpeg', 1019724, '/views/assets/files/6a910930335458.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(10, 1, '11', 'jpg', 'image/jpeg', 575426, '/views/assets/files/6a9109303d0a38.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(11, 1, '13', 'jpg', 'image/jpeg', 465875, '/views/assets/files/6a910930435ef8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(12, 1, '10', 'jpg', 'image/jpeg', 463699, '/views/assets/files/6a9109304de188.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(13, 1, '14', 'jpg', 'image/jpeg', 643553, '/views/assets/files/6a91093054ddc8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(14, 1, '15', 'jpg', 'image/jpeg', 584212, '/views/assets/files/6a91093060d2e8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(15, 1, '12', 'jpg', 'image/jpeg', 793089, '/views/assets/files/6a910930677ed8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(16, 1, '16', 'jpg', 'image/jpeg', 671141, '/views/assets/files/6a91093072e3a8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(17, 1, '19', 'jpg', 'image/jpeg', 668045, '/views/assets/files/6a91093079fd38.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(18, 1, '18', 'jpg', 'image/jpeg', 677327, '/views/assets/files/6a9109308520e8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(19, 1, '17', 'jpg', 'image/jpeg', 538132, '/views/assets/files/6a9109308d3dd8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(20, 1, '20', 'jpg', 'image/jpeg', 480823, '/views/assets/files/6a910930948538.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(21, 1, '24', 'jpg', 'image/jpeg', 581314, '/views/assets/files/6a9109309b7bf8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(22, 1, '22', 'jpg', 'image/jpeg', 450444, '/views/assets/files/6a910930a31038.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(23, 1, '23', 'jpg', 'image/jpeg', 359422, '/views/assets/files/6a910930aab718.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(24, 1, '28', 'jpg', 'image/jpeg', 406158, '/views/assets/files/6a910930b36538.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(25, 1, '21', 'jpg', 'image/jpeg', 774628, '/views/assets/files/6a910930bb8b58.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(26, 1, '29', 'jpg', 'image/jpeg', 882349, '/views/assets/files/6a910930c2f6b8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(27, 1, '26', 'jpg', 'image/jpeg', 428663, '/views/assets/files/6a910930c99348.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(28, 1, '27', 'jpg', 'image/jpeg', 434772, '/views/assets/files/6a910930d055c8.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(29, 1, '25', 'jpg', 'image/jpeg', 1138288, '/views/assets/files/6a910930d7d878.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(30, 1, '30', 'jpg', 'image/jpeg', 448669, '/views/assets/files/6a910930df1478.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(31, 1, 'CAT-1', 'jpg', 'image/jpeg', 289410, '/views/assets/files/6a91093e4768822.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(32, 1, 'CAT-2', 'jpg', 'image/jpeg', 300727, '/views/assets/files/6a91093e4e16622.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(33, 1, 'CAT-3', 'jpg', 'image/jpeg', 304819, '/views/assets/files/6a91093e57b3022.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(34, 1, 'CAT-5', 'jpg', 'image/jpeg', 243102, '/views/assets/files/6a91093e5e46d22.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(35, 1, 'CAT-6', 'jpg', 'image/jpeg', 288262, '/views/assets/files/6a91093e6536b22.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(36, 1, 'CAT-4', 'jpg', 'image/jpeg', 343290, '/views/assets/files/6a91093e6f05a22.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50'),
(37, 1, 'PRINCIPAL', 'jpg', 'image/jpeg', 806985, '/views/assets/files/6a9109461c23530.jpg', NULL, NULL, '2026-08-28', '2026-08-29 03:21:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `folders`
--

CREATE TABLE `folders` (
  `id_folder` int(11) NOT NULL,
  `name_folder` varchar(150) DEFAULT NULL,
  `size_folder` varchar(30) DEFAULT NULL,
  `total_folder` double DEFAULT 0,
  `max_upload_folder` varchar(30) DEFAULT NULL,
  `url_folder` varchar(500) DEFAULT NULL,
  `keys_folder` mediumtext DEFAULT NULL,
  `date_created_folder` date DEFAULT NULL,
  `date_updated_folder` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `folders`
--

INSERT INTO `folders` (`id_folder`, `name_folder`, `size_folder`, `total_folder`, `max_upload_folder`, `url_folder`, `keys_folder`, `date_created_folder`, `date_updated_folder`) VALUES
(1, 'Server', '200000000000', 21379604, '500000000', '', NULL, '2025-02-14', '2026-08-29 03:21:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id_attempt` int(11) NOT NULL,
  `who_attempt` varchar(64) NOT NULL COMMENT 'sha256 of email and address',
  `tries_attempt` int(11) DEFAULT 0,
  `date_first_attempt` datetime DEFAULT NULL,
  `date_created_attempt` date DEFAULT NULL,
  `date_updated_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modules`
--

CREATE TABLE `modules` (
  `id_module` int(11) NOT NULL,
  `id_page_module` int(11) DEFAULT 0,
  `type_module` varchar(30) DEFAULT NULL,
  `title_module` varchar(100) DEFAULT NULL,
  `suffix_module` varchar(50) DEFAULT NULL,
  `content_module` mediumtext DEFAULT NULL,
  `width_module` int(11) DEFAULT 100,
  `editable_module` int(11) DEFAULT 1,
  `date_created_module` date DEFAULT NULL,
  `date_updated_module` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modules`
--

INSERT INTO `modules` (`id_module`, `id_page_module`, `type_module`, `title_module`, `suffix_module`, `content_module`, `width_module`, `editable_module`, `date_created_module`, `date_updated_module`) VALUES
(1, 2, 'breadcrumbs', 'Administradores', NULL, NULL, 100, 1, '2025-02-14', '2025-02-14 07:06:16'),
(2, 2, 'tables', 'admins', 'admin', '', 100, 0, '2025-02-14', '2025-03-28 05:46:20'),
(3, 4, 'breadcrumbs', 'sucursales', '', '', 100, 1, '2025-02-15', '2025-02-15 00:59:02'),
(4, 4, 'tables', 'offices', 'office', '', 100, 1, '2025-02-15', '2025-02-15 01:05:35'),
(5, 5, 'breadcrumbs', 'clientes', '', '', 100, 1, '2025-02-15', '2025-02-15 01:46:45'),
(6, 5, 'tables', 'clients', 'client', '', 100, 1, '2025-02-15', '2025-02-15 01:51:48'),
(7, 6, 'breadcrumbs', 'categorías', '', '', 100, 1, '2025-02-16', '2025-02-16 03:14:49'),
(8, 6, 'tables', 'categories', 'category', '', 100, 1, '2025-02-16', '2025-02-16 03:20:10'),
(9, 7, 'breadcrumbs', 'productos', '', '', 100, 1, '2025-02-18', '2025-02-18 22:35:55'),
(10, 7, 'tables', 'products', 'product', '', 100, 1, '2025-02-18', '2025-02-18 22:46:26'),
(11, 8, 'breadcrumbs', 'compras', '', '', 100, 1, '2025-02-20', '2025-02-20 22:30:54'),
(12, 8, 'tables', 'purchases', 'purchase', '', 100, 1, '2025-02-20', '2025-02-20 22:36:21'),
(13, 9, 'breadcrumbs', 'Órdenes', '', '', 100, 1, '2025-02-21', '2025-02-21 00:42:21'),
(14, 9, 'tables', 'orders', 'order', '', 100, 0, '2025-02-21', '2025-02-21 00:55:16'),
(15, 10, 'breadcrumbs', 'ventas', '', '', 100, 1, '2025-02-21', '2025-02-21 03:21:59'),
(16, 10, 'tables', 'sales', 'sale', '', 100, 0, '2025-02-21', '2025-02-21 03:25:48'),
(17, 11, 'breadcrumbs', 'caja', '', '', 100, 1, '2025-02-22', '2025-02-22 04:51:55'),
(18, 11, 'tables', 'cashs', 'cash', '', 100, 1, '2025-02-22', '2025-02-22 04:59:07'),
(19, 12, 'breadcrumbs', 'gastos', '', '', 100, 1, '2025-02-22', '2025-02-22 05:04:33'),
(20, 12, 'tables', 'bills', 'bill', '', 100, 1, '2025-02-22', '2025-02-22 05:07:19'),
(21, 1, 'custom', 'orders', '', '', 100, 1, '2025-03-29', '2025-03-28 23:45:34'),
(22, 1, 'custom', 'products', '', '', 50, 1, '2025-03-29', '2025-03-28 23:48:48'),
(23, 1, 'custom', 'panel', '', '', 50, 1, '2025-03-29', '2025-03-28 23:49:09'),
(24, 13, 'metrics', 'ventas', '', '{\"type\":\"add\",\"table\":\"orders\", \"column\":\"total_order\",\"config\":\"price\",\"icon\":\"fas fa-cart-arrow-down\",\"color\":\"28, 175, 159\"  }', 25, 1, '2025-04-03', '2025-04-03 20:02:03'),
(25, 13, 'metrics', 'compras', '', '{\"type\":\"add\",\"table\":\"purchases\", \"column\":\"invest_purchase\",\"config\":\"price\",\"icon\":\"fas fa-shopping-basket\",\"color\":\"128, 0, 0\"  }', 25, 1, '2025-04-03', '2025-04-03 20:07:04'),
(26, 13, 'metrics', 'productos', '', '{\"type\":\"add\",\"table\":\"stocks\",\"column\":\"qty_stock\",\"config\":\"unit\",\"icon\":\"fas fa-box\",\"color\":\"77, 93, 219\"}', 25, 1, '2025-04-03', '2026-08-25 02:35:24'),
(27, 13, 'metrics', 'clientes', '', '{\"type\":\"total\",\"table\":\"clients\", \"column\":\"id_client\",\"config\":\"unit\",\"icon\":\"fas fa-users\",\"color\":\"43, 62, 101\"  }', 25, 1, '2025-04-03', '2025-04-03 20:09:23'),
(28, 13, 'graphics', 'gráfico de ventas diarias', '', '{\"type\":\"bar\",\"table\":\"orders\",\"xAxis\":\"date_created_order\",\"yAxis\":\"total_order\",\"color\":\"134, 153, 163\"}', 100, 1, '2025-04-03', '2025-04-03 20:57:30'),
(29, 13, 'graphics', 'gráfico de ventas mensuales', '', '{\"type\":\"line\",\"table\":\"orders\",\"xAxis\":\"date_created_order\",\"yAxis\":\"total_order\",\"color\":\"252, 115, 3\"}', 100, 1, '2025-04-03', '2025-04-03 21:39:11'),
(30, 13, 'graphics', 'ventas por sucursal', '', '{\"type\":\"bar\",\"table\":\"orders\",\"xAxis\":\"id_office_order\",\"yAxis\":\"total_order\",\"color\":\"5, 195, 251\"}', 50, 1, '2025-04-03', '2025-04-03 22:16:53'),
(31, 13, 'graphics', 'compras por sucursal', '', '{\"type\":\"bar\",\"table\":\"purchases\",\"xAxis\":\"id_office_purchase\",\"yAxis\":\"invest_purchase\",\"color\":\"247, 183, 49\"}', 50, 1, '2025-04-03', '2025-04-03 22:32:08'),
(32, 13, 'custom', 'productos mas vendidos', '', '', 50, 1, '2025-04-03', '2025-04-03 22:53:46'),
(33, 13, 'custom', 'clientes más activos', '', '', 50, 1, '2025-04-03', '2025-04-03 23:30:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `offices`
--

CREATE TABLE `offices` (
  `id_office` int(11) NOT NULL,
  `title_office` varchar(500) DEFAULT NULL,
  `address_office` varchar(500) DEFAULT NULL,
  `phone_office` varchar(500) DEFAULT NULL,
  `date_created_office` date DEFAULT NULL,
  `date_updated_office` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `offices`
--

INSERT INTO `offices` (`id_office`, `title_office`, `address_office`, `phone_office`, `date_created_office`, `date_updated_office`) VALUES
(1, 'Surtihogar Centro', 'Calle 13 # 8-45, Bogota', '601 234 5678', '2025-02-15', '2026-08-28 03:53:53'),
(2, 'Surtihogar Norte', 'Carrera 15 # 85-20, Bogota', '601 234 5679', '2025-02-15', '2026-08-28 03:53:53'),
(3, 'Surtihogar Sur', 'Calle 48 Sur # 24-12, Bogota', '601 234 5680', '2025-02-15', '2026-08-28 03:53:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `transaction_order` varchar(50) DEFAULT NULL,
  `id_admin_order` int(11) DEFAULT 0,
  `id_client_order` int(11) DEFAULT 0,
  `subtotal_order` decimal(14,2) DEFAULT 0.00,
  `discount_order` decimal(14,2) DEFAULT 0.00,
  `extra_discount_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_order` decimal(14,2) DEFAULT 0.00,
  `total_order` decimal(14,2) DEFAULT 0.00,
  `method_order` varchar(30) DEFAULT NULL,
  `transfer_order` varchar(500) DEFAULT NULL,
  `cash_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `card_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `note_order` varchar(255) NOT NULL DEFAULT '',
  `status_order` varchar(30) DEFAULT NULL,
  `date_order` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_office_order` int(11) DEFAULT 0,
  `date_created_order` date DEFAULT NULL,
  `date_updated_order` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages`
--

CREATE TABLE `pages` (
  `id_page` int(11) NOT NULL,
  `title_page` varchar(150) DEFAULT NULL,
  `url_page` varchar(150) DEFAULT NULL,
  `icon_page` varchar(100) DEFAULT NULL,
  `type_page` varchar(30) DEFAULT NULL,
  `order_page` int(11) DEFAULT 1,
  `date_created_page` date DEFAULT NULL,
  `date_updated_page` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pages`
--

INSERT INTO `pages` (`id_page`, `title_page`, `url_page`, `icon_page`, `type_page`, `order_page`, `date_created_page`, `date_updated_page`) VALUES
(1, 'POSify', 'posify', 'bi bi-house-door-fill', 'modules', 1, '2025-02-14', '2025-02-14 07:06:16'),
(2, 'Admins', 'admins', 'bi bi-person-fill-gear', 'modules', 2, '2025-02-14', '2025-02-14 07:06:16'),
(3, 'Archivos', 'archivos', 'bi bi-file-earmark-image', 'custom', 4, '2025-02-14', '2025-02-15 00:58:28'),
(4, 'Sucursales', 'sucursales', 'bi bi-shop', 'modules', 3, '2025-02-15', '2025-02-15 00:58:28'),
(5, 'Clientes', 'clientes', 'bi bi-people', 'modules', 5, '2025-02-15', '2025-04-03 01:57:14'),
(6, 'Categorías', 'categorias', 'bi bi-card-list', 'modules', 6, '2025-02-16', '2025-04-03 01:57:14'),
(7, 'Productos', 'productos', 'bi bi-box', 'modules', 7, '2025-02-18', '2025-04-03 01:57:14'),
(8, 'Compras', 'compras', 'bi bi-basket-fill', 'modules', 8, '2025-02-20', '2025-04-03 01:57:14'),
(9, 'Órdenes', 'ordenes', 'bi bi-ticket-detailed', 'modules', 9, '2025-02-21', '2025-04-03 01:57:14'),
(10, 'Ventas', 'ventas', 'bi bi-cash-coin', 'modules', 10, '2025-02-21', '2025-04-03 01:57:14'),
(11, 'Caja', 'caja', 'fas fa-cash-register', 'modules', 11, '2025-02-22', '2025-04-03 01:57:14'),
(12, 'Gastos', 'gastos', 'fas fa-money-bill-wave', 'modules', 12, '2025-02-22', '2025-04-03 01:57:14'),
(13, 'Informes', 'informes', 'bi bi-file-earmark-bar-graph-fill', 'modules', 1000, '2025-04-03', '2025-04-03 19:58:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `title_product` varchar(500) DEFAULT NULL,
  `img_product` varchar(500) DEFAULT NULL,
  `id_category_product` int(11) DEFAULT 0,
  `sku_product` varchar(500) DEFAULT NULL,
  `unit_product` varchar(100) DEFAULT NULL,
  `tax_product` varchar(100) DEFAULT NULL,
  `rte_product` varchar(100) DEFAULT NULL,
  `discount_product` double DEFAULT 0,
  `status_product` int(11) DEFAULT 1,
  `date_created_product` date DEFAULT NULL,
  `date_updated_product` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id_product`, `title_product`, `img_product`, `id_category_product`, `sku_product`, `unit_product`, `tax_product`, `rte_product`, `discount_product`, `status_product`, `date_created_product`, `date_updated_product`) VALUES
(1, 'Gaseosa Naranjita 400 ml', '/views/assets/files/6a910930b36538.jpg', 1, '7702431000019', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(2, 'Gaseosa Naranjita 1.5 L', '/views/assets/files/6a910930c99348.jpg', 1, '7702431000026', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(3, 'Gaseosa Cola Andina 400 ml', '/views/assets/files/6a910930df1478.jpg', 1, '7702559000014', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(4, 'Agua Manantial 600 ml', '/views/assets/files/6a91092fe95f37.jpg', 1, '7702688000015', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(5, 'Jugo Frutal de mango 200 ml', '/views/assets/files/6a9109300a23c8.jpg', 1, '7703105000014', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(6, 'Té frío Brisa limón 400 ml', '/views/assets/files/6a910930435ef8.jpg', 1, '7703417000016', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(7, 'Papas Criollitas 105 g', '/views/assets/files/6a910930948538.jpg', 2, '7704022000019', 'unidad', 'IVA_19', 'NULL', 10, 1, '2026-08-27', '2026-08-28 04:28:38'),
(8, 'Platanitos Chiplat 90 g', '/views/assets/files/6a9109308d3dd8.jpg', 2, '7704519000010', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(9, 'Maní Manisol 60 g', '/views/assets/files/6a91093060d2e8.jpg', 2, '7704863000018', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(10, 'Chocolatina Cacaoteca 40 g', '/views/assets/files/6a91093054ddc8.jpg', 2, '7705204000018', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(11, 'Galletas Dulcita wafer 6 uds', '/views/assets/files/6a91093079fd38.jpg', 2, '7705671000016', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(12, 'Leche Vaquita entera 1 L', '/views/assets/files/6a91093072e3a8.jpg', 3, '7706130000011', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(13, 'Leche Vaquita deslactosada 1 L', '/views/assets/files/6a910930c2f6b8.jpg', 3, '7706130000028', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(14, 'Yogur Cremoso de fresa 200 g', '/views/assets/files/6a9109301a6838.jpg', 3, '7706547000017', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(15, 'Queso Campesino 250 g', '/views/assets/files/6a91092ff1b587.jpg', 3, '7706982000016', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(16, 'Mantequilla Doradita 250 g', '/views/assets/files/6a910930d7d878.jpg', 3, '7707315000017', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(17, 'Huevos Granja Dorada AA x 12', '/views/assets/files/6a910930d055c8.jpg', 3, '7707502000011', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(18, 'Arroz El Molino 500 g', '/views/assets/files/6a910930a31038.jpg', 4, '7707748000011', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(19, 'Arroz El Molino 3 kg', '/views/assets/files/6a910930335458.jpg', 4, '7707748000028', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(20, 'Espagueti Trigal 250 g', '/views/assets/files/6a910930aab718.jpg', 4, '7708106000018', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(21, 'Aceite Solar de girasol 1 L', '/views/assets/files/6a91092fdb9a57.jpg', 4, '7708542000016', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(22, 'Panela Dulcaña pulverizada 500 g', '/views/assets/files/6a9109309b7bf8.jpg', 4, '7708917000016', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(23, 'Azúcar Dulcaña refinada 1 kg', '/views/assets/files/6a9109304de188.jpg', 4, '7708917000023', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(24, 'Café Montecielo molido 250 g', '/views/assets/files/6a9109302d0e68.jpg', 4, '7709204000016', 'unidad', 'IVA_19', 'NULL', 15, 1, '2026-08-27', '2026-08-28 04:28:38'),
(25, 'Jabón Blanquito en barra 300 g', '/views/assets/files/6a9109303d0a38.jpg', 5, '7709631000016', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(26, 'Detergente Espuma en polvo 1 kg', '/views/assets/files/6a9109302272a8.jpg', 5, '7709875000018', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(27, 'Lavaplatos Espuma en crema 500 g', '/views/assets/files/6a910930118558.jpg', 5, '7709875000025', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(28, 'Papel higiénico Suavito x 4', '/views/assets/files/6a9109308520e8.jpg', 5, '7701043000011', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(29, 'Ponqué Chocopon de chocolate 65 g', '/views/assets/files/6a910930bb8b58.jpg', 6, '7701276000017', 'unidad', 'IVA_19', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38'),
(30, 'Pan Blandito tajado 450 g', '/views/assets/files/6a910930677ed8.jpg', 6, '7701508000013', 'unidad', 'IVA_0', 'NULL', 0, 1, '2026-08-27', '2026-08-28 04:28:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `purchases`
--

CREATE TABLE `purchases` (
  `id_purchase` int(11) NOT NULL,
  `supplier_purchase` varchar(500) DEFAULT NULL,
  `id_product_purchase` int(11) DEFAULT 0,
  `cost_purchase` decimal(14,2) DEFAULT 0.00,
  `utility_purchase` varchar(30) DEFAULT NULL,
  `price_purchase` decimal(14,2) DEFAULT 0.00,
  `qty_purchase` int(11) DEFAULT 0,
  `invest_purchase` decimal(14,2) DEFAULT 0.00,
  `contact_purchase` varchar(500) DEFAULT NULL,
  `id_office_purchase` int(11) DEFAULT 0,
  `date_created_purchase` date DEFAULT NULL,
  `date_updated_purchase` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `purchases`
--

INSERT INTO `purchases` (`id_purchase`, `supplier_purchase`, `id_product_purchase`, `cost_purchase`, `utility_purchase`, `price_purchase`, `qty_purchase`, `invest_purchase`, `contact_purchase`, `id_office_purchase`, `date_created_purchase`, `date_updated_purchase`) VALUES
(1, 'Distribuidora La Sabana', 1, 2520.00, '28', 3500.00, 96, 241920.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(2, 'Distribuidora La Sabana', 1, 2520.00, '28', 3500.00, 74, 186480.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(3, 'Distribuidora La Sabana', 1, 2520.00, '28', 3500.00, 58, 146160.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(4, 'Distribuidora La Sabana', 2, 4248.00, '28', 5900.00, 64, 271872.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(5, 'Distribuidora La Sabana', 2, 4248.00, '28', 5900.00, 48, 203904.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(6, 'Distribuidora La Sabana', 2, 4248.00, '28', 5900.00, 36, 152928.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(7, 'Distribuidora La Sabana', 3, 2520.00, '28', 3500.00, 88, 221760.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(8, 'Distribuidora La Sabana', 3, 2520.00, '28', 3500.00, 70, 176400.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(9, 'Distribuidora La Sabana', 3, 2520.00, '28', 3500.00, 52, 131040.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(10, 'Distribuidora La Sabana', 4, 1584.00, '28', 2200.00, 120, 190080.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(11, 'Distribuidora La Sabana', 4, 1584.00, '28', 2200.00, 95, 150480.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(12, 'Distribuidora La Sabana', 4, 1584.00, '28', 2200.00, 70, 110880.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(13, 'Distribuidora La Sabana', 5, 2016.00, '28', 2800.00, 72, 145152.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(14, 'Distribuidora La Sabana', 5, 2016.00, '28', 2800.00, 55, 110880.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(15, 'Distribuidora La Sabana', 5, 2016.00, '28', 2800.00, 41, 82656.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(16, 'Distribuidora La Sabana', 6, 2808.00, '28', 3900.00, 58, 162864.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(17, 'Distribuidora La Sabana', 6, 2808.00, '28', 3900.00, 44, 123552.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(18, 'Distribuidora La Sabana', 6, 2808.00, '28', 3900.00, 33, 92664.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(19, 'Distribuidora La Sabana', 7, 3456.00, '28', 4800.00, 84, 290304.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(20, 'Distribuidora La Sabana', 7, 3456.00, '28', 4800.00, 66, 228096.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(21, 'Distribuidora La Sabana', 7, 3456.00, '28', 4800.00, 49, 169344.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(22, 'Distribuidora La Sabana', 8, 3024.00, '28', 4200.00, 67, 202608.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(23, 'Distribuidora La Sabana', 8, 3024.00, '28', 4200.00, 52, 157248.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(24, 'Distribuidora La Sabana', 8, 3024.00, '28', 4200.00, 38, 114912.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(25, 'Distribuidora La Sabana', 9, 2088.00, '28', 2900.00, 93, 194184.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(26, 'Distribuidora La Sabana', 9, 2088.00, '28', 2900.00, 71, 148248.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(27, 'Distribuidora La Sabana', 9, 2088.00, '28', 2900.00, 54, 112752.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(28, 'Distribuidora La Sabana', 10, 1800.00, '28', 2500.00, 110, 198000.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(29, 'Distribuidora La Sabana', 10, 1800.00, '28', 2500.00, 87, 156600.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(30, 'Distribuidora La Sabana', 10, 1800.00, '28', 2500.00, 63, 113400.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(31, 'Distribuidora La Sabana', 11, 2592.00, '28', 3600.00, 76, 196992.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(32, 'Distribuidora La Sabana', 11, 2592.00, '28', 3600.00, 59, 152928.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(33, 'Distribuidora La Sabana', 11, 2592.00, '28', 3600.00, 44, 114048.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(34, 'Distribuidora La Sabana', 12, 3384.00, '28', 4700.00, 102, 345168.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(35, 'Distribuidora La Sabana', 12, 3384.00, '28', 4700.00, 80, 270720.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(36, 'Distribuidora La Sabana', 12, 3384.00, '28', 4700.00, 60, 203040.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(37, 'Distribuidora La Sabana', 13, 3888.00, '28', 5400.00, 68, 264384.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(38, 'Distribuidora La Sabana', 13, 3888.00, '28', 5400.00, 53, 206064.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(39, 'Distribuidora La Sabana', 13, 3888.00, '28', 5400.00, 39, 151632.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(40, 'Distribuidora La Sabana', 14, 2304.00, '28', 3200.00, 59, 135936.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(41, 'Distribuidora La Sabana', 14, 2304.00, '28', 3200.00, 45, 103680.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(42, 'Distribuidora La Sabana', 14, 2304.00, '28', 3200.00, 34, 78336.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(43, 'Distribuidora La Sabana', 15, 6408.00, '28', 8900.00, 42, 269136.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(44, 'Distribuidora La Sabana', 15, 6408.00, '28', 8900.00, 32, 205056.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(45, 'Distribuidora La Sabana', 15, 6408.00, '28', 8900.00, 24, 153792.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(46, 'Distribuidora La Sabana', 16, 5400.00, '28', 7500.00, 38, 205200.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(47, 'Distribuidora La Sabana', 16, 5400.00, '28', 7500.00, 29, 156600.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(48, 'Distribuidora La Sabana', 16, 5400.00, '28', 7500.00, 22, 118800.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(49, 'Distribuidora La Sabana', 17, 9000.00, '28', 12500.00, 54, 486000.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(50, 'Distribuidora La Sabana', 17, 9000.00, '28', 12500.00, 41, 369000.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(51, 'Distribuidora La Sabana', 17, 9000.00, '28', 12500.00, 31, 279000.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(52, 'Distribuidora La Sabana', 18, 2304.00, '28', 3200.00, 130, 299520.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(53, 'Distribuidora La Sabana', 18, 2304.00, '28', 3200.00, 101, 232704.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(54, 'Distribuidora La Sabana', 18, 2304.00, '28', 3200.00, 76, 175104.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(55, 'Distribuidora La Sabana', 19, 12168.00, '28', 16900.00, 47, 571896.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(56, 'Distribuidora La Sabana', 19, 12168.00, '28', 16900.00, 36, 438048.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(57, 'Distribuidora La Sabana', 19, 12168.00, '28', 16900.00, 27, 328536.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(58, 'Distribuidora La Sabana', 20, 2088.00, '28', 2900.00, 81, 169128.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(59, 'Distribuidora La Sabana', 20, 2088.00, '28', 2900.00, 63, 131544.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(60, 'Distribuidora La Sabana', 20, 2088.00, '28', 2900.00, 47, 98136.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(61, 'Distribuidora La Sabana', 21, 9000.00, '28', 12500.00, 45, 405000.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(62, 'Distribuidora La Sabana', 21, 9000.00, '28', 12500.00, 35, 315000.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(63, 'Distribuidora La Sabana', 21, 9000.00, '28', 12500.00, 26, 234000.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(64, 'Distribuidora La Sabana', 22, 3312.00, '28', 4600.00, 73, 241776.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(65, 'Distribuidora La Sabana', 22, 3312.00, '28', 4600.00, 57, 188784.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(66, 'Distribuidora La Sabana', 22, 3312.00, '28', 4600.00, 42, 139104.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(67, 'Distribuidora La Sabana', 23, 3744.00, '28', 5200.00, 91, 340704.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(68, 'Distribuidora La Sabana', 23, 3744.00, '28', 5200.00, 70, 262080.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(69, 'Distribuidora La Sabana', 23, 3744.00, '28', 5200.00, 53, 198432.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(70, 'Distribuidora La Sabana', 24, 8568.00, '28', 11900.00, 52, 445536.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(71, 'Distribuidora La Sabana', 24, 8568.00, '28', 11900.00, 40, 342720.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(72, 'Distribuidora La Sabana', 24, 8568.00, '28', 11900.00, 30, 257040.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(73, 'Distribuidora La Sabana', 25, 2808.00, '28', 3900.00, 86, 241488.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(74, 'Distribuidora La Sabana', 25, 2808.00, '28', 3900.00, 67, 188136.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(75, 'Distribuidora La Sabana', 25, 2808.00, '28', 3900.00, 50, 140400.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(76, 'Distribuidora La Sabana', 26, 7056.00, '28', 9800.00, 49, 345744.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(77, 'Distribuidora La Sabana', 26, 7056.00, '28', 9800.00, 38, 268128.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(78, 'Distribuidora La Sabana', 26, 7056.00, '28', 9800.00, 28, 197568.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(79, 'Distribuidora La Sabana', 27, 4608.00, '28', 6400.00, 63, 290304.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(80, 'Distribuidora La Sabana', 27, 4608.00, '28', 6400.00, 49, 225792.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(81, 'Distribuidora La Sabana', 27, 4608.00, '28', 6400.00, 36, 165888.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(82, 'Distribuidora La Sabana', 28, 6264.00, '28', 8700.00, 57, 357048.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(83, 'Distribuidora La Sabana', 28, 6264.00, '28', 8700.00, 44, 275616.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(84, 'Distribuidora La Sabana', 28, 6264.00, '28', 8700.00, 33, 206712.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(85, 'Distribuidora La Sabana', 29, 2016.00, '28', 2800.00, 98, 197568.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(86, 'Distribuidora La Sabana', 29, 2016.00, '28', 2800.00, 76, 153216.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(87, 'Distribuidora La Sabana', 29, 2016.00, '28', 2800.00, 57, 114912.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38'),
(88, 'Distribuidora La Sabana', 30, 4968.00, '28', 6900.00, 44, 218592.00, '601 456 7890', 1, '2026-08-27', '2026-08-28 09:28:38'),
(89, 'Distribuidora La Sabana', 30, 4968.00, '28', 6900.00, 34, 168912.00, '601 456 7890', 2, '2026-08-27', '2026-08-28 09:28:38'),
(90, 'Distribuidora La Sabana', 30, 4968.00, '28', 6900.00, 25, 124200.00, '601 456 7890', 3, '2026-08-27', '2026-08-28 09:28:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales`
--

CREATE TABLE `sales` (
  `id_sale` int(11) NOT NULL,
  `id_order_sale` int(11) DEFAULT 0,
  `id_product_sale` int(11) DEFAULT 0,
  `tax_type_sale` varchar(500) DEFAULT NULL,
  `tax_sale` double DEFAULT 0,
  `discount_sale` double DEFAULT 0,
  `qty_sale` int(11) DEFAULT 0,
  `subtotal_sale` decimal(14,2) DEFAULT 0.00,
  `status_sale` varchar(30) DEFAULT NULL,
  `id_admin_sale` int(11) DEFAULT 0,
  `id_client_sale` int(11) DEFAULT 0,
  `id_office_sale` int(11) DEFAULT 0,
  `date_created_sale` date DEFAULT NULL,
  `date_updated_sale` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stocks`
--

CREATE TABLE `stocks` (
  `id_stock` int(11) NOT NULL,
  `id_product_stock` int(11) DEFAULT 0,
  `id_office_stock` int(11) DEFAULT 0,
  `qty_stock` int(11) DEFAULT 0,
  `date_created_stock` date DEFAULT NULL,
  `date_updated_stock` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `stocks`
--

INSERT INTO `stocks` (`id_stock`, `id_product_stock`, `id_office_stock`, `qty_stock`, `date_created_stock`, `date_updated_stock`) VALUES
(1, 1, 1, 96, '2026-08-28', '2026-08-29 02:50:37'),
(2, 1, 2, 74, '2026-08-28', '2026-08-29 02:50:37'),
(3, 1, 3, 58, '2026-08-28', '2026-08-29 02:50:37'),
(4, 2, 1, 64, '2026-08-28', '2026-08-29 02:50:37'),
(5, 2, 2, 48, '2026-08-28', '2026-08-29 02:50:37'),
(6, 2, 3, 36, '2026-08-28', '2026-08-29 02:50:37'),
(7, 3, 1, 88, '2026-08-28', '2026-08-29 02:50:37'),
(8, 3, 2, 70, '2026-08-28', '2026-08-29 02:50:37'),
(9, 3, 3, 52, '2026-08-28', '2026-08-29 02:50:37'),
(10, 4, 1, 120, '2026-08-28', '2026-08-29 02:50:37'),
(11, 4, 2, 95, '2026-08-28', '2026-08-29 02:50:37'),
(12, 4, 3, 70, '2026-08-28', '2026-08-29 02:50:37'),
(13, 5, 1, 72, '2026-08-28', '2026-08-29 02:50:37'),
(14, 5, 2, 55, '2026-08-28', '2026-08-29 02:50:37'),
(15, 5, 3, 41, '2026-08-28', '2026-08-29 02:50:37'),
(16, 6, 1, 58, '2026-08-28', '2026-08-29 02:50:37'),
(17, 6, 2, 44, '2026-08-28', '2026-08-29 02:50:37'),
(18, 6, 3, 33, '2026-08-28', '2026-08-29 02:50:37'),
(19, 7, 1, 84, '2026-08-28', '2026-08-29 02:50:37'),
(20, 7, 2, 66, '2026-08-28', '2026-08-29 02:50:37'),
(21, 7, 3, 49, '2026-08-28', '2026-08-29 02:50:37'),
(22, 8, 1, 67, '2026-08-28', '2026-08-29 02:50:37'),
(23, 8, 2, 52, '2026-08-28', '2026-08-29 02:50:37'),
(24, 8, 3, 38, '2026-08-28', '2026-08-29 02:50:37'),
(25, 9, 1, 93, '2026-08-28', '2026-08-29 02:50:37'),
(26, 9, 2, 71, '2026-08-28', '2026-08-29 02:50:37'),
(27, 9, 3, 54, '2026-08-28', '2026-08-29 02:50:37'),
(28, 10, 1, 110, '2026-08-28', '2026-08-29 02:50:37'),
(29, 10, 2, 87, '2026-08-28', '2026-08-29 02:50:37'),
(30, 10, 3, 63, '2026-08-28', '2026-08-29 02:50:37'),
(31, 11, 1, 76, '2026-08-28', '2026-08-29 02:50:37'),
(32, 11, 2, 59, '2026-08-28', '2026-08-29 02:50:37'),
(33, 11, 3, 44, '2026-08-28', '2026-08-29 02:50:37'),
(34, 12, 1, 102, '2026-08-28', '2026-08-29 02:50:37'),
(35, 12, 2, 80, '2026-08-28', '2026-08-29 02:50:37'),
(36, 12, 3, 60, '2026-08-28', '2026-08-29 02:50:37'),
(37, 13, 1, 68, '2026-08-28', '2026-08-29 02:50:37'),
(38, 13, 2, 53, '2026-08-28', '2026-08-29 02:50:37'),
(39, 13, 3, 39, '2026-08-28', '2026-08-29 02:50:37'),
(40, 14, 1, 59, '2026-08-28', '2026-08-29 02:50:37'),
(41, 14, 2, 45, '2026-08-28', '2026-08-29 02:50:37'),
(42, 14, 3, 34, '2026-08-28', '2026-08-29 02:50:37'),
(43, 15, 1, 42, '2026-08-28', '2026-08-29 02:50:37'),
(44, 15, 2, 32, '2026-08-28', '2026-08-29 02:50:37'),
(45, 15, 3, 24, '2026-08-28', '2026-08-29 02:50:37'),
(46, 16, 1, 38, '2026-08-28', '2026-08-29 02:50:37'),
(47, 16, 2, 29, '2026-08-28', '2026-08-29 02:50:37'),
(48, 16, 3, 22, '2026-08-28', '2026-08-29 02:50:37'),
(49, 17, 1, 54, '2026-08-28', '2026-08-29 02:50:37'),
(50, 17, 2, 41, '2026-08-28', '2026-08-29 02:50:37'),
(51, 17, 3, 31, '2026-08-28', '2026-08-29 02:50:37'),
(52, 18, 1, 130, '2026-08-28', '2026-08-29 02:50:37'),
(53, 18, 2, 101, '2026-08-28', '2026-08-29 02:50:37'),
(54, 18, 3, 76, '2026-08-28', '2026-08-29 02:50:37'),
(55, 19, 1, 47, '2026-08-28', '2026-08-29 02:50:37'),
(56, 19, 2, 36, '2026-08-28', '2026-08-29 02:50:37'),
(57, 19, 3, 27, '2026-08-28', '2026-08-29 02:50:37'),
(58, 20, 1, 81, '2026-08-28', '2026-08-29 02:50:37'),
(59, 20, 2, 63, '2026-08-28', '2026-08-29 02:50:37'),
(60, 20, 3, 47, '2026-08-28', '2026-08-29 02:50:37'),
(61, 21, 1, 45, '2026-08-28', '2026-08-29 02:50:37'),
(62, 21, 2, 35, '2026-08-28', '2026-08-29 02:50:37'),
(63, 21, 3, 26, '2026-08-28', '2026-08-29 02:50:37'),
(64, 22, 1, 73, '2026-08-28', '2026-08-29 02:50:37'),
(65, 22, 2, 57, '2026-08-28', '2026-08-29 02:50:37'),
(66, 22, 3, 42, '2026-08-28', '2026-08-29 02:50:37'),
(67, 23, 1, 91, '2026-08-28', '2026-08-29 02:50:37'),
(68, 23, 2, 70, '2026-08-28', '2026-08-29 02:50:37'),
(69, 23, 3, 53, '2026-08-28', '2026-08-29 02:50:37'),
(70, 24, 1, 52, '2026-08-28', '2026-08-29 02:50:37'),
(71, 24, 2, 40, '2026-08-28', '2026-08-29 02:50:37'),
(72, 24, 3, 30, '2026-08-28', '2026-08-29 02:50:37'),
(73, 25, 1, 86, '2026-08-28', '2026-08-29 02:50:37'),
(74, 25, 2, 67, '2026-08-28', '2026-08-29 02:50:37'),
(75, 25, 3, 50, '2026-08-28', '2026-08-29 02:50:37'),
(76, 26, 1, 49, '2026-08-28', '2026-08-29 02:50:37'),
(77, 26, 2, 38, '2026-08-28', '2026-08-29 02:50:37'),
(78, 26, 3, 28, '2026-08-28', '2026-08-29 02:50:37'),
(79, 27, 1, 63, '2026-08-28', '2026-08-29 02:50:37'),
(80, 27, 2, 49, '2026-08-28', '2026-08-29 02:50:37'),
(81, 27, 3, 36, '2026-08-28', '2026-08-29 02:50:37'),
(82, 28, 1, 57, '2026-08-28', '2026-08-29 02:50:37'),
(83, 28, 2, 44, '2026-08-28', '2026-08-29 02:50:37'),
(84, 28, 3, 33, '2026-08-28', '2026-08-29 02:50:37'),
(85, 29, 1, 98, '2026-08-28', '2026-08-29 02:50:37'),
(86, 29, 2, 76, '2026-08-28', '2026-08-29 02:50:37'),
(87, 29, 3, 57, '2026-08-28', '2026-08-29 02:50:37'),
(88, 30, 1, 44, '2026-08-28', '2026-08-29 02:50:37'),
(89, 30, 2, 34, '2026-08-28', '2026-08-29 02:50:37'),
(90, 30, 3, 25, '2026-08-28', '2026-08-29 02:50:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id_movement` int(11) NOT NULL,
  `id_product_movement` int(11) DEFAULT 0,
  `id_office_movement` int(11) DEFAULT 0,
  `id_admin_movement` int(11) DEFAULT 0,
  `type_movement` mediumtext DEFAULT NULL COMMENT 'purchase, sale, return, adjustment, transfer',
  `qty_movement` int(11) DEFAULT 0 COMMENT 'signed: positive comes in, negative goes out',
  `reference_movement` int(11) DEFAULT 0 COMMENT 'id_order or id_purchase behind the movement',
  `note_movement` mediumtext DEFAULT NULL,
  `date_movement` datetime DEFAULT NULL COMMENT 'when it happened, never rewritten',
  `date_created_movement` date DEFAULT NULL,
  `date_updated_movement` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `idx_admin_email` (`email_admin`),
  ADD KEY `idx_admin_reset` (`reset_admin`);

--
-- Indices de la tabla `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id_bill`),
  ADD KEY `idx_bill_office_date` (`id_office_bill`,`date_created_bill`);

--
-- Indices de la tabla `cashs`
--
ALTER TABLE `cashs`
  ADD PRIMARY KEY (`id_cash`),
  ADD KEY `idx_cash_office_date` (`id_office_cash`,`date_created_cash`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_category`);

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id_client`),
  ADD KEY `idx_client_office` (`id_office_client`);

--
-- Indices de la tabla `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`id_column`),
  ADD KEY `idx_column_module` (`id_module_column`);

--
-- Indices de la tabla `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id_file`),
  ADD KEY `idx_file_folder` (`id_folder_file`);

--
-- Indices de la tabla `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id_folder`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id_attempt`),
  ADD UNIQUE KEY `uq_attempt_who` (`who_attempt`);

--
-- Indices de la tabla `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id_module`);

--
-- Indices de la tabla `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id_office`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `idx_order_date` (`date_created_order`,`id_office_order`),
  ADD KEY `idx_order_office_status` (`id_office_order`,`status_order`),
  ADD KEY `idx_order_transaction` (`transaction_order`),
  ADD KEY `fk_order_client` (`id_client_order`);

--
-- Indices de la tabla `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id_page`),
  ADD KEY `idx_page_url` (`url_page`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`),
  ADD KEY `idx_product_office` (`status_product`,`id_category_product`),
  ADD KEY `fk_product_category` (`id_category_product`);

--
-- Indices de la tabla `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id_purchase`),
  ADD KEY `idx_purchase_product` (`id_product_purchase`),
  ADD KEY `idx_purchase_office_date` (`id_office_purchase`,`date_created_purchase`);

--
-- Indices de la tabla `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id_sale`),
  ADD KEY `idx_sale_order` (`id_order_sale`),
  ADD KEY `idx_sale_office_date` (`id_office_sale`,`date_created_sale`),
  ADD KEY `fk_sale_product` (`id_product_sale`);

--
-- Indices de la tabla `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id_stock`),
  ADD UNIQUE KEY `uq_stock_product_office` (`id_product_stock`,`id_office_stock`),
  ADD KEY `fk_stock_office` (`id_office_stock`);

--
-- Indices de la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id_movement`),
  ADD KEY `idx_movement_product_office` (`id_product_movement`,`id_office_movement`,`date_movement`),
  ADD KEY `idx_movement_reference` (`reference_movement`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `bills`
--
ALTER TABLE `bills`
  MODIFY `id_bill` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cashs`
--
ALTER TABLE `cashs`
  MODIFY `id_cash` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `columns`
--
ALTER TABLE `columns`
  MODIFY `id_column` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de la tabla `files`
--
ALTER TABLE `files`
  MODIFY `id_file` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `folders`
--
ALTER TABLE `folders`
  MODIFY `id_folder` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id_attempt` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modules`
--
ALTER TABLE `modules`
  MODIFY `id_module` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `offices`
--
ALTER TABLE `offices`
  MODIFY `id_office` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pages`
--
ALTER TABLE `pages`
  MODIFY `id_page` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id_purchase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `id_sale` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id_stock` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id_movement` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_client` FOREIGN KEY (`id_client_order`) REFERENCES `clients` (`id_client`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`id_category_product`) REFERENCES `categories` (`id_category`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchase_product` FOREIGN KEY (`id_product_purchase`) REFERENCES `products` (`id_product`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sale_order` FOREIGN KEY (`id_order_sale`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_product` FOREIGN KEY (`id_product_sale`) REFERENCES `products` (`id_product`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `fk_stock_office` FOREIGN KEY (`id_office_stock`) REFERENCES `offices` (`id_office`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`id_product_stock`) REFERENCES `products` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_movement_product` FOREIGN KEY (`id_product_movement`) REFERENCES `products` (`id_product`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
