-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2026 at 06:00 PM
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
-- Database: `negosyotrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `commodity_variants`
--

CREATE TABLE `commodity_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `commodity_id` bigint(20) UNSIGNED NOT NULL,
  `unit_of_measure` varchar(50) NOT NULL,
  `srp` decimal(10,2) DEFAULT NULL,
  `prevailing_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commodity_variants`
--

INSERT INTO `commodity_variants` (`id`, `commodity_id`, `unit_of_measure`, `srp`, `prevailing_price`, `created_at`, `updated_at`) VALUES
(1, 1, '5kg', 5.00, NULL, '2026-10-13 02:42:52', '2026-10-13 02:42:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commodity_variants`
--
ALTER TABLE `commodity_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `commodity_variants_commodity_id_unit_of_measure_unique` (`commodity_id`,`unit_of_measure`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commodity_variants`
--
ALTER TABLE `commodity_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commodity_variants`
--
ALTER TABLE `commodity_variants`
  ADD CONSTRAINT `commodity_variants_commodity_id_foreign` FOREIGN KEY (`commodity_id`) REFERENCES `commodities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
