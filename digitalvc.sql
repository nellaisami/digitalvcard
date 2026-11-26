-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 04:14 PM
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
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1795674544),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1795674544;', 1795674544),
('laravel-cache-login:superadmin@example.com|127.0.0.1', 'i:1;', 1795674469),
('laravel-cache-login:superadmin@example.com|127.0.0.1:timer', 'i:1795674469;', 1795674469);

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
(10, '2026_11_25_000005_upgrade_organizations_for_owners_and_plans', 2),
(11, '2026_11_25_000006_add_usernames_to_accounts', 2);

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `email`, `owner_user_id`, `plan_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Brightside Studio', NULL, NULL, NULL, 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(2, 'Fieldwork Collective', NULL, NULL, NULL, 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(3, 'Juniper & Co.', NULL, NULL, NULL, 'inactive', '2026-11-25 06:03:43', '2026-11-25 06:03:43');

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
(1, 1, 8, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(2, 1, 13, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(3, 1, 4, 'member', 'inactive', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(4, 1, 11, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(5, 2, 15, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(6, 2, 10, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(7, 2, 11, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(8, 2, 19, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(9, 3, 1, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(10, 3, 6, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(11, 3, 3, 'member', 'active', '2026-11-25 06:03:43', '2026-11-25 06:03:43'),
(12, 3, 16, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(13, 1, 16, 'member', 'inactive', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(14, 1, 18, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(15, 1, 3, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(16, 1, 10, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(17, 2, 5, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(18, 2, 18, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(19, 2, 13, 'member', 'inactive', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(20, 2, 17, 'member', 'inactive', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(21, 3, 15, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(22, 3, 13, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(23, 3, 9, 'member', 'active', '2026-11-26 00:53:18', '2026-11-26 00:53:18'),
(24, 1, 5, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(25, 1, 1, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(26, 1, 19, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(27, 2, 7, 'member', 'inactive', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(28, 2, 3, 'member', 'inactive', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(29, 2, 14, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(30, 3, 5, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(31, 3, 12, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23'),
(32, 3, 4, 'member', 'active', '2026-11-26 00:54:23', '2026-11-26 00:54:23');

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
(1, 'Starter', 0.00, 'monthly', NULL, 0, '[\"no_of_vcards\"]', '[]', 1, 'active', '2026-11-25 06:03:43', '2026-11-25 07:07:12'),
(2, 'Growth', 29.00, 'monthly', NULL, 0, '[\"no_of_vcards\"]', '[]', 0, 'active', '2026-11-25 06:03:43', '2026-11-25 07:07:12'),
(3, 'Enterprise', 99.00, 'monthly', NULL, 0, '[\"services\",\"products\",\"gallery\",\"testimonials\",\"blogs\",\"social_links_website\",\"custom_links\",\"analytics\",\"seo\",\"inquiry_form\",\"appointments\",\"custom_qr_code\",\"instagram_feed\",\"linkedin_feed\",\"iframes\",\"dynamic_vcard\",\"allow_custom_domain\",\"whatsapp_stores\",\"no_of_visitors\",\"hide_branding\",\"no_of_organization_users\",\"custom_css\",\"custom_js\",\"custom_fonts\",\"password_protection\",\"no_of_vcards\"]', '{\"no_of_visitors\":30,\"no_of_organization_users\":10}', 0, 'active', '2026-11-25 06:03:43', '2026-11-25 07:07:12');

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

--
-- Dumping data for table `plan_vcard_template`
--

INSERT INTO `plan_vcard_template` (`plan_id`, `vcard_template_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-11-25 06:59:27', '2026-11-25 06:59:27'),
(1, 2, '2026-11-25 06:59:51', '2026-11-25 06:59:51'),
(1, 3, '2026-11-25 06:59:51', '2026-11-25 06:59:51'),
(1, 4, '2026-11-25 06:59:51', '2026-11-25 06:59:51'),
(1, 5, '2026-11-25 06:59:51', '2026-11-25 06:59:51'),
(2, 1, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 2, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 3, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 4, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 5, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 6, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 7, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 8, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 9, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(2, 10, '2026-11-25 07:00:09', '2026-11-25 07:00:09'),
(3, 1, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 2, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 3, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 4, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 5, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 6, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 7, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 8, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 9, '2026-11-25 07:00:55', '2026-11-25 07:00:55'),
(3, 10, '2026-11-25 07:00:55', '2026-11-25 07:00:55');

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
('F6oGedn1LG1yRqmS1VXatkQ1v2ftmCUdZ1W0aNcG', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36 Edg/158.0.0.0', 'eyJfdG9rZW4iOiJUR0gwSkNncE5KRFBCanY2M2FsN1gzNFFEcWlHd0lGVUNzM1hvbUVUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hY2NvdW50Iiwicm91dGUiOiJhY2NvdW50LmVkaXQifSwiX2ZsYXNoIjp7Im9sZCI6WyJzdGF0dXMiXSwibmV3IjpbXX0sImxvZ2luX3N1cGVyYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF9zdXBlcmFkbWluIjoiMTI2MjhmN2YyMjQ2YjU2ZDhjMTUxZWZmNTQ3NmUyNDBjYzgxZjliOGExYWQ5OGRiZTQ2MTZkZTc5YzJiMGZkYSIsInN0YXR1cyI6IkFjY291bnQgZGV0YWlscyB1cGRhdGVkLiJ9', 1795674316),
('TmYzORxrQZvUkwf99LncJhLxEqAZCnWHZ1Ro6mVW', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36 Edg/158.0.0.0', 'eyJfdG9rZW4iOiJ1OGt5S2xKa0Z5elFhV1djajh0TEllRENkV3ZVUmswOVVFTk5RTENFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInBhc3N3b3JkX2hhc2hfc3VwZXJhZG1pbiI6ImRhNDk1M2M5ZTQ0MGU5MjFiNmRmMmVlMWVjMzZjNzYzNGI5OTBmMGY0MjY2YzFhNTkwOTY2N2VkODUwNzhmNzYiLCJsb2dpbl9zdXBlcmFkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9', 1795674637);

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
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
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

INSERT INTO `super_admins` (`id`, `name`, `email`, `email_verified_at`, `password`, `contact_no`, `address`, `city`, `country`, `postal_code`, `profile_photo`, `status`, `remember_token`, `created_at`, `updated_at`, `username`) VALUES
(1, 'Saminathan', 'nellai.digitalmedia@gmail.com', '2026-11-26 06:27:28', '$2y$12$IqJoY7R5MspX6.nobI.lJ.vie2Ty1aZn.SCBz.kCDmRAXUd3aVVH6', '9894308054', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-11-25 06:03:42', '2026-11-26 00:55:16', 'superuadmin');

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
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'staff',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `contact_no`, `address`, `city`, `country`, `postal_code`, `profile_photo`, `plan_id`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `username`) VALUES
(1, 'Alex Admin', 'admin@example.com', '2026-11-26 00:54:22', '$2y$12$gc8TNRYYSdYpxXvgyE0aju/GYyL.PgJdxLVCj0pkadqc64KTlJ1ZG', NULL, NULL, NULL, NULL, NULL, NULL, 2, 'admin', 'active', NULL, '2026-11-25 06:03:42', '2026-11-26 00:54:23', 'u_alex_admin_1'),
(2, 'Morgan Manager', 'manager@example.com', '2026-11-26 00:54:23', '$2y$12$xoZ04FcKMsUEccL51xvPa.EY2rS7x2m6sO90eAZK63UPXrw0WqVvi', NULL, NULL, NULL, NULL, NULL, NULL, 2, 'manager', 'active', NULL, '2026-11-25 06:03:42', '2026-11-26 00:54:23', 'u_morgan_manager_2'),
(3, 'Elliot Editor', 'editor@example.com', '2026-11-26 00:54:23', '$2y$12$QYTEw89QDYMYb03PYa8G1u6Y2fxeJtrX2LpQqBPUpvSWOSVvLgAeu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'editor', 'active', NULL, '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_elliot_editor_3'),
(4, 'Sam Staff', 'staff@example.com', '2026-11-26 00:54:23', '$2y$12$vJihluWcRGuVsgaNMnT4UeBoT1n83XbV6UWlQzVbXDADaAe8SN.7q', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', NULL, '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_sam_staff_4'),
(5, 'Cruz Barrows DVM', 'mcglynn.isobel@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'crdQQFBoTZ', '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_cruz_barrows_dvm_5'),
(6, 'Dr. Jean Weissnat Sr.', 'raynor.darrin@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', 'B21x1FkEMW', '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_dr_jean_weissnat_sr_6'),
(7, 'Hillard Greenfelder Jr.', 'anais48@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', '5G08nZRtzl', '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_hillard_greenfelder_jr_7'),
(8, 'Miss Eula Herzog', 'xkilback@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'inactive', '8k6Coy3tj8', '2026-11-25 06:03:43', '2026-11-26 00:54:23', 'u_miss_eula_herzog_8'),
(9, 'Lorna Crona', 'krajcik.keely@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'QIPpYWnUUT', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_lorna_crona_9'),
(10, 'Hadley Bosco', 'hailie02@example.net', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'SLT74NXoo8', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_hadley_bosco_10'),
(11, 'Leila Torp IV', 'moshe.johnson@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'O2IAiVy4tO', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_leila_torp_iv_11'),
(12, 'Brionna Langworth', 'laura.harber@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'gbBIPd0AY3', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_brionna_langworth_12'),
(13, 'Prof. Griffin Moore', 'julien.rau@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'XzZquvXol0', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_prof_griffin_moore_13'),
(14, 'Annamae Murray', 'fritchie@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', '0FMcCVh1qE', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_annamae_murray_14'),
(15, 'Dr. Darryl Satterfield Sr.', 'kylee.funk@example.net', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'pFP5rDQh3T', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_dr_darryl_satterfield_sr_15'),
(16, 'Mrs. Mayra Streich', 'huels.titus@example.com', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'NQ5woJ9nGN', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_mrs_mayra_streich_16'),
(17, 'Palma Breitenberg', 'dvonrueden@example.net', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'TZMZwpbSbW', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_palma_breitenberg_17'),
(18, 'Corbin Koepp', 'aliyah.mante@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'nCq7o7iHLV', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_corbin_koepp_18'),
(19, 'Salvador Jacobson', 'glenda.wilderman@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'gPv1ywX2H9', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_salvador_jacobson_19'),
(20, 'Gussie Jerde', 'ziemann.tyson@example.org', '2026-11-25 06:03:43', '$2y$12$0FYgt1fM9QU0yRgGcanK.OfKsLcSDbAJ8pdBEfEaQtBIhGXb8imWC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'staff', 'active', 'xGuRI038Lq', '2026-11-25 06:03:43', '2026-11-25 06:03:43', 'u_gussie_jerde_20');

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
(1, 'Business card 1', 3, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(2, 'Business card 2', 5, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(3, 'Business card 3', 8, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(4, 'Business card 4', 10, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(5, 'Business card 5', 18, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(6, 'Business card 6', 9, 3, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(7, 'Business card 7', 14, 2, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(8, 'Business card 8', 16, 1, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(9, 'Business card 9', 20, NULL, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(10, 'Business card 10', 8, 1, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(11, 'Business card 11', 14, 2, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(12, 'Business card 12', 10, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(13, 'Business card 13', 14, 2, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(14, 'Business card 14', 3, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(15, 'Business card 15', 15, 2, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(16, 'Business card 16', 14, 2, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(17, 'Business card 17', 12, 3, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(18, 'Business card 18', 20, NULL, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(19, 'Business card 19', 13, 1, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(20, 'Business card 20', 20, NULL, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(21, 'Business card 21', 7, 2, 'inactive', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(22, 'Business card 22', 6, 3, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(23, 'Business card 23', 18, 1, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23'),
(24, 'Business card 24', 14, 2, 'active', '2026-11-25 06:03:43', '2026-11-26 00:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `vcard_templates`
--

CREATE TABLE `vcard_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vcard_templates`
--

INSERT INTO `vcard_templates` (`id`, `name`, `description`, `image_path`, `status`, `created_at`, `updated_at`, `image_url`) VALUES
(1, 'Sample Template 01', NULL, 'vcard-templates/QT5IVOnENTbgZlu0ttjBaoREVEIJv5qEJPUAZMc1.png', 'active', '2026-11-25 06:08:39', '2026-11-25 06:08:39', 'storage/vcard-templates/QT5IVOnENTbgZlu0ttjBaoREVEIJv5qEJPUAZMc1.png'),
(2, 'Sample Template 02', NULL, 'vcard-templates/FtpRdcFw6gem0DZddDdIMlC1A1Stqcvs6hNRLBdA.png', 'active', '2026-11-25 06:09:31', '2026-11-25 06:09:31', 'storage/vcard-templates/FtpRdcFw6gem0DZddDdIMlC1A1Stqcvs6hNRLBdA.png'),
(3, 'Sample Template 03', NULL, 'vcard-templates/z8e7UUos5m9YjZVwQnMhzZLMScH9Axn8IwP8rnNh.png', 'active', '2026-11-25 06:09:41', '2026-11-25 06:09:41', 'storage/vcard-templates/z8e7UUos5m9YjZVwQnMhzZLMScH9Axn8IwP8rnNh.png'),
(4, 'Sample Template 04', NULL, 'vcard-templates/wyfNPY9w8tS70v2qjQT8SzPUcl27dRh23gZaFZ9f.png', 'active', '2026-11-25 06:09:51', '2026-11-25 06:09:51', 'storage/vcard-templates/wyfNPY9w8tS70v2qjQT8SzPUcl27dRh23gZaFZ9f.png'),
(5, 'Sample Template 05', NULL, 'vcard-templates/q52NzPi4bIVPDQtNwVDbzIgb7uOSv9uEu84HoIo2.png', 'active', '2026-11-25 06:10:02', '2026-11-25 06:10:02', 'storage/vcard-templates/q52NzPi4bIVPDQtNwVDbzIgb7uOSv9uEu84HoIo2.png'),
(6, 'Sample Template 06', NULL, 'vcard-templates/1dXTrLjqCOUooleVm72OSVA5QVQnYeCMY8l3vZvR.png', 'active', '2026-11-25 06:10:10', '2026-11-25 06:10:10', 'storage/vcard-templates/1dXTrLjqCOUooleVm72OSVA5QVQnYeCMY8l3vZvR.png'),
(7, 'Sample Template 07', NULL, 'vcard-templates/9x1zdxsuIRxCNIRZNpAFBcZtN6doUiBAw3ULU8gE.png', 'active', '2026-11-25 06:10:19', '2026-11-25 06:10:19', 'storage/vcard-templates/9x1zdxsuIRxCNIRZNpAFBcZtN6doUiBAw3ULU8gE.png'),
(8, 'Sample Template 08', NULL, 'vcard-templates/7dgimWmwiPEhSetP4O8VmCxjVuDhIjcQBM6aeFJr.png', 'active', '2026-11-25 06:10:45', '2026-11-25 06:10:45', 'storage/vcard-templates/7dgimWmwiPEhSetP4O8VmCxjVuDhIjcQBM6aeFJr.png'),
(9, 'Sample Template 09', NULL, 'vcard-templates/TKkvkBNXpNULiMf5lnEYh1d8NgRLkrcyjVtKONTR.png', 'active', '2026-11-25 06:10:52', '2026-11-25 06:10:52', 'storage/vcard-templates/TKkvkBNXpNULiMf5lnEYh1d8NgRLkrcyjVtKONTR.png'),
(10, 'Sample Template 10', NULL, 'vcard-templates/BWWufA1JRKCChoNa07987SaGfiiT7kk2IcuDFWAG.png', 'active', '2026-11-25 06:11:00', '2026-11-25 06:11:00', 'storage/vcard-templates/BWWufA1JRKCChoNa07987SaGfiiT7kk2IcuDFWAG.png');

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
  ADD KEY `organizations_plan_id_foreign` (`plan_id`);

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
  ADD KEY `users_plan_id_foreign` (`plan_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `organization_users`
--
ALTER TABLE `organization_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  ADD CONSTRAINT `organizations_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `users_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL;

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
