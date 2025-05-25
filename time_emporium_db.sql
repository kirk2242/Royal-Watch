-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 04:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `time_emporium_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `lastname`, `firstname`, `age`, `sex`, `contact_number`, `address`, `created_at`, `updated_at`) VALUES
(1, 3, 'labay', 'kirk lhoel', 20, 'Male', '09925728981', 'liloan', '2025-03-19 15:39:22', '2025-03-25 02:09:38'),
(2, 6, 'lopez', 'kayte', 22, 'Female', '1122314123', 'asdqwdasd', '2025-03-21 03:11:05', '2025-03-21 03:11:05'),
(3, 8, 'Valencia', 'Billy Eddie', 20, 'Male', '09634160877', 'Compostela, Cebu', '2025-03-21 03:52:16', '2025-03-21 03:52:16'),
(4, 9, 'way pako', 'angel', 20, 'Female', '911', 'langit', '2025-03-26 12:42:05', '2025-03-26 12:42:05'),
(5, 11, 'asdasd', 'asd', 12, 'Male', '1213', 'dasdasd', '2025-03-28 03:14:35', '2025-03-28 03:14:35'),
(6, 12, 'jake', 'rekce', 23, 'Male', 'asdasd', 'dasd', '2025-04-03 01:24:14', '2025-04-03 01:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `gender` enum('Men','Women') NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `barcode`, `name`, `price`, `stock`, `image`, `created_at`, `updated_at`, `category`, `brand`, `gender`, `description`) VALUES
(1, '01111115', 'Audemars Piguet Royal Oak Ref. 25572BA', 5000.00, 3, '../uploads/1744305834_67f7feaae4552.jpg', '2025-03-21 03:57:19', '2025-04-11 07:05:14', 'Analog', 'Audemars Piguet', 'Men', 'The ref. 25572BA “The Owl” is a relatively rare and unusual specimen that first appeared in the 1980s, and has since made a lasting impression on the watch industry.'),
(2, '01111112', 'Patek Philippe Nautilus', 5000.00, 4, '../uploads/1742993820_pp-blue.jpg', '2025-03-26 12:57:00', '2025-04-11 07:05:14', 'Analog', 'Patek Philippe', 'Women', 'The sunburst blue dial of the Nautilus Ref. 5711/1P includes a bright/dark gradation from the inside to the outside, complete with the signature horizontal embossing.'),
(4, '01222225', 'Xiaomi Poco Watch', 3000.00, 6, '../uploads/1744306886_67f802c6a30c7.jpg', '2025-03-28 02:38:01', '2025-04-11 06:18:10', 'Smart Watch', 'Xiaomi', 'Men', 'The large 1.6\" AMOLED high-resolution colour display with 2.5D curved glass and an ultra-slim bezel delivers a breathtaking visual experience that lets you see more with greater clarity.'),
(5, '01333331', 'Casio F19W-1', 3500.00, 8, '../uploads/1744308329_67f8086948cb8.jpg', '2025-03-28 02:38:28', '2025-04-11 04:56:35', 'Digital', 'Casio', 'Women', 'Designed by Ryūsuke Moriai as his first design for Casio, the case of the F-91W measures 37.5 by 34.5 by 8.5 millimetres (1.48 by 1.36 by 0.33 in).'),
(7, '01111116', 'Royal Oak Selfwinding Chronograph \"50th Anniversary\"', 4500.00, 7, '../uploads/1743129584_AP green.jpg', '2025-03-28 02:39:44', '2025-04-11 06:45:34', 'Analog', 'Audemars Piguet', 'Women', 'This 41 mm Chronograph, which benefits from the new Royal Oak design evolution, marries stainless steel with a khaki “Grande Tapisserie” dial.'),
(8, '01111111', '5935A - Complications', 5000.00, 0, '../uploads/1744351521_67f8b1211ee73.jpg', '2025-03-28 02:40:16', '2025-05-15 13:50:17', 'Digital', 'new brand', 'Men', 'As the first stainless steel version of a cult model, the new Ref. 5935A-001 self-winding World Time flyback chronograph stands out with its sporty vintage looks. updated.'),
(9, '01333332', 'Twin Sensory Digital Compass', 1400.00, 5, '../uploads/1744308406_67f808b6b78d9.jpeg', '2025-04-03 07:22:58', '2025-04-11 04:58:23', 'Digital', 'Casio', 'Men', 'Casio Men\'s Digital Compass Twin Sensor Sport Watch. This model packs twin sensor and temperature and direction readings into a 200M water-resistant design.'),
(36, '01111114', 'Rolex Datejust Turn-O-Graph', 4000.00, 8, '../uploads/1744252437_67f72e1584ff7.jpg', '2025-04-10 02:33:57', '2025-04-11 07:07:32', 'Analog', 'Rolex', 'Women', 'The Rolex Thunderbird, also known as the Datejust Turn-O-Graph, is a timepiece steeped in history and distinction.'),
(37, '1111113', 'Rolex GMT-Master II', 4500.00, 9, '../uploads/1744256425_67f73da9e57c5.jpg', '2025-04-10 03:40:25', '2025-04-11 03:32:15', 'Analog', 'Rolex', 'Men', 'The Rolex Batman, officially known as the Rolex GMT-Master II, is a highly sought-after luxury watch that combines functionality and style.'),
(38, '01222221', 'Xiaomi Redmi Watch 5 Active', 1500.00, 10, '../uploads/1744306982_67f80326d7aa6.jpg', '2025-04-10 17:43:02', '2025-04-11 04:58:42', 'Smart Watch', 'Xiaomi', 'Women', 'Adopting cutting-edge vacuum filling sealing technology to deliver an impressively slim 2mm quadrilateral bezel and a 2.07\" AMOLED display, the Redmi Watch 5 boasts exceptional visual performance.'),
(39, '01222222', 'Samsung Galaxy Fit3', 1250.00, 9, '../uploads/1744307050_67f8036ae08ce.jpg', '2025-04-10 17:44:10', '2025-04-11 04:57:21', 'Smart Watch', 'Samsung', 'Women', 'Galaxy Fit3 featuring a slim, light design with a 1.6\" display. Track 100+ workouts, sleep, daily activity and more for up to 13 days on a single charge.'),
(40, '01222223', 'Samsung Galaxy Watch 7', 1350.00, 9, '../uploads/1744307094_67f803961fc31.jpg', '2025-04-10 17:44:54', '2025-04-11 06:20:29', 'Smart Watch', 'Samsung', 'Men', 'Power through your day with the new 3nm processor. Find your way with Dual-Frequency GPS. Track heart rate, workouts and sleep with the advanced BioActive Sensor.'),
(41, '01222224', 'Apple Watch Hermes Ultra 2', 1550.00, 6, '../uploads/1744307173_67f803e5e35c2.png', '2025-04-10 17:46:13', '2025-04-11 06:15:54', 'Smart Watch', 'Apple', 'Men', 'Apple Watch Hermès Ultra case in titanium 49 mm & Single tour band in Bleu Nuit knitted nylon'),
(42, '01222226', 'Apple Watch Ultra milanese', 1550.00, 8, '../uploads/1744307413_67f804d577d1b.jpg', '2025-04-10 17:50:13', '2025-04-11 04:56:20', 'Smart Watch', 'Apple', 'Women', 'The ultimate sports and adventure watch. Now in black. The rugged 49mm aerospace-grade titanium case comes with built-in GPS + Cellular connectivity.'),
(43, '01333333', 'Timex T498519J expedition', 1350.00, 13, '../uploads/1744308527_67f8092f4c73a.jpg', '2025-04-10 18:08:47', '2025-05-15 13:52:04', 'Digital', 'Timex', 'Men', 'A sporty, durable watch, the Timex Men\'s T49851 Expedition Vibration Alarm Black Resin Strap Watch belongs to the great outdoors. The 43mm case and dial creates a fantastic appearance on this sleek, stylish watch.'),
(44, '01333334', 'Timex ladies expedition', 1325.00, 10, '../uploads/1744308578_67f80962dda75.jpg', '2025-04-10 18:09:38', '2025-05-15 13:52:04', 'Digital', 'Timex', 'Women', 'The T41181 features a stunning analogue cream face in a 26mm diameter 6mm thick case. The Timex T41181 features include indiglo backlight and push/pull Crown.'),
(45, '01333335', 'Seiko STP013 black', 1225.00, 8, '../uploads/1744308638_67f8099e09ffe.jpg', '2025-04-10 18:10:38', '2025-04-11 04:57:47', 'Digital', 'Seiko', 'Men', 'Stainless steel case with a black silicone rubber strap. Fixed red accented bezel. Digital dial. Hours, minutes, seconds, am/pm, day of the week, date and year.'),
(46, '01333336', 'Seiko SBPG001 Spirit', 1525.00, 6, '../uploads/1744308688_67f809d095002.jpg', '2025-04-10 18:11:28', '2025-04-11 04:57:38', 'Digital', 'Seiko', 'Women', 'This rare Seiko Spirit SBFG001 Solar Atomic digital watch that offer a Hardlex Hardened Crystal, Stainless Steel Band, Overcharge Protection function.');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `cashier_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit card','Gcash') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `cashier_id`, `user_id`, `customer_id`, `total`, `payment_amount`, `change_amount`, `payment_method`, `created_at`) VALUES
(14, NULL, 7, 3, 10000.00, 0.00, 0.00, 'cash', '2025-03-27 14:55:22'),
(15, NULL, 7, 3, 10000.00, 0.00, 0.00, 'cash', '2025-03-27 15:03:19'),
(16, NULL, 7, 3, 5000.00, 0.00, 0.00, 'cash', '2025-03-27 15:10:15'),
(18, NULL, 7, 3, 5000.00, 0.00, 0.00, 'cash', '2025-03-28 01:56:36'),
(19, NULL, 7, 3, 5000.00, 0.00, 0.00, 'cash', '2025-03-28 02:19:10'),
(37, NULL, 7, NULL, 5000.00, 0.00, 0.00, 'cash', '2025-04-06 09:23:40'),
(38, NULL, 3, NULL, 0.00, 0.00, 0.00, 'cash', '2025-04-09 15:37:06'),
(39, NULL, 3, NULL, 0.00, 0.00, 0.00, 'cash', '2025-04-09 15:37:41'),
(40, NULL, 3, NULL, 0.00, 0.00, 0.00, '', '2025-04-09 15:50:19'),
(41, NULL, 1, NULL, 0.00, 0.00, 0.00, 'cash', '2025-04-09 15:53:10'),
(42, NULL, 3, NULL, 5000.00, 0.00, 0.00, 'cash', '2025-04-09 16:08:06'),
(43, NULL, 3, NULL, 3000.00, 0.00, 0.00, 'cash', '2025-04-09 16:24:29'),
(44, NULL, 3, NULL, 3500.00, 0.00, 0.00, 'cash', '2025-04-09 16:41:54'),
(45, NULL, 3, NULL, 3500.00, 0.00, 0.00, 'cash', '2025-04-09 16:47:45'),
(46, NULL, 7, NULL, 4500.00, 0.00, 0.00, 'cash', '2025-04-10 03:29:20'),
(49, NULL, 7, NULL, 5000.00, 0.00, 0.00, 'cash', '2025-04-10 11:32:17'),
(50, NULL, 7, NULL, 7100.00, 0.00, 0.00, 'cash', '2025-04-11 02:43:52'),
(51, NULL, 7, NULL, 5000.00, 5000.00, 0.00, 'cash', '2025-04-11 03:28:21'),
(52, NULL, 7, NULL, 4500.00, 4500.00, 0.00, 'cash', '2025-04-11 03:32:15'),
(53, NULL, 3, NULL, 5000.00, 0.00, 0.00, 'cash', '2025-04-11 03:34:32'),
(54, NULL, 3, NULL, 3000.00, 3000.00, 0.00, 'cash', '2025-04-11 03:38:10'),
(55, NULL, 3, NULL, 5000.00, 5000.00, 0.00, 'cash', '2025-04-11 03:44:06'),
(56, NULL, 3, NULL, 4000.00, 5000.00, 1000.00, 'cash', '2025-04-11 03:44:42'),
(57, NULL, 7, NULL, 11550.00, 15000.00, 3450.00, 'cash', '2025-04-11 06:15:54'),
(58, NULL, 7, NULL, 3000.00, 5000.00, 2000.00, 'cash', '2025-04-11 06:18:10'),
(59, NULL, 7, NULL, 1350.00, 2000.00, 650.00, 'cash', '2025-04-11 06:20:28'),
(60, NULL, 7, NULL, 5000.00, 5000.00, 0.00, 'Gcash', '2025-04-11 06:21:48'),
(61, NULL, 3, NULL, 5000.00, 5000.00, 0.00, 'cash', '2025-04-11 06:25:29'),
(62, NULL, 3, NULL, 4500.00, 5000.00, 500.00, 'cash', '2025-04-11 06:45:34'),
(63, NULL, 7, NULL, 10000.00, 10000.00, 0.00, 'cash', '2025-04-11 07:05:14'),
(64, NULL, 3, NULL, 4000.00, 5000.00, 1000.00, 'cash', '2025-04-11 07:07:32'),
(65, NULL, 7, NULL, 5000.00, 10000.00, 5000.00, 'cash', '2025-05-15 13:50:17'),
(66, NULL, 3, NULL, 2675.00, 5000.00, 2325.00, 'cash', '2025-05-15 13:52:04');

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `barcode`, `quantity`, `price`, `product_id`, `category`) VALUES
(4, 14, '', 1, 5000.00, 2, NULL),
(5, 14, '', 1, 5000.00, 1, NULL),
(6, 15, '', 1, 5000.00, 1, NULL),
(7, 15, '', 1, 5000.00, 2, NULL),
(8, 16, '', 1, 5000.00, 1, NULL),
(9, 18, '', 1, 5000.00, 2, NULL),
(10, 19, '', 1, 5000.00, 2, NULL),
(11, 37, '', 1, 5000.00, 1, NULL),
(12, 38, '', 1, 5000.00, 1, NULL),
(15, 41, '', 1, 3000.00, 4, NULL),
(16, 42, '', 1, 5000.00, 1, NULL),
(17, 43, '', 1, 3000.00, 4, NULL),
(18, 44, '', 1, 3500.00, 5, NULL),
(19, 45, '', 1, 3500.00, 5, NULL),
(20, 46, '', 1, 4500.00, 7, NULL),
(21, 49, '', 1, 5000.00, 8, NULL),
(22, 50, '', 1, 4500.00, 7, NULL),
(23, 50, '', 1, 1250.00, 39, NULL),
(24, 50, '', 1, 1350.00, 43, NULL),
(25, 51, '', 1, 5000.00, 1, NULL),
(26, 52, '', 1, 4500.00, 37, NULL),
(27, 53, '', 1, 5000.00, 1, NULL),
(28, 54, '', 1, 3000.00, 4, NULL),
(29, 55, '', 1, 5000.00, 8, NULL),
(30, 56, '', 1, 4000.00, 36, NULL),
(31, 57, '', 1, 1550.00, 41, NULL),
(32, 57, '', 1, 5000.00, 8, NULL),
(33, 57, '', 1, 5000.00, 2, NULL),
(34, 58, '', 1, 3000.00, 4, NULL),
(35, 59, '', 1, 1350.00, 40, NULL),
(36, 60, '', 1, 5000.00, 2, NULL),
(37, 61, '', 1, 5000.00, 2, NULL),
(38, 62, '', 1, 4500.00, 7, NULL),
(39, 63, '', 1, 5000.00, 1, NULL),
(40, 63, '', 1, 5000.00, 2, NULL),
(41, 64, '', 1, 4000.00, 36, NULL),
(42, 65, '', 1, 5000.00, 8, NULL),
(43, 66, '', 1, 1350.00, 43, NULL),
(44, 66, '', 1, 1325.00, 44, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','cashier','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$YUlKMjfCEjsg34Tg5eMlm.vl.bBVuHunChVXNkTyDvTvcBRM7QHvy', 'superadmin', '2025-03-19 15:22:19', '2025-03-19 15:22:19'),
(2, 'admin', '$2y$10$FanxzOhvvK2OYou0KcOrwepmhgXcJ3J1IwA9N.TfFrIKgTjX84fOS', 'admin', '2025-03-19 15:23:47', '2025-03-19 15:23:47'),
(3, 'kirk123updated', '$2y$10$fsFKefrKYcGGe/NbC.h5F.D4Rm2kL9PNbSkttIZepd3P4RQTD3W1O', 'customer', '2025-03-19 15:39:22', '2025-04-11 06:24:45'),
(5, 'adds', '$2y$10$gmsygiWRyXQZDGIcC9J3JO/0H/0DHpFhEoxE3Zw7hdeXwXf/OVVte', 'admin', '2025-03-20 15:49:23', '2025-03-20 15:49:23'),
(6, 'kayte', '$2y$10$hnMuf7aSbZvn74U.tR/LVeBAQQ89DhKYd9VUZcJJWl3Xv9wGqi0SO', 'customer', '2025-03-21 03:11:05', '2025-03-21 03:11:05'),
(7, 'cashier', '$2y$10$A4kbUrXBXsTdgko9V9oJLONPiFSBQPjmv6VdDsAavKCahTYXsvGYG', 'cashier', '2025-03-21 03:37:00', '2025-03-21 03:37:00'),
(8, 'medley', '$2y$10$d64VWI9Hjn0dY15UrKzLCuP2dttXIy5YlV25DL8tmfHnJdMkIujse', 'customer', '2025-03-21 03:52:16', '2025-03-21 03:52:16'),
(9, 'angel', '$2y$10$m4WgKAgD1XINDWLlL3vGiOXoU42uUv9GHfGp3dsYAGSHLknvH4LK6', 'customer', '2025-03-26 12:42:05', '2025-03-26 12:42:05'),
(11, 'cutomer2', '$2y$10$v2WBYYsNzD4MvKkiq2Aa2ejMEEyZr0dtpGEIiHxSeKhQ./o3crGX.', 'customer', '2025-03-28 03:14:35', '2025-03-28 03:14:35'),
(12, 'rekce', '$2y$10$9ImaNmP11hRHN/v3ndbnIeyVStGJn8fqYWgaWNQDU16i3xEBQW7qG', 'customer', '2025-04-03 01:24:14', '2025-04-03 01:24:14'),
(13, 'admin1', '$2y$10$AgLJMvDDCh08DSOUS18GRuJMcMs5AC34N1YjOGv5Uas551aaAPC8K', 'admin', '2025-04-08 02:55:09', '2025-04-08 02:55:09'),
(14, 'angel123', '$2y$10$/84m3QBZy5Vf39U8vxCT.u5EEgXRRxjifn0qS4iTpGx6Q5I3m1rmO', 'admin', '2025-04-11 05:54:33', '2025-04-11 05:54:33'),
(15, 'kayte112', '$2y$10$wpPxMy46.SXZ4YtfvmbIv.g0rrYMHFdAxICDPew7kw7XagOgogera', 'admin', '2025-04-11 06:41:53', '2025-04-11 06:41:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `barcode` (`barcode`),
  ADD KEY `sales_items_ibfk_2` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
