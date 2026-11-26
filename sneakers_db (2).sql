-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 04:42 AM
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
-- Database: `sneakers_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL,
  `action` varchar(80) NOT NULL,
  `details` varchar(255) DEFAULT '',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `user_name`, `role`, `action`, `details`, `created_at`) VALUES
(1, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0003', '2026-10-28 15:23:26'),
(2, NULL, 'Guest', 'guest', 'Registered account', 'bogart (shit@gmail.com) as customer', '2026-10-28 15:24:56'),
(3, 4, 'bogart', 'customer', 'Logged in', 'shit@gmail.com', '2026-10-28 15:25:16'),
(4, 4, 'bogart', 'customer', 'Placed order', '#0004 Nike Air Force 1 size 40 x2 = ₱9,600.00 via Maya', '2026-10-28 15:25:53'),
(5, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-10-28 15:26:05'),
(6, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Other Airforce size 45 +19', '2026-10-28 15:27:27'),
(7, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Other Airforce size 45 +19', '2026-10-28 15:33:55'),
(8, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Other Airforce size 45 +19', '2026-10-28 15:48:55'),
(9, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-10-28 15:53:32'),
(10, 2, 'Sneakers Room Admin', 'admin', 'Admin logged in', 'sneakersroom.admin@gmail.com', '2026-10-28 15:55:52'),
(11, 2, 'Sneakers Room Admin', 'admin', 'Admin logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 13:27:00'),
(12, 2, 'Sneakers Room Admin', 'admin', 'Admin logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 13:28:51'),
(13, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 14:01:51'),
(14, NULL, 'Guest', 'guest', 'Registered account', 'joshua123 (joshua@gmail.com) as customer', '2026-11-20 14:20:59'),
(15, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 14:27:23'),
(16, 2, 'Sneakers Room Admin', 'admin', 'Admin logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 14:42:28'),
(17, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 15:01:18'),
(18, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 15:02:19'),
(19, 5, 'joshua123', 'customer', 'Placed order', '#0005 Adidas Ultraboost size 43 x1 = ₱8,900.00 via GCash', '2026-11-20 15:09:42'),
(20, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 15:09:54'),
(21, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 15:13:23'),
(22, 5, 'joshua123', 'customer', 'Placed order', '#0006 Adidas Samba OG size 42 x2 = ₱10,400.00 via COD (J&T)', '2026-11-20 15:13:40'),
(23, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 15:13:51'),
(24, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0006', '2026-11-20 15:14:47'),
(25, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0005', '2026-11-20 15:14:47'),
(26, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0004', '2026-11-20 15:14:48'),
(27, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 15:26:48'),
(28, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 15:27:59'),
(29, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 15:28:13'),
(30, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 15:43:06'),
(31, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'OnCloud sapatos size 41', '2026-11-20 15:51:21'),
(32, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'Other Airforce size 45', '2026-11-20 15:51:43'),
(33, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', '#9', '2026-11-20 15:52:07'),
(34, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Ultraboost size 43 now qty 123, price ₱8,900.00', '2026-11-20 15:52:14'),
(35, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Samba OG size 42 now qty 34, price ₱5,200.00', '2026-11-20 15:52:19'),
(36, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Asics Gel-Kayano 14 size 41 now qty 314, price ₱8,200.00', '2026-11-20 15:52:27'),
(37, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Samba OG size 42 now qty 34, price ₱5,200.00', '2026-11-20 15:53:20'),
(38, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Adidas Samba OG size 42', '2026-11-20 15:53:37'),
(39, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Adidas Ultraboost size 43', '2026-11-20 15:53:50'),
(40, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Asics Gel-Kayano 14 size 41', '2026-11-20 15:53:57'),
(41, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Nike Air Force 1 size 40', '2026-11-20 15:54:05'),
(42, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'OnCloud Cloud 5 size 42', '2026-11-20 15:54:32'),
(43, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Nike Dunk Low size 41', '2026-11-20 15:54:42'),
(44, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Nike Air Force 1 size 42', '2026-11-20 15:54:50'),
(45, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Air Force 1 size 40 now qty 123, price ₱4,800.00', '2026-11-20 15:55:07'),
(46, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Air Force 1 size 42 now qty 1233, price ₱4,800.00', '2026-11-20 15:55:12'),
(47, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 15:55:19'),
(48, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 16:01:51'),
(49, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 16:01:56'),
(50, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 16:07:34'),
(51, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 16:08:12'),
(52, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Nike Dunk Low size 41 now qty 12334, price ₱5,500.00', '2026-11-20 16:08:16'),
(53, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Samba OG size 42 now qty 34, price ₱4,999.00', '2026-11-20 16:27:17'),
(54, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 16:31:09'),
(55, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:34:15'),
(56, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:35:43'),
(57, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 16:35:57'),
(58, 5, 'joshua123', 'customer', 'Placed order', '#0007 Nike Air Force 1 size 40 x4 = ₱19,200.00 via COD (J&T)', '2026-11-20 16:36:13'),
(59, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:36:18'),
(60, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0007 to In transit', '2026-11-20 16:36:48'),
(61, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 16:36:58'),
(62, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:37:05'),
(63, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-20 16:40:12'),
(64, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:42:51'),
(65, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'kike sapatos size 41 +6', '2026-11-20 16:46:33'),
(66, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kike sapatos size 41', '2026-11-20 16:47:21'),
(67, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:47:59'),
(68, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:48:19'),
(69, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'kike sapatos size 45 +65', '2026-11-20 16:49:09'),
(70, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-20 16:50:59'),
(71, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 15:20:35'),
(72, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 15:21:04'),
(73, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 15:22:17'),
(74, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 15:43:48'),
(75, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 15:52:08'),
(76, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'kine Airforce 2.0 - Grey sizes 36 +10, 37 +10, 38 +10, 39 +10, 40 +10, 41 +10, 42 +10, 45 +5, 46 +8', '2026-11-21 15:54:09'),
(77, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 15:54:51'),
(78, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 15:55:34'),
(79, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 16:11:23'),
(80, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 16:19:13'),
(81, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 16:32:00'),
(82, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 16:33:02'),
(83, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 36', '2026-11-21 16:33:17'),
(84, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 37', '2026-11-21 16:33:23'),
(85, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', '#15', '2026-11-21 16:33:26'),
(86, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 38', '2026-11-21 16:33:29'),
(87, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 39', '2026-11-21 16:33:44'),
(88, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 40', '2026-11-21 16:33:46'),
(89, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 41', '2026-11-21 16:33:48'),
(90, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 42', '2026-11-21 16:33:50'),
(91, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 45', '2026-11-21 16:33:52'),
(92, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kine Airforce 2.0 - Grey size 46', '2026-11-21 16:33:54'),
(93, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'kike sapatos size 45', '2026-11-21 16:34:01'),
(94, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Kine Wellie Revillame - White sizes 36 +12, 37 +13, 38 +40, 39 +1, 40 +1, 41 +23, 43 +4, 45 +32, 47 +34', '2026-11-21 16:36:00'),
(95, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 16:36:52'),
(96, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 16:37:12'),
(97, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 16:37:24'),
(98, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 16:39:27'),
(99, 5, 'joshua123', 'customer', 'Placed order', '#0008 Kine Wellie Revillame - White size 40 x1 = ₱2,999.00 via GCash', '2026-11-21 16:41:02'),
(100, 5, 'joshua123', 'customer', 'Placed order', '#0009 Kine Wellie Revillame - White size 39 x1 = ₱2,999.00 via GCash', '2026-11-21 16:41:10'),
(101, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 16:51:49'),
(102, 2, 'Sneakers Room Admin', 'admin', 'Restocked shoe', 'Adidas Ultraboost size 43 +23', '2026-11-21 17:18:25'),
(103, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Adidas Samba OG sizes 39 +5', '2026-11-21 17:29:58'),
(104, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Samba OG size 39 now qty 56, price ₱3,999.00', '2026-11-21 17:39:23'),
(105, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 23:06:41'),
(106, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-21 23:07:43'),
(107, 2, 'Sneakers Room Admin', 'admin', 'Restocked sold-out shoe', 'Kine Wellie Revillame size 39 +1', '2026-11-21 23:10:25'),
(108, 2, 'Sneakers Room Admin', 'admin', 'Restocked sold-out shoe', 'Kine Wellie Revillame size 40 +33', '2026-11-21 23:10:34'),
(109, 5, 'joshua123', 'customer', 'Logged in', 'joshua@gmail.com', '2026-11-21 23:48:19'),
(110, 5, 'joshua123', 'customer', 'Customer cancelled order', '#0008 Kine Wellie Revillame - White size 40 x1 - Changed my mind', '2026-11-22 00:41:41'),
(111, 4, 'bogart', 'customer', 'Logged in', 'shit@gmail.com', '2026-11-24 12:17:48'),
(112, 4, 'bogart', 'customer', 'Placed order', '#0010 Adidas Samba OG size 42 x2 = ₱9,998.00 via GCash', '2026-11-24 12:19:04'),
(113, 4, 'bogart', 'customer', 'Placed order', '#0011 Adidas Samba OG size 42 x2 = ₱9,998.00 via GCash', '2026-11-24 12:19:45'),
(114, 4, 'bogart', 'customer', 'Placed order', '#0012 Asics Gel-Kayano 14 size 41 x1 = ₱8,200.00 via GCash', '2026-11-24 12:20:25'),
(115, 4, 'bogart', 'customer', 'Customer cancelled order', '#0011 Adidas Samba OG size 42 x2 - Better price available', '2026-11-24 12:20:54'),
(116, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-24 12:21:03'),
(117, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0012 to Delivered', '2026-11-24 12:21:22'),
(118, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0012', '2026-11-24 12:21:24'),
(119, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0010', '2026-11-24 12:21:25'),
(120, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0009', '2026-11-24 12:21:26'),
(121, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0010 to Delivered', '2026-11-24 12:21:30'),
(122, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0009 to Delivered', '2026-11-24 12:21:34'),
(123, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0007', '2026-11-24 12:21:36'),
(124, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0007 to Delivered', '2026-11-24 12:21:39'),
(125, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0006 to Delivered', '2026-11-24 12:21:46'),
(126, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0004 to Delivered', '2026-11-24 12:21:50'),
(127, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0005 to Delivered', '2026-11-24 12:21:53'),
(128, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-24 12:22:16'),
(129, 4, 'bogart', 'customer', 'Logged in', 'shit@gmail.com', '2026-11-24 12:40:20'),
(130, 4, 'bogart', 'customer', 'Logged in', 'shit@gmail.com', '2026-11-26 10:19:31'),
(131, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-26 10:20:15'),
(132, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Adidas Samba OG sizes 39 +23', '2026-11-26 10:31:13'),
(133, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Adidas Samba OG sizes 43 +12', '2026-11-26 10:31:52'),
(134, NULL, 'Guest', 'guest', 'Registered account', 'joshua345 (joshua2@gmail.com) as customer', '2026-11-26 10:40:29'),
(135, 9, 'joshua345', 'customer', 'Logged in', 'joshua2@gmail.com', '2026-11-26 10:40:49'),
(136, 9, 'joshua345', 'customer', 'Placed order', '#0013 Kine Wellie Revillame - White size 41 x3 = ₱8,997.00 via COD (J&T)', '2026-11-26 10:41:35'),
(137, 9, 'joshua345', 'customer', 'Placed order', '#0014 Asics Gel-Kayano 14 size 41 x2 = ₱16,400.00 via GCash', '2026-11-26 10:42:22'),
(138, 9, 'joshua345', 'customer', 'Customer cancelled order', '#0013 Kine Wellie Revillame - White size 41 x3 - Better price available', '2026-11-26 10:42:53'),
(139, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-26 10:43:34'),
(140, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'nike Airforce - White sizes 36 +5, 37 +4, 39 +6, 40 +34, 41 +35, 42 +5, 43 +45, 44 +55, 45 +34', '2026-11-26 10:45:50'),
(141, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Adidas Samba OG sizes 45 +10', '2026-11-26 10:46:42'),
(142, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Samba OG size 45 now qty 20, price ₱2,599.00', '2026-11-26 10:47:07'),
(143, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'Adidas Samba OG size 39', '2026-11-26 10:47:20'),
(144, 2, 'Sneakers Room Admin', 'admin', 'Recorded online order', '#0015 Kine Wellie Revillame - White size 47 x2 = ₱5,998.00 via GCash', '2026-11-26 10:48:37'),
(145, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0015 to Delivered', '2026-11-26 10:49:31'),
(146, 2, 'Sneakers Room Admin', 'admin', 'Marked order paid', '#0015', '2026-11-26 10:49:33'),
(147, 2, 'Sneakers Room Admin', 'admin', 'Added admin account', 'admin2@gmail.com', '2026-11-26 10:51:11'),
(148, 10, 'admin123', 'admin', 'Logged in', 'admin2@gmail.com', '2026-11-26 10:51:31'),
(149, NULL, 'Guest', 'guest', 'Registered account', 'joshua (joshua12@gmail.com) as customer', '2026-11-26 11:05:50'),
(150, 11, 'joshua', 'customer', 'Logged in', 'joshua12@gmail.com', '2026-11-26 11:06:08'),
(151, 11, 'joshua', 'customer', 'Placed order', '#0016 nike Airforce - White size 42 x3 = ₱8,997.00 via COD (J&T)', '2026-11-26 11:07:06'),
(152, 11, 'joshua', 'customer', 'Placed order', '#0017 Kine Wellie Revillame - White size 41 x1 = ₱2,999.00 via GCash', '2026-11-26 11:08:19'),
(153, 11, 'joshua', 'customer', 'Customer cancelled order', '#0017 Kine Wellie Revillame - White size 41 x1 - High delivery costs: darqwcvwe', '2026-11-26 11:08:40'),
(154, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-26 11:09:17'),
(155, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Asics sapatos - White sizes 36 +23, 37 +34, 39 +234, 40 +234, 42 +234, 43 +234, 44 +13, 46 +123, 47 +32', '2026-11-26 11:12:14'),
(156, 2, 'Sneakers Room Admin', 'admin', 'Added/restocked stock', 'Adidas Ultraboost sizes 42 +34', '2026-11-26 11:12:53'),
(157, 2, 'Sneakers Room Admin', 'admin', 'Edited stock', 'Adidas Ultraboost size 43 now qty 28, price ₱6,999.00', '2026-11-26 11:13:25'),
(158, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe', 'Adidas Ultraboost size 42', '2026-11-26 11:13:38'),
(159, 2, 'Sneakers Room Admin', 'admin', 'Removed shoe (all sizes)', 'Asics sapatos - White', '2026-11-26 11:13:54'),
(160, 2, 'Sneakers Room Admin', 'admin', 'Updated shoe photo', 'Kine Wellie Revillame - White size 36', '2026-11-26 11:14:19'),
(161, 2, 'Sneakers Room Admin', 'admin', 'Recorded walk-in order', '#0018 Adidas Ultraboost size 43 x5 = ₱34,995.00 via Maya', '2026-11-26 11:15:11'),
(162, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0016 to At J&T hub', '2026-11-26 11:16:35'),
(163, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0016 to In transit', '2026-11-26 11:16:40'),
(164, 2, 'Sneakers Room Admin', 'admin', 'Updated delivery', '#0016 to Delivered', '2026-11-26 11:16:44'),
(165, 2, 'Sneakers Room Admin', 'admin', 'Added admin account', 'joshuaadmin@gmail.com', '2026-11-26 11:19:12'),
(166, 12, 'joshuaadmin', 'admin', 'Logged in', 'joshuaadmin@gmail.com', '2026-11-26 11:19:32'),
(167, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-26 11:29:44'),
(168, 2, 'Sneakers Room Admin', 'admin', 'Logged in', 'sneakersroom.admin@gmail.com', '2026-11-26 11:30:17'),
(169, 2, 'Sneakers Room Admin', 'admin', 'Removed admin account', 'joshuaadmin@gmail.com', '2026-11-26 11:30:24'),
(170, 2, 'Sneakers Room Admin', 'admin', 'Removed admin account', 'admin2@gmail.com', '2026-11-26 11:30:29'),
(171, 2, 'Sneakers Room Admin', 'admin', 'Added admin account', 'sneakersroom.admin2@gmail.com', '2026-11-26 11:31:09'),
(172, NULL, 'Guest', 'guest', 'Registered account', 'customer (customer00@gmail.com) as customer', '2026-11-26 11:32:03');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `qty`) VALUES
(1, 4, 32, 1),
(3, 11, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `address` varchar(200) DEFAULT '',
  `email` varchar(100) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `mobile`, `address`, `email`) VALUES
(1, 'FQGEWR', 'WERWERERT3R', '2R3EFWDF', '2REFWEWF@gmail.com'),
(2, 'JOSHUA', '09456453464', 'saa,inabanga,bohol', 'jjoshua@gmail.com'),
(3, 'JOSHUAqw', '09456453464', 'saa,inabanga,bohol', 'jjoshua@gmail.com'),
(4, 'bogart', '09111111111', 'cogon,inabanga,bohol', 'shit@gmail.com'),
(5, 'joshua123', '091213456789', 'saa,inabanga,bohol', 'joshua@gmail.com'),
(6, 'joshua123', '091213456789', 'saa,inabanga,bohol', 'joshua@gmail.com'),
(7, 'joshua123', '091213456789', 'saa,inabanga,bohol', 'joshua@gmail.com'),
(8, 'joshua123', '091213456789', 'saa,inabanga,bohol', 'joshua@gmail.com'),
(9, 'joshua123', '091213456789', 'saa,inabanga,bohol', 'joshua@gmail.com'),
(10, 'bogart', '09111111111', 'cogon,inabanga,bohol', 'shit@gmail.com'),
(11, 'bogart', '09111111111', 'cogon,inabanga,bohol', 'shit@gmail.com'),
(12, 'bogart', '09111111111', 'cogon,inabanga,bohol', 'shit@gmail.com'),
(13, 'joshua345', '09111111111', 'saa,inabanga,bohol', 'joshua2@gmail.com'),
(14, 'joshua345', '09111111111', 'saa,inabanga,bohol', 'joshua2@gmail.com'),
(15, 'JOSHUA', '09456453464', 'saa,inabanga,bohol', 'joshua2@gmail.com'),
(16, 'joshua', '09111111111', 'saa,inabanga,bohol', 'joshua12@gmail.com'),
(17, 'joshua', '09111111111', 'saa,inabanga,bohol', 'joshua12@gmail.com'),
(18, 'JOSHUA', '09111111111', 'saa,inabanga,bohol', 'joshua@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `channel` enum('Walk-in','Online') NOT NULL,
  `payment_method` enum('Cash','GCash','Maya','COD (J&T)') NOT NULL,
  `payment_status` enum('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  `delivery_status` enum('Released','Preparing','At J&T hub','In transit','Delivered') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `cancel_seen` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `product_id`, `qty`, `total`, `channel`, `payment_method`, `payment_status`, `delivery_status`, `created_at`, `user_id`, `cancelled_at`, `cancel_reason`, `cancel_seen`) VALUES
(1, 1, 7, 1, 8200.00, 'Online', 'COD (J&T)', 'Paid', 'Delivered', '2026-10-27 16:25:01', NULL, NULL, NULL, 1),
(2, 2, 2, 1, 4800.00, 'Online', 'Cash', 'Paid', 'Delivered', '2026-10-27 16:26:42', NULL, NULL, NULL, 1),
(3, 3, 4, 1, 5200.00, 'Online', 'Maya', 'Paid', 'Delivered', '2026-10-27 16:27:04', NULL, NULL, NULL, 1),
(4, 4, 1, 2, 9600.00, 'Online', 'Maya', 'Paid', 'Delivered', '2026-10-28 15:25:53', 4, NULL, NULL, 1),
(5, 5, 5, 1, 8900.00, 'Online', 'GCash', 'Paid', 'Delivered', '2026-11-20 15:09:42', 5, NULL, NULL, 1),
(6, 6, 4, 2, 10400.00, 'Online', 'COD (J&T)', 'Paid', 'Delivered', '2026-11-20 15:13:40', 5, NULL, NULL, 1),
(7, 7, 1, 4, 19200.00, 'Online', 'COD (J&T)', 'Paid', 'Delivered', '2026-11-20 16:36:13', 5, NULL, NULL, 1),
(8, 8, 27, 1, 2999.00, 'Online', 'GCash', 'Unpaid', 'Preparing', '2026-11-21 16:41:02', 5, '2026-11-22 00:41:41', 'Changed my mind', 1),
(9, 9, 26, 1, 2999.00, 'Online', 'GCash', 'Paid', 'Delivered', '2026-11-21 16:41:10', 5, NULL, NULL, 1),
(10, 10, 4, 2, 9998.00, 'Online', 'GCash', 'Paid', 'Delivered', '2026-11-24 12:19:04', 4, NULL, NULL, 1),
(11, 11, 4, 2, 9998.00, 'Online', 'GCash', 'Unpaid', 'Preparing', '2026-11-24 12:19:45', 4, '2026-11-24 12:20:54', 'Better price available', 1),
(12, 12, 7, 1, 8200.00, 'Online', 'GCash', 'Paid', 'Delivered', '2026-11-24 12:20:25', 4, NULL, NULL, 1),
(13, 13, 28, 3, 8997.00, 'Online', 'COD (J&T)', 'Unpaid', 'Preparing', '2026-11-26 10:41:35', 9, '2026-11-26 10:42:53', 'Better price available', 1),
(14, 14, 7, 2, 16400.00, 'Online', 'GCash', 'Unpaid', 'Preparing', '2026-11-26 10:42:22', 9, NULL, NULL, 1),
(15, 15, 31, 2, 5998.00, 'Online', 'GCash', 'Paid', 'Delivered', '2026-11-26 10:48:37', NULL, NULL, NULL, 1),
(16, 16, 40, 3, 8997.00, 'Online', 'COD (J&T)', 'Paid', 'Delivered', '2026-11-26 11:07:06', 11, NULL, NULL, 1),
(17, 17, 28, 1, 2999.00, 'Online', 'GCash', 'Unpaid', 'Preparing', '2026-11-26 11:08:19', 11, '2026-11-26 11:08:40', 'High delivery costs: darqwcvwe', 1),
(18, 18, 5, 5, 34995.00, 'Walk-in', 'Maya', 'Unpaid', 'Released', '2026-11-26 11:15:11', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `brand` varchar(40) NOT NULL,
  `model` varchar(80) NOT NULL,
  `color` varchar(40) NOT NULL DEFAULT '',
  `size` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand`, `model`, `color`, `size`, `price`, `qty`, `image`) VALUES
(1, 'Nike', 'Air Force 1', '', '40', 4800.00, 119, '355f5fb836a426f4.webp'),
(2, 'Nike', 'Air Force 1', '', '42', 4800.00, 1233, '7d86319fee74c5f3.webp'),
(3, 'Nike', 'Dunk Low', '', '41', 5500.00, 12334, '1d9e090b8c676440.webp'),
(4, 'Adidas', 'Samba OG', '', '42', 4999.00, 32, 'e2f47c9514adfe6e.jpg'),
(5, 'Adidas', 'Ultraboost', '', '43', 6999.00, 23, '14ac8458ea727cab.webp'),
(6, 'OnCloud', 'Cloud 5', '', '42', 9500.00, 2, '7ce060b584cc4d8a.webp'),
(7, 'Asics', 'Gel-Kayano 14', '', '41', 8200.00, 311, '0fc6ac2d10774e8f.webp'),
(23, 'Kine', 'Wellie Revillame', 'White', '36', 2999.00, 12, '402da4c04cb3e8ad.webp'),
(24, 'Kine', 'Wellie Revillame', 'White', '37', 2999.00, 13, '402da4c04cb3e8ad.webp'),
(25, 'Kine', 'Wellie Revillame', 'White', '38', 2999.00, 40, '402da4c04cb3e8ad.webp'),
(26, 'Kine', 'Wellie Revillame', 'White', '39', 2999.00, 1, '402da4c04cb3e8ad.webp'),
(27, 'Kine', 'Wellie Revillame', 'White', '40', 2999.00, 34, '402da4c04cb3e8ad.webp'),
(28, 'Kine', 'Wellie Revillame', 'White', '41', 2999.00, 23, '402da4c04cb3e8ad.webp'),
(29, 'Kine', 'Wellie Revillame', 'White', '43', 2999.00, 4, '402da4c04cb3e8ad.webp'),
(30, 'Kine', 'Wellie Revillame', 'White', '45', 2999.00, 32, '402da4c04cb3e8ad.webp'),
(31, 'Kine', 'Wellie Revillame', 'White', '47', 2999.00, 32, '402da4c04cb3e8ad.webp'),
(34, 'Adidas', 'Samba OG', '', '43', 3999.00, 12, 'e2f47c9514adfe6e.jpg'),
(35, 'nike', 'Airforce', 'White', '36', 2999.00, 5, 'ca521d15d54cbecd.webp'),
(36, 'nike', 'Airforce', 'White', '37', 2999.00, 4, 'ca521d15d54cbecd.webp'),
(37, 'nike', 'Airforce', 'White', '39', 2999.00, 6, 'ca521d15d54cbecd.webp'),
(38, 'nike', 'Airforce', 'White', '40', 2999.00, 34, 'ca521d15d54cbecd.webp'),
(39, 'nike', 'Airforce', 'White', '41', 2999.00, 35, 'ca521d15d54cbecd.webp'),
(40, 'nike', 'Airforce', 'White', '42', 2999.00, 2, 'ca521d15d54cbecd.webp'),
(41, 'nike', 'Airforce', 'White', '43', 2999.00, 45, 'ca521d15d54cbecd.webp'),
(42, 'nike', 'Airforce', 'White', '44', 2999.00, 55, 'ca521d15d54cbecd.webp'),
(43, 'nike', 'Airforce', 'White', '45', 2999.00, 34, 'ca521d15d54cbecd.webp'),
(44, 'Adidas', 'Samba OG', '', '45', 2599.00, 20, 'e2f47c9514adfe6e.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `address` varchar(200) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `address`, `password_hash`, `role`) VALUES
(1, 'JOSHUA', '2refwewf@gmail.com', 'WERWERERT3R', '2R3EFWDF', '$2y$10$1blOq9mxgDyItCXza3yGM.GjK1AnH.46QcHGAu.f38GmL00xpx/h2', 'customer'),
(2, 'Sneakers Room Admin', 'sneakersroom.admin@gmail.com', '09077768906', 'poblacion', '$2y$10$/GS03KeyV.We3TvbcIhbYuZfdOhbbA92V0mT1uvnfKGWvQCnOeN0a', 'admin'),
(4, 'bogart', 'shit@gmail.com', '09111111111', 'cogon,inabanga,bohol', '$2y$10$7tRVGxIu0vhKe5H7g1uqKOW9PmraZypGOy6JjUUzgA93od3e.9lgK', 'customer'),
(5, 'joshua123', 'joshua@gmail.com', '091213456789', 'saa,inabanga,bohol', '$2y$10$ewM9I5fMBtOIg.k42kTIX.gY2DHwCQYio2lpGMonGD/ztggQeAoZC', 'customer'),
(9, 'joshua345', 'joshua2@gmail.com', '09111111111', 'saa,inabanga,bohol', '$2y$10$dMYGZA6U.7syBHcJCEcPwu5Gxwdbb7PfMsL6oZwgJwqlcaQvAjX.W', 'customer'),
(11, 'joshua', 'joshua12@gmail.com', '09111111111', 'saa,inabanga,bohol', '$2y$10$b2f8mOYNYU74wCjoU.lDSOR3kaE7P.kJ98dam/gWCbwzesTtWNaGe', 'customer'),
(13, 'admin', 'sneakersroom.admin2@gmail.com', '', '', '$2y$10$gScToD3q5UjjjpPCmawRBeeyxelOhMCNinkG0vPK7UCRMUWAHRQnm', 'admin'),
(14, 'customer', 'customer00@gmail.com', '091213456789', 'cogon,inabanga,bohol', '$2y$10$bmkP0lNPIJV7kyBruCxOJOuhxps1k6w56qaCBppMAuEmP.9CR/Ixm', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart` (`user_id`,`product_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_variant` (`brand`,`model`,`color`,`size`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
