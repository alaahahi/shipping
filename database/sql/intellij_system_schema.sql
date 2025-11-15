-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 15, 2025 at 01:27 AM
-- Server version: 5.7.41-cll-lve
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `intellij_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `results` int(11) NOT NULL DEFAULT '0',
  `no` int(11) NOT NULL,
  `deleted_at` date DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `note` text,
  `car_type` varchar(255) DEFAULT NULL,
  `vin` varchar(255) DEFAULT NULL,
  `car_number` int(20) DEFAULT NULL,
  `dinar` double DEFAULT '0',
  `dolar_price` double DEFAULT '0',
  `dolar_custom` double DEFAULT '0',
  `checkout` double DEFAULT '0',
  `shipping_dolar` double DEFAULT '0',
  `coc_dolar` double DEFAULT '0',
  `note1` varchar(255) DEFAULT NULL,
  `total` double DEFAULT '0',
  `paid` double DEFAULT '0',
  `profit` double DEFAULT '0',
  `date` date DEFAULT NULL,
  `car_color` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `expenses` double DEFAULT NULL,
  `dinar_s` double DEFAULT '0',
  `dolar_price_s` double DEFAULT '0',
  `dolar_custom_s` double DEFAULT '0',
  `checkout_s` double DEFAULT '0',
  `shipping_dolar_s` double DEFAULT '0',
  `coc_dolar_s` double DEFAULT '0',
  `total_s` double DEFAULT '0',
  `discount` int(11) NOT NULL DEFAULT '0',
  `expenses_s` double DEFAULT '0',
  `is_exit` int(11) DEFAULT '0',
  `contract_id` int(11) DEFAULT '0',
  `owner_id` int(11) DEFAULT NULL,
  `year_date` int(11) DEFAULT NULL,
  `car_have_expenses` int(10) DEFAULT '0',
  `land_shipping` int(11) DEFAULT '0',
  `land_shipping_dinar` int(11) DEFAULT '0',
  `land_shipping_dinar_s` int(11) DEFAULT '0',
  `land_shipping_s` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `car_contract`
--

CREATE TABLE `car_contract` (
  `id` int(11) NOT NULL,
  `name_seller` varchar(255) NOT NULL,
  `phone_seller` varchar(255) DEFAULT NULL,
  `address_seller` varchar(255) DEFAULT NULL,
  `seller_id_number` varchar(100) DEFAULT NULL,
  `name_buyer` varchar(255) NOT NULL,
  `phone_buyer` varchar(255) DEFAULT NULL,
  `address_buyer` varchar(255) DEFAULT NULL,
  `buyer_id_number` varchar(100) DEFAULT NULL,
  `tex_seller` int(11) DEFAULT NULL,
  `tex_seller_dinar` int(11) DEFAULT NULL,
  `tex_buyer` int(11) DEFAULT NULL,
  `tex_buyer_dinar` int(11) DEFAULT NULL,
  `vin` varchar(255) NOT NULL,
  `car_name` varchar(255) NOT NULL,
  `modal` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `no` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `system_note` varchar(255) DEFAULT NULL,
  `car_price` int(11) DEFAULT NULL,
  `car_paid` int(11) DEFAULT NULL,
  `tex_seller_paid` int(11) DEFAULT NULL,
  `tex_seller_dinar_paid` int(11) DEFAULT NULL,
  `tex_buyer_paid` int(11) DEFAULT NULL,
  `tex_buyer_dinar_paid` int(11) DEFAULT NULL,
  `no_s` varchar(255) DEFAULT NULL,
  `car_name_s` varchar(255) DEFAULT NULL,
  `modal_s` varchar(255) DEFAULT NULL,
  `size_s` int(11) DEFAULT NULL,
  `color_s` varchar(255) DEFAULT NULL,
  `vin_s` varchar(255) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `verification_token` varchar(64) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `year_date` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `car_expenses`
--

CREATE TABLE `car_expenses` (
  `id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amount_dinar` int(11) DEFAULT NULL,
  `amount_dollar` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason_id` int(11) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `car_images`
--

CREATE TABLE `car_images` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT '2025'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `car_model`
--

CREATE TABLE `car_model` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contract`
--

CREATE TABLE `contract` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` bigint(20) DEFAULT '0',
  `paid` bigint(20) DEFAULT '0',
  `note` varchar(255) DEFAULT NULL,
  `created` date NOT NULL,
  `price_dinar` double DEFAULT '0',
  `paid_dinar` double DEFAULT '0',
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='1';

-- --------------------------------------------------------

--
-- Table structure for table `contract_img`
--

CREATE TABLE `contract_img` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `driving`
--

CREATE TABLE `driving` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `car_number` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `year_date` int(11) NOT NULL,
  `car_type` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `deleted_at` date DEFAULT NULL,
  `note` text,
  `color` varchar(255) DEFAULT NULL,
  `vin` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='3';

-- --------------------------------------------------------

--
-- Table structure for table `exit_car`
--

CREATE TABLE `exit_car` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` date DEFAULT NULL,
  `expenses_type_id` int(11) NOT NULL,
  `factor` int(11) DEFAULT '1',
  `transaction_id` int(11) DEFAULT NULL,
  `year_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `expenses_type`
--

CREATE TABLE `expenses_type` (
  `id` int(11) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_kr` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `info`
--

CREATE TABLE `info` (
  `id` varchar(255) NOT NULL,
  `phone` longtext,
  `fname` longtext,
  `lname` varchar(255) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `p1` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `work` varchar(255) DEFAULT NULL,
  `study` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `p2` varchar(255) DEFAULT NULL,
  `p3` varchar(255) DEFAULT NULL,
  `p4` varchar(255) DEFAULT NULL,
  `date1` varchar(255) DEFAULT NULL,
  `date2` varchar(255) DEFAULT NULL,
  `p5` varchar(255) DEFAULT NULL,
  `p6` varchar(255) DEFAULT NULL,
  `p7` varchar(255) DEFAULT NULL,
  `updated_at` text,
  `created_at` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `massage`
--

CREATE TABLE `massage` (
  `id` int(11) NOT NULL,
  `image` text,
  `voice` text,
  `text` text,
  `sender_id` bigint(20) DEFAULT NULL,
  `receiver_id` bigint(20) DEFAULT NULL,
  `aes` varchar(255) DEFAULT NULL,
  `Lat` varchar(255) DEFAULT NULL,
  `lng` varchar(255) DEFAULT NULL,
  `is_download` tinyint(4) NOT NULL DEFAULT '0',
  `is_read` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `name`
--

CREATE TABLE `name` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(20) NOT NULL,
  `husband_b` varchar(255) DEFAULT NULL,
  `husband_hb` varchar(255) DEFAULT NULL,
  `husband_mcv` varchar(255) DEFAULT NULL,
  `husband_mch` varchar(255) DEFAULT NULL,
  `husband_hemoglobin_a` varchar(255) DEFAULT NULL,
  `husband_hemoglobin_f` varchar(255) DEFAULT NULL,
  `husband_hemoglobin_a2` varchar(255) DEFAULT NULL,
  `husband_hbs` varchar(255) DEFAULT NULL,
  `husband_hcv` varchar(255) DEFAULT NULL,
  `husband_hiv` varchar(255) DEFAULT NULL,
  `husband_tb` varchar(255) DEFAULT NULL,
  `husband_syphilis` varchar(255) DEFAULT NULL,
  `husband_tpha` varchar(255) DEFAULT NULL,
  `husband_results` tinyint(4) DEFAULT '1',
  `wife_b` varchar(255) DEFAULT NULL,
  `wife_hb` varchar(255) DEFAULT NULL,
  `wife_mcv` varchar(255) DEFAULT NULL,
  `wife_mch` varchar(255) DEFAULT NULL,
  `wife_hemoglobin_a` varchar(255) DEFAULT NULL,
  `wife_hemoglobin_f` varchar(255) DEFAULT NULL,
  `wife_hemoglobin_a2` varchar(255) DEFAULT NULL,
  `wife_hbs` varchar(255) DEFAULT NULL,
  `wife_hcv` varchar(255) DEFAULT NULL,
  `wife_hiv` varchar(255) DEFAULT NULL,
  `wife_tb` varchar(255) DEFAULT NULL,
  `wife_syphilis` varchar(255) DEFAULT NULL,
  `wife_tpha` varchar(255) DEFAULT NULL,
  `wife_results` tinyint(4) DEFAULT '1',
  `user_id` bigint(20) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `first_title_ar` varchar(255) DEFAULT NULL,
  `first_title_kr` varchar(255) DEFAULT NULL,
  `second_title_ar` varchar(255) DEFAULT NULL,
  `second_title_kr` varchar(255) DEFAULT NULL,
  `third_title_ar` varchar(255) DEFAULT NULL,
  `third_title_kr` varchar(255) DEFAULT NULL,
  `mobile_kik` varchar(255) DEFAULT NULL,
  `mobile_erb` varchar(255) DEFAULT NULL,
  `address_kik` varchar(255) DEFAULT NULL,
  `address_erb` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `default_price_p` longtext,
  `default_price_s` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `is_pay` int(11) NOT NULL DEFAULT '0',
  `morphed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `morphed_id` int(11) DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$',
  `user_added` int(11) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `discount` double DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `details` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions_contract`
--

CREATE TABLE `transactions_contract` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `is_pay` int(11) NOT NULL DEFAULT '0',
  `morphed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `morphed_id` int(11) DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$',
  `user_added` int(11) DEFAULT NULL,
  `created` date DEFAULT NULL,
  `discount` double DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `s_amount` int(11) DEFAULT '0',
  `b_amount` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions_images`
--

CREATE TABLE `transactions_images` (
  `id` int(11) NOT NULL,
  `transactions_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `no` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `sender_note` varchar(255) DEFAULT NULL,
  `currency` varchar(255) NOT NULL DEFAULT '$',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` date DEFAULT NULL,
  `stauts` varchar(255) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `receiver_note` varchar(255) DEFAULT NULL,
  `fee` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type_id` bigint(20) DEFAULT NULL,
  `is_band` tinyint(4) NOT NULL DEFAULT '0',
  `percentage` int(11) DEFAULT '0',
  `morphed_id` bigint(20) UNSIGNED DEFAULT NULL,
  `morphed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` date DEFAULT NULL,
  `owner_id` int(11) DEFAULT '1',
  `year_date` int(11) DEFAULT '2024'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE `user_type` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance` bigint(20) NOT NULL DEFAULT '0',
  `card` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `balance_dinar` double DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE `warehouse` (
  `id` int(11) NOT NULL,
  `car_type` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `car_color` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `note` text,
  `car_number` varchar(255) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

ALTER TABLE `car`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vina` (`vin`);

ALTER TABLE `car_contract`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `car_contract_verification_token_unique` (`verification_token`);

ALTER TABLE `car_expenses`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `car_images`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `car_model`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contract`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `car_id` (`car_id`);

ALTER TABLE `contract_img`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `driving`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `exit_car`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `expenses_type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

ALTER TABLE `massage`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `name`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `owner`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_wallet_id_foreign` (`wallet_id`);

ALTER TABLE `transactions_contract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_contract_wallet_id_foreign` (`wallet_id`);

ALTER TABLE `transactions_images`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `a` (`type_id`);

ALTER TABLE `user_type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallets_user_id_foreign` (`user_id`);

ALTER TABLE `warehouse`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT sections
--

ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `car_contract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `car_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `car_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `car_model`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contract_img`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `driving`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `exit_car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `expenses_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `massage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `name`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `owner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `results`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `transactions_contract`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `transactions_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `warehouse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints
--

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `user_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

