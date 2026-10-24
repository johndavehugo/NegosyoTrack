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
-- Table structure for table `calamities`
--

CREATE TABLE `calamities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `calamity_type` varchar(50) NOT NULL,
  `declaration_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calamities`
--

INSERT INTO `calamities` (`id`, `name`, `calamity_type`, `declaration_date`, `description`, `created_at`, `updated_at`) VALUES
(1, '123', 'LANDSLIDE', '2026-10-05', '123', '2026-10-24 06:22:06', '2026-10-24 06:22:06'),
(2, 'Bagyong Amihan', 'TYPHOON', '2026-01-12', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(3, 'Bagyong Betty', 'TYPHOON', '2026-02-03', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(4, 'Bagyong Chedeng', 'TYPHOON', '2026-03-18', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(5, 'Bagyong Dodong', 'TYPHOON', '2026-04-22', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(6, 'Bagyong Egay', 'TYPHOON', '2026-05-09', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(7, 'Magnitude 5.6 Earthquake', 'EARTHQUAKE', '2026-06-14', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(8, 'Magnitude 6.1 Earthquake', 'EARTHQUAKE', '2026-07-02', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(9, 'Barangay I Flash Flood', 'FLOOD', '2026-07-19', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(10, 'Barangay II River Flood', 'FLOOD', '2026-08-05', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(11, 'Barangay III Coastal Flood', 'FLOOD', '2026-08-21', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(12, 'Barangay IV Hillside Landslide', 'LANDSLIDE', '2026-09-03', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(13, 'Barangay V Landslide', 'LANDSLIDE', '2026-09-17', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(14, 'Bagyong Falcon', 'TYPHOON', '2026-10-01', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(15, 'Bagyong Goring', 'TYPHOON', '2026-10-12', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(16, 'Barangay VI Flooding', 'FLOOD', '2026-11-06', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(17, 'Magnitude 5.2 Earthquake', 'EARTHQUAKE', '2026-11-20', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(18, 'Barangay II Slope Collapse', 'LANDSLIDE', '2026-12-02', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(19, 'Bagyong Hanna', 'TYPHOON', '2026-12-15', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(20, 'Citywide Flooding', 'FLOOD', '2026-12-24', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(21, 'Barangay III Landslide', 'LANDSLIDE', '2026-12-28', 'Sample calamity record for reporting and chart testing.', '2026-10-24 06:33:41', '2026-10-24 06:33:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calamities`
--
ALTER TABLE `calamities`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calamities`
--
ALTER TABLE `calamities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
