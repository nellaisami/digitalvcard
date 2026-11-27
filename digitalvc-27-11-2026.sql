-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2026 at 10:52 AM
-- Server version: 10.11.19-MariaDB
-- PHP Version: 8.5.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `digitalvc`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1795771286),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1795771286;', 1795771286);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `login_otps`
--

CREATE TABLE `login_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_11_24_000001_create_dashboard_tables', 1),
(5, '2026_11_24_000002_add_profile_photos_and_plan_to_users', 1),
(6, '2026_11_25_000001_extend_plans_and_create_taxes', 1),
(7, '2026_11_25_000002_create_vcard_templates_and_plan_assignments', 1),
(8, '2026_11_25_000003_move_superadmins_to_separate_table', 1),
(9, '2026_11_25_000004_add_image_url_to_vcard_templates', 1),
(10, '2026_11_25_000005_upgrade_organizations_for_owners_and_plans', 1),
(11, '2026_11_25_000006_add_usernames_to_accounts', 1),
(12, '2026_11_27_000001_remove_plan_assignment_from_users', 1),
(13, '2026_11_27_000002_remove_superadmin_address_fields', 1),
(14, '2026_11_27_000003_add_logo_path_to_organizations', 1),
(15, '2026_11_27_000004_convert_organization_admin_memberships_to_managers', 1),
(16, '2026_11_27_000005_add_vcard_template_code_price_and_selections', 1);

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `owner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `vcard_template_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `email`, `owner_user_id`, `plan_id`, `status`, `created_at`, `updated_at`, `logo_path`, `vcard_template_id`) VALUES
(1, 'Brightside Studio', NULL, NULL, 2, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(2, 'Fieldwork Collective', NULL, NULL, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(3, 'Juniper & Co.', NULL, NULL, NULL, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `organization_users`
--

CREATE TABLE `organization_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'member',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organization_users`
--

INSERT INTO `organization_users` (`id`, `organization_id`, `user_id`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'member', 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(2, 1, 20, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(3, 1, 3, 'member', 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(4, 1, 19, 'member', 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(5, 2, 13, 'member', 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(6, 2, 19, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(7, 2, 6, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(8, 2, 4, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(9, 3, 12, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(10, 3, 1, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(11, 3, 9, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(12, 3, 5, 'member', 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09');

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
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frequency` varchar(255) NOT NULL DEFAULT 'monthly',
  `no_of_vcards` int(10) UNSIGNED DEFAULT NULL,
  `trial_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `feature_limits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`feature_limits`)),
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price`, `frequency`, `no_of_vcards`, `trial_days`, `features`, `feature_limits`, `is_default`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 0.00, 'monthly', NULL, 0, NULL, NULL, 0, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(2, 'Growth', 29.00, 'monthly', NULL, 0, NULL, NULL, 0, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(3, 'Enterprise', 99.00, 'monthly', NULL, 0, NULL, NULL, 0, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09');

-- --------------------------------------------------------

--
-- Table structure for table `plan_vcard_template`
--

CREATE TABLE `plan_vcard_template` (
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `vcard_template_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('LszQpzqFI6ELJd0f2keNE37OIE4aOBrPB5T166YA', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJxOXZYUEJjSGw1SUVEc3hETGZJSGY3NnAzZk9SaVlHSmd5S0pjTndHIiwidXJsIjpbXSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sImxvZ2luX3N1cGVyYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF9zdXBlcmFkbWluIjoiOGUyNjYwYTY4ZTMyOGY2MDIxNzZlY2YyMThlNmNmOThiNmRhMDRlOGYwMTg2NGM3MzNiZjM4MzRlMDkwOGQxMSJ9', 1795772137);

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `name`, `email`, `email_verified_at`, `password`, `contact_no`, `profile_photo`, `status`, `remember_token`, `created_at`, `updated_at`, `username`) VALUES
(1, 'Northstar Super Admin', 'superadmin@example.com', '2026-11-27 03:50:08', '$2y$12$BUNiTQLJRmXhQJZmVc5tmecrFfg.pi4wGnjYIK4kkAtd.12ovk7UC', NULL, NULL, 'active', NULL, '2026-11-27 03:50:08', '2026-11-27 03:50:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `super_admin_login_otps`
--

CREATE TABLE `super_admin_login_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `super_admin_id` bigint(20) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tax_name` varchar(255) NOT NULL,
  `tax_value` decimal(7,4) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'staff',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `vcard_template_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `contact_no`, `address`, `city`, `country`, `postal_code`, `profile_photo`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `username`, `vcard_template_id`) VALUES
(1, 'Alex Admin', 'admin@example.com', '2026-11-27 03:50:08', '$2y$12$EbhhrXOQ0SSNUHZegVjyKuabuvmOgXY9EKOSLBO7n28WhEp6.CBD2', NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 'active', NULL, '2026-11-27 03:50:08', '2026-11-27 03:50:08', NULL, NULL),
(2, 'Morgan Manager', 'manager@example.com', '2026-11-27 03:50:08', '$2y$12$VucBZYehRwU3h/GGhUhQkuesj/H6dRhtFfMT8UQ1vs3211DbUgBle', NULL, NULL, NULL, NULL, NULL, NULL, 'manager', 'active', NULL, '2026-11-27 03:50:08', '2026-11-27 03:50:08', NULL, NULL),
(3, 'Elliot Editor', 'editor@example.com', '2026-11-27 03:50:08', '$2y$12$g1ZxBuPIBT1J4doD0PJ9muCgOh8DMw6197OIkpStsAEqs3OL.Kx/e', NULL, NULL, NULL, NULL, NULL, NULL, 'editor', 'active', NULL, '2026-11-27 03:50:08', '2026-11-27 03:50:08', NULL, NULL),
(4, 'Sam Staff', 'staff@example.com', '2026-11-27 03:50:08', '$2y$12$oP1d50q3qoqVPEV8q8cN5upldZT35hmxqUsIO4/LiddbCKilelnY.', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', NULL, '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(5, 'Thurman Bins', 'janis94@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'aWEU7OdCk7', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(6, 'Mr. Kevon Graham PhD', 'desiree07@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'nk5tlUFFiN', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(7, 'Holly Jacobson', 'joseph.waelchi@example.com', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'N6LLZO3KRP', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(8, 'Dr. Merl Hyatt', 'heathcote.leonie@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'Ycw1wKYLmw', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(9, 'Sydnee Ruecker', 'bethany.wisozk@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'X1Azy90w5D', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(10, 'Meda Haag', 'janie85@example.com', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'dlAzbIYj0m', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(11, 'Rebecca Murray', 'amelia.swaniawski@example.com', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', '2No6tSkgaK', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(12, 'Jessy Beier', 'adams.jose@example.com', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'EhNhMmMHqd', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(13, 'Adele Crooks', 'stroman.leilani@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'NRlKnYhWPR', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(14, 'Ophelia Ondricka', 'freida18@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'oj0oSWcQ8m', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(15, 'Easton Parker', 'thiel.wilburn@example.org', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'Q2mNmAloY1', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(16, 'Mr. Lamont Padberg I', 'tara40@example.com', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'swS9kKpOGg', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(17, 'Anne Trantow', 'keith.waelchi@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'mSFpzApShs', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(18, 'Ms. Heath Satterfield', 'harris.cecelia@example.org', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'PidP8dLQCm', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(19, 'Miss Nona Jones', 'steuber.claire@example.net', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'aLeeDMl0ya', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL),
(20, 'Prof. Kayley Hilpert', 'bernie.mclaughlin@example.org', '2026-11-27 03:50:09', '$2y$12$FG3HWrxDIpOhMpQuN5TyReEed5VVwi1/LQOmtnT9ZVF8Od8VanvxO', NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'Nr7UhN9tiR', '2026-11-27 03:50:09', '2026-11-27 03:50:09', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vcards`
--

CREATE TABLE `vcards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vcards`
--

INSERT INTO `vcards` (`id`, `title`, `user_id`, `organization_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Business card 1', 11, NULL, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(2, 'Business card 2', 13, 2, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(3, 'Business card 3', 16, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(4, 'Business card 4', 8, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(5, 'Business card 5', 12, 3, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(6, 'Business card 6', 5, 3, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(7, 'Business card 7', 12, 3, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(8, 'Business card 8', 8, NULL, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(9, 'Business card 9', 5, 3, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(10, 'Business card 10', 7, NULL, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(11, 'Business card 11', 18, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(12, 'Business card 12', 10, 1, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(13, 'Business card 13', 14, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(14, 'Business card 14', 18, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(15, 'Business card 15', 5, 3, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(16, 'Business card 16', 19, 1, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(17, 'Business card 17', 10, 1, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(18, 'Business card 18', 7, NULL, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(19, 'Business card 19', 14, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(20, 'Business card 20', 14, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(21, 'Business card 21', 8, NULL, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(22, 'Business card 22', 3, 1, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(23, 'Business card 23', 3, 1, 'active', '2026-11-27 03:50:09', '2026-11-27 03:50:09'),
(24, 'Business card 24', 10, 1, 'inactive', '2026-11-27 03:50:09', '2026-11-27 03:50:09');

-- --------------------------------------------------------

--
-- Table structure for table `vcard_templates`
--

CREATE TABLE `vcard_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `html_code` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_otps_user_id_unique` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizations_email_unique` (`email`),
  ADD KEY `organizations_status_index` (`status`),
  ADD KEY `organizations_owner_user_id_foreign` (`owner_user_id`),
  ADD KEY `organizations_plan_id_foreign` (`plan_id`),
  ADD KEY `organizations_vcard_template_id_foreign` (`vcard_template_id`);

--
-- Indexes for table `organization_users`
--
ALTER TABLE `organization_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organization_users_organization_id_user_id_unique` (`organization_id`,`user_id`),
  ADD KEY `organization_users_user_id_foreign` (`user_id`),
  ADD KEY `organization_users_status_index` (`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plans_status_index` (`status`),
  ADD KEY `plans_is_default_index` (`is_default`);

--
-- Indexes for table `plan_vcard_template`
--
ALTER TABLE `plan_vcard_template`
  ADD PRIMARY KEY (`plan_id`,`vcard_template_id`),
  ADD KEY `plan_vcard_template_vcard_template_id_foreign` (`vcard_template_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `super_admins_email_unique` (`email`),
  ADD UNIQUE KEY `super_admins_username_unique` (`username`),
  ADD KEY `super_admins_status_index` (`status`);

--
-- Indexes for table `super_admin_login_otps`
--
ALTER TABLE `super_admin_login_otps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `super_admin_login_otps_super_admin_id_unique` (`super_admin_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `taxes_tax_name_unique` (`tax_name`),
  ADD KEY `taxes_status_index` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_vcard_template_id_foreign` (`vcard_template_id`);

--
-- Indexes for table `vcards`
--
ALTER TABLE `vcards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vcards_user_id_foreign` (`user_id`),
  ADD KEY `vcards_status_index` (`status`),
  ADD KEY `vcards_organization_id_foreign` (`organization_id`);

--
-- Indexes for table `vcard_templates`
--
ALTER TABLE `vcard_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vcard_templates_name_unique` (`name`),
  ADD KEY `vcard_templates_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_otps`
--
ALTER TABLE `login_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `organization_users`
--
ALTER TABLE `organization_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `super_admin_login_otps`
--
ALTER TABLE `super_admin_login_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vcards`
--
ALTER TABLE `vcards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `vcard_templates`
--
ALTER TABLE `vcard_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD CONSTRAINT `login_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organizations`
--
ALTER TABLE `organizations`
  ADD CONSTRAINT `organizations_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `organizations_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `organizations_vcard_template_id_foreign` FOREIGN KEY (`vcard_template_id`) REFERENCES `vcard_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `organization_users`
--
ALTER TABLE `organization_users`
  ADD CONSTRAINT `organization_users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plan_vcard_template`
--
ALTER TABLE `plan_vcard_template`
  ADD CONSTRAINT `plan_vcard_template_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plan_vcard_template_vcard_template_id_foreign` FOREIGN KEY (`vcard_template_id`) REFERENCES `vcard_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `super_admin_login_otps`
--
ALTER TABLE `super_admin_login_otps`
  ADD CONSTRAINT `super_admin_login_otps_super_admin_id_foreign` FOREIGN KEY (`super_admin_id`) REFERENCES `super_admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_vcard_template_id_foreign` FOREIGN KEY (`vcard_template_id`) REFERENCES `vcard_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vcards`
--
ALTER TABLE `vcards`
  ADD CONSTRAINT `vcards_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vcards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
