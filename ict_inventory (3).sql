-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 29, 2026 at 07:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ict_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `user_role` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `duration` float DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_name`, `user_email`, `user_role`, `ip_address`, `user_agent`, `url`, `method`, `action`, `module`, `description`, `old_data`, `new_data`, `status`, `duration`, `performed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Super Admin', 'admin@example.com', 'super_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/admin/settings/general', 'PUT', 'updated', 'settings', 'General settings updated', NULL, NULL, 'success', NULL, '2026-08-06 11:41:54', '2026-08-06 11:41:54', '2026-08-06 11:41:54'),
(2, 1, 'Super Admin', 'admin@example.com', 'super_admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'http://127.0.0.1:8000/admin/settings/backup/schedule', 'POST', 'updated', 'settings', 'Backup schedule updated', NULL, NULL, 'success', NULL, '2026-08-09 10:29:33', '2026-08-09 10:29:33', '2026-08-09 10:29:33');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(100) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED DEFAULT NULL,
  `default_discount` decimal(5,2) DEFAULT 0.00,
  `default_tax` decimal(5,2) DEFAULT 0.00,
  `invoice_prefix` varchar(50) DEFAULT 'INV',
  `quotation_prefix` varchar(50) DEFAULT 'QUO',
  `debit_note_prefix` varchar(50) DEFAULT 'DN',
  `credit_note_prefix` varchar(50) DEFAULT 'CN',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-example@gmail.com|127.0.0.1', 'i:1;', 1784750170),
('laravel-cache-example@gmail.com|127.0.0.1:timer', 'i:1784750170;', 1784750170),
('laravel-cache-settings_all', 'a:28:{s:8:\"app_name\";s:16:\"\"\\\"ICT SYSTEM\\\"\"\";s:8:\"timezone\";s:17:\"\"Africa\\/Abidjan\"\";s:6:\"locale\";s:4:\"\"en\"\";s:8:\"currency\";s:5:\"\"TZS\"\";s:11:\"date_format\";s:9:\"\"m\\/d\\/Y\"\";s:11:\"time_format\";s:7:\"\"H:i:s\"\";s:14:\"items_per_page\";s:4:\"\"15\"\";s:19:\"enable_registration\";s:3:\"\"1\"\";s:16:\"maintenance_mode\";s:3:\"\"1\"\";s:11:\"mail_mailer\";s:5:\"\"log\"\";s:9:\"mail_host\";s:11:\"\"127.0.0.1\"\";s:9:\"mail_port\";s:6:\"\"2525\"\";s:17:\"mail_from_address\";s:15:\"\"test@mail.com\"\";s:14:\"mail_from_name\";s:12:\"\"ICT STSTEM\"\";s:15:\"mail_encryption\";s:5:\"\"ssl\"\";s:13:\"mail_username\";N;s:13:\"mail_password\";N;s:16:\"session_lifetime\";s:5:\"\"120\"\";s:19:\"password_min_length\";s:3:\"\"8\"\";s:11:\"require_2fa\";s:3:\"\"1\"\";s:18:\"max_login_attempts\";s:3:\"\"5\"\";s:16:\"lockout_duration\";s:4:\"\"15\"\";s:23:\"session_expire_on_close\";s:3:\"\"1\"\";s:20:\"password_expiry_days\";s:4:\"\"90\"\";s:16:\"remember_me_days\";s:4:\"\"30\"\";s:16:\"backup_frequency\";s:7:\"\"daily\"\";s:11:\"backup_time\";s:7:\"\"00:00\"\";s:16:\"backup_keep_days\";s:4:\"\"30\"\";}', 1787769931),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"group_name\";s:1:\"d\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:305:{i:0;a:5:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:9:\"Dashboard\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:5:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"dashboard.edit\";s:1:\"c\";s:9:\"Dashboard\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:5:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"user.view\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:5:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"user.create\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:5:{s:1:\"a\";i:5;s:1:\"b\";s:9:\"user.edit\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:5:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"user.delete\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:5:{s:1:\"a\";i:7;s:1:\"b\";s:9:\"role.view\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:5:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"role.create\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:5:{s:1:\"a\";i:9;s:1:\"b\";s:9:\"role.edit\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:5:{s:1:\"a\";i:10;s:1:\"b\";s:11:\"role.delete\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:5:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"department.view\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:5:{s:1:\"a\";i:12;s:1:\"b\";s:17:\"department.create\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:5:{s:1:\"a\";i:13;s:1:\"b\";s:15:\"department.edit\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:5:{s:1:\"a\";i:14;s:1:\"b\";s:17:\"department.delete\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:5:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"category.view\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:5:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"category.create\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:5:{s:1:\"a\";i:17;s:1:\"b\";s:13:\"category.edit\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:5:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"category.delete\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:5:{s:1:\"a\";i:19;s:1:\"b\";s:13:\"supplier.view\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:5:{s:1:\"a\";i:20;s:1:\"b\";s:15:\"supplier.create\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:5:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"supplier.edit\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:5:{s:1:\"a\";i:22;s:1:\"b\";s:15:\"supplier.delete\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:5:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"product.view\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:5:{s:1:\"a\";i:24;s:1:\"b\";s:14:\"product.create\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:5:{s:1:\"a\";i:25;s:1:\"b\";s:12:\"product.edit\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:5:{s:1:\"a\";i:26;s:1:\"b\";s:14:\"product.delete\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:5:{s:1:\"a\";i:27;s:1:\"b\";s:15:\"assignment.view\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:5:{s:1:\"a\";i:28;s:1:\"b\";s:17:\"assignment.create\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:5:{s:1:\"a\";i:29;s:1:\"b\";s:15:\"assignment.edit\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:5:{s:1:\"a\";i:30;s:1:\"b\";s:17:\"assignment.delete\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:5:{s:1:\"a\";i:31;s:1:\"b\";s:10:\"stock.view\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:5:{s:1:\"a\";i:32;s:1:\"b\";s:12:\"stock.create\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:5:{s:1:\"a\";i:33;s:1:\"b\";s:19:\"purchase_order.view\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:5:{s:1:\"a\";i:34;s:1:\"b\";s:21:\"purchase_order.create\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:5:{s:1:\"a\";i:35;s:1:\"b\";s:19:\"purchase_order.edit\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:5:{s:1:\"a\";i:36;s:1:\"b\";s:21:\"purchase_order.delete\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:5:{s:1:\"a\";i:37;s:1:\"b\";s:16:\"maintenance.view\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:5:{s:1:\"a\";i:38;s:1:\"b\";s:18:\"maintenance.create\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:5:{s:1:\"a\";i:39;s:1:\"b\";s:16:\"maintenance.edit\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:5:{s:1:\"a\";i:40;s:1:\"b\";s:18:\"asset_request.view\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:40;a:5:{s:1:\"a\";i:41;s:1:\"b\";s:20:\"asset_request.create\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:41;a:5:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"asset_request.edit\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:42;a:5:{s:1:\"a\";i:43;s:1:\"b\";s:20:\"asset_request.delete\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:43;a:5:{s:1:\"a\";i:44;s:1:\"b\";s:20:\"asset_request.review\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:44;a:5:{s:1:\"a\";i:45;s:1:\"b\";s:21:\"asset_request.approve\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:45;a:5:{s:1:\"a\";i:46;s:1:\"b\";s:20:\"asset_request.reject\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:46;a:5:{s:1:\"a\";i:47;s:1:\"b\";s:33:\"asset_request.forward_to_director\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:47;a:5:{s:1:\"a\";i:48;s:1:\"b\";s:36:\"asset_request.forward_to_procurement\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:48;a:5:{s:1:\"a\";i:49;s:1:\"b\";s:21:\"asset_request.specify\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:49;a:5:{s:1:\"a\";i:50;s:1:\"b\";s:16:\"procurement.view\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:5:{s:1:\"a\";i:51;s:1:\"b\";s:18:\"procurement.create\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:51;a:5:{s:1:\"a\";i:52;s:1:\"b\";s:16:\"procurement.edit\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:5:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"inspection.view\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:53;a:5:{s:1:\"a\";i:54;s:1:\"b\";s:17:\"inspection.create\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:54;a:5:{s:1:\"a\";i:55;s:1:\"b\";s:15:\"inspection.edit\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:55;a:5:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"inspection.pass\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:56;a:5:{s:1:\"a\";i:57;s:1:\"b\";s:15:\"inspection.fail\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:5:{s:1:\"a\";i:58;s:1:\"b\";s:19:\"direct_request.view\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:5:{s:1:\"a\";i:59;s:1:\"b\";s:21:\"direct_request.create\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:5:{s:1:\"a\";i:60;s:1:\"b\";s:19:\"direct_request.edit\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:5:{s:1:\"a\";i:61;s:1:\"b\";s:17:\"notification.view\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:5:{s:1:\"a\";i:62;s:1:\"b\";s:11:\"report.view\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:5:{s:1:\"a\";i:63;s:1:\"b\";s:12:\"user.approve\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:5:{s:1:\"a\";i:64;s:1:\"b\";s:12:\"user.suspend\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:5:{s:1:\"a\";i:65;s:1:\"b\";s:15:\"user.reactivate\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:5:{s:1:\"a\";i:66;s:1:\"b\";s:10:\"user.block\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:5:{s:1:\"a\";i:67;s:1:\"b\";s:19:\"user.reset_password\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:5:{s:1:\"a\";i:68;s:1:\"b\";s:11:\"user.export\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:5:{s:1:\"a\";i:69;s:1:\"b\";s:11:\"user.import\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:69;a:5:{s:1:\"a\";i:70;s:1:\"b\";s:16:\"user.assign_role\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:70;a:5:{s:1:\"a\";i:71;s:1:\"b\";s:16:\"user.remove_role\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:71;a:5:{s:1:\"a\";i:72;s:1:\"b\";s:23:\"user.manage_permissions\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:72;a:5:{s:1:\"a\";i:73;s:1:\"b\";s:23:\"role.assign_permissions\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:73;a:5:{s:1:\"a\";i:74;s:1:\"b\";s:15:\"category.export\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:74;a:5:{s:1:\"a\";i:75;s:1:\"b\";s:15:\"category.import\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:75;a:5:{s:1:\"a\";i:76;s:1:\"b\";s:15:\"supplier.export\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:76;a:5:{s:1:\"a\";i:77;s:1:\"b\";s:15:\"supplier.import\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:77;a:5:{s:1:\"a\";i:78;s:1:\"b\";s:16:\"supplier.approve\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:78;a:5:{s:1:\"a\";i:79;s:1:\"b\";s:14:\"product.export\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:79;a:5:{s:1:\"a\";i:80;s:1:\"b\";s:14:\"product.import\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:80;a:5:{s:1:\"a\";i:81;s:1:\"b\";s:19:\"product.bulk_delete\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:81;a:5:{s:1:\"a\";i:82;s:1:\"b\";s:19:\"product.bulk_export\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:82;a:5:{s:1:\"a\";i:83;s:1:\"b\";s:21:\"product.view_hardware\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:83;a:5:{s:1:\"a\";i:84;s:1:\"b\";s:21:\"product.view_software\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:84;a:5:{s:1:\"a\";i:85;s:1:\"b\";s:23:\"product.manage_hardware\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:85;a:5:{s:1:\"a\";i:86;s:1:\"b\";s:23:\"product.manage_software\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:86;a:5:{s:1:\"a\";i:87;s:1:\"b\";s:17:\"assignment.return\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:87;a:5:{s:1:\"a\";i:88;s:1:\"b\";s:22:\"assignment.bulk_create\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:88;a:5:{s:1:\"a\";i:89;s:1:\"b\";s:22:\"assignment.bulk_return\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:89;a:5:{s:1:\"a\";i:90;s:1:\"b\";s:23:\"assignment.view_history\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:90;a:5:{s:1:\"a\";i:91;s:1:\"b\";s:17:\"assignment.export\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:91;a:5:{s:1:\"a\";i:92;s:1:\"b\";s:12:\"stock.adjust\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:92;a:5:{s:1:\"a\";i:93;s:1:\"b\";s:12:\"stock.export\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:93;a:5:{s:1:\"a\";i:94;s:1:\"b\";s:12:\"stock.report\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:94;a:5:{s:1:\"a\";i:95;s:1:\"b\";s:11:\"stock.audit\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:95;a:5:{s:1:\"a\";i:96;s:1:\"b\";s:21:\"purchase_order.submit\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:96;a:5:{s:1:\"a\";i:97;s:1:\"b\";s:22:\"purchase_order.approve\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:97;a:5:{s:1:\"a\";i:98;s:1:\"b\";s:21:\"purchase_order.reject\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:98;a:5:{s:1:\"a\";i:99;s:1:\"b\";s:22:\"purchase_order.receive\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:99;a:5:{s:1:\"a\";i:100;s:1:\"b\";s:21:\"purchase_order.export\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:100;a:5:{s:1:\"a\";i:101;s:1:\"b\";s:21:\"purchase_order.import\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:101;a:5:{s:1:\"a\";i:102;s:1:\"b\";s:26:\"purchase_order.bulk_delete\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:102;a:5:{s:1:\"a\";i:103;s:1:\"b\";s:27:\"purchase_order.bulk_approve\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:103;a:5:{s:1:\"a\";i:104;s:1:\"b\";s:27:\"purchase_order.download_pdf\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:104;a:5:{s:1:\"a\";i:105;s:1:\"b\";s:20:\"purchase_order.print\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:105;a:5:{s:1:\"a\";i:106;s:1:\"b\";s:18:\"maintenance.delete\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:106;a:5:{s:1:\"a\";i:107;s:1:\"b\";s:19:\"maintenance.approve\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:107;a:5:{s:1:\"a\";i:108;s:1:\"b\";s:18:\"maintenance.reject\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:108;a:5:{s:1:\"a\";i:109;s:1:\"b\";s:18:\"maintenance.repair\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:109;a:5:{s:1:\"a\";i:110;s:1:\"b\";s:20:\"maintenance.complete\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:110;a:5:{s:1:\"a\";i:111;s:1:\"b\";s:18:\"maintenance.export\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:111;a:5:{s:1:\"a\";i:112;s:1:\"b\";s:18:\"maintenance.import\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:112;a:5:{s:1:\"a\";i:113;s:1:\"b\";s:20:\"maintenance.download\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:113;a:5:{s:1:\"a\";i:114;s:1:\"b\";s:20:\"asset_request.cancel\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:114;a:5:{s:1:\"a\";i:115;s:1:\"b\";s:20:\"asset_request.export\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:115;a:5:{s:1:\"a\";i:116;s:1:\"b\";s:20:\"asset_request.import\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:116;a:5:{s:1:\"a\";i:117;s:1:\"b\";s:25:\"asset_request.bulk_delete\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:117;a:5:{s:1:\"a\";i:118;s:1:\"b\";s:26:\"asset_request.bulk_approve\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:118;a:5:{s:1:\"a\";i:119;s:1:\"b\";s:25:\"asset_request.bulk_reject\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:119;a:5:{s:1:\"a\";i:120;s:1:\"b\";s:30:\"asset_request.view_my_requests\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:120;a:5:{s:1:\"a\";i:121;s:1:\"b\";s:31:\"asset_request.view_all_requests\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:121;a:5:{s:1:\"a\";i:122;s:1:\"b\";s:18:\"procurement.delete\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:122;a:5:{s:1:\"a\";i:123;s:1:\"b\";s:19:\"procurement.approve\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:123;a:5:{s:1:\"a\";i:124;s:1:\"b\";s:18:\"procurement.reject\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:124;a:5:{s:1:\"a\";i:125;s:1:\"b\";s:19:\"procurement.receive\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:125;a:5:{s:1:\"a\";i:126;s:1:\"b\";s:18:\"procurement.export\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:126;a:5:{s:1:\"a\";i:127;s:1:\"b\";s:18:\"procurement.import\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:127;a:5:{s:1:\"a\";i:128;s:1:\"b\";s:23:\"procurement.bulk_delete\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:128;a:5:{s:1:\"a\";i:129;s:1:\"b\";s:24:\"procurement.bulk_approve\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:129;a:5:{s:1:\"a\";i:130;s:1:\"b\";s:24:\"procurement.download_pdf\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:130;a:5:{s:1:\"a\";i:131;s:1:\"b\";s:17:\"inspection.delete\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:131;a:5:{s:1:\"a\";i:132;s:1:\"b\";s:17:\"inspection.export\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:132;a:5:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"inspection.import\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:133;a:5:{s:1:\"a\";i:134;s:1:\"b\";s:29:\"inspection.return_to_supplier\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:134;a:5:{s:1:\"a\";i:135;s:1:\"b\";s:20:\"inspection.bulk_pass\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:135;a:5:{s:1:\"a\";i:136;s:1:\"b\";s:20:\"inspection.bulk_fail\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:136;a:5:{s:1:\"a\";i:137;s:1:\"b\";s:21:\"direct_request.delete\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:137;a:5:{s:1:\"a\";i:138;s:1:\"b\";s:22:\"direct_request.approve\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:138;a:5:{s:1:\"a\";i:139;s:1:\"b\";s:21:\"direct_request.reject\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:139;a:5:{s:1:\"a\";i:140;s:1:\"b\";s:29:\"direct_request.forward_to_ict\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:140;a:5:{s:1:\"a\";i:141;s:1:\"b\";s:37:\"direct_request.forward_to_procurement\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:141;a:5:{s:1:\"a\";i:142;s:1:\"b\";s:31:\"direct_request.merge_to_request\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:142;a:5:{s:1:\"a\";i:143;s:1:\"b\";s:21:\"direct_request.export\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:143;a:5:{s:1:\"a\";i:144;s:1:\"b\";s:19:\"notification.create\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:144;a:5:{s:1:\"a\";i:145;s:1:\"b\";s:22:\"notification.mark_read\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:145;a:5:{s:1:\"a\";i:146;s:1:\"b\";s:24:\"notification.mark_unread\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:146;a:5:{s:1:\"a\";i:147;s:1:\"b\";s:26:\"notification.mark_all_read\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:147;a:5:{s:1:\"a\";i:148;s:1:\"b\";s:19:\"notification.delete\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:148;a:5:{s:1:\"a\";i:149;s:1:\"b\";s:24:\"notification.bulk_delete\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:149;a:5:{s:1:\"a\";i:150;s:1:\"b\";s:19:\"notification.export\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:150;a:5:{s:1:\"a\";i:151;s:1:\"b\";s:21:\"notification.settings\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:151;a:5:{s:1:\"a\";i:152;s:1:\"b\";s:22:\"notification.configure\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:152;a:5:{s:1:\"a\";i:153;s:1:\"b\";s:15:\"report.generate\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:153;a:5:{s:1:\"a\";i:154;s:1:\"b\";s:13:\"report.export\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:154;a:5:{s:1:\"a\";i:155;s:1:\"b\";s:12:\"report.print\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:155;a:5:{s:1:\"a\";i:156;s:1:\"b\";s:15:\"report.schedule\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:156;a:5:{s:1:\"a\";i:157;s:1:\"b\";s:16:\"report.inventory\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:157;a:5:{s:1:\"a\";i:158;s:1:\"b\";s:17:\"report.assignment\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:158;a:5:{s:1:\"a\";i:159;s:1:\"b\";s:14:\"report.request\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:159;a:5:{s:1:\"a\";i:160;s:1:\"b\";s:18:\"report.procurement\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:160;a:5:{s:1:\"a\";i:161;s:1:\"b\";s:18:\"report.maintenance\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:161;a:5:{s:1:\"a\";i:162;s:1:\"b\";s:16:\"report.financial\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:162;a:5:{s:1:\"a\";i:163;s:1:\"b\";s:13:\"report.custom\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:163;a:5:{s:1:\"a\";i:164;s:1:\"b\";s:13:\"settings.view\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:164;a:5:{s:1:\"a\";i:165;s:1:\"b\";s:13:\"settings.edit\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:165;a:5:{s:1:\"a\";i:166;s:1:\"b\";s:15:\"settings.manage\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:166;a:5:{s:1:\"a\";i:167;s:1:\"b\";s:22:\"settings.system_config\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:167;a:5:{s:1:\"a\";i:168;s:1:\"b\";s:21:\"settings.email_config\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:168;a:5:{s:1:\"a\";i:169;s:1:\"b\";s:15:\"settings.backup\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:169;a:5:{s:1:\"a\";i:170;s:1:\"b\";s:19:\"settings.audit_logs\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:170;a:5:{s:1:\"a\";i:171;s:1:\"b\";s:12:\"license.view\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:171;a:5:{s:1:\"a\";i:172;s:1:\"b\";s:14:\"license.create\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:172;a:5:{s:1:\"a\";i:173;s:1:\"b\";s:12:\"license.edit\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:173;a:5:{s:1:\"a\";i:174;s:1:\"b\";s:14:\"license.delete\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:174;a:5:{s:1:\"a\";i:175;s:1:\"b\";s:14:\"license.assign\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:175;a:5:{s:1:\"a\";i:176;s:1:\"b\";s:14:\"license.revoke\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:176;a:5:{s:1:\"a\";i:177;s:1:\"b\";s:14:\"license.export\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:177;a:5:{s:1:\"a\";i:178;s:1:\"b\";s:14:\"license.import\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:178;a:5:{s:1:\"a\";i:179;s:1:\"b\";s:13:\"license.renew\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:179;a:5:{s:1:\"a\";i:180;s:1:\"b\";s:20:\"license.view_expired\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:180;a:5:{s:1:\"a\";i:181;s:1:\"b\";s:22:\"license.view_available\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:181;a:5:{s:1:\"a\";i:182;s:1:\"b\";s:17:\"activity_log.view\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:182;a:5:{s:1:\"a\";i:183;s:1:\"b\";s:19:\"activity_log.export\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:183;a:5:{s:1:\"a\";i:184;s:1:\"b\";s:19:\"activity_log.delete\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:184;a:5:{s:1:\"a\";i:185;s:1:\"b\";s:18:\"activity_log.clear\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:9;}}i:185;a:5:{s:1:\"a\";i:186;s:1:\"b\";s:19:\"activity_log.filter\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:9;}}i:186;a:5:{s:1:\"a\";i:187;s:1:\"b\";s:10:\"api.access\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:187;a:5:{s:1:\"a\";i:188;s:1:\"b\";s:17:\"api.manage_tokens\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:188;a:5:{s:1:\"a\";i:189;s:1:\"b\";s:17:\"api.revoke_tokens\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:189;a:5:{s:1:\"a\";i:190;s:1:\"b\";s:11:\"backup.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:190;a:5:{s:1:\"a\";i:191;s:1:\"b\";s:13:\"backup.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:191;a:5:{s:1:\"a\";i:192;s:1:\"b\";s:13:\"backup.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:192;a:5:{s:1:\"a\";i:193;s:1:\"b\";s:15:\"backup.download\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:193;a:5:{s:1:\"a\";i:194;s:1:\"b\";s:14:\"backup.restore\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:194;a:5:{s:1:\"a\";i:195;s:1:\"b\";s:15:\"backup.schedule\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:195;a:5:{s:1:\"a\";i:196;s:1:\"b\";s:16:\"backup.configure\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:196;a:5:{s:1:\"a\";i:197;s:1:\"b\";s:13:\"facility.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:197;a:5:{s:1:\"a\";i:198;s:1:\"b\";s:15:\"facility.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:198;a:5:{s:1:\"a\";i:199;s:1:\"b\";s:13:\"facility.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:199;a:5:{s:1:\"a\";i:200;s:1:\"b\";s:15:\"facility.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:200;a:5:{s:1:\"a\";i:201;s:1:\"b\";s:15:\"facility.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:201;a:5:{s:1:\"a\";i:202;s:1:\"b\";s:15:\"facility.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:202;a:5:{s:1:\"a\";i:203;s:1:\"b\";s:15:\"facility.verify\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:203;a:5:{s:1:\"a\";i:204;s:1:\"b\";s:12:\"sponsor.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:204;a:5:{s:1:\"a\";i:205;s:1:\"b\";s:14:\"sponsor.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:205;a:5:{s:1:\"a\";i:206;s:1:\"b\";s:12:\"sponsor.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:206;a:5:{s:1:\"a\";i:207;s:1:\"b\";s:14:\"sponsor.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:207;a:5:{s:1:\"a\";i:208;s:1:\"b\";s:14:\"sponsor.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:208;a:5:{s:1:\"a\";i:209;s:1:\"b\";s:14:\"sponsor.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:209;a:5:{s:1:\"a\";i:210;s:1:\"b\";s:14:\"sponsor.verify\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:210;a:5:{s:1:\"a\";i:211;s:1:\"b\";s:13:\"currency.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:211;a:5:{s:1:\"a\";i:212;s:1:\"b\";s:15:\"currency.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:212;a:5:{s:1:\"a\";i:213;s:1:\"b\";s:13:\"currency.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:213;a:5:{s:1:\"a\";i:214;s:1:\"b\";s:15:\"currency.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:214;a:5:{s:1:\"a\";i:215;s:1:\"b\";s:15:\"currency.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:215;a:5:{s:1:\"a\";i:216;s:1:\"b\";s:15:\"currency.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:216;a:5:{s:1:\"a\";i:217;s:1:\"b\";s:22:\"currency.toggle_status\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:217;a:5:{s:1:\"a\";i:218;s:1:\"b\";s:17:\"currency.set_base\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:218;a:5:{s:1:\"a\";i:219;s:1:\"b\";s:18:\"exchange_rate.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:219;a:5:{s:1:\"a\";i:220;s:1:\"b\";s:20:\"exchange_rate.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:220;a:5:{s:1:\"a\";i:221;s:1:\"b\";s:18:\"exchange_rate.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:221;a:5:{s:1:\"a\";i:222;s:1:\"b\";s:20:\"exchange_rate.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:222;a:5:{s:1:\"a\";i:223;s:1:\"b\";s:20:\"exchange_rate.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:223;a:5:{s:1:\"a\";i:224;s:1:\"b\";s:26:\"exchange_rate.fetch_latest\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:224;a:5:{s:1:\"a\";i:225;s:1:\"b\";s:14:\"warehouse.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:225;a:5:{s:1:\"a\";i:226;s:1:\"b\";s:16:\"warehouse.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:226;a:5:{s:1:\"a\";i:227;s:1:\"b\";s:14:\"warehouse.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:227;a:5:{s:1:\"a\";i:228;s:1:\"b\";s:16:\"warehouse.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:228;a:5:{s:1:\"a\";i:229;s:1:\"b\";s:16:\"warehouse.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:229;a:5:{s:1:\"a\";i:230;s:1:\"b\";s:16:\"warehouse.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:230;a:5:{s:1:\"a\";i:231;s:1:\"b\";s:23:\"warehouse.toggle_status\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:231;a:5:{s:1:\"a\";i:232;s:1:\"b\";s:12:\"variant.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:232;a:5:{s:1:\"a\";i:233;s:1:\"b\";s:14:\"variant.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:233;a:5:{s:1:\"a\";i:234;s:1:\"b\";s:12:\"variant.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:234;a:5:{s:1:\"a\";i:235;s:1:\"b\";s:14:\"variant.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:235;a:5:{s:1:\"a\";i:236;s:1:\"b\";s:21:\"variant.toggle_status\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:236;a:5:{s:1:\"a\";i:237;s:1:\"b\";s:14:\"quotation.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:237;a:5:{s:1:\"a\";i:238;s:1:\"b\";s:16:\"quotation.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:238;a:5:{s:1:\"a\";i:239;s:1:\"b\";s:14:\"quotation.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:239;a:5:{s:1:\"a\";i:240;s:1:\"b\";s:16:\"quotation.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:240;a:5:{s:1:\"a\";i:241;s:1:\"b\";s:16:\"quotation.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:241;a:5:{s:1:\"a\";i:242;s:1:\"b\";s:14:\"quotation.send\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:242;a:5:{s:1:\"a\";i:243;s:1:\"b\";s:16:\"quotation.accept\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:243;a:5:{s:1:\"a\";i:244;s:1:\"b\";s:16:\"quotation.reject\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:244;a:5:{s:1:\"a\";i:245;s:1:\"b\";s:25:\"quotation.convert_to_sale\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:245;a:5:{s:1:\"a\";i:246;s:1:\"b\";s:15:\"quotation.print\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:246;a:5:{s:1:\"a\";i:247;s:1:\"b\";s:13:\"quotation.pdf\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:247;a:5:{s:1:\"a\";i:248;s:1:\"b\";s:9:\"sale.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:248;a:5:{s:1:\"a\";i:249;s:1:\"b\";s:11:\"sale.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:249;a:5:{s:1:\"a\";i:250;s:1:\"b\";s:9:\"sale.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:250;a:5:{s:1:\"a\";i:251;s:1:\"b\";s:11:\"sale.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:251;a:5:{s:1:\"a\";i:252;s:1:\"b\";s:11:\"sale.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:252;a:5:{s:1:\"a\";i:253;s:1:\"b\";s:16:\"sale.bulk_delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:253;a:5:{s:1:\"a\";i:254;s:1:\"b\";s:8:\"sale.pay\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:254;a:5:{s:1:\"a\";i:255;s:1:\"b\";s:11:\"sale.cancel\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:255;a:5:{s:1:\"a\";i:256;s:1:\"b\";s:10:\"sale.print\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:256;a:5:{s:1:\"a\";i:257;s:1:\"b\";s:8:\"sale.pdf\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:257;a:5:{s:1:\"a\";i:258;s:1:\"b\";s:12:\"invoice.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:258;a:5:{s:1:\"a\";i:259;s:1:\"b\";s:13:\"invoice.print\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:259;a:5:{s:1:\"a\";i:260;s:1:\"b\";s:11:\"invoice.pdf\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:260;a:5:{s:1:\"a\";i:261;s:1:\"b\";s:14:\"invoice.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:261;a:5:{s:1:\"a\";i:262;s:1:\"b\";s:17:\"invoice.mark_paid\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:262;a:5:{s:1:\"a\";i:263;s:1:\"b\";s:21:\"invoice.send_reminder\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:263;a:5:{s:1:\"a\";i:264;s:1:\"b\";s:15:\"debit_note.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:264;a:5:{s:1:\"a\";i:265;s:1:\"b\";s:17:\"debit_note.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:265;a:5:{s:1:\"a\";i:266;s:1:\"b\";s:15:\"debit_note.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:266;a:5:{s:1:\"a\";i:267;s:1:\"b\";s:17:\"debit_note.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:267;a:5:{s:1:\"a\";i:268;s:1:\"b\";s:17:\"debit_note.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:268;a:5:{s:1:\"a\";i:269;s:1:\"b\";s:18:\"debit_note.approve\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:269;a:5:{s:1:\"a\";i:270;s:1:\"b\";s:17:\"debit_note.reject\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:270;a:5:{s:1:\"a\";i:271;s:1:\"b\";s:16:\"debit_note.print\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:271;a:5:{s:1:\"a\";i:272;s:1:\"b\";s:14:\"debit_note.pdf\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:272;a:5:{s:1:\"a\";i:273;s:1:\"b\";s:16:\"credit_note.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:273;a:5:{s:1:\"a\";i:274;s:1:\"b\";s:18:\"credit_note.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:274;a:5:{s:1:\"a\";i:275;s:1:\"b\";s:16:\"credit_note.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:275;a:5:{s:1:\"a\";i:276;s:1:\"b\";s:18:\"credit_note.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:276;a:5:{s:1:\"a\";i:277;s:1:\"b\";s:18:\"credit_note.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:277;a:5:{s:1:\"a\";i:278;s:1:\"b\";s:19:\"credit_note.approve\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:278;a:5:{s:1:\"a\";i:279;s:1:\"b\";s:18:\"credit_note.reject\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:279;a:5:{s:1:\"a\";i:280;s:1:\"b\";s:17:\"credit_note.print\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:280;a:5:{s:1:\"a\";i:281;s:1:\"b\";s:15:\"credit_note.pdf\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:281;a:5:{s:1:\"a\";i:282;s:1:\"b\";s:21:\"stock_adjustment.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:282;a:5:{s:1:\"a\";i:283;s:1:\"b\";s:23:\"stock_adjustment.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:283;a:5:{s:1:\"a\";i:284;s:1:\"b\";s:21:\"stock_adjustment.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:284;a:5:{s:1:\"a\";i:285;s:1:\"b\";s:23:\"stock_adjustment.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:285;a:5:{s:1:\"a\";i:286;s:1:\"b\";s:23:\"stock_adjustment.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:286;a:5:{s:1:\"a\";i:287;s:1:\"b\";s:24:\"stock_adjustment.approve\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:287;a:5:{s:1:\"a\";i:288;s:1:\"b\";s:23:\"stock_adjustment.cancel\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:288;a:5:{s:1:\"a\";i:289;s:1:\"b\";s:19:\"stock_transfer.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:289;a:5:{s:1:\"a\";i:290;s:1:\"b\";s:21:\"stock_transfer.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:290;a:5:{s:1:\"a\";i:291;s:1:\"b\";s:19:\"stock_transfer.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:291;a:5:{s:1:\"a\";i:292;s:1:\"b\";s:21:\"stock_transfer.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:292;a:5:{s:1:\"a\";i:293;s:1:\"b\";s:21:\"stock_transfer.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:293;a:5:{s:1:\"a\";i:294;s:1:\"b\";s:22:\"stock_transfer.approve\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:294;a:5:{s:1:\"a\";i:295;s:1:\"b\";s:23:\"stock_transfer.complete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:295;a:5:{s:1:\"a\";i:296;s:1:\"b\";s:21:\"stock_transfer.cancel\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:296;a:5:{s:1:\"a\";i:297;s:1:\"b\";s:14:\"invoice.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:297;a:5:{s:1:\"a\";i:298;s:1:\"b\";s:13:\"customer.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:298;a:5:{s:1:\"a\";i:299;s:1:\"b\";s:15:\"customer.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:299;a:5:{s:1:\"a\";i:300;s:1:\"b\";s:13:\"customer.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:300;a:5:{s:1:\"a\";i:301;s:1:\"b\";s:15:\"customer.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:301;a:5:{s:1:\"a\";i:302;s:1:\"b\";s:15:\"customer.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:302;a:5:{s:1:\"a\";i:303;s:1:\"b\";s:15:\"customer.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:303;a:5:{s:1:\"a\";i:304;s:1:\"b\";s:22:\"customer.toggle_status\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:304;a:5:{s:1:\"a\";i:305;s:1:\"b\";s:20:\"customer.bulk_delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"d\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:18:\"Operations manager\";s:1:\"d\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:2:\"HR\";s:1:\"d\";s:3:\"web\";}}}', 1788058661),
('laravel-cache-test@gmail.com|127.0.0.1', 'i:1;', 1784740690),
('laravel-cache-test@gmail.com|127.0.0.1:timer', 'i:1784740690;', 1784740690),
('laravel-cache-webdevercyber@gmail.com|127.0.0.1', 'i:1;', 1786190057),
('laravel-cache-webdevercyber@gmail.com|127.0.0.1:timer', 'i:1786190057;', 1786190057);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'N/A', '2026-07-19 11:57:37', '2026-08-01 19:19:21'),
(2, 'Test2', 'Not Applicable', '2026-07-23 17:07:06', '2026-08-08 06:57:27');

-- --------------------------------------------------------

--
-- Table structure for table `credit_notes`
--

CREATE TABLE `credit_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `credit_note_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `credit_note_date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('draft','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `subtotal` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `stock_updated` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_note_items`
--

CREATE TABLE `credit_note_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `credit_note_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `decimal_places` tinyint(4) NOT NULL DEFAULT 2,
  `is_base` tinyint(1) NOT NULL DEFAULT 0,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `name`, `symbol`, `decimal_places`, `is_base`, `exchange_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'TZS', 'Tanzanian Shilling', 'TSh', 2, 1, 1.000000, 1, '2026-08-25 10:49:24', '2026-08-25 10:49:24'),
(2, 'USD', 'US Dollar', '$', 2, 0, 2300.000000, 1, '2026-08-25 10:49:24', '2026-08-25 10:49:24'),
(3, 'EUR', 'Euro', '€', 2, 0, 2500.000000, 1, '2026-08-25 10:49:24', '2026-08-25 10:49:24'),
(4, 'KES', 'Kenyan Shilling', 'KSh', 2, 0, 18.000000, 0, '2026-08-25 10:49:24', '2026-08-27 18:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `type` enum('individual','company') DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `tax_id`, `type`, `credit_limit`, `balance`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'ABDALA MAHAJA', 'webdevercyber@gmail.com', NULL, 'Mwanza, Tanzania', NULL, 'individual', 0.00, 0.00, '2026-08-27 19:27:04', '2026-08-28 10:06:06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `debit_notes`
--

CREATE TABLE `debit_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `debit_note_number` varchar(50) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `debit_note_date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('draft','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `subtotal` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `stock_updated` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debit_note_items`
--

CREATE TABLE `debit_note_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `debit_note_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `debit_note_items`
--

INSERT INTO `debit_note_items` (`id`, `debit_note_id`, `product_id`, `quantity`, `unit_price`, `total`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3.00, 56.00, 168.00, NULL, '2026-08-27 18:01:34', '2026-08-27 18:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `head_of_department` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `head_of_department`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'HR DEPARTMENTS', 'NA', 'ABC GEEKS', 'abc@gmail.com', '09472648664', '2026-07-19 11:24:48', '2026-07-19 11:24:48'),
(2, 'ICT DEPARTMENT', NULL, 'Geeks For Geeks', 'geeks@gmail.com', NULL, '2026-08-02 02:40:15', '2026-08-02 02:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_currency_id` bigint(20) UNSIGNED NOT NULL,
  `to_currency_id` bigint(20) UNSIGNED NOT NULL,
  `rate` decimal(15,6) NOT NULL,
  `date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exchange_rates`
--

INSERT INTO `exchange_rates` (`id`, `from_currency_id`, `to_currency_id`, `rate`, `date`, `created_by`, `created_at`) VALUES
(1, 2, 1, 2500.000000, '2026-08-27', 1, '2026-08-27 21:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid','overdue') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 6),
(4, 'App\\Models\\User', 5),
(4, 'App\\Models\\User', 9),
(7, 'App\\Models\\User', 2),
(7, 'App\\Models\\User', 3),
(9, 'App\\Models\\User', 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('warranty_expiry','low_stock','assignment','maintenance') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `is_read`, `link`, `created_at`) VALUES
(1, 7, 'assignment', 'Your asset request #10 has been assigned to user', 0, 'http://127.0.0.1:8000/user/asset-requests/10', '2026-08-05 13:55:07'),
(2, 4, 'assignment', 'New asset request #12 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/12', '2026-08-06 11:55:53'),
(3, 6, 'assignment', 'New asset request #12 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/12', '2026-08-06 11:55:53'),
(4, 1, 'low_stock', 'Low stock alert: Test (SKU: test) has only 1 units remaining. Minimum stock level is 7.', 1, 'http://127.0.0.1:8000/admin/products/1', '2026-08-06 11:57:44'),
(5, 4, 'low_stock', 'Low stock alert: Test (SKU: test) has only 1 units remaining. Minimum stock level is 7.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-06 11:57:44'),
(6, 8, 'low_stock', 'Low stock alert: Test (SKU: test) has only 1 units remaining. Minimum stock level is 7.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-06 11:57:44'),
(7, 4, 'assignment', 'Specification revision requested for request #1: This Request can move on this request because you don\'t know spec', 0, 'http://127.0.0.1:8000/admin/specification-amendments/1', '2026-08-06 20:07:36'),
(8, 6, 'assignment', 'Specification revision requested for request #1: This Request can move on this request because you don\'t know spec', 0, 'http://127.0.0.1:8000/admin/specification-amendments/1', '2026-08-06 20:07:36'),
(9, 1, 'assignment', 'You have been assigned: Test', 1, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-06 20:10:56'),
(10, 3, 'assignment', 'Inspection completed for your request #2. Result: Failed', 0, 'http://127.0.0.1:8000/admin/inspections/1', '2026-08-06 20:50:19'),
(11, 1, 'assignment', 'Your asset request #12 has been assigned to user', 1, 'http://127.0.0.1:8000/user/asset-requests/12', '2026-08-07 13:15:07'),
(12, 7, 'assignment', 'Asset \"Test5\" has been assigned to you.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-07 13:15:45'),
(13, 1, 'assignment', 'Your asset request #8 has been sent to director', 1, 'http://127.0.0.1:8000/user/asset-requests/8', '2026-08-07 13:17:10'),
(14, 3, 'assignment', 'Specifications for your request #1 have been approved and updated.', 0, 'http://127.0.0.1:8000/user/asset-requests/1', '2026-08-08 03:00:54'),
(15, 1, 'assignment', 'New direct request from Super Admin: Test6', 1, 'http://127.0.0.1:8000/admin/direct-requests/12', '2026-08-08 07:06:02'),
(16, 1, 'assignment', 'Your direct request has been rejected by Super Admin', 1, 'http://127.0.0.1:8000/admin/direct-requests/5', '2026-08-08 07:38:05'),
(17, 4, 'assignment', 'Specification revision requested for request #1: Just a Simple Test', 0, 'http://127.0.0.1:8000/admin/specification-amendments/2', '2026-08-08 09:53:02'),
(18, 6, 'assignment', 'Specification revision requested for request #1: Just a Simple Test', 0, 'http://127.0.0.1:8000/admin/specification-amendments/2', '2026-08-08 09:53:02'),
(19, 3, 'assignment', 'Asset \"Test\" has been assigned to you.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 04:32:51'),
(20, 1, 'assignment', 'Purchase Order PO-20260405-720 has been partially/fully received', 1, 'http://127.0.0.1:8000/admin/purchase-orders/2', '2026-08-09 05:07:28'),
(21, 1, 'assignment', 'Purchase Order PO-20260809-0001 has been partially/fully received', 1, 'http://127.0.0.1:8000/admin/purchase-orders/3', '2026-08-09 05:26:51'),
(22, 2, 'assignment', 'You have been assigned a license for Test (License Key: MS-001-2024)', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 07:31:20'),
(23, 2, 'assignment', 'Your license for Test has been revoked.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 07:43:04'),
(24, 5, 'assignment', 'Asset request #6 needs your review', 0, 'http://127.0.0.1:8000/admin/asset-requests/6', '2026-08-09 10:08:07'),
(25, 4, 'assignment', 'New asset request #14 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/14', '2026-08-10 10:25:28'),
(26, 6, 'assignment', 'New asset request #14 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/14', '2026-08-10 10:25:28'),
(27, 1, 'assignment', 'Purchase Order PO-20260828-0001 has been received.', 0, 'http://127.0.0.1:8000/admin/purchase-orders/4', '2026-08-28 13:25:10'),
(28, 1, 'assignment', 'Purchase Order PO-20260828-0003 has been received.', 0, 'http://127.0.0.1:8000/admin/purchase-orders/6', '2026-08-28 16:11:14'),
(29, 1, 'low_stock', 'Low stock alert: Demo (SKU: test967) has only 4 units remaining. Minimum stock level is 4.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-29 04:55:39'),
(30, 4, 'low_stock', 'Low stock alert: Demo (SKU: test967) has only 4 units remaining. Minimum stock level is 4.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-29 04:55:39'),
(31, 8, 'low_stock', 'Low stock alert: Demo (SKU: test967) has only 4 units remaining. Minimum stock level is 4.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-29 04:55:39'),
(32, 9, 'low_stock', 'Low stock alert: Demo (SKU: test967) has only 4 units remaining. Minimum stock level is 4.', 0, 'http://127.0.0.1:8000/admin/products/1', '2026-08-29 04:55:39'),
(33, 1, 'assignment', 'Purchase Order PO-20260829-0001 has been received.', 0, 'http://127.0.0.1:8000/admin/purchase-orders/1', '2026-08-29 05:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `paymentable_type` varchar(255) NOT NULL,
  `paymentable_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `payment_date` date NOT NULL,
  `method` enum('cash','bank_transfer','cheque','mobile') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `group_name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard.view', 'Dashboard', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(2, 'dashboard.edit', 'Dashboard', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(3, 'user.view', 'User', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(4, 'user.create', 'User', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(5, 'user.edit', 'User', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(6, 'user.delete', 'User', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(7, 'role.view', 'Role', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(8, 'role.create', 'Role', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(9, 'role.edit', 'Role', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(10, 'role.delete', 'Role', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(11, 'department.view', 'Department', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(12, 'department.create', 'Department', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(13, 'department.edit', 'Department', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(14, 'department.delete', 'Department', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(15, 'category.view', 'Category', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(16, 'category.create', 'Category', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(17, 'category.edit', 'Category', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(18, 'category.delete', 'Category', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(19, 'supplier.view', 'Supplier', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(20, 'supplier.create', 'Supplier', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(21, 'supplier.edit', 'Supplier', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(22, 'supplier.delete', 'Supplier', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(23, 'product.view', 'Product', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(24, 'product.create', 'Product', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(25, 'product.edit', 'Product', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(26, 'product.delete', 'Product', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(27, 'assignment.view', 'Assignment', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(28, 'assignment.create', 'Assignment', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(29, 'assignment.edit', 'Assignment', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(30, 'assignment.delete', 'Assignment', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(31, 'stock.view', 'Stock', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(32, 'stock.create', 'Stock', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(33, 'purchase_order.view', 'Purchase Order', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(34, 'purchase_order.create', 'Purchase Order', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(35, 'purchase_order.edit', 'Purchase Order', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(36, 'purchase_order.delete', 'Purchase Order', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(37, 'maintenance.view', 'Maintenance', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(38, 'maintenance.create', 'Maintenance', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(39, 'maintenance.edit', 'Maintenance', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(40, 'asset_request.view', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(41, 'asset_request.create', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(42, 'asset_request.edit', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(43, 'asset_request.delete', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(44, 'asset_request.review', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(45, 'asset_request.approve', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(46, 'asset_request.reject', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(47, 'asset_request.forward_to_director', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(48, 'asset_request.forward_to_procurement', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(49, 'asset_request.specify', 'Asset Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(50, 'procurement.view', 'Procurement', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(51, 'procurement.create', 'Procurement', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(52, 'procurement.edit', 'Procurement', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(53, 'inspection.view', 'Inspection', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(54, 'inspection.create', 'Inspection', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(55, 'inspection.edit', 'Inspection', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(56, 'inspection.pass', 'Inspection', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(57, 'inspection.fail', 'Inspection', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(58, 'direct_request.view', 'Direct Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(59, 'direct_request.create', 'Direct Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(60, 'direct_request.edit', 'Direct Request', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(61, 'notification.view', 'Notification', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(62, 'report.view', 'Report', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(63, 'user.approve', 'User', 'web', '2026-08-05 09:51:07', '2026-08-05 09:51:07'),
(64, 'user.suspend', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(65, 'user.reactivate', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(66, 'user.block', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(67, 'user.reset_password', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(68, 'user.export', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(69, 'user.import', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(70, 'user.assign_role', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(71, 'user.remove_role', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(72, 'user.manage_permissions', 'User', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(73, 'role.assign_permissions', 'Role', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(74, 'category.export', 'Category', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(75, 'category.import', 'Category', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(76, 'supplier.export', 'Supplier', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(77, 'supplier.import', 'Supplier', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(78, 'supplier.approve', 'Supplier', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(79, 'product.export', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(80, 'product.import', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(81, 'product.bulk_delete', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(82, 'product.bulk_export', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(83, 'product.view_hardware', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(84, 'product.view_software', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(85, 'product.manage_hardware', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(86, 'product.manage_software', 'Product', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(87, 'assignment.return', 'Assignment', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(88, 'assignment.bulk_create', 'Assignment', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(89, 'assignment.bulk_return', 'Assignment', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(90, 'assignment.view_history', 'Assignment', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(91, 'assignment.export', 'Assignment', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(92, 'stock.adjust', 'Stock', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(93, 'stock.export', 'Stock', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(94, 'stock.report', 'Stock', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(95, 'stock.audit', 'Stock', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(96, 'purchase_order.submit', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(97, 'purchase_order.approve', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(98, 'purchase_order.reject', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(99, 'purchase_order.receive', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(100, 'purchase_order.export', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(101, 'purchase_order.import', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(102, 'purchase_order.bulk_delete', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(103, 'purchase_order.bulk_approve', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(104, 'purchase_order.download_pdf', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(105, 'purchase_order.print', 'Purchase Order', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(106, 'maintenance.delete', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(107, 'maintenance.approve', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(108, 'maintenance.reject', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(109, 'maintenance.repair', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(110, 'maintenance.complete', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(111, 'maintenance.export', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(112, 'maintenance.import', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(113, 'maintenance.download', 'Maintenance', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(114, 'asset_request.cancel', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(115, 'asset_request.export', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(116, 'asset_request.import', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(117, 'asset_request.bulk_delete', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(118, 'asset_request.bulk_approve', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(119, 'asset_request.bulk_reject', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(120, 'asset_request.view_my_requests', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(121, 'asset_request.view_all_requests', 'Asset Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(122, 'procurement.delete', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(123, 'procurement.approve', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(124, 'procurement.reject', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(125, 'procurement.receive', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(126, 'procurement.export', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(127, 'procurement.import', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(128, 'procurement.bulk_delete', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(129, 'procurement.bulk_approve', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(130, 'procurement.download_pdf', 'Procurement', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(131, 'inspection.delete', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(132, 'inspection.export', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(133, 'inspection.import', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(134, 'inspection.return_to_supplier', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(135, 'inspection.bulk_pass', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(136, 'inspection.bulk_fail', 'Inspection', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(137, 'direct_request.delete', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(138, 'direct_request.approve', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(139, 'direct_request.reject', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(140, 'direct_request.forward_to_ict', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(141, 'direct_request.forward_to_procurement', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(142, 'direct_request.merge_to_request', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(143, 'direct_request.export', 'Direct Request', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(144, 'notification.create', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(145, 'notification.mark_read', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(146, 'notification.mark_unread', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(147, 'notification.mark_all_read', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(148, 'notification.delete', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(149, 'notification.bulk_delete', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(150, 'notification.export', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(151, 'notification.settings', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(152, 'notification.configure', 'Notification', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(153, 'report.generate', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(154, 'report.export', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(155, 'report.print', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(156, 'report.schedule', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(157, 'report.inventory', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(158, 'report.assignment', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(159, 'report.request', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(160, 'report.procurement', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(161, 'report.maintenance', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(162, 'report.financial', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(163, 'report.custom', 'Report', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(164, 'settings.view', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(165, 'settings.edit', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(166, 'settings.manage', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(167, 'settings.system_config', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(168, 'settings.email_config', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(169, 'settings.backup', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(170, 'settings.audit_logs', 'Settings', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(171, 'license.view', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(172, 'license.create', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(173, 'license.edit', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(174, 'license.delete', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(175, 'license.assign', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(176, 'license.revoke', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(177, 'license.export', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(178, 'license.import', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(179, 'license.renew', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(180, 'license.view_expired', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(181, 'license.view_available', 'License', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(182, 'activity_log.view', 'Activity Log', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(183, 'activity_log.export', 'Activity Log', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(184, 'activity_log.delete', 'Activity Log', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(185, 'activity_log.clear', 'Activity Log', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(186, 'activity_log.filter', 'Activity Log', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(187, 'api.access', 'API', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(188, 'api.manage_tokens', 'API', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(189, 'api.revoke_tokens', 'API', 'web', '2026-08-05 09:51:08', '2026-08-05 09:51:08'),
(190, 'backup.view', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(191, 'backup.create', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(192, 'backup.delete', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(193, 'backup.download', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(194, 'backup.restore', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(195, 'backup.schedule', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(196, 'backup.configure', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(197, 'facility.view', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(198, 'facility.create', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(199, 'facility.edit', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(200, 'facility.delete', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(201, 'facility.export', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(202, 'facility.import', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(203, 'facility.verify', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(204, 'sponsor.view', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(205, 'sponsor.create', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(206, 'sponsor.edit', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(207, 'sponsor.delete', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(208, 'sponsor.export', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(209, 'sponsor.import', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(210, 'sponsor.verify', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08'),
(211, 'currency.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(212, 'currency.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(213, 'currency.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(214, 'currency.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(215, 'currency.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(216, 'currency.import', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(217, 'currency.toggle_status', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(218, 'currency.set_base', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(219, 'exchange_rate.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(220, 'exchange_rate.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(221, 'exchange_rate.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(222, 'exchange_rate.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(223, 'exchange_rate.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(224, 'exchange_rate.fetch_latest', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(225, 'warehouse.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(226, 'warehouse.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(227, 'warehouse.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(228, 'warehouse.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(229, 'warehouse.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(230, 'warehouse.import', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(231, 'warehouse.toggle_status', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(232, 'variant.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(233, 'variant.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(234, 'variant.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(235, 'variant.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(236, 'variant.toggle_status', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(237, 'quotation.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(238, 'quotation.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(239, 'quotation.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(240, 'quotation.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(241, 'quotation.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(242, 'quotation.send', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(243, 'quotation.accept', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(244, 'quotation.reject', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(245, 'quotation.convert_to_sale', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(246, 'quotation.print', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(247, 'quotation.pdf', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(248, 'sale.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(249, 'sale.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(250, 'sale.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(251, 'sale.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(252, 'sale.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(253, 'sale.bulk_delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(254, 'sale.pay', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(255, 'sale.cancel', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(256, 'sale.print', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(257, 'sale.pdf', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(258, 'invoice.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(259, 'invoice.print', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(260, 'invoice.pdf', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(261, 'invoice.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(262, 'invoice.mark_paid', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(263, 'invoice.send_reminder', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(264, 'debit_note.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(265, 'debit_note.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(266, 'debit_note.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(267, 'debit_note.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(268, 'debit_note.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(269, 'debit_note.approve', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(270, 'debit_note.reject', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(271, 'debit_note.print', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(272, 'debit_note.pdf', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(273, 'credit_note.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(274, 'credit_note.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(275, 'credit_note.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(276, 'credit_note.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(277, 'credit_note.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(278, 'credit_note.approve', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(279, 'credit_note.reject', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(280, 'credit_note.print', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(281, 'credit_note.pdf', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(282, 'stock_adjustment.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(283, 'stock_adjustment.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(284, 'stock_adjustment.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(285, 'stock_adjustment.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(286, 'stock_adjustment.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(287, 'stock_adjustment.approve', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(288, 'stock_adjustment.cancel', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(289, 'stock_transfer.view', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(290, 'stock_transfer.create', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(291, 'stock_transfer.edit', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(292, 'stock_transfer.delete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(293, 'stock_transfer.export', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(294, 'stock_transfer.approve', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(295, 'stock_transfer.complete', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(296, 'stock_transfer.cancel', NULL, 'web', '2026-08-26 10:58:32', '2026-08-26 10:58:32'),
(297, 'invoice.create', NULL, 'web', '2026-08-27 17:23:34', '2026-08-27 17:23:34'),
(298, 'customer.view', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(299, 'customer.create', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(300, 'customer.edit', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(301, 'customer.delete', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(302, 'customer.export', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(303, 'customer.import', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(304, 'customer.toggle_status', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23'),
(305, 'customer.bulk_delete', NULL, 'web', '2026-08-27 18:44:23', '2026-08-27 18:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `selling_price` decimal(12,2) DEFAULT NULL,
  `wholesale_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) DEFAULT 0.00,
  `current_value` decimal(12,2) DEFAULT NULL,
  `status` enum('available','assigned','under_repair','retired','expired') NOT NULL DEFAULT 'available',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 0,
  `reorder_quantity` int(11) DEFAULT 0,
  `unit` varchar(50) DEFAULT 'piece',
  `weight` decimal(10,3) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `warehouse_id`, `name`, `sku`, `barcode`, `description`, `type`, `purchase_date`, `warranty_end_date`, `purchase_price`, `selling_price`, `wholesale_price`, `cost_price`, `current_value`, `status`, `quantity`, `min_stock_level`, `reorder_level`, `reorder_quantity`, `unit`, `weight`, `dimensions`, `is_active`, `is_taxable`, `tax_rate`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 'Demo', 'test967', NULL, NULL, 'accessory', NULL, NULL, NULL, 5000.00, NULL, NULL, NULL, 'available', 5, 4, 0, 0, 'kg', NULL, NULL, 1, 1, NULL, '2026-08-29 01:40:01', '2026-08-29 02:04:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `selling_price` decimal(15,2) NOT NULL,
  `cost_price` decimal(15,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `barcode` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `selling_price`, `cost_price`, `quantity`, `attributes`, `barcode`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'test967', 'red', 5600.00, 5000.00, 8, NULL, NULL, NULL, 1, '2026-08-26 13:06:41', '2026-08-26 13:06:56');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exchange_rate` decimal(10,4) DEFAULT 1.0000,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_invoice_number` varchar(50) DEFAULT NULL,
  `order_date` date NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `status` enum('draft','ordered','received','invoiced','paid','cancelled') NOT NULL DEFAULT 'draft',
  `total_amount` decimal(12,2) DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `currency_id`, `exchange_rate`, `user_id`, `po_number`, `supplier_invoice_number`, `order_date`, `invoice_date`, `due_date`, `expected_delivery_date`, `actual_delivery_date`, `status`, `total_amount`, `paid_amount`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 1.0000, 1, 'PO-20260829-0001', NULL, '2026-08-29', NULL, NULL, '2026-08-30', '2026-08-29', 'received', 500.00, 0.00, 'pending', NULL, '2026-08-29 02:04:10', '2026-08-29 02:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `quantity_received` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `product_id`, `quantity_ordered`, `quantity_received`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 500.00, 500.00, '2026-08-01 20:30:54', '2026-08-01 20:31:28'),
(2, 2, 3, 1, 1, 700.00, 700.00, '2026-08-05 14:28:03', '2026-08-09 02:07:28'),
(3, 3, 1, 1, 1, 560.00, 560.00, '2026-08-09 02:26:25', '2026-08-09 02:26:51'),
(4, 3, 1, 1, 1, 600.00, 600.00, '2026-08-09 02:26:25', '2026-08-09 02:26:51'),
(5, 4, 1, 1, 1, 60.00, 60.00, '2026-08-28 10:18:03', '2026-08-28 10:25:10'),
(6, 5, 1, 1, 0, 670.00, 670.00, '2026-08-28 10:42:23', '2026-08-28 10:42:23'),
(7, 6, 3, 4, 4, 6000.00, 24000.00, '2026-08-28 13:10:53', '2026-08-28 13:11:13'),
(8, 1, 1, 1, 1, 500.00, 500.00, '2026-08-29 02:04:10', '2026-08-29 02:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000,
  `subtotal` decimal(15,2) NOT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  `status` enum('draft','sent','accepted','rejected','expired','converted') NOT NULL DEFAULT 'draft',
  `converted_to_sale_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quotation_number`, `customer_id`, `user_id`, `warehouse_id`, `quotation_date`, `valid_until`, `currency_id`, `exchange_rate`, `subtotal`, `discount_type`, `discount`, `tax`, `total`, `notes`, `internal_notes`, `status`, `converted_to_sale_id`, `created_at`, `updated_at`) VALUES
(1, 'QUO-20260829-0001', 1, 1, 3, '2026-08-29', '2026-09-28', 1, 1.000000, 900.00, 'percentage', 0.00, 0.00, 900.00, NULL, NULL, 'accepted', NULL, '2026-08-29 02:05:36', '2026-08-29 02:05:49');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `variant_id`, `product_name`, `product_sku`, `quantity`, `unit_price`, `discount`, `tax_rate`, `tax_amount`, `total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Demo', 'test967', 2.00, 450.00, 0.00, 0.00, 0.00, 900.00, '2026-08-29 02:05:36', '2026-08-29 02:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(2, 'admin', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(3, 'ict_officer', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(4, 'director', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(5, 'procurement_officer', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(6, 'manager', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(7, 'user', 'web', '2026-07-16 22:51:16', '2026-07-16 22:51:16'),
(8, 'Operations manager', 'web', '2026-08-02 01:16:33', '2026-08-02 01:16:33'),
(9, 'HR', 'web', '2026-08-02 01:43:09', '2026-08-02 01:43:09');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(40, 8),
(40, 9),
(41, 1),
(41, 8),
(42, 1),
(42, 8),
(43, 1),
(43, 8),
(44, 1),
(44, 8),
(45, 1),
(45, 8),
(46, 1),
(46, 8),
(47, 1),
(47, 8),
(48, 1),
(48, 8),
(48, 9),
(49, 1),
(49, 8),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(141, 1),
(142, 1),
(143, 1),
(144, 1),
(145, 1),
(146, 1),
(147, 1),
(148, 1),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(172, 1),
(173, 1),
(174, 1),
(175, 1),
(176, 1),
(177, 1),
(178, 1),
(179, 1),
(180, 1),
(181, 1),
(182, 1),
(182, 9),
(183, 1),
(183, 9),
(184, 1),
(184, 9),
(185, 9),
(186, 9),
(187, 1),
(187, 9),
(188, 1),
(188, 9),
(189, 1),
(189, 9),
(190, 1),
(191, 1),
(192, 1),
(193, 1),
(194, 1),
(195, 1),
(196, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(204, 1),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(209, 1),
(210, 1),
(211, 1),
(212, 1),
(213, 1),
(214, 1),
(215, 1),
(216, 1),
(217, 1),
(218, 1),
(219, 1),
(220, 1),
(221, 1),
(222, 1),
(223, 1),
(224, 1),
(225, 1),
(226, 1),
(227, 1),
(228, 1),
(229, 1),
(230, 1),
(231, 1),
(232, 1),
(233, 1),
(234, 1),
(235, 1),
(236, 1),
(237, 1),
(238, 1),
(239, 1),
(240, 1),
(241, 1),
(242, 1),
(243, 1),
(244, 1),
(245, 1),
(246, 1),
(247, 1),
(248, 1),
(249, 1),
(250, 1),
(251, 1),
(252, 1),
(253, 1),
(254, 1),
(255, 1),
(256, 1),
(257, 1),
(258, 1),
(259, 1),
(260, 1),
(261, 1),
(262, 1),
(263, 1),
(264, 1),
(265, 1),
(266, 1),
(267, 1),
(268, 1),
(269, 1),
(270, 1),
(271, 1),
(272, 1),
(273, 1),
(274, 1),
(275, 1),
(276, 1),
(277, 1),
(278, 1),
(279, 1),
(280, 1),
(281, 1),
(282, 1),
(283, 1),
(284, 1),
(285, 1),
(286, 1),
(287, 1),
(288, 1),
(289, 1),
(290, 1),
(291, 1),
(292, 1),
(293, 1),
(294, 1),
(295, 1),
(296, 1),
(297, 1),
(298, 1),
(299, 1),
(300, 1),
(301, 1),
(302, 1),
(303, 1),
(304, 1),
(305, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sale_date` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `payment_method` enum('cash','card','mobile','credit','bank_transfer','cheque') NOT NULL,
  `payment_status` enum('pending','partial','paid','cancelled') NOT NULL DEFAULT 'pending',
  `status` enum('draft','confirmed','delivered','invoiced','paid','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('F5MQ2wAp4M42nz8b6zUCQQwfxKM1Y6lnpFgLJvlq', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidWl5dGh5YnZGc0k1Ujhkd1RYTjhUOFRlOVloYllDZTMxU0k5V0h6SCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvbm90aWZpY2F0aW9ucy9jb3VudCI7czo1OiJyb3V0ZSI7czoyMzoiYXBpLm5vdGlmaWNhdGlvbnMuY291bnQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1787983180);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `type` varchar(50) NOT NULL DEFAULT 'string',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'app_name', '\"\\\"ICT SYSTEM\\\"\"', 'general', 'string', 0, '2026-08-06 10:59:09', '2026-08-06 11:41:54'),
(2, 'timezone', '\"Africa\\/Abidjan\"', 'general', 'string', 0, '2026-08-06 10:59:09', '2026-08-06 11:41:54'),
(3, 'locale', '\"en\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(4, 'currency', '\"TZS\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(5, 'date_format', '\"m\\/d\\/Y\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(6, 'time_format', '\"H:i:s\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(7, 'items_per_page', '\"15\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(8, 'enable_registration', '\"1\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(9, 'maintenance_mode', '\"1\"', 'general', 'string', 0, '2026-08-06 10:59:10', '2026-08-06 10:59:10'),
(10, 'mail_mailer', '\"log\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(11, 'mail_host', '\"127.0.0.1\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(12, 'mail_port', '\"2525\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(13, 'mail_from_address', '\"test@mail.com\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(14, 'mail_from_name', '\"ICT STSTEM\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(15, 'mail_encryption', '\"ssl\"', 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(16, 'mail_username', NULL, 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(17, 'mail_password', NULL, 'general', 'string', 0, '2026-08-06 11:02:42', '2026-08-06 11:02:42'),
(18, 'session_lifetime', '\"120\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(19, 'password_min_length', '\"8\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(20, 'require_2fa', '\"1\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(21, 'max_login_attempts', '\"5\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(22, 'lockout_duration', '\"15\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(23, 'session_expire_on_close', '\"1\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(24, 'password_expiry_days', '\"90\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(25, 'remember_me_days', '\"30\"', 'general', 'string', 0, '2026-08-06 11:03:10', '2026-08-06 11:03:10'),
(26, 'backup_frequency', '\"daily\"', 'general', 'string', 0, '2026-08-09 10:29:33', '2026-08-09 10:29:33'),
(27, 'backup_time', '\"00:00\"', 'general', 'string', 0, '2026-08-09 10:29:33', '2026-08-09 10:29:33'),
(28, 'backup_keep_days', '\"30\"', 'general', 'string', 0, '2026-08-09 10:29:33', '2026-08-09 10:29:33');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_number` varchar(50) NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_date` date NOT NULL,
  `type` enum('increase','decrease','transfer') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('draft','approved','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `adjustment_number`, `warehouse_id`, `user_id`, `adjustment_date`, `type`, `reason`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'SA-20260826-0001', 2, 1, '2026-08-26', 'increase', 'Test', 'draft', NULL, '2026-08-26 13:55:21', '2026-08-26 13:55:21'),
(2, 'SA-20260826-0002', 2, 1, '2026-08-26', 'increase', 'Test', 'draft', NULL, '2026-08-26 13:56:04', '2026-08-26 13:56:04'),
(3, 'SA-20260826-0003', 2, 1, '2026-08-26', 'decrease', 'Test', 'draft', NULL, '2026-08-26 13:58:26', '2026-08-26 13:58:26'),
(4, 'SA-20260826-0004', 2, 1, '2026-08-26', 'increase', 'Test', 'approved', NULL, '2026-08-26 14:01:25', '2026-08-26 14:02:00');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_items`
--

CREATE TABLE `stock_adjustment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_before` decimal(15,2) NOT NULL,
  `quantity_adjustment` decimal(15,2) NOT NULL,
  `quantity_after` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustment_items`
--

INSERT INTO `stock_adjustment_items` (`id`, `stock_adjustment_id`, `product_id`, `quantity_before`, `quantity_adjustment`, `quantity_after`, `reason`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 2.00, -1.00, 1.00, NULL, '2026-08-26 14:00:25', '2026-08-26 14:00:25'),
(2, 4, 1, 2.00, 3.00, 5.00, 'test', '2026-08-26 14:01:25', '2026-08-26 14:01:25'),
(3, 4, 3, 50.00, 3.00, 53.00, 'test', '2026-08-26 14:01:25', '2026-08-26 14:01:25'),
(4, 4, 4, 3.00, 4.00, 7.00, 'test', '2026-08-26 14:01:25', '2026-08-26 14:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('purchase','adjustment','return','damage','transfer_out','transfer_in','sale') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `user_id`, `type`, `quantity_change`, `previous_quantity`, `new_quantity`, `reference`, `notes`, `created_at`) VALUES
(1, 1, 1, 'return', 1, 0, 1, NULL, NULL, '2026-08-06 11:57:44'),
(2, 1, 1, 'adjustment', -1, 1, 0, 'ASSIGN-3', 'Asset assigned to user', '2026-08-06 20:10:56'),
(3, 4, 1, 'purchase', 1, 0, 1, 'INITIAL', 'Initial product creation', '2026-08-08 08:23:30'),
(4, 4, 1, 'adjustment', 4, 1, 5, 'UPDATE', 'Quantity updated from 1 to 5', '2026-08-08 08:29:21'),
(5, 3, 1, 'purchase', 1, 49, 50, 'PO-20260405-720', 'Received from PO: PO-20260405-720', '2026-08-09 05:07:28'),
(6, 1, 1, 'purchase', 1, 0, 1, 'PO-20260809-0001', 'Received from PO: PO-20260809-0001', '2026-08-09 05:26:51'),
(7, 1, 1, 'purchase', 1, 1, 2, 'PO-20260809-0001', 'Received from PO: PO-20260809-0001', '2026-08-09 05:26:51'),
(8, 1, 1, 'adjustment', 3, 2, 5, 'SA-20260826-0004', 'Stock adjustment approved: Test', '2026-08-26 17:02:00'),
(9, 3, 1, 'adjustment', 3, 50, 53, 'SA-20260826-0004', 'Stock adjustment approved: Test', '2026-08-26 17:02:00'),
(10, 4, 1, 'adjustment', 4, 3, 7, 'SA-20260826-0004', 'Stock adjustment approved: Test', '2026-08-26 17:02:00'),
(11, 1, 1, 'transfer_out', -2, 3, 1, 'ST-20260826-0005', 'Transfer out to warehouse #2', '2026-08-26 17:27:20'),
(12, 1, 1, 'transfer_in', 2, 1, 3, 'ST-20260826-0005', 'Transfer in from warehouse #3', '2026-08-26 17:27:20'),
(13, 1, 1, 'purchase', 1, 3, 4, 'PO-20260828-0001', 'Received from PO: PO-20260828-0001', '2026-08-28 13:25:10'),
(14, 3, 1, 'purchase', 4, 53, 57, 'PO-20260828-0003', 'Received from PO: PO-20260828-0003', '2026-08-28 16:11:14'),
(15, 5, 1, 'purchase', 6, 0, 6, 'INITIAL', 'Initial product creation', '2026-08-28 16:24:23'),
(16, 1, 1, 'purchase', 1, 0, 1, 'INITIAL', 'Initial product creation', '2026-08-29 04:40:01'),
(17, 1, 1, 'adjustment', 3, 1, 4, 'UPDATE', 'Quantity updated from 1 to 4', '2026-08-29 04:55:39'),
(18, 1, 1, 'adjustment', 1, 4, 5, 'ADJUST', 'NA', '2026-08-29 04:56:19'),
(19, 1, 1, 'adjustment', -2, 5, 3, 'ADJUST', 'NA', '2026-08-29 04:57:03'),
(20, 1, 1, 'purchase', 1, 3, 4, 'PO-20260829-0001', 'Received from PO: PO-20260829-0001', '2026-08-29 05:04:38'),
(21, 1, 1, 'purchase', 1, 4, 5, 'PO-20260829-0001', 'Received from PO: PO-20260829-0001', '2026-08-29 05:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `to_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `transfer_date` date NOT NULL,
  `status` enum('draft','pending','in_transit','completed','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`id`, `transfer_number`, `from_warehouse_id`, `to_warehouse_id`, `user_id`, `transfer_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ST-20260826-0001', 2, 3, 1, '2026-08-26', 'draft', NULL, '2026-08-26 14:09:39', '2026-08-26 14:09:39'),
(2, 'ST-20260826-0002', 2, 3, 1, '2026-08-26', 'draft', NULL, '2026-08-26 14:09:48', '2026-08-26 14:09:48'),
(3, 'ST-20260826-0003', 2, 3, 1, '2026-08-26', 'draft', NULL, '2026-08-26 14:09:53', '2026-08-26 14:09:53'),
(4, 'ST-20260826-0004', 2, 3, 1, '2026-08-26', 'draft', NULL, '2026-08-26 14:10:29', '2026-08-26 14:10:29'),
(5, 'ST-20260826-0005', 3, 2, 1, '2026-08-26', 'completed', NULL, '2026-08-26 14:13:57', '2026-08-26 14:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer_items`
--

INSERT INTO `stock_transfer_items` (`id`, `stock_transfer_id`, `product_id`, `variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 5, 1, NULL, 2.00, '2026-08-26 14:13:57', '2026-08-26 14:13:57'),
(2, 3, 3, NULL, 2.00, '2026-08-26 14:18:57', '2026-08-26 14:18:57'),
(3, 2, 3, NULL, 2.00, '2026-08-26 14:19:16', '2026-08-26 14:19:16');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(100) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `registration_number`, `notes`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test', 'Test', 'webdevercyber@gmail.com', '094726482748', 'Mwanza, Tanzania', NULL, NULL, NULL, 1, '2026-07-19 11:59:30', '2026-08-01 19:24:07', NULL),
(2, 'ABDALA MAHAJA', 'ABDALA MAHAJA', NULL, NULL, 'Mwanza, Tanzania', NULL, NULL, NULL, 1, '2026-08-01 19:24:20', '2026-08-01 19:24:20', NULL),
(3, 'Test4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-06 17:03:45', '2026-08-06 17:03:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE `tax_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tax_rates`
--

INSERT INTO `tax_rates` (`id`, `name`, `rate`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'VAT', 18.00, 1, '2026-08-29 03:06:51', '2026-08-29 03:06:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `department_id`, `name`, `email`, `email_verified_at`, `phone`, `avatar`, `bio`, `password`, `employee_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Super Admin', 'admin@example.com', NULL, NULL, 'avatars/1786039565_1.png', NULL, '$2y$12$vJpOwqelGNQcCO3WUNJiFugrfiNcA9KlgGyZzDDaCotikA6bgtmCe', NULL, NULL, '2026-07-16 22:55:35', '2026-08-06 15:06:05'),
(2, NULL, 'geek', 'webdevercyber@gmail.com', NULL, NULL, NULL, NULL, '$2y$12$pZUlk32u3Asy/NTdEQRbOewdzhvDw.960pqlTHsB3XB8J/v.Z18Rq', NULL, NULL, '2026-07-18 07:35:37', '2026-07-18 07:35:37'),
(3, NULL, 'test', 'test10@gmail.com', NULL, NULL, 'avatars/1784863870_3.png', NULL, '$2y$12$ze3qQaD8h4LiDfFsnmPtg.gdZtQrEP1mHK0Xt5wDTha7XBf6koKae', NULL, NULL, '2026-07-19 02:18:27', '2026-08-05 01:29:38'),
(4, NULL, 'User1', 'user@gmail.com', NULL, NULL, NULL, NULL, '$2y$12$4VlYbXvsLTPtNoBPVxCWquYGuKzAFlFLHVLLiG6XuffG90d92hdLm', NULL, NULL, '2026-08-01 21:02:39', '2026-08-01 21:02:39'),
(5, 2, 'ABDALA MAHAJA', 'webdevercyber1@gmail.com', '2026-08-06 17:46:06', NULL, NULL, NULL, '$2y$12$wWhVS0PKfb3CF2YkjUmbSOHUGOZ9AOGCp1XZ1Ka1rQYl.JSNnrsQ.', NULL, NULL, '2026-08-01 21:09:10', '2026-08-06 17:46:06'),
(6, 1, 'DRCTS', 'director@gmail.com', NULL, NULL, NULL, NULL, '$2y$12$fB2KX8gF/Ub/8TUOckDz2.MvXKQeWlovKjjOC.3I6btx6lAT781ti', NULL, NULL, '2026-08-02 01:54:54', '2026-08-02 01:54:54'),
(7, 1, 'AKA', 'abdallahmahaja@gmail.com', '2026-08-09 03:52:22', NULL, NULL, NULL, '$2y$12$ehxSTZaEs3VSSbv5Ualk0eKOpl32HrMsztJgIlZ/JZ6iazY9lazVy', NULL, NULL, '2026-08-02 02:32:38', '2026-08-09 06:19:09'),
(8, 2, 'Geeks Geeks', 'geeeks@gmail.com', '2026-08-06 00:44:56', NULL, NULL, NULL, '$2y$12$dGLPe/9.xA9QuKXTD0UlY.z6kvZs5ZiwP1AHLwZiAiXJdXkm6IWle', NULL, NULL, '2026-08-02 03:09:44', '2026-08-06 00:44:56'),
(9, NULL, 'DirectTest', 'director1@gmail.com', '2026-08-09 07:10:33', NULL, NULL, NULL, '$2y$12$zgQyj4j.12eTPTYJAk2V4Onxqbh.Z8x2yo0C6aiEH3iEvfcwgJXb.', NULL, NULL, '2026-08-09 07:10:22', '2026-08-09 07:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `state`, `country`, `manager_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ABDALA MAHAJA', 'DDM', 'Mwanza, Tanzania', 'Mwanza', NULL, 'Tanzania', NULL, 0, '2026-08-26 13:14:56', '2026-08-26 13:29:02'),
(2, 'ABDALA MAHAJA', 'DDMW', 'Mwanza, Tanzania', 'Mwanza', NULL, 'Tanzania', NULL, 1, '2026-08-26 13:28:46', '2026-08-26 13:28:46'),
(3, 'Geeks', '65cadb', 'Mwanza, Tanzania', 'Mwanza', NULL, 'Tanzania', NULL, 1, '2026-08-26 14:09:21', '2026-08-26 14:09:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_performed_at_index` (`user_id`,`performed_at`),
  ADD KEY `activity_logs_action_module_index` (`action`,`module`),
  ADD KEY `activity_logs_performed_at_index` (`performed_at`),
  ADD KEY `activity_logs_status_index` (`status`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `businesses_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `credit_notes`
--
ALTER TABLE `credit_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `credit_note_number` (`credit_note_number`),
  ADD KEY `credit_notes_customer_id_foreign` (`customer_id`),
  ADD KEY `credit_notes_sale_id_foreign` (`sale_id`),
  ADD KEY `credit_notes_user_id_foreign` (`user_id`),
  ADD KEY `credit_notes_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `credit_note_items`
--
ALTER TABLE `credit_note_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_note_items_credit_note_id_foreign` (`credit_note_id`),
  ADD KEY `credit_note_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `currencies_code_unique` (`code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `debit_notes`
--
ALTER TABLE `debit_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `debit_note_number` (`debit_note_number`),
  ADD KEY `debit_notes_supplier_id_foreign` (`supplier_id`),
  ADD KEY `debit_notes_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `debit_notes_user_id_foreign` (`user_id`),
  ADD KEY `debit_notes_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `debit_note_items`
--
ALTER TABLE `debit_note_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debit_note_items_debit_note_id_foreign` (`debit_note_id`),
  ADD KEY `debit_note_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exchange_rates_from_currency_id_foreign` (`from_currency_id`),
  ADD KEY `exchange_rates_to_currency_id_foreign` (`to_currency_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `invoices_sale_id_foreign` (`sale_id`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_index` (`user_id`),
  ADD KEY `notifications_is_read_index` (`is_read`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_paymentable_type_id_index` (`paymentable_type`,`paymentable_id`),
  ADD KEY `payments_currency_id_foreign` (`currency_id`),
  ADD KEY `payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  ADD KEY `idx_permissions_group_name` (`group_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `products_barcode_unique` (`barcode`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_supplier_id_index` (`supplier_id`),
  ADD KEY `products_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `idx_products_deleted_at` (`deleted_at`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `purchase_orders_currency_id_foreign` (`currency_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `purchase_order_items_po_id_index` (`purchase_order_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `quotations_customer_id_foreign` (`customer_id`),
  ADD KEY `quotations_user_id_foreign` (`user_id`),
  ADD KEY `quotations_currency_id_foreign` (`currency_id`),
  ADD KEY `quotations_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  ADD KEY `quotation_items_product_id_foreign` (`product_id`),
  ADD KEY `quotation_items_variant_id_foreign` (`variant_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_group_index` (`group`),
  ADD KEY `settings_is_public_index` (`is_public`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_warehouse_unique` (`product_id`,`warehouse_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adjustment_number_unique` (`adjustment_number`),
  ADD KEY `stock_adjustments_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `stock_adjustments_user_id_foreign` (`user_id`);

--
-- Indexes for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_items_stock_adjustment_id_foreign` (`stock_adjustment_id`),
  ADD KEY `stock_adjustment_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `stock_movements_product_id_index` (`product_id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfer_number` (`transfer_number`),
  ADD UNIQUE KEY `stock_transfers_transfer_number_unique` (`transfer_number`),
  ADD KEY `stock_transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `stock_transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  ADD KEY `stock_transfers_user_id_foreign` (`user_id`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfer_items_stock_transfer_id_foreign` (`stock_transfer_id`),
  ADD KEY `stock_transfer_items_product_id_foreign` (`product_id`),
  ADD KEY `stock_transfer_items_variant_id_foreign` (`variant_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_tax_id` (`tax_id`),
  ADD KEY `idx_registration_number` (`registration_number`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_department_id_index` (`department_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `warehouses_code_unique` (`code`),
  ADD KEY `warehouses_manager_id_foreign` (`manager_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `credit_notes`
--
ALTER TABLE `credit_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_note_items`
--
ALTER TABLE `credit_note_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `debit_notes`
--
ALTER TABLE `debit_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debit_note_items`
--
ALTER TABLE `debit_note_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `businesses_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `credit_notes`
--
ALTER TABLE `credit_notes`
  ADD CONSTRAINT `credit_notes_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `credit_notes_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_notes_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `credit_note_items`
--
ALTER TABLE `credit_note_items`
  ADD CONSTRAINT `credit_note_items_credit_note_id_foreign` FOREIGN KEY (`credit_note_id`) REFERENCES `credit_notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_note_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `debit_notes`
--
ALTER TABLE `debit_notes`
  ADD CONSTRAINT `debit_notes_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `debit_notes_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `debit_notes_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debit_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `debit_note_items`
--
ALTER TABLE `debit_note_items`
  ADD CONSTRAINT `debit_note_items_debit_note_id_foreign` FOREIGN KEY (`debit_note_id`) REFERENCES `debit_notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `debit_note_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD CONSTRAINT `exchange_rates_from_currency_id_foreign` FOREIGN KEY (`from_currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exchange_rates_to_currency_id_foreign` FOREIGN KEY (`to_currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_purchase_order_items_po_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotation_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_stock_adjustment_id_foreign` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_movements_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_stock_movements_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD CONSTRAINT `stock_transfer_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfer_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
