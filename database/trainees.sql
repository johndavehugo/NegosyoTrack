-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 10:07 AM
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
-- Database: `negosyo_center_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `trainees`
--

CREATE TABLE `trainees` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `api_entity_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `mobile_no` varchar(50) DEFAULT NULL,
  `training_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `trainees`
--

INSERT INTO `trainees` (`id`, `api_entity_no`, `full_name`, `gender`, `birth_date`, `mobile_no`, `training_id`, `contact_no`) VALUES
('tren-01a169b1-1c75-716f-aa2f-2fdc8b43103f', 'GLPLCA5343', 'VLAIZE L. RICABORDA', 'Female', '2026-05-16', '', 'trin-01a15f60-ec4f-70b9-9f3f-13d37b4e5424', '09102323232'),
('tren-01a16c29-d0bb-7336-849c-bc094f6efb41', 'KCJTTG0667', 'REYNEL DEGAMO GASALATAN', 'Male', '2000-12-03', '09707205869', 'trin-01a15f60-ec4f-70b9-9f3f-13d37b4e5424', '0910232323'),
('tren-01a16c9a-3b7b-7089-8c5e-86030472df4f', 'IXVHVE5449', 'JOSEFA CAÑETE MONTILLA', 'Female', '1983-02-06', '09268288974', 'trin-01a15f60-ec4f-70b9-9f3f-13d37b4e5424', '09268288974');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trainees`
--
ALTER TABLE `trainees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_entity_no` (`api_entity_no`),
  ADD KEY `training_id` (`training_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trainees`
--
ALTER TABLE `trainees`
  ADD CONSTRAINT `trainees_ibfk_1` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
