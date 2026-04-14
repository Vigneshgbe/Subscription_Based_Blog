-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2026 at 07:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `subscription_blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:57:58'),
(2, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:58:04'),
(3, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:58:17'),
(4, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:58:29'),
(5, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:58:38'),
(6, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 04:58:45'),
(7, 2, 'user_registered', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 04:59:46'),
(8, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 04:59:56'),
(9, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 05:00:39'),
(10, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"padak.service@gmail.com\"}', '2026-03-26 05:00:48'),
(11, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"admin@blog.com\"}', '2026-03-26 05:01:36'),
(12, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 05:02:13'),
(13, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 06:31:00'),
(14, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 06:32:07'),
(15, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:05:13'),
(16, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:05:24'),
(17, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:08:58'),
(18, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:09:08'),
(19, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:23:19'),
(20, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 08:23:55'),
(21, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 09:04:09'),
(22, NULL, 'user_registered', 'user', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 09:04:37'),
(23, NULL, 'user_login', 'user', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 09:04:50'),
(24, NULL, 'user_logout', 'user', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 09:20:25'),
(25, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 09:20:33'),
(26, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:21:07'),
(27, NULL, 'user_login', 'user', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:21:20'),
(28, NULL, 'user_logout', 'user', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:29:22'),
(29, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:29:30'),
(30, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:30:24'),
(31, NULL, 'user_registered', 'user', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:30:44'),
(32, NULL, 'user_login', 'user', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:30:57'),
(33, NULL, 'user_logout', 'user', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:32:20'),
(34, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:32:28'),
(35, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 11:35:20'),
(36, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"padak.service@gmail.com\"}', '2026-03-26 12:00:26'),
(37, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-26 12:00:34'),
(38, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"padak.service@gmail.com\"}', '2026-03-27 05:54:37'),
(39, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-27 05:54:43'),
(40, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1EfNBKOvzZcslVS3X73jraWh7qaT7hXG8X6pKXEQWKEnyWZ0QXbLSatax\"}', '2026-03-27 06:34:57'),
(41, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-27 06:41:07'),
(42, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1HOA5Z6P9woqhHOuYmJI6Hyz7bE4Vbc9D7gEpCK4mxFF7xUOSSUyk1ILR\"}', '2026-03-27 06:41:20'),
(43, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-27 07:05:06'),
(44, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"session_id\":\"cs_test_a1nEZZLyGg5pikT4QQbpBaeBu0sbtT2S6DYMQe8gmB2Rz0bnFPgJmqdByT\"}', '2026-03-27 07:05:43'),
(45, 2, 'subscription_activated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"amount\":2999}', '2026-03-27 07:06:59'),
(46, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-27 15:14:37'),
(47, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-27 15:16:04'),
(48, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-27 15:16:15'),
(49, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-27 15:16:21'),
(50, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-27 15:16:29'),
(51, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"padak.service@gmail.com\"}', '2026-03-29 04:10:36'),
(52, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 04:10:43'),
(53, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 08:13:00'),
(54, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-29 08:13:15'),
(55, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-29 08:13:21'),
(56, NULL, 'failed_login', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"email\":\"a@gmail.com\"}', '2026-03-29 08:13:30'),
(57, 5, 'user_registered', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 08:40:34'),
(58, 5, 'user_login', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 08:40:54'),
(59, 5, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"session_id\":\"cs_test_a1AmhKpEdPwLcM8r95MC3zOPUvUOWv9yTfF4GVsUFtxfB1mEDUV50CTce5\"}', '2026-03-29 08:47:26'),
(60, 5, 'subscription_activated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"amount\":2999}', '2026-03-29 08:48:40'),
(61, 5, 'user_logout', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 08:49:15'),
(62, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 08:51:59'),
(63, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 09:10:12'),
(64, 5, 'user_login', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-29 09:10:23'),
(65, 5, 'user_login', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:18:38'),
(66, 5, 'user_logout', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:20:29'),
(67, 6, 'user_registered', 'user', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:20:58'),
(68, 6, 'user_login', 'user', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:21:15'),
(69, 6, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1bqx0223ZVGeLRLxRZpk58fdKPw7ScCtlMeJRxuSLIrGumJCqzxfLYr5Z\"}', '2026-03-31 15:25:28'),
(70, 6, 'subscription_activated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"amount\":299}', '2026-03-31 15:26:39'),
(71, 6, 'user_logout', 'user', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:27:38'),
(72, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:28:09'),
(73, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:55:41'),
(74, 5, 'user_login', 'user', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-03-31 15:55:57'),
(75, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-02 18:22:29'),
(76, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-02 18:41:21'),
(77, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:23:11'),
(78, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:56:46'),
(79, 6, 'user_login', 'user', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:56:59'),
(80, 6, 'user_logout', 'user', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:57:29'),
(81, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:57:37'),
(82, 2, 'user_logout', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:57:40'),
(83, 7, 'user_registered', 'user', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:58:11'),
(84, 7, 'user_login', 'user', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:58:23'),
(85, 7, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1JVMRORlYk8ZxeRJi8z9E86u2xkhgwKYFAtdh0Abblgj0Ar0mKeXL7Pfe\"}', '2026-04-03 03:58:45'),
(86, 7, 'user_login', 'user', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 03:59:53'),
(87, 7, 'user_logout', 'user', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 04:00:10'),
(88, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-03 04:00:17'),
(89, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-05 13:56:08'),
(90, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 06:25:44'),
(91, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 06:27:59'),
(92, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 07:07:37'),
(93, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 07:08:00'),
(94, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 07:08:22'),
(95, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 07:08:31'),
(96, 2, 'password_reset_requested', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 07:17:35'),
(97, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 08:47:22'),
(98, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a17jCyJ04uQMZfabNw9IVmhqF44VoVzbMAATLaSKHCSdj2nBPVsRt0XYqb\"}', '2026-04-13 09:44:53'),
(99, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 09:48:06'),
(100, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a15M2ODwYP3Jpa1tXonFBCgbrtvIQe4bewBTfGdbmcMmXTOu9j5xJckUFp\"}', '2026-04-13 09:48:16'),
(101, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1QeCksErCU8pJsVLOxzaEilA6KCGNQ321Ey6BihfHr7qxz7JxSQp5iwUN\"}', '2026-04-13 09:49:12'),
(102, 2, 'subscription_activated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"amount\":299}', '2026-04-13 09:51:48'),
(103, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1bIZVACy028GUvZ3W4geCQvNa88lClfc81qWYi6wZpToEeQq3RdfULcQ8\"}', '2026-04-13 09:52:41'),
(104, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1ZmalSMbrV2cmu4FtZRZTZTV9nImxJEwCU1MzyQ28h1yf5N8nUaiMACdQ\"}', '2026-04-13 10:01:54'),
(105, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1VlJQylhMAkJSj6yrp8qnHaQvWqC7AJJXx2F6COiTA0mmRQfThlBm875u\"}', '2026-04-13 10:03:08'),
(106, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1JRHZBJVAJEBAIhGoDAy2S9QgCfyJ9zkn9dwtNtNGgBqFrH6HqkVzrKKE\"}', '2026-04-13 10:03:57'),
(107, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1pXTNQQlZaKSbFm2WaOqLC38NaSre9bYKaM4ys2C9Zl3JeOfYPSe3oAAT\"}', '2026-04-13 10:05:44'),
(108, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1pUea6EK3zBpAZ34mnMQnUVLDVbQ8RMDtEDKEbsQcACPMrRC6bgOzkbXo\"}', '2026-04-13 10:06:22'),
(109, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1fatoDWuetuz5F5SZWaipOpqwOhgUMItyueDEDuuffnrs35ZHU6s2DvY1\"}', '2026-04-13 10:08:22'),
(110, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"session_id\":\"cs_test_a1DqocQixbZwkwJBkWKOnXeYbywseg2hyd2nSgq6ALh49z1l3ZHoDwQ4gV\"}', '2026-04-13 10:08:32'),
(111, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 10:09:26'),
(112, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1PacwcJO6RmaPeebplZQnBM15ybstrLCH0bIWw8QEegoCp03n5J71xKAo\"}', '2026-04-13 10:11:51'),
(113, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-13 10:12:10'),
(114, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1uYz4DMPP4ihjugGmcscbnnYeJYMqMEn4MuELXEpV2PL5d81nxBN76e9n\"}', '2026-04-13 10:14:03'),
(115, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"yearly\",\"session_id\":\"cs_test_a1OWls1zhjsnzpG8fWcIb10vU5Hqil4hGp8px4cp9PHGty2JolQVrbPLKs\"}', '2026-04-13 10:19:23'),
(116, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1CQ7k5hsu41iqp9EuO8Lfb2BPtc1n3n5IaK6oEjFUE1fVBzBcfO0TIS2K\"}', '2026-04-13 10:20:24'),
(117, 2, 'subscription_activated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"amount\":3}', '2026-04-13 10:20:54'),
(118, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-14 02:42:54'),
(119, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-14 04:35:25'),
(120, 2, 'checkout_initiated', 'subscription', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"plan_type\":\"monthly\",\"session_id\":\"cs_test_a1NnpyFUiuscLy07JjVPfVB8bGnpEMmU4VWhEIlB4l5JH6WFXd4VAEj9Vi\"}', '2026-04-14 04:35:55'),
(121, 2, 'user_login', 'user', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, '2026-04-14 04:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `is_premium`, `is_published`, `views`, `meta_title`, `meta_description`, `meta_keywords`, `published_at`, `created_at`, `updated_at`) VALUES
(7, 'When the 80/20 Rule Fails: The Downside of Being Effective', 'when-the-8020-rule-fails-the-downside-of-being-effective', 'James Clear writes about habits, decision making, and continuous improvement. He is the author of the #1 New York Times bestseller, Atomic Habits. The book has sold over 25 million copies worldwide and has been translated into more than 60 languages.', '<p>Audrey Hepburn was an icon.</p>\r\n<p>Rising to fame in the 1950s, she was one of the greatest actresses of her era. In 1953, Hepburn became the first actress to win an Academy Award, a Golden Globe Award, and a BAFTA Award for a single performance: her leading role in the romantic comedy<span>&nbsp;</span><em>Roman Holiday</em>.</p>\r\n<p>Even today, over half a century later, she remains one of just 15 people to earn an &ldquo;EGOT&rdquo; by winning all four major entertainment awards: Emmy, Grammy, Oscar, and Tony. By the 1960s, she was averaging more than one new film per year and, by everyone\'s estimation, she was on a trajectory to be a movie star for decades to come.</p>\r\n<p>But then something funny happened: she stopped acting.</p>\r\n<p>Despite being in her 30s and at the height of her popularity, Hepburn basically stopped appearing in films after 1967. She would perform in television shows or movies just five times during the rest of her life.</p>\r\n<p>Instead, she switched careers. She spent the next 25 years working tirelessly for UNICEF, the arm of the United Nations that provides food and healthcare to children in war-torn countries. She performed volunteer work throughout Africa, South America, and Asia.</p>\r\n<p>Hepburn\'s first act was on stage. Her next act was one of service. In December 1992, she was awarded the Presidential Medal of Freedom for her efforts, which is the highest civilian award of the United States.</p>\r\n<p>We will return to her story in a moment.</p>\r\n<figure id=\"attachment_32122\" aria-describedby=\"caption-attachment-32122\" class=\"wp-caption alignnone\"><img fetchpriority=\"high\" decoding=\"async\" class=\"size-full wp-image-32122\" src=\"https://jamesclear.com/wp-content/uploads/2018/10/Audrey-Hepburn-by-Bud-Fraker-1956.jpg\" alt=\"\" width=\"632\" height=\"759\">\r\n<figcaption id=\"caption-attachment-32122\" class=\"wp-caption-text\">Audrey Hepburn in 1956. Photo by Bud Fraker.</figcaption>\r\n</figure>\r\n<h2 id=\"title_0\">Efficient vs. Effective</h2>\r\n<p>You get one, precious life. How do you decide the best way to spend your time? Productivity gurus will often suggest that you focus on being effective rather than being efficient.</p>\r\n<p>Efficiency is about getting more things done. Effectiveness is about getting the<span>&nbsp;</span><em>right</em><span>&nbsp;</span>things done. Peter Drucker, the well-known management consultant, once encapsulated the idea by writing, &ldquo;There is nothing so useless as doing efficiently that which should not be done at all.&rdquo;</p>\r\n<p>In other words, making progress is not just about being productive. It\'s about being productive on the right things.</p>\r\n<p>But how do you decide what the &ldquo;right things&rdquo; are? One of the most trusted approaches is to use the Pareto Principle, which is more commonly known as the 80/20 Rule.</p>\r\n<p>The 80/20 Rule states that, in any particular domain, a small number of things account for the majority of the results. For example, 80 percent of the land in Italy is owned by 20 percent of the people. Or, 75 percent of NBA championships are won by 20 percent of the teams. The numbers don\'t have to add up to 100. The point is that the majority of the results are driven by a minority of causes.</p>\r\n<h2 id=\"title_1\">The Upside of the 80/20 Rule</h2>\r\n<p>When applied to your life and work, the 80/20 Rule can help you separate &ldquo;the vital few from the trivial many.&rdquo;<a href=\"https://jamesclear.com/the-downside-of-being-effective\" class=\"footnote-button\" name=\"note-1-31514\" data-footnote-number=\"1\" data-footnote-identifier=\"1\" alt=\"See Footnote 1\" rel=\"footnote\" data-footnote-content=\"&lt;p&gt;This phrase was coined by engineer and manufacturing consultant Joseph Juran.&lt;/p&gt;\"><span class=\"footnote-circle\" data-footnote-number=\"1\"></span><span class=\"footnote-circle\"></span><span class=\"footnote-circle\"></span></a></p>\r\n<p>For example, business owners may discover the majority of revenue comes from a handful of important clients. The 80/20 Rule would recommend that the most effective course of action would be to focus exclusively on serving these clients (and on finding others like them) and either stop serving others or let the majority of customers gradually fade away because they account for a small portion of the bottom line.</p>\r\n<p>This same strategy can be useful<span>&nbsp;</span><a href=\"https://jamesclear.com/inversion\">if you practice inversion</a><span>&nbsp;</span>and look at the sources of your problems. You may find that the majority of your complaints come from a handful of problem clients. The 80/20 Rule would suggest that you can clear out your backlog of customer service requests by firing these clients.</p>\r\n<p>The 80/20 Rule is like a form of judo for life and work. By finding precisely the right area to apply pressure, you can get more results with less effort. It\'s a great strategy, and I have used it many times.</p>\r\n<p>But there is a downside to this approach, as well, and it is often overlooked. To understand this pitfall, we return to Audrey Hepburn.</p>\r\n<h2 id=\"title_2\">The Downside of the 80/20 Rule</h2>\r\n<p>Imagine it is 1967. Audrey Hepburn is in the prime of her career and trying to decide how to spend her time.</p>\r\n<p>If she uses the 80/20 Rule as part of her decision-making process, she will discover a clear answer: do more romantic comedies.</p>\r\n<p>Many of Hepburn\'s best films were romantic comedies like<span>&nbsp;</span><em>Roman Holiday</em>,<span>&nbsp;</span><em>Sabrina</em>,<span>&nbsp;</span><em>Breakfast at Tiffany\'s</em>, and<span>&nbsp;</span><em>Charade</em>. She starred in these four films between 1953 and 1963; by 1967, she was due for another one. They attracted large audiences, earned her awards, and were an obvious path to greater fame and fortune. Romantic comedies were<span>&nbsp;</span><em>effective</em><span>&nbsp;</span>for Audrey Hepburn.</p>\r\n<p>In fact, even if we take into account her desire to help children through UNICEF, an 80/20 analysis might have revealed that starring in more romantic comedies was still the best option because she could have maximized her earning power and donated the additional earnings to UNICEF.</p>\r\n<p>Of course, that\'s all well and good if she wanted to continue acting. But she didn\'t want to be an actress. She wanted to serve. And no reasonable analysis of the highest and best use of her time in 1967 would have suggested that volunteering for UNICEF was the most effective use of her time.</p>\r\n<p>This is the downside of the 80/20 Rule: A new path will never look like the most effective option in the beginning.</p>\r\n<h2 id=\"title_3\">Optimizing for Your Past or Your Future</h2>\r\n<p>Here\'s another example:</p>\r\n<p>Jeff Bezos, the founder of Amazon, worked on Wall Street and climbed the corporate ladder to become senior vice-president of a hedge fund before leaving it all in 1994 to start the company.</p>\r\n<p>If Bezos had applied the 80/20 Rule in 1993 in an attempt to discover the most effective areas to focus on in his career, it is virtually impossible to imagine that founding an internet company would have been on the list. At that point in time, there is no doubt that the most effective path&mdash;whether measured by financial gain, social status, or otherwise&mdash;would have been the one where he continued his career in finance.</p>\r\n<p>The 80/20 Rule is calculated and determined by your<span>&nbsp;</span><em>recent</em><span>&nbsp;</span>effectiveness. Whatever seems like the &ldquo;highest value&rdquo; use of your time in any given moment will be dependent on your previous skills and current opportunities.</p>\r\n<p>The 80/20 Rule will help you find the useful things in your past and get more of them in the future. But if you don&rsquo;t want your future to be more of your past, then you need a different approach.</p>\r\n<p>The downside of being effective is that you often optimize for your past rather than for your future.</p>\r\n<h2 id=\"title_4\">Where to Go From Here</h2>\r\n<p>Here\'s the good news: given enough practice and enough time, the thing that previously seemed ineffective can become very effective. You get good at what you practice.</p>\r\n<p>When Audrey Hepburn dialed down her acting career in 1967, volunteering didn\'t seem nearly as effective. But three decades later, she received the Presidential Medal of Freedom&mdash;a remarkable feat she is unlikely to have accomplished by acting in more romantic comedies.</p>\r\n<p>The process of learning a new skill or starting a new company or taking on a new adventure of any sort will often appear to be an ineffective use of time at first. Compared to the other things you already know how to do, the new thing will seem like a waste of time. It will never win the 80/20 analysis.</p>\r\n<p>But that doesn\'t mean it\'s the wrong decision.</p>', 'https://jamesclear.com/wp-content/uploads/2018/10/Audrey-Hepburn-by-Bud-Fraker-1956.jpg', 2, 3, 1, 1, 4, 'When the 80/20 Rule Fails: The Downside of Being Effective', 'James Clear writes about habits, decision making, and continuous improvement. He is the author of the #1 New York Times bestseller, Atomic Habits. The book has sold over 25 million copies worldwide and has been translated into more than 60 languages.', 'Rule Fails, The Downside of Being Effective,', '2026-04-03 09:21:56', '2026-04-03 03:51:56', '2026-04-14 04:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Technology', 'technology', 'Latest tech news and tutorials', '2026-03-26 04:56:21'),
(2, 'Business', 'business', 'Business insights and strategies', '2026-03-26 04:56:21'),
(3, 'Lifestyle', 'lifestyle', 'Health, wellness, and lifestyle tips', '2026-03-26 04:56:21'),
(4, 'Finance', 'finance', 'Financial advice and investment tips', '2026-03-26 04:56:21'),
(5, 'Education', 'education', 'Learning resources and educational content', '2026-03-26 04:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Padak', 'padak.service@gmail.com', 'Subscription Help', 'Testing Contact support', 'replied', '2026-04-14 03:15:37', '2026-04-14 03:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `reading_history`
--

CREATE TABLE `reading_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(64) NOT NULL,
  `article_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading_history`
--

INSERT INTO `reading_history` (`id`, `user_id`, `session_id`, `article_id`, `ip_address`, `user_agent`, `read_at`) VALUES
(49, 2, '80578232f1ec978d8f689506a15758a1f05a44b996cec27d47db3f629c82e182', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-14 04:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `plan_type` enum('free','monthly','yearly') DEFAULT 'free',
  `status` enum('active','canceled','expired','past_due') DEFAULT 'active',
  `current_period_start` datetime DEFAULT NULL,
  `current_period_end` datetime DEFAULT NULL,
  `cancel_at_period_end` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `stripe_customer_id`, `stripe_subscription_id`, `plan_type`, `status`, `current_period_start`, `current_period_end`, `cancel_at_period_end`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, 'free', 'active', NULL, NULL, 0, '2026-03-26 04:59:46', '2026-03-26 04:59:46'),
(4, 2, NULL, 'sub_1TFUTsJQkm3o5433k0rmXMu7', 'yearly', 'canceled', NULL, NULL, 1, '2026-03-27 07:06:59', '2026-04-13 09:43:22'),
(5, 5, NULL, NULL, 'free', 'active', NULL, NULL, 0, '2026-03-29 08:40:34', '2026-03-29 08:40:34'),
(6, 5, NULL, 'sub_1TGF1MJQkm3o5433PQ6Sn1JI', 'yearly', 'active', NULL, NULL, 0, '2026-03-29 08:48:40', '2026-04-13 09:43:22'),
(7, 6, NULL, NULL, 'free', 'active', NULL, NULL, 0, '2026-03-31 15:20:58', '2026-03-31 15:20:58'),
(8, 6, NULL, 'sub_1TH4BbJQkm3o54336El9BGEP', 'monthly', 'canceled', NULL, NULL, 1, '2026-03-31 15:26:39', '2026-04-13 09:43:22'),
(9, 7, NULL, NULL, 'free', 'active', NULL, NULL, 0, '2026-04-03 03:58:11', '2026-04-03 03:58:11'),
(10, 2, NULL, 'sub_1TLh9gGOn7LmR4LcnTsuWNLF', 'monthly', 'canceled', NULL, NULL, 1, '2026-04-13 09:51:48', '2026-04-13 10:00:47'),
(11, 2, 'cus_UKMKHRXH9f6CmJ', 'sub_1TLhbqGOn7LmR4LcRs45Pgo6', 'monthly', 'canceled', NULL, NULL, 1, '2026-04-13 10:20:54', '2026-04-14 04:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_charge_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'INR',
  `status` enum('pending','succeeded','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `stripe_payment_intent_id`, `stripe_charge_id`, `amount`, `currency`, `status`, `payment_method`, `description`, `metadata`, `created_at`) VALUES
(1, 2, NULL, NULL, 3.00, 'USD', 'succeeded', 'card', 'Monthly subscription', NULL, '2026-04-13 10:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `created_at`, `updated_at`, `last_login`) VALUES
(2, 'padak.service@gmail.com', '$2y$10$AaM9IEp1US.87zv0xzRJXuHJy3MKv7Y7WRKD1UT4rbvcOozJk2iwq', 'Padak', 'admin', 1, 0, '46afe8583aa6d42747f4488ad7b1153c382eef57bffe2f59b1d1323a81b06708', '0125b5ad55ecf0f487dbb5a5580939c170fa105cf6bba4ba0b85924640d1686b', '2026-04-13 13:47:30', '2026-03-26 04:59:45', '2026-04-14 04:53:50', '2026-04-14 04:53:50'),
(5, 'a@gmail.com', '$2y$10$q.ZvqITZ/EUxxmXBg1Bm4OrIiUWN2IDHLBHMHhidAA0yL5/PI6at2', 'Padak', 'user', 1, 0, 'c5d99181ba7257c8c305790bdb8fc1cc646ca8fbc8c6b53c3e34674aa899a78e', NULL, NULL, '2026-03-29 08:40:34', '2026-03-31 15:55:57', '2026-03-31 15:55:57'),
(6, 'vicky@gmail.com', '$2y$10$YVY25TV/l/Wj6GHP.HygcOVJUiQnVQXxw55SBzahLAaEO6IwMtf3q', 'Vignesh G', 'user', 1, 0, '1e848cc39f34042848d61bb24554dcc2916b5a0ed9a4ca3f3975c2aff2da22e2', NULL, NULL, '2026-03-31 15:20:58', '2026-04-03 03:56:59', '2026-04-03 03:56:59'),
(7, 'b@gmail.com', '$2y$10$z717V1onrUU3rR8l935wS.SIYxJYaywqEdlMrpxqvnests5t2ppAq', 'Blog', 'user', 1, 0, 'f75f39cd6d72b28d8bc76ba40e17b4439c25f91edd700cef75cdadfaea2b6ff6', NULL, NULL, '2026-04-03 03:58:11', '2026-04-03 03:59:53', '2026-04-03 03:59:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_author_id` (`author_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_is_published` (`is_published`),
  ADD KEY `idx_published_at` (`published_at`);
ALTER TABLE `articles` ADD FULLTEXT KEY `idx_search` (`title`,`excerpt`,`content`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read` (`user_id`,`session_id`,`article_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_article_id` (`article_id`),
  ADD KEY `idx_read_at` (`read_at`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_stripe_customer_id` (`stripe_customer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reading_history`
--
ALTER TABLE `reading_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reading_history`
--
ALTER TABLE `reading_history`
  ADD CONSTRAINT `reading_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reading_history_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
