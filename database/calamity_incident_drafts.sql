-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 06:21 PM
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
-- Database: `zortzy`
--

-- --------------------------------------------------------

--
-- Table structure for table `calamity_incident_drafts`
--

CREATE TABLE `calamity_incident_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `draft_key` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `calamity_id` bigint(20) UNSIGNED NOT NULL,
  `juridical_id` varchar(255) NOT NULL,
  `juridical_name` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `estimated_cost_of_damages` decimal(15,2) DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calamity_incident_drafts`
--
ALTER TABLE `calamity_incident_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `calamity_incident_drafts_draft_key_juridical_id_unique` (`draft_key`,`juridical_id`),
  ADD KEY `calamity_incident_drafts_draft_key_index` (`draft_key`),
  ADD KEY `calamity_incident_drafts_user_id_index` (`user_id`),
  ADD KEY `calamity_incident_drafts_calamity_id_index` (`calamity_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calamity_incident_drafts`
--
ALTER TABLE `calamity_incident_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
