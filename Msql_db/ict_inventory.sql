-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2026 at 12:12 PM
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
-- Table structure for table `asset_requests`
--

CREATE TABLE `asset_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `request_type` enum('new','replacement','upgrade') NOT NULL DEFAULT 'new',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `specifications` text DEFAULT NULL,
  `quantity_requested` int(11) NOT NULL DEFAULT 1,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `justification` text DEFAULT NULL,
  `status` enum('pending_ict_review','ict_reviewing','approved_by_ict','rejected_by_ict','sent_to_director','approved_by_director','rejected_by_director','sent_to_procurement','procurement_reviewing','specs_revision_needed','ready_for_purchase','ordered','received','under_inspection','inspection_passed','inspection_failed','assigned_to_user','completed') NOT NULL DEFAULT 'pending_ict_review',
  `assigned_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ict_notes` text DEFAULT NULL,
  `director_notes` text DEFAULT NULL,
  `procurement_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_requests`
--

INSERT INTO `asset_requests` (`id`, `user_id`, `category_id`, `request_type`, `title`, `description`, `specifications`, `quantity_requested`, `priority`, `justification`, `status`, `assigned_product_id`, `ict_notes`, `director_notes`, `procurement_notes`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 'replacement', 'Test', 'Test', 'It should have minimum 8GB Ram etc.', 1, 'medium', 'Test', 'specs_revision_needed', NULL, 'Specifications updated via amendment #1: It should have minimum 8GB Ram etc.', NULL, NULL, NULL, '2026-07-19 03:31:31', '2026-08-08 06:53:02'),
(2, 3, 1, 'upgrade', 'Test', 'Test', 'Test', 1, 'high', 'Test', 'inspection_failed', NULL, 'NA', NULL, NULL, NULL, '2026-07-19 03:31:31', '2026-08-06 17:50:19'),
(3, 1, 1, 'replacement', 'Test3', 'Test', NULL, 1, 'medium', NULL, 'rejected_by_ict', NULL, NULL, NULL, NULL, 'Low Stock Till Next Month', '2026-07-23 19:51:39', '2026-08-01 18:40:37'),
(4, 1, 1, 'replacement', 'Test3', 'Test', NULL, 1, 'medium', NULL, 'assigned_to_user', 1, 'N.A', NULL, NULL, NULL, '2026-07-23 19:56:29', '2026-08-01 18:31:35'),
(5, 1, 1, 'replacement', 'Test3', 'Test', NULL, 1, 'medium', NULL, 'sent_to_director', NULL, NULL, NULL, NULL, NULL, '2026-07-23 19:57:23', '2026-08-01 18:27:54'),
(6, 1, 1, 'replacement', 'Test3', 'NA', NULL, 1, 'medium', NULL, 'sent_to_director', NULL, NULL, NULL, NULL, NULL, '2026-07-23 20:04:10', '2026-08-09 07:08:06'),
(8, 1, 1, 'replacement', 'Test', 'N/A', 'N/A', 1, 'urgent', NULL, 'sent_to_director', NULL, NULL, NULL, NULL, NULL, '2026-08-01 18:43:09', '2026-08-07 10:17:10'),
(9, 1, 1, 'replacement', 'Test5', 'My Laptop need battery replacement.', NULL, 1, 'low', NULL, 'sent_to_director', NULL, NULL, NULL, NULL, NULL, '2026-08-01 18:53:43', '2026-08-01 21:31:27'),
(10, 7, NULL, 'new', 'Test 1', 'N.A', NULL, 1, 'medium', NULL, 'assigned_to_user', NULL, NULL, NULL, NULL, NULL, '2026-08-02 02:51:10', '2026-08-05 10:55:07'),
(11, 7, NULL, 'replacement', 'TTEST', 'NA', NULL, 1, 'medium', NULL, 'assigned_to_user', 3, NULL, NULL, NULL, NULL, '2026-08-02 08:24:21', '2026-08-07 10:15:45'),
(12, 1, 1, 'new', 'Test', 'Not Applicable', NULL, 1, 'medium', NULL, 'assigned_to_user', NULL, NULL, NULL, NULL, NULL, '2026-08-06 08:55:53', '2026-08-07 10:15:07'),
(13, 3, 1, 'new', 'Test', 'I need this to use for de complition of my tasks', NULL, 1, 'medium', NULL, 'assigned_to_user', 4, NULL, NULL, NULL, NULL, '2026-08-09 01:32:13', '2026-08-09 01:32:51'),
(14, 1, 1, 'new', 'test4', 'NA', NULL, 1, 'medium', NULL, 'pending_ict_review', NULL, NULL, NULL, NULL, NULL, '2026-08-10 07:25:28', '2026-08-10 07:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `condition_out` enum('new','good','fair','poor') DEFAULT NULL,
  `condition_in` enum('good','fair','poor','damaged') DEFAULT NULL,
  `assigned_license_key` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `product_id`, `user_id`, `assigned_by`, `assigned_date`, `expected_return_date`, `actual_return_date`, `condition_out`, `condition_in`, `assigned_license_key`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-08-01', NULL, NULL, 'good', NULL, NULL, 'N.A', '2026-08-01 18:31:35', '2026-08-01 18:31:35'),
(2, 1, 7, 1, '2026-08-02', NULL, NULL, 'new', NULL, NULL, NULL, '2026-08-02 08:34:57', '2026-08-02 08:34:57'),
(3, 1, 1, 1, '2026-08-06', NULL, NULL, 'fair', NULL, NULL, NULL, '2026-08-06 17:10:56', '2026-08-06 17:10:56'),
(4, 3, 7, 1, '2026-08-07', NULL, NULL, 'good', NULL, NULL, 'Assigned from request #11', '2026-08-07 10:15:45', '2026-08-07 10:15:45'),
(5, 4, 3, 1, '2026-08-09', '2026-08-27', NULL, 'good', NULL, NULL, NULL, '2026-08-09 01:32:51', '2026-08-09 01:58:01');

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
('laravel-cache-settings_all', 'a:28:{s:8:\"app_name\";s:16:\"\"\\\"ICT SYSTEM\\\"\"\";s:8:\"timezone\";s:17:\"\"Africa\\/Abidjan\"\";s:6:\"locale\";s:4:\"\"en\"\";s:8:\"currency\";s:5:\"\"TZS\"\";s:11:\"date_format\";s:9:\"\"m\\/d\\/Y\"\";s:11:\"time_format\";s:7:\"\"H:i:s\"\";s:14:\"items_per_page\";s:4:\"\"15\"\";s:19:\"enable_registration\";s:3:\"\"1\"\";s:16:\"maintenance_mode\";s:3:\"\"1\"\";s:11:\"mail_mailer\";s:5:\"\"log\"\";s:9:\"mail_host\";s:11:\"\"127.0.0.1\"\";s:9:\"mail_port\";s:6:\"\"2525\"\";s:17:\"mail_from_address\";s:15:\"\"test@mail.com\"\";s:14:\"mail_from_name\";s:12:\"\"ICT STSTEM\"\";s:15:\"mail_encryption\";s:5:\"\"ssl\"\";s:13:\"mail_username\";N;s:13:\"mail_password\";N;s:16:\"session_lifetime\";s:5:\"\"120\"\";s:19:\"password_min_length\";s:3:\"\"8\"\";s:11:\"require_2fa\";s:3:\"\"1\"\";s:18:\"max_login_attempts\";s:3:\"\"5\"\";s:16:\"lockout_duration\";s:4:\"\"15\"\";s:23:\"session_expire_on_close\";s:3:\"\"1\"\";s:20:\"password_expiry_days\";s:4:\"\"90\"\";s:16:\"remember_me_days\";s:4:\"\"30\"\";s:16:\"backup_frequency\";s:7:\"\"daily\"\";s:11:\"backup_time\";s:7:\"\"00:00\"\";s:16:\"backup_keep_days\";s:4:\"\"30\"\";}', 1786285773),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"group_name\";s:1:\"d\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:210:{i:0;a:5:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:9:\"Dashboard\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:5:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"dashboard.edit\";s:1:\"c\";s:9:\"Dashboard\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:5:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"user.view\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:5:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"user.create\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:5:{s:1:\"a\";i:5;s:1:\"b\";s:9:\"user.edit\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:5:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"user.delete\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:5:{s:1:\"a\";i:7;s:1:\"b\";s:9:\"role.view\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:5:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"role.create\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:5:{s:1:\"a\";i:9;s:1:\"b\";s:9:\"role.edit\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:5:{s:1:\"a\";i:10;s:1:\"b\";s:11:\"role.delete\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:5:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"department.view\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:5:{s:1:\"a\";i:12;s:1:\"b\";s:17:\"department.create\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:5:{s:1:\"a\";i:13;s:1:\"b\";s:15:\"department.edit\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:5:{s:1:\"a\";i:14;s:1:\"b\";s:17:\"department.delete\";s:1:\"c\";s:10:\"Department\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:5:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"category.view\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:5:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"category.create\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:5:{s:1:\"a\";i:17;s:1:\"b\";s:13:\"category.edit\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:5:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"category.delete\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:5:{s:1:\"a\";i:19;s:1:\"b\";s:13:\"supplier.view\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:5:{s:1:\"a\";i:20;s:1:\"b\";s:15:\"supplier.create\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:5:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"supplier.edit\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:5:{s:1:\"a\";i:22;s:1:\"b\";s:15:\"supplier.delete\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:5:{s:1:\"a\";i:23;s:1:\"b\";s:12:\"product.view\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:5:{s:1:\"a\";i:24;s:1:\"b\";s:14:\"product.create\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:5:{s:1:\"a\";i:25;s:1:\"b\";s:12:\"product.edit\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:5:{s:1:\"a\";i:26;s:1:\"b\";s:14:\"product.delete\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:5:{s:1:\"a\";i:27;s:1:\"b\";s:15:\"assignment.view\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:5:{s:1:\"a\";i:28;s:1:\"b\";s:17:\"assignment.create\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:5:{s:1:\"a\";i:29;s:1:\"b\";s:15:\"assignment.edit\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:5:{s:1:\"a\";i:30;s:1:\"b\";s:17:\"assignment.delete\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:5:{s:1:\"a\";i:31;s:1:\"b\";s:10:\"stock.view\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:5:{s:1:\"a\";i:32;s:1:\"b\";s:12:\"stock.create\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:5:{s:1:\"a\";i:33;s:1:\"b\";s:19:\"purchase_order.view\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:5:{s:1:\"a\";i:34;s:1:\"b\";s:21:\"purchase_order.create\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:5:{s:1:\"a\";i:35;s:1:\"b\";s:19:\"purchase_order.edit\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:5:{s:1:\"a\";i:36;s:1:\"b\";s:21:\"purchase_order.delete\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:5:{s:1:\"a\";i:37;s:1:\"b\";s:16:\"maintenance.view\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:5:{s:1:\"a\";i:38;s:1:\"b\";s:18:\"maintenance.create\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:5:{s:1:\"a\";i:39;s:1:\"b\";s:16:\"maintenance.edit\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:5:{s:1:\"a\";i:40;s:1:\"b\";s:18:\"asset_request.view\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:40;a:5:{s:1:\"a\";i:41;s:1:\"b\";s:20:\"asset_request.create\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:41;a:5:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"asset_request.edit\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:42;a:5:{s:1:\"a\";i:43;s:1:\"b\";s:20:\"asset_request.delete\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:43;a:5:{s:1:\"a\";i:44;s:1:\"b\";s:20:\"asset_request.review\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:44;a:5:{s:1:\"a\";i:45;s:1:\"b\";s:21:\"asset_request.approve\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:45;a:5:{s:1:\"a\";i:46;s:1:\"b\";s:20:\"asset_request.reject\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:46;a:5:{s:1:\"a\";i:47;s:1:\"b\";s:33:\"asset_request.forward_to_director\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:47;a:5:{s:1:\"a\";i:48;s:1:\"b\";s:36:\"asset_request.forward_to_procurement\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:8;i:2;i:9;}}i:48;a:5:{s:1:\"a\";i:49;s:1:\"b\";s:21:\"asset_request.specify\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:8;}}i:49;a:5:{s:1:\"a\";i:50;s:1:\"b\";s:16:\"procurement.view\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:5:{s:1:\"a\";i:51;s:1:\"b\";s:18:\"procurement.create\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:51;a:5:{s:1:\"a\";i:52;s:1:\"b\";s:16:\"procurement.edit\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:5:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"inspection.view\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:53;a:5:{s:1:\"a\";i:54;s:1:\"b\";s:17:\"inspection.create\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:54;a:5:{s:1:\"a\";i:55;s:1:\"b\";s:15:\"inspection.edit\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:55;a:5:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"inspection.pass\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:56;a:5:{s:1:\"a\";i:57;s:1:\"b\";s:15:\"inspection.fail\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:5:{s:1:\"a\";i:58;s:1:\"b\";s:19:\"direct_request.view\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:5:{s:1:\"a\";i:59;s:1:\"b\";s:21:\"direct_request.create\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:5:{s:1:\"a\";i:60;s:1:\"b\";s:19:\"direct_request.edit\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:5:{s:1:\"a\";i:61;s:1:\"b\";s:17:\"notification.view\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:5:{s:1:\"a\";i:62;s:1:\"b\";s:11:\"report.view\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:5:{s:1:\"a\";i:63;s:1:\"b\";s:12:\"user.approve\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:5:{s:1:\"a\";i:64;s:1:\"b\";s:12:\"user.suspend\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:5:{s:1:\"a\";i:65;s:1:\"b\";s:15:\"user.reactivate\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:5:{s:1:\"a\";i:66;s:1:\"b\";s:10:\"user.block\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:5:{s:1:\"a\";i:67;s:1:\"b\";s:19:\"user.reset_password\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:5:{s:1:\"a\";i:68;s:1:\"b\";s:11:\"user.export\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:5:{s:1:\"a\";i:69;s:1:\"b\";s:11:\"user.import\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:69;a:5:{s:1:\"a\";i:70;s:1:\"b\";s:16:\"user.assign_role\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:70;a:5:{s:1:\"a\";i:71;s:1:\"b\";s:16:\"user.remove_role\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:71;a:5:{s:1:\"a\";i:72;s:1:\"b\";s:23:\"user.manage_permissions\";s:1:\"c\";s:4:\"User\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:72;a:5:{s:1:\"a\";i:73;s:1:\"b\";s:23:\"role.assign_permissions\";s:1:\"c\";s:4:\"Role\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:73;a:5:{s:1:\"a\";i:74;s:1:\"b\";s:15:\"category.export\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:74;a:5:{s:1:\"a\";i:75;s:1:\"b\";s:15:\"category.import\";s:1:\"c\";s:8:\"Category\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:75;a:5:{s:1:\"a\";i:76;s:1:\"b\";s:15:\"supplier.export\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:76;a:5:{s:1:\"a\";i:77;s:1:\"b\";s:15:\"supplier.import\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:77;a:5:{s:1:\"a\";i:78;s:1:\"b\";s:16:\"supplier.approve\";s:1:\"c\";s:8:\"Supplier\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:78;a:5:{s:1:\"a\";i:79;s:1:\"b\";s:14:\"product.export\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:79;a:5:{s:1:\"a\";i:80;s:1:\"b\";s:14:\"product.import\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:80;a:5:{s:1:\"a\";i:81;s:1:\"b\";s:19:\"product.bulk_delete\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:81;a:5:{s:1:\"a\";i:82;s:1:\"b\";s:19:\"product.bulk_export\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:82;a:5:{s:1:\"a\";i:83;s:1:\"b\";s:21:\"product.view_hardware\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:83;a:5:{s:1:\"a\";i:84;s:1:\"b\";s:21:\"product.view_software\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:84;a:5:{s:1:\"a\";i:85;s:1:\"b\";s:23:\"product.manage_hardware\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:85;a:5:{s:1:\"a\";i:86;s:1:\"b\";s:23:\"product.manage_software\";s:1:\"c\";s:7:\"Product\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:86;a:5:{s:1:\"a\";i:87;s:1:\"b\";s:17:\"assignment.return\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:87;a:5:{s:1:\"a\";i:88;s:1:\"b\";s:22:\"assignment.bulk_create\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:88;a:5:{s:1:\"a\";i:89;s:1:\"b\";s:22:\"assignment.bulk_return\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:89;a:5:{s:1:\"a\";i:90;s:1:\"b\";s:23:\"assignment.view_history\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:90;a:5:{s:1:\"a\";i:91;s:1:\"b\";s:17:\"assignment.export\";s:1:\"c\";s:10:\"Assignment\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:91;a:5:{s:1:\"a\";i:92;s:1:\"b\";s:12:\"stock.adjust\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:92;a:5:{s:1:\"a\";i:93;s:1:\"b\";s:12:\"stock.export\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:93;a:5:{s:1:\"a\";i:94;s:1:\"b\";s:12:\"stock.report\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:94;a:5:{s:1:\"a\";i:95;s:1:\"b\";s:11:\"stock.audit\";s:1:\"c\";s:5:\"Stock\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:95;a:5:{s:1:\"a\";i:96;s:1:\"b\";s:21:\"purchase_order.submit\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:96;a:5:{s:1:\"a\";i:97;s:1:\"b\";s:22:\"purchase_order.approve\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:97;a:5:{s:1:\"a\";i:98;s:1:\"b\";s:21:\"purchase_order.reject\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:98;a:5:{s:1:\"a\";i:99;s:1:\"b\";s:22:\"purchase_order.receive\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:99;a:5:{s:1:\"a\";i:100;s:1:\"b\";s:21:\"purchase_order.export\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:100;a:5:{s:1:\"a\";i:101;s:1:\"b\";s:21:\"purchase_order.import\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:101;a:5:{s:1:\"a\";i:102;s:1:\"b\";s:26:\"purchase_order.bulk_delete\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:102;a:5:{s:1:\"a\";i:103;s:1:\"b\";s:27:\"purchase_order.bulk_approve\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:103;a:5:{s:1:\"a\";i:104;s:1:\"b\";s:27:\"purchase_order.download_pdf\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:104;a:5:{s:1:\"a\";i:105;s:1:\"b\";s:20:\"purchase_order.print\";s:1:\"c\";s:14:\"Purchase Order\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:105;a:5:{s:1:\"a\";i:106;s:1:\"b\";s:18:\"maintenance.delete\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:106;a:5:{s:1:\"a\";i:107;s:1:\"b\";s:19:\"maintenance.approve\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:107;a:5:{s:1:\"a\";i:108;s:1:\"b\";s:18:\"maintenance.reject\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:108;a:5:{s:1:\"a\";i:109;s:1:\"b\";s:18:\"maintenance.repair\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:109;a:5:{s:1:\"a\";i:110;s:1:\"b\";s:20:\"maintenance.complete\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:110;a:5:{s:1:\"a\";i:111;s:1:\"b\";s:18:\"maintenance.export\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:111;a:5:{s:1:\"a\";i:112;s:1:\"b\";s:18:\"maintenance.import\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:112;a:5:{s:1:\"a\";i:113;s:1:\"b\";s:20:\"maintenance.download\";s:1:\"c\";s:11:\"Maintenance\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:113;a:5:{s:1:\"a\";i:114;s:1:\"b\";s:20:\"asset_request.cancel\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:114;a:5:{s:1:\"a\";i:115;s:1:\"b\";s:20:\"asset_request.export\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:115;a:5:{s:1:\"a\";i:116;s:1:\"b\";s:20:\"asset_request.import\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:116;a:5:{s:1:\"a\";i:117;s:1:\"b\";s:25:\"asset_request.bulk_delete\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:117;a:5:{s:1:\"a\";i:118;s:1:\"b\";s:26:\"asset_request.bulk_approve\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:118;a:5:{s:1:\"a\";i:119;s:1:\"b\";s:25:\"asset_request.bulk_reject\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:119;a:5:{s:1:\"a\";i:120;s:1:\"b\";s:30:\"asset_request.view_my_requests\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:120;a:5:{s:1:\"a\";i:121;s:1:\"b\";s:31:\"asset_request.view_all_requests\";s:1:\"c\";s:13:\"Asset Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:121;a:5:{s:1:\"a\";i:122;s:1:\"b\";s:18:\"procurement.delete\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:122;a:5:{s:1:\"a\";i:123;s:1:\"b\";s:19:\"procurement.approve\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:123;a:5:{s:1:\"a\";i:124;s:1:\"b\";s:18:\"procurement.reject\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:124;a:5:{s:1:\"a\";i:125;s:1:\"b\";s:19:\"procurement.receive\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:125;a:5:{s:1:\"a\";i:126;s:1:\"b\";s:18:\"procurement.export\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:126;a:5:{s:1:\"a\";i:127;s:1:\"b\";s:18:\"procurement.import\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:127;a:5:{s:1:\"a\";i:128;s:1:\"b\";s:23:\"procurement.bulk_delete\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:128;a:5:{s:1:\"a\";i:129;s:1:\"b\";s:24:\"procurement.bulk_approve\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:129;a:5:{s:1:\"a\";i:130;s:1:\"b\";s:24:\"procurement.download_pdf\";s:1:\"c\";s:11:\"Procurement\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:130;a:5:{s:1:\"a\";i:131;s:1:\"b\";s:17:\"inspection.delete\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:131;a:5:{s:1:\"a\";i:132;s:1:\"b\";s:17:\"inspection.export\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:132;a:5:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"inspection.import\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:133;a:5:{s:1:\"a\";i:134;s:1:\"b\";s:29:\"inspection.return_to_supplier\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:134;a:5:{s:1:\"a\";i:135;s:1:\"b\";s:20:\"inspection.bulk_pass\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:135;a:5:{s:1:\"a\";i:136;s:1:\"b\";s:20:\"inspection.bulk_fail\";s:1:\"c\";s:10:\"Inspection\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:136;a:5:{s:1:\"a\";i:137;s:1:\"b\";s:21:\"direct_request.delete\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:137;a:5:{s:1:\"a\";i:138;s:1:\"b\";s:22:\"direct_request.approve\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:138;a:5:{s:1:\"a\";i:139;s:1:\"b\";s:21:\"direct_request.reject\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:139;a:5:{s:1:\"a\";i:140;s:1:\"b\";s:29:\"direct_request.forward_to_ict\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:140;a:5:{s:1:\"a\";i:141;s:1:\"b\";s:37:\"direct_request.forward_to_procurement\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:141;a:5:{s:1:\"a\";i:142;s:1:\"b\";s:31:\"direct_request.merge_to_request\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:142;a:5:{s:1:\"a\";i:143;s:1:\"b\";s:21:\"direct_request.export\";s:1:\"c\";s:14:\"Direct Request\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:143;a:5:{s:1:\"a\";i:144;s:1:\"b\";s:19:\"notification.create\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:144;a:5:{s:1:\"a\";i:145;s:1:\"b\";s:22:\"notification.mark_read\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:145;a:5:{s:1:\"a\";i:146;s:1:\"b\";s:24:\"notification.mark_unread\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:146;a:5:{s:1:\"a\";i:147;s:1:\"b\";s:26:\"notification.mark_all_read\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:147;a:5:{s:1:\"a\";i:148;s:1:\"b\";s:19:\"notification.delete\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:148;a:5:{s:1:\"a\";i:149;s:1:\"b\";s:24:\"notification.bulk_delete\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:149;a:5:{s:1:\"a\";i:150;s:1:\"b\";s:19:\"notification.export\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:150;a:5:{s:1:\"a\";i:151;s:1:\"b\";s:21:\"notification.settings\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:151;a:5:{s:1:\"a\";i:152;s:1:\"b\";s:22:\"notification.configure\";s:1:\"c\";s:12:\"Notification\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:152;a:5:{s:1:\"a\";i:153;s:1:\"b\";s:15:\"report.generate\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:153;a:5:{s:1:\"a\";i:154;s:1:\"b\";s:13:\"report.export\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:154;a:5:{s:1:\"a\";i:155;s:1:\"b\";s:12:\"report.print\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:155;a:5:{s:1:\"a\";i:156;s:1:\"b\";s:15:\"report.schedule\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:156;a:5:{s:1:\"a\";i:157;s:1:\"b\";s:16:\"report.inventory\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:157;a:5:{s:1:\"a\";i:158;s:1:\"b\";s:17:\"report.assignment\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:158;a:5:{s:1:\"a\";i:159;s:1:\"b\";s:14:\"report.request\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:159;a:5:{s:1:\"a\";i:160;s:1:\"b\";s:18:\"report.procurement\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:160;a:5:{s:1:\"a\";i:161;s:1:\"b\";s:18:\"report.maintenance\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:161;a:5:{s:1:\"a\";i:162;s:1:\"b\";s:16:\"report.financial\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:162;a:5:{s:1:\"a\";i:163;s:1:\"b\";s:13:\"report.custom\";s:1:\"c\";s:6:\"Report\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:163;a:5:{s:1:\"a\";i:164;s:1:\"b\";s:13:\"settings.view\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:164;a:5:{s:1:\"a\";i:165;s:1:\"b\";s:13:\"settings.edit\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:165;a:5:{s:1:\"a\";i:166;s:1:\"b\";s:15:\"settings.manage\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:166;a:5:{s:1:\"a\";i:167;s:1:\"b\";s:22:\"settings.system_config\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:167;a:5:{s:1:\"a\";i:168;s:1:\"b\";s:21:\"settings.email_config\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:168;a:5:{s:1:\"a\";i:169;s:1:\"b\";s:15:\"settings.backup\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:169;a:5:{s:1:\"a\";i:170;s:1:\"b\";s:19:\"settings.audit_logs\";s:1:\"c\";s:8:\"Settings\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:170;a:5:{s:1:\"a\";i:171;s:1:\"b\";s:12:\"license.view\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:171;a:5:{s:1:\"a\";i:172;s:1:\"b\";s:14:\"license.create\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:172;a:5:{s:1:\"a\";i:173;s:1:\"b\";s:12:\"license.edit\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:173;a:5:{s:1:\"a\";i:174;s:1:\"b\";s:14:\"license.delete\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:174;a:5:{s:1:\"a\";i:175;s:1:\"b\";s:14:\"license.assign\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:175;a:5:{s:1:\"a\";i:176;s:1:\"b\";s:14:\"license.revoke\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:176;a:5:{s:1:\"a\";i:177;s:1:\"b\";s:14:\"license.export\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:177;a:5:{s:1:\"a\";i:178;s:1:\"b\";s:14:\"license.import\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:178;a:5:{s:1:\"a\";i:179;s:1:\"b\";s:13:\"license.renew\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:179;a:5:{s:1:\"a\";i:180;s:1:\"b\";s:20:\"license.view_expired\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:180;a:5:{s:1:\"a\";i:181;s:1:\"b\";s:22:\"license.view_available\";s:1:\"c\";s:7:\"License\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:181;a:5:{s:1:\"a\";i:182;s:1:\"b\";s:17:\"activity_log.view\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:182;a:5:{s:1:\"a\";i:183;s:1:\"b\";s:19:\"activity_log.export\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:183;a:5:{s:1:\"a\";i:184;s:1:\"b\";s:19:\"activity_log.delete\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:184;a:5:{s:1:\"a\";i:185;s:1:\"b\";s:18:\"activity_log.clear\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:9;}}i:185;a:5:{s:1:\"a\";i:186;s:1:\"b\";s:19:\"activity_log.filter\";s:1:\"c\";s:12:\"Activity Log\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:9;}}i:186;a:5:{s:1:\"a\";i:187;s:1:\"b\";s:10:\"api.access\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:187;a:5:{s:1:\"a\";i:188;s:1:\"b\";s:17:\"api.manage_tokens\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:188;a:5:{s:1:\"a\";i:189;s:1:\"b\";s:17:\"api.revoke_tokens\";s:1:\"c\";s:3:\"API\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:9;}}i:189;a:5:{s:1:\"a\";i:190;s:1:\"b\";s:11:\"backup.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:190;a:5:{s:1:\"a\";i:191;s:1:\"b\";s:13:\"backup.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:191;a:5:{s:1:\"a\";i:192;s:1:\"b\";s:13:\"backup.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:192;a:5:{s:1:\"a\";i:193;s:1:\"b\";s:15:\"backup.download\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:193;a:5:{s:1:\"a\";i:194;s:1:\"b\";s:14:\"backup.restore\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:194;a:5:{s:1:\"a\";i:195;s:1:\"b\";s:15:\"backup.schedule\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:195;a:5:{s:1:\"a\";i:196;s:1:\"b\";s:16:\"backup.configure\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:196;a:5:{s:1:\"a\";i:197;s:1:\"b\";s:13:\"facility.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:197;a:5:{s:1:\"a\";i:198;s:1:\"b\";s:15:\"facility.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:198;a:5:{s:1:\"a\";i:199;s:1:\"b\";s:13:\"facility.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:199;a:5:{s:1:\"a\";i:200;s:1:\"b\";s:15:\"facility.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:200;a:5:{s:1:\"a\";i:201;s:1:\"b\";s:15:\"facility.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:201;a:5:{s:1:\"a\";i:202;s:1:\"b\";s:15:\"facility.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:202;a:5:{s:1:\"a\";i:203;s:1:\"b\";s:15:\"facility.verify\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:203;a:5:{s:1:\"a\";i:204;s:1:\"b\";s:12:\"sponsor.view\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:204;a:5:{s:1:\"a\";i:205;s:1:\"b\";s:14:\"sponsor.create\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:205;a:5:{s:1:\"a\";i:206;s:1:\"b\";s:12:\"sponsor.edit\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:206;a:5:{s:1:\"a\";i:207;s:1:\"b\";s:14:\"sponsor.delete\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:207;a:5:{s:1:\"a\";i:208;s:1:\"b\";s:14:\"sponsor.export\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:208;a:5:{s:1:\"a\";i:209;s:1:\"b\";s:14:\"sponsor.import\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:209;a:5:{s:1:\"a\";i:210;s:1:\"b\";s:14:\"sponsor.verify\";s:1:\"c\";N;s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"d\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:18:\"Operations manager\";s:1:\"d\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:2:\"HR\";s:1:\"d\";s:3:\"web\";}}}', 1786443851),
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
-- Table structure for table `direct_requests`
--

CREATE TABLE `direct_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `asset_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `director_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `urgency` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending_director','director_approved','director_rejected','sent_to_ict','ict_specified','sent_to_director_back','director_sent_to_procurement','merged_to_request','completed') NOT NULL DEFAULT 'pending_director',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `director_notes` text DEFAULT NULL,
  `ict_notes` text DEFAULT NULL,
  `specifications_added` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `direct_requests`
--

INSERT INTO `direct_requests` (`id`, `user_id`, `asset_request_id`, `director_id`, `subject`, `description`, `urgency`, `category_id`, `status`, `deleted_at`, `director_notes`, `ict_notes`, `specifications_added`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'TEST', 'N.A', 'medium', NULL, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 00:10:26', '2026-08-08 00:10:26'),
(2, 1, NULL, NULL, 'Test', 'Test', 'medium', NULL, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 01:18:46', '2026-08-08 01:18:46'),
(3, 1, NULL, NULL, 'Test', 'N.A', 'medium', NULL, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 01:37:22', '2026-08-08 01:37:22'),
(4, 1, NULL, NULL, 'TEST2', 'N.A', 'medium', NULL, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 01:39:38', '2026-08-08 01:39:38'),
(5, 1, NULL, 1, 'TEST3', 'NA', 'medium', NULL, 'director_rejected', NULL, 'nnnnn', NULL, NULL, '2026-08-08 01:47:31', '2026-08-08 04:38:05'),
(6, 1, NULL, 1, 'Test4', 'NA', 'medium', NULL, 'director_approved', NULL, NULL, NULL, NULL, '2026-08-08 01:56:40', '2026-08-08 02:42:15'),
(7, 1, NULL, 1, 'Test5', 'Not Applicable', 'medium', NULL, 'director_rejected', NULL, 'nn', NULL, NULL, '2026-08-08 02:44:32', '2026-08-08 04:36:29'),
(8, 1, NULL, NULL, 'Test5', 'Not Applicable', 'medium', NULL, 'pending_director', '2026-08-08 04:23:37', NULL, NULL, NULL, '2026-08-08 02:44:40', '2026-08-08 04:23:37'),
(9, 1, NULL, NULL, 'Test5', 'TEST', 'medium', 1, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 02:49:21', '2026-08-08 02:49:21'),
(10, 1, NULL, NULL, 'undefined', 'undefined', 'low', 1, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 02:51:27', '2026-08-08 03:03:54'),
(11, 1, NULL, 1, 'GOSUSNH22654398', 'NA', 'medium', 1, 'director_rejected', NULL, 'NARTHRTH', NULL, NULL, '2026-08-08 03:05:43', '2026-08-08 04:30:21'),
(12, 1, NULL, NULL, 'Test6', 'Not Applicable', 'medium', 2, 'pending_director', NULL, NULL, NULL, NULL, '2026-08-08 04:06:02', '2026-08-08 04:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('hospital','school','office','warehouse','other') NOT NULL DEFAULT 'other',
  `category` varchar(100) DEFAULT NULL,
  `sub_category` varchar(100) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','pending','suspended') NOT NULL DEFAULT 'active',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `type`, `category`, `sub_category`, `code`, `email`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `latitude`, `longitude`, `website`, `contact_person`, `contact_phone`, `contact_email`, `description`, `status`, `is_verified`, `verified_at`, `verified_by`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dever Web', 'other', NULL, NULL, 'OTH-DEVE-2912', 'webdevercyber@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, 1, NULL, '2026-08-06 17:08:57', '2026-08-06 17:08:57', NULL),
(2, 'Dever Web', 'other', NULL, NULL, 'OTH-DEVE-49E3', 'webdevercyber@gmail.com', '094726482748', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, 1, 1, '2026-08-06 17:08:58', '2026-08-08 05:34:40', NULL),
(3, 'ABDALA MAHAJA', 'hospital', NULL, NULL, 'HOS-ABDA-E611', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, '2026-08-08 05:36:40', 1, NULL, 1, NULL, '2026-08-08 05:36:40', '2026-08-08 05:36:40', NULL);

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
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_request_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `inspected_by` bigint(20) UNSIGNED NOT NULL,
  `inspection_date` date NOT NULL,
  `result` enum('passed','failed','partial') NOT NULL DEFAULT 'passed',
  `physical_condition` enum('new','excellent','good','fair','poor','damaged') NOT NULL,
  `functional_condition` enum('working','minor_issues','major_issues','non_functional') NOT NULL,
  `issues_found` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `is_returned` tinyint(1) NOT NULL DEFAULT 0,
  `return_reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `returned_to_supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspections`
--

INSERT INTO `inspections` (`id`, `asset_request_id`, `product_id`, `inspected_by`, `inspection_date`, `result`, `physical_condition`, `functional_condition`, `issues_found`, `recommendations`, `is_returned`, `return_reason`, `return_date`, `returned_to_supplier_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2026-08-06', 'failed', 'poor', 'working', 'NA', NULL, 0, NULL, NULL, NULL, '2026-08-06 17:50:19', '2026-08-09 05:46:09');

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
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('reported','in_progress','fixed','unrepairable') NOT NULL DEFAULT 'reported',
  `cost` decimal(12,2) DEFAULT NULL,
  `repaired_by` varchar(100) DEFAULT NULL,
  `repair_date` date DEFAULT NULL,
  `resolved_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `product_id`, `user_id`, `issue_description`, `priority`, `status`, `cost`, `repaired_by`, `repair_date`, `resolved_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'test', 'high', 'fixed', 600.00, NULL, '2026-08-02', '2026-08-01', '2026-07-19 12:20:49', '2026-08-01 20:55:46'),
(2, 1, 1, 'Need To Be Repaired', 'medium', 'reported', NULL, 'NA', '2026-08-04', NULL, '2026-08-01 20:50:59', '2026-08-01 20:50:59'),
(3, 3, 1, 'Not Applicable', 'low', 'reported', NULL, NULL, NULL, NULL, '2026-08-02 08:42:08', '2026-08-09 05:43:47');

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
(11, 1, 'assignment', 'Your asset request #12 has been assigned to user', 0, 'http://127.0.0.1:8000/user/asset-requests/12', '2026-08-07 13:15:07'),
(12, 7, 'assignment', 'Asset \"Test5\" has been assigned to you.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-07 13:15:45'),
(13, 1, 'assignment', 'Your asset request #8 has been sent to director', 0, 'http://127.0.0.1:8000/user/asset-requests/8', '2026-08-07 13:17:10'),
(14, 3, 'assignment', 'Specifications for your request #1 have been approved and updated.', 0, 'http://127.0.0.1:8000/user/asset-requests/1', '2026-08-08 03:00:54'),
(15, 1, 'assignment', 'New direct request from Super Admin: Test6', 0, 'http://127.0.0.1:8000/admin/direct-requests/12', '2026-08-08 07:06:02'),
(16, 1, 'assignment', 'Your direct request has been rejected by Super Admin', 0, 'http://127.0.0.1:8000/admin/direct-requests/5', '2026-08-08 07:38:05'),
(17, 4, 'assignment', 'Specification revision requested for request #1: Just a Simple Test', 0, 'http://127.0.0.1:8000/admin/specification-amendments/2', '2026-08-08 09:53:02'),
(18, 6, 'assignment', 'Specification revision requested for request #1: Just a Simple Test', 0, 'http://127.0.0.1:8000/admin/specification-amendments/2', '2026-08-08 09:53:02'),
(19, 3, 'assignment', 'Asset \"Test\" has been assigned to you.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 04:32:51'),
(20, 1, 'assignment', 'Purchase Order PO-20260405-720 has been partially/fully received', 0, 'http://127.0.0.1:8000/admin/purchase-orders/2', '2026-08-09 05:07:28'),
(21, 1, 'assignment', 'Purchase Order PO-20260809-0001 has been partially/fully received', 0, 'http://127.0.0.1:8000/admin/purchase-orders/3', '2026-08-09 05:26:51'),
(22, 2, 'assignment', 'You have been assigned a license for Test (License Key: MS-001-2024)', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 07:31:20'),
(23, 2, 'assignment', 'Your license for Test has been revoked.', 0, 'http://127.0.0.1:8000/user/my-assignments', '2026-08-09 07:43:04'),
(24, 5, 'assignment', 'Asset request #6 needs your review', 0, 'http://127.0.0.1:8000/admin/asset-requests/6', '2026-08-09 10:08:07'),
(25, 4, 'assignment', 'New asset request #14 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/14', '2026-08-10 10:25:28'),
(26, 6, 'assignment', 'New asset request #14 submitted by Super Admin', 0, 'http://127.0.0.1:8000/admin/asset-requests/14', '2026-08-10 10:25:28');

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
(210, 'sponsor.verify', NULL, 'web', '2026-08-06 16:19:08', '2026-08-06 16:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `procurements`
--

CREATE TABLE `procurements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_request_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `procurement_officer_id` bigint(20) UNSIGNED NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery` date DEFAULT NULL,
  `actual_delivery` date DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `delivery_status` enum('pending','ordered','shipped','delivered','partial_delivery','returned_to_supplier') NOT NULL DEFAULT 'pending',
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','partial','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('hardware','software') NOT NULL DEFAULT 'hardware',
  `serial_number` varchar(100) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `facility_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sponsor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `device_code` varchar(255) DEFAULT NULL,
  `facility_administrator` varchar(255) DEFAULT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `specs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specs`)),
  `location` varchar(100) DEFAULT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `license_type` enum('perpetual','subscription','trial') DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL,
  `max_licenses` int(11) DEFAULT NULL,
  `available_licenses` int(11) DEFAULT NULL,
  `software_version` varchar(50) DEFAULT NULL,
  `vendor_website` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `current_value` decimal(12,2) DEFAULT NULL,
  `status` enum('available','assigned','under_repair','retired','expired') NOT NULL DEFAULT 'available',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `min_stock_level` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `name`, `sku`, `description`, `type`, `serial_number`, `user_id`, `facility_id`, `sponsor_id`, `device_code`, `facility_administrator`, `asset_tag`, `specs`, `location`, `license_key`, `license_type`, `license_expiry_date`, `max_licenses`, `available_licenses`, `software_version`, `vendor_website`, `purchase_date`, `office`, `brand`, `model`, `warranty_end_date`, `purchase_price`, `current_value`, `status`, `quantity`, `min_stock_level`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Test', 'test', NULL, 'software', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'under_repair', 2, 7, '2026-07-19 12:13:56', '2026-08-09 04:43:04', NULL),
(3, 1, 1, 'Test5', 'SKUTR', NULL, 'hardware', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 600000.00, NULL, 'under_repair', 50, 45, '2026-08-01 19:00:17', '2026-08-09 05:43:47', NULL),
(4, 1, 1, 'Test', '7998TR', NULL, 'software', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'DELL', 'Latitude 3420', NULL, NULL, NULL, 'assigned', 3, 0, '2026-08-08 05:23:30', '2026-08-09 08:08:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `status` enum('draft','ordered','received','cancelled') NOT NULL DEFAULT 'draft',
  `total_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `user_id`, `po_number`, `order_date`, `expected_delivery_date`, `actual_delivery_date`, `status`, `total_amount`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'PO-202608-001', '2026-08-02', '2026-08-08', '2026-08-20', 'received', 500.00, 'N.A', '2026-08-01 20:30:54', '2026-08-01 20:31:28'),
(2, 1, 1, 'PO-20260405-720', '2026-08-05', '2026-08-15', '2026-08-09', 'received', 700.00, 'NA', '2026-08-05 14:28:03', '2026-08-09 02:07:28'),
(3, 2, 1, 'PO-20260809-0001', '2026-08-09', '2026-08-20', '2026-08-09', 'received', 1160.00, NULL, '2026-08-09 02:26:25', '2026-08-09 02:26:51');

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
(4, 3, 1, 1, 1, 600.00, 600.00, '2026-08-09 02:26:25', '2026-08-09 02:26:51');

-- --------------------------------------------------------

--
-- Table structure for table `request_workflows`
--

CREATE TABLE `request_workflows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_id` bigint(20) UNSIGNED NOT NULL,
  `from_department` varchar(100) NOT NULL,
  `to_department` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `status_before` varchar(100) DEFAULT NULL,
  `status_after` varchar(100) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_workflows`
--

INSERT INTO `request_workflows` (`id`, `asset_request_id`, `actor_id`, `from_department`, `to_department`, `action`, `status_before`, `status_after`, `comments`, `created_at`) VALUES
(1, 1, 3, 'user', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by user', '2026-07-19 06:31:31'),
(2, 2, 3, 'user', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by user', '2026-07-19 06:31:31'),
(3, 1, 1, 'ict', 'user', 'approved', 'approved_by_ict', 'approved_by_ict', 'Request approved by ICT', '2026-07-19 13:33:42'),
(5, 5, 1, 'ict', 'director', 'forwarded', 'sent_to_director', 'sent_to_director', 'Forwarded to Director for approval', '2026-08-01 21:27:54'),
(6, 4, 1, 'ict', 'user', 'assigned', 'assigned_to_user', 'assigned_to_user', 'N.A', '2026-08-01 21:31:35'),
(7, 3, 1, 'ict', 'user', 'rejected', 'rejected_by_ict', 'rejected_by_ict', 'Low Stock Till Next Month', '2026-08-01 21:40:37'),
(8, 8, 1, 'ict', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by admin', '2026-08-01 21:43:09'),
(9, 9, 1, 'ict', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by admin', '2026-08-01 21:53:43'),
(10, 9, 1, 'ict', 'director', 'forwarded', 'sent_to_director', 'sent_to_director', 'Forwarded to Director for approval', '2026-08-02 00:31:27'),
(11, 10, 7, 'user', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by user', '2026-08-02 05:51:10'),
(12, 11, 7, 'user', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by user', '2026-08-02 11:24:21'),
(13, 10, 1, 'ict', 'user', 'assign_existing', 'assigned_to_user', 'assigned_to_user', 'ICT reviewed request', '2026-08-05 13:55:07'),
(14, 12, 1, 'ict', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by admin', '2026-08-06 11:55:53'),
(15, 1, 1, 'ict', 'ict', 'specification_revision_requested', 'approved_by_ict', 'specs_revision_needed', 'Specification revision requested. Reason: This Request can move on this request because you don\'t know spec', '2026-08-06 20:07:36'),
(16, 12, 1, 'ict', 'ict', 'updated', 'pending_ict_review', 'pending_ict_review', 'Request details updated', '2026-08-06 20:42:03'),
(17, 2, 1, 'ict', 'procurement', 'inspected', 'pending_ict_review', 'inspection_failed', 'Inspection result: failed. No issues found.', '2026-08-06 20:50:19'),
(18, 12, 1, 'ict', 'user', 'assign_existing', 'assigned_to_user', 'assigned_to_user', 'ICT reviewed request', '2026-08-07 13:15:07'),
(19, 11, 1, 'ict', 'user', 'assigned_existing', 'pending_ict_review', 'assigned_to_user', 'Asset assigned to user', '2026-08-07 13:15:45'),
(20, 8, 1, 'ict', 'director', 'forward_director', 'sent_to_director', 'sent_to_director', 'ICT reviewed request', '2026-08-07 13:17:10'),
(21, 1, 1, 'ict', 'procurement', 'specification_approved', 'specs_revision_needed', 'sent_to_procurement', 'Specification amendment #1 approved', '2026-08-08 03:00:54'),
(22, NULL, 1, 'approval', 'user', 'rejected_direct', 'pending_director', 'director_rejected', 'nnnnn', '2026-08-08 07:38:05'),
(23, 1, 1, 'ict', 'ict', 'specification_revision_requested', 'sent_to_procurement', 'specs_revision_needed', 'Specification revision requested. Reason: Just a Simple Test', '2026-08-08 09:53:02'),
(24, 13, 3, 'user', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by user', '2026-08-09 04:32:13'),
(25, 13, 1, 'ict', 'user', 'assigned_existing', 'pending_ict_review', 'assigned_to_user', 'Asset assigned to user', '2026-08-09 04:32:51'),
(26, 6, 1, 'ict', 'director', 'forwarded_to_director', 'pending_ict_review', 'sent_to_director', 'Forwarded to Director for approval', '2026-08-09 10:08:06'),
(27, 14, 1, 'ict', 'ict', 'submitted', NULL, 'pending_ict_review', 'Request submitted by admin', '2026-08-10 10:25:28');

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
(210, 1);

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
('0bGbPk9W2NHygShSoyGeACDRXIv8MbcPA9n0zn2t', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiMlhzZVFzZWRPNDRkRkM5Z0dYbWFEaGNnd1dTQkg0YmZrN2F4VnBJaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370903),
('1PoUCUOlDjGI8jC5h2ozVX1FTA54tZPUVcg1g10V', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiRDBqVmhaeGdodEJTcUpRWWNRNXZiSEtIWnd5MVRHUkI0M21yNUdwdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370901),
('2XxU6lNaTosbEklyKlNx8OovrY3pi86Th0rjLHaI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiWHEyNG1DY2hVRnF0OXZyaUJ4V1pBbFdyYWJzSld4WVZsS05XMEZnNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370896),
('4GLPfZ7ZMxbwtBpV6FyqBrROtBExiVoNiaa0UxZo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZXlDUGJvdzZrMDhmVUtzd3ZNN1JuSXRma3dYak9nV2JwU2U1RjI0UiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9ub3RpZmljYXRpb25zL2NvdW50Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786370911),
('4qgU2k5catKBEAJyMt5JsJPBm41fINmDNbB04Nz3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiV0VqVDlzTkxrTEM3bVFZbWJSZW9jajUwNFI2cjdJdU1vN0xTczRTdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370900),
('5qGDhqTVbcNgRa2WcEzlNvrWPYtIK9dqJt5ydwwu', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiRG13OEp3bjdJWEZPNVpPbWh4VFJyNVhRcE02dFFIdENJZnZmcFBmSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370893),
('5VVzjSsK4y7kcCmB4HNEWULopXoO44dGUTfizpTl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiRnVSV2JFWFBieTd1cVYzQlVCWkJTaEV3WWZtY3FnZ1c3cGxKWlFOeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370894),
('8o0DQIJfnKxW8tJMQtU3OLKyjYdIUSE9UjqZIcm4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieVZ4VU40RnRwMlg3VU1zUjVXMUhPME84MGt3S3lvaE5qT2JUQTBDSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370899),
('9QW0VMiH5z368L43pXVNp8i7BPmC6YWLmKIPedQK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoid1g2UWxJSzBhOFBkMnp3WFIwSlZRME1DMFNkUjgzWkx1aEU2TWdkaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370902),
('B1IFbJ8pBglv0yrLEUgWtD1fir640YYOxSlpzE9D', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiWEVGWmpLWnB6NWhwM2x2M1h6SEVyenZPbmlFRzBxaDFlREdWQzE3byI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370906),
('cuRrfervC4wnWzCSi5dFtXRaK1w640QuFkXuCr4k', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiS2RBdzJXQXY1Mk42cnVJRjJpZ0dTb282Z1ljWEdJWmV2YlpoV1hOZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370904),
('GWN0Prio30iFdA00IjRATTdV1ajlPZYTXtBHGbeJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiSzNBcGl5VkJrbklEblNJTmZwNFNYM2E0aFpBbjZBUmlVWWZEallSOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370903),
('hVezTkJT1zxOU181ErYHhZksIcnomCNf4zM43KMn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieVdsRnF4V2c4aG41MHc4SzZKemJ0ZXE4Z3EyYWNZaEkwOVJ6RjFTViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370905),
('IfR9qw5gQtD1kAkS6t62pp7FOEcWLrpCEqX4CjQb', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVJnQklxeW5uMmlXUXZHQWM0Q1RjRUZQekV6bmlOU2hmdHoxeVNKYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjptTkgwRG85cU5xekFlRHhqIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787652640),
('Ikxr4SwlwzW6IOapL3bF1DhVUkKQLdM8TrxBxpLq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiS2h5NEhrcTFxek0zcXJFTUNQekkwQlJ3VDhGbHN1OEVBc1dvZkx4bCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370895),
('J9DnX6b2K01919A8STWRwAhvxq1xtKHpAaHehyaB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiSEg1bW5QaHUyNThKWXU5OFRjOGFQbUxpVnFmZHdPNllaYllUakNESSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370906),
('kkGnJTEH1N78cKcrCFrzO0ZyCUODCgK0VSNcPd35', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUEIwUE9vYUVYZXJFUlpRekJsRlF0Z20xQndYdGxBRzhld0MwUjVWNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370894),
('mDeJjlp1Xd9q2m0jhs6tUVaWZyU2djKFxjvVBEem', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiT0s2eW1CMzJhNTl2MkJkZ2JldjFGOW9QQllKSU5idlVPRlh2RVJDZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370907),
('NB6fsrMoBYH5e4uHnOAAJRaZMUTX1IjXRp5cOTqp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNkVHVmx3RkxqanEyWFBUNE9odEsybTRLTlpSVHJXZ2FyNFFFN25WRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370900),
('ND26eZ9IPEcwQUtlZ2lnzE0zFi9a0EE7psSLgV8O', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiaFVURWVjWmI2bEZXeFJTdFdUOVNpWXdnSWVORno1VzZuOFdKZ1BRdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370899),
('oQISmtCW8b5HtSL3KtrdhLl8HhHjBG0sFmEejFAI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiZUZjWnBqaDBXZWtrV2NGUjBQVmliVVFTaUp3NEpkcjAySzRFY2Y2ViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370898),
('RdHZPZYBnIlg95pB33B21ZIhTD7nCST0vXnPxT1Y', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYVY5dzlKVGx6bjYyc1pjZjVEREhyb1o3MjZQa016VUYyWU1JU1Z1MSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370905),
('rOziKjPcH2cv22fsepDJTRA6KVEgbEGx8ig5hmy1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoidUttMWZKUktXTW5XVnVheFc1bEJIdkNPMm5VSEFXbG54SUJIekpUTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370897),
('Rrd4kgLbRzGZ0JKksxUpvU5OIwHf70nG5JnT3VMO', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibFRKWGV0MFFxMFVUMHdoZjNJa3M0dFV4VnZjR3JtRlZaeElIand0dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvbm90aWZpY2F0aW9ucy9jb3VudCI7czo1OiJyb3V0ZSI7czoyMzoiYXBpLm5vdGlmaWNhdGlvbnMuY291bnQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1787652711),
('rrWC3ZN1ZQ7vXGW0WwtiCRmSZ2KcluBU9DE2VX8D', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYU1oVXlPQjE1cXlybFZsbGF6TklGZ1RWS0hRTzVIckFkNW95Zzg5aiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370898),
('Sd9A3Nt0aFqnuVzepjDKdi3eDq8y1ugcWwaaFyv3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVFRKQVg4d2FFRVhvMWFNclRLa3dnMm5EM0pYYTFuS0NKckEzV2xSSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786456105),
('SJDSV43Af4kbm5PXkH0pPI14KjFBANWMNATser9a', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaDlmUXRqNG9wZXNVN1FWRnhGc3BqWDU4UHhhZ1A0eThkOFVOTmRLQiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9hZG1pbi9wZW5kaW5nLXJlcXVlc3RzL2NvdW50Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786370910),
('Tda0jVAG7He3wxSGXUp5u9aEndxKtR8WJxPDRv2J', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNXFENDZaRlNUYnJzdlpyUWExejBOSjlGNHB3OGJOUmQ2T2pxYUh4VCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370901),
('ulkrT0Cu1Evm9m1R5iOyGklk15TEsNZBDgQZnHn7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiakdzUXA4ZTA1M1hYVzdNVGR4QzlYSUF4Y1hMbTI3VjZLVTVveW8wbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370895),
('VAny8qGwsCH9jFsqu6a0LWvBC0YC9XCypvvUbybe', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYjdkclEzcXVPaTlmWHlzdzM2UFhBWkFEOGxUUFVtQ0tKMXduQ244diI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9hZG1pbi9wZW5kaW5nLXJlcXVlc3RzL2NvdW50Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786370911),
('VJ4F676T1yEvQ9vFtB5yORG0gzbg0XSQ5kTETQsQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMU9LMVNZeE93ZjE5MVFOaUxSRzJHMGJ2SDRvc1czYXhSQ2lBWVE3dyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9ub3RpZmljYXRpb25zL2NvdW50Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvbm90aWZpY2F0aW9ucy9jb3VudCI7czo1OiJyb3V0ZSI7czoyMzoiYXBpLm5vdGlmaWNhdGlvbnMuY291bnQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1786373980),
('ykyGN9m2MWbWHWAdjG0QSFis4lsmECOGeTRgIigh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNThpVDAybGF0aWdvQk5qUnNVdG53TEI5WGxobDdEQW5wbUp6aGxiMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1786370897);

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
-- Table structure for table `software_license_usage`
--

CREATE TABLE `software_license_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to_type` enum('user','facility') NOT NULL DEFAULT 'user',
  `facility_id` bigint(20) UNSIGNED DEFAULT NULL,
  `license_key` varchar(255) NOT NULL,
  `assigned_at` date NOT NULL,
  `revoked_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `software_license_usage`
--

INSERT INTO `software_license_usage` (`id`, `product_id`, `user_id`, `assigned_to_type`, `facility_id`, `license_key`, `assigned_at`, `revoked_at`, `created_at`) VALUES
(1, 1, 2, 'user', NULL, 'MS-001-2027', '2026-08-09', '2026-08-09', '2026-08-09 07:31:20'),
(2, 4, NULL, 'facility', 1, 'MS-001-2026', '2026-08-09', NULL, '2026-08-09 07:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `specification_amendments`
--

CREATE TABLE `specification_amendments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_request_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `requested_from` enum('ict','director','procurement') NOT NULL,
  `old_specifications` text DEFAULT NULL,
  `new_specifications` text NOT NULL,
  `reason_for_revision` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specification_amendments`
--

INSERT INTO `specification_amendments` (`id`, `asset_request_id`, `requested_by`, `requested_from`, `old_specifications`, `new_specifications`, `reason_for_revision`, `status`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'ict', 'na', 'It should have minimum 8GB Ram etc.', 'This Request can move on this request because you don\'t know spec', 'approved', 1, '2026-08-08 00:00:53', '2026-08-06 17:07:36', '2026-08-08 00:00:53'),
(2, 1, 1, 'ict', NULL, 'Just a Simple Test', 'Just a Simple Test', 'pending', NULL, NULL, '2026-08-08 06:53:02', '2026-08-08 06:53:02');

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('government','ngo','corporate','individual','foundation','other') NOT NULL DEFAULT 'other',
  `category` varchar(100) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','pending','suspended') NOT NULL DEFAULT 'active',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `donation_type` enum('financial','equipment','services','training','other') DEFAULT NULL,
  `total_donations` decimal(15,2) DEFAULT 0.00,
  `donation_currency` varchar(10) DEFAULT 'USD',
  `last_donation_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sponsors`
--

INSERT INTO `sponsors` (`id`, `name`, `type`, `category`, `code`, `registration_number`, `email`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `website`, `contact_person`, `contact_phone`, `contact_email`, `description`, `status`, `is_verified`, `verified_at`, `verified_by`, `donation_type`, `total_donations`, `donation_currency`, `last_donation_date`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dever Web', 'ngo', NULL, 'NGO-DEVE-641E', NULL, 'webdevercyber@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, '2026-08-08 06:51:54', 1, NULL, NULL, 'USD', NULL, NULL, 1, 1, '2026-08-06 17:09:54', '2026-08-08 06:51:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('purchase','adjustment','return','damage') NOT NULL,
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
(7, 1, 1, 'purchase', 1, 1, 2, 'PO-20260809-0001', 'Received from PO: PO-20260809-0001', '2026-08-09 05:26:51');

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
-- Indexes for table `asset_requests`
--
ALTER TABLE `asset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `assigned_product_id` (`assigned_product_id`),
  ADD KEY `asset_requests_user_id_index` (`user_id`),
  ADD KEY `asset_requests_status_index` (`status`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `assignments_product_id_index` (`product_id`),
  ADD KEY `assignments_user_id_index` (`user_id`);

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
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `direct_requests`
--
ALTER TABLE `direct_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_request_id` (`asset_request_id`),
  ADD KEY `direct_requests_user_id_index` (`user_id`),
  ADD KEY `direct_requests_status_index` (`status`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `direct_requests_director_id_foreign` (`director_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facilities_code_unique` (`code`),
  ADD KEY `facilities_type_index` (`type`),
  ADD KEY `facilities_category_index` (`category`),
  ADD KEY `facilities_status_index` (`status`),
  ADD KEY `facilities_city_index` (`city`),
  ADD KEY `facilities_state_index` (`state`),
  ADD KEY `facilities_country_index` (`country`),
  ADD KEY `facilities_created_by_foreign` (`created_by`),
  ADD KEY `facilities_updated_by_foreign` (`updated_by`),
  ADD KEY `facilities_verified_by_foreign` (`verified_by`),
  ADD KEY `facilities_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspected_by` (`inspected_by`),
  ADD KEY `returned_to_supplier_id` (`returned_to_supplier_id`),
  ADD KEY `inspections_request_id_index` (`asset_request_id`),
  ADD KEY `inspections_product_id_index` (`product_id`);

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
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `maintenance_logs_product_id_index` (`product_id`);

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
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  ADD KEY `idx_permissions_group_name` (`group_name`);

--
-- Indexes for table `procurements`
--
ALTER TABLE `procurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `procurement_officer_id` (`procurement_officer_id`),
  ADD KEY `received_by` (`received_by`),
  ADD KEY `procurements_request_id_index` (`asset_request_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD UNIQUE KEY `asset_tag` (`asset_tag`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_type_index` (`type`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `idx_products_purchase_date` (`purchase_date`),
  ADD KEY `idx_products_office` (`office`),
  ADD KEY `idx_products_brand` (`brand`),
  ADD KEY `idx_products_model` (`model`),
  ADD KEY `idx_products_user_id` (`user_id`),
  ADD KEY `idx_products_device_code` (`device_code`),
  ADD KEY `idx_products_head_of_school` (`facility_administrator`),
  ADD KEY `idx_products_deleted_at` (`deleted_at`),
  ADD KEY `idx_products_facility_id` (`facility_id`),
  ADD KEY `idx_products_sponsor_id` (`sponsor_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `purchase_orders_supplier_id_index` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `purchase_order_items_po_id_index` (`purchase_order_id`);

--
-- Indexes for table `request_workflows`
--
ALTER TABLE `request_workflows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_id` (`actor_id`),
  ADD KEY `request_workflows_request_id_index` (`asset_request_id`);

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
-- Indexes for table `software_license_usage`
--
ALTER TABLE `software_license_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_key_unique` (`license_key`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_assigned_to_type` (`assigned_to_type`),
  ADD KEY `idx_facility_id` (`facility_id`);

--
-- Indexes for table `specification_amendments`
--
ALTER TABLE `specification_amendments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `spec_amendments_request_id_index` (`asset_request_id`);

--
-- Indexes for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sponsors_code_unique` (`code`),
  ADD UNIQUE KEY `sponsors_registration_number_unique` (`registration_number`),
  ADD KEY `sponsors_type_index` (`type`),
  ADD KEY `sponsors_category_index` (`category`),
  ADD KEY `sponsors_status_index` (`status`),
  ADD KEY `sponsors_city_index` (`city`),
  ADD KEY `sponsors_country_index` (`country`),
  ADD KEY `sponsors_created_by_foreign` (`created_by`),
  ADD KEY `sponsors_updated_by_foreign` (`updated_by`),
  ADD KEY `sponsors_verified_by_foreign` (`verified_by`),
  ADD KEY `sponsors_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `stock_movements_product_id_index` (`product_id`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_department_id_index` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `direct_requests`
--
ALTER TABLE `direct_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `procurements`
--
ALTER TABLE `procurements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `request_workflows`
--
ALTER TABLE `request_workflows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `software_license_usage`
--
ALTER TABLE `software_license_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `specification_amendments`
--
ALTER TABLE `specification_amendments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `asset_requests`
--
ALTER TABLE `asset_requests`
  ADD CONSTRAINT `asset_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `asset_requests_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `asset_requests_ibfk_3` FOREIGN KEY (`assigned_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asset_requests_assigned_product_id` FOREIGN KEY (`assigned_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asset_requests_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_asset_requests_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_assignments_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_assignments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `direct_requests`
--
ALTER TABLE `direct_requests`
  ADD CONSTRAINT `direct_requests_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `direct_requests_director_id_foreign` FOREIGN KEY (`director_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `direct_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `direct_requests_ibfk_2` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `direct_requests_ibfk_3` FOREIGN KEY (`director_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_direct_requests_asset_request_id` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_direct_requests_director_id` FOREIGN KEY (`director_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_direct_requests_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `facilities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `facilities_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `fk_inspections_asset_request_id` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`),
  ADD CONSTRAINT `fk_inspections_inspected_by` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_inspections_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_inspections_returned_to_supplier_id` FOREIGN KEY (`returned_to_supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inspections_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`),
  ADD CONSTRAINT `inspections_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inspections_ibfk_3` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inspections_ibfk_4` FOREIGN KEY (`returned_to_supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `fk_maintenance_logs_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_maintenance_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `maintenance_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `maintenance_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
-- Constraints for table `procurements`
--
ALTER TABLE `procurements`
  ADD CONSTRAINT `fk_procurements_asset_request_id` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`),
  ADD CONSTRAINT `fk_procurements_officer_id` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_procurements_purchase_order_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_procurements_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_procurements_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `procurements_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`),
  ADD CONSTRAINT `procurements_ibfk_2` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `procurements_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `procurements_ibfk_4` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `procurements_ibfk_5` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_products_facility_id` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_sponsor_id` FOREIGN KEY (`sponsor_id`) REFERENCES `sponsors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_purchase_orders_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `fk_purchase_orders_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_purchase_order_items_po_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `request_workflows`
--
ALTER TABLE `request_workflows`
  ADD CONSTRAINT `fk_request_workflows_actor_id` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_request_workflows_asset_request_id` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_workflows_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_workflows_ibfk_2` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `software_license_usage`
--
ALTER TABLE `software_license_usage`
  ADD CONSTRAINT `fk_software_license_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `software_license_usage_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `software_license_usage_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `software_license_usage_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specification_amendments`
--
ALTER TABLE `specification_amendments`
  ADD CONSTRAINT `fk_spec_amendments_asset_request_id` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_spec_amendments_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_spec_amendments_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `specification_amendments_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `specification_amendments_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `specification_amendments_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD CONSTRAINT `sponsors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sponsors_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sponsors_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_movements_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_stock_movements_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
