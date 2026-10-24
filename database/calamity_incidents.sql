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
-- Table structure for table `calamity_incidents`
--

CREATE TABLE `calamity_incidents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `juridical_id` varchar(100) NOT NULL,
  `juridical_name` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `calamity_id` bigint(20) UNSIGNED NOT NULL,
  `date_occurred` date NOT NULL,
  `nature_of_damage` enum('PARTIAL','TOTAL') NOT NULL,
  `estimated_cost_of_damages` decimal(15,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `status` enum('PENDING_VERIFICATION','VERIFIED','AID_RELEASED') NOT NULL DEFAULT 'VERIFIED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calamity_incidents`
--

INSERT INTO `calamity_incidents` (`id`, `juridical_id`, `juridical_name`, `barangay`, `calamity_id`, `date_occurred`, `nature_of_damage`, `estimated_cost_of_damages`, `remarks`, `photos`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ZIEGOO4360', ' DIONISIO O. VILLARIZ-CARENDERIA', 'Barangay VI', 1, '2026-10-05', 'PARTIAL', 975000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(2, 'HULELN3094', ' KYLE MOTORS MOTORCYCLE PARTS AND ACCESSORIES STORE', 'San Juan', 1, '2026-10-05', 'PARTIAL', 50000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(3, 'ZIHCMX8590', ' LJJ PRINTING SERVICES', 'Barangay IV', 1, '2026-10-05', 'TOTAL', 125000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(4, 'SHNLUU7068', ' MARICEL A. CARBILLEDO', 'Bagonbon', 1, '2026-10-05', 'PARTIAL', 200000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(5, 'ZIAYXK4212', ' NOEL S. GONZAGA', 'Codcod', 1, '2026-10-05', 'PARTIAL', 275000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(6, 'GPRCZX1275', ' REVELYN N. BENEMERITO', 'Guadalupe', 2, '2026-01-12', 'PARTIAL', 900000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(7, 'YUHWOJ8867', ' REVELYN N. BENEMERITO', 'Guadalupe', 2, '2026-01-12', 'TOTAL', 975000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(8, 'SJCSLY9871', '\'MAYA GUEST HOUSE - PO. TALISAY', 'San Juan', 2, '2026-01-12', 'PARTIAL', 50000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(9, 'SNVVTO3850', '1.16 SARI-SARI STORE - FATIMA VILL.', 'Rizal', 2, '2026-01-12', 'PARTIAL', 125000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(10, 'WWMWCP5418', '1.16 SARI-SARI STORE - FATIMA VILL.', 'Rizal', 2, '2026-01-12', 'TOTAL', 200000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(11, 'QBTUOI9416', '10 -25 SARI SARI STORE', 'Palampas', 3, '2026-02-03', 'TOTAL', 825000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(12, 'MFUMMK9377', '2 CJ\'S MOTORCYCLE PARTS SUPPLY - BRGY.II', 'Barangay II', 3, '2026-02-03', 'PARTIAL', 900000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(13, 'QRQTNQ1489', '247 SARI-SARI STORE', 'Barangay IV', 3, '2026-02-03', 'PARTIAL', 975000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(14, 'NBIAEN1117', '247 SARI-SARI STORE - BRGY. V', 'Barangay V', 3, '2026-02-03', 'TOTAL', 50000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(15, 'CPGZAM8626', '2JS\' E-LOAD AND PREPAID CARDS RETAILING - LOCSIN ST.', 'Barangay II', 3, '2026-02-03', 'PARTIAL', 125000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(16, 'CIGXDB3998', '3/21 FOOD HUB - BRGY. I', 'Barangay I', 4, '2026-03-18', 'PARTIAL', 750000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(17, 'JUYQQG6788', '3D-CCAM CORPORATION', 'Barangay III', 4, '2026-03-18', 'PARTIAL', 825000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(18, 'JUFIIV6590', '3D-CCAM CORPORATION - S. CARMONA ST.', 'Barangay III', 4, '2026-03-18', 'TOTAL', 900000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(19, 'UFQBXY2860', '3M\"S AQUATIB WATER REFILLING STATION - PO. MOLAVE', 'Barangay I', 4, '2026-03-18', 'PARTIAL', 975000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(20, 'HEDSVU7205', '3M\'S TIBO SARI-SARI STORE', 'Barangay I', 4, '2026-03-18', 'PARTIAL', 50000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(21, 'YJTXIJ3339', '3S MARIAS WELDING SHOP', 'Barangay I', 5, '2026-04-22', 'PARTIAL', 675000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(22, 'KNUKOQ9653', '4 C\'S REAL ESTATE LEASING - F.C. LEDESMA AVE.', 'Barangay II', 5, '2026-04-22', 'TOTAL', 750000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(23, 'XZYKAC5365', '4\'S PISONET - BRGY. GUADALUPE', 'Ermita', 5, '2026-04-22', 'PARTIAL', 825000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(24, 'XYEYVM8634', '4A CONSTRUCTION SERVICES', 'Barangay I', 5, '2026-04-22', 'PARTIAL', 900000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(25, 'DVQPIW3730', '4M DEVELOPMENT CORP. - BRGY. PALAMPAS', 'Palampas', 5, '2026-04-22', 'TOTAL', 975000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(26, 'KFXYUA0765', '4M DEVELOPMENT CORP. - BRGY. PALAMPAS', 'Palampas', 6, '2026-05-09', 'TOTAL', 600000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(27, 'RBHWSI4916', '506 CAFE - REFRESHMENT', 'Rizal', 6, '2026-05-09', 'PARTIAL', 675000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(28, 'EGLPBX4553', '528 ALKQUA\'S WATER REFILLING STATION - V. GUSTILO ST.', 'Barangay V', 6, '2026-05-09', 'PARTIAL', 750000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(29, 'HRUNDX9470', '528 ALKQUA\'S WATER REFILLING STATION - V. GUSTILO ST.', 'Barangay V', 6, '2026-05-09', 'TOTAL', 825000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(30, 'IALTIW2435', '528 APARTMENT', 'Barangay V', 6, '2026-05-09', 'PARTIAL', 900000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(31, 'XKFFUR0548', '5J\'S FOOD PRODUCTS', 'Barangay I', 7, '2026-06-14', 'PARTIAL', 525000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(32, 'IPWWGR8251', '6127 AIRCONDITIONING SERVICES - ECO-TOURISM HIGHWAY', 'Barangay II', 7, '2026-06-14', 'PARTIAL', 600000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(33, 'OKZZPW5537', '7777 SCHOOL, OFFICE SUPPLIES & EQUIPMENT TRADING-MANSFIELD SUBD.', 'Barangay IV', 7, '2026-06-14', 'TOTAL', 675000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(34, 'QGLCXT4195', '8-12 CONVENIENCE STORE - BROCE ST.', 'Barangay IV', 7, '2026-06-14', 'PARTIAL', 750000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(35, 'WKYEKG1726', '99 THANKS SARI-SARI- STORE - PUBLIC MARKET', 'Barangay IV', 7, '2026-06-14', 'PARTIAL', 825000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(36, 'RWBOWQ3576', '9K INTERNATIONAL MANPOWER AND RECRUITMENT SERVICES CORP. - RECRUITMENT AGENCY', 'Barangay III', 8, '2026-07-02', 'PARTIAL', 450000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(37, 'BPBLXD6176', 'A AND R HI WAY TIRE RECAPPING SERVICES - BRGY. II', 'Barangay II', 8, '2026-07-02', 'TOTAL', 525000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(38, 'AWANEX6753', 'A\'G LIQUEFIED PETROLEUM GAS TRADING - BRGY. QUEZON', 'Quezon', 8, '2026-07-02', 'PARTIAL', 600000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(39, 'HGDBWH5786', 'A\'LOYS ENTERPRISES - V. GUSTILO ST.', 'Barangay IV', 8, '2026-07-02', 'PARTIAL', 675000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(40, 'QGROEI9167', 'A\'LOYS ENTERPRISES - V. GUSTILO ST.', 'Barangay IV', 8, '2026-07-02', 'TOTAL', 750000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(41, 'YOUSOK7775', 'A\'LOYS ENTERPRISES - V. GUSTILO ST.', 'Barangay IV', 9, '2026-07-19', 'TOTAL', 375000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(42, 'IKAAMA6607', 'A.C.E GALEN PHARMACY - BRGY. PROSPERIDAD', 'Prosperidad', 9, '2026-07-19', 'PARTIAL', 450000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(43, 'OGJCYJ6217', 'A.E.C. MEDS PHARMACY - BRGY. IV', 'Barangay IV', 9, '2026-07-19', 'PARTIAL', 525000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(44, 'PDTOQE9468', 'A.H.A PRINTING SERVICES', 'Rizal', 9, '2026-07-19', 'TOTAL', 600000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(45, 'TCHCDD0816', 'A2J COMMERCIAL CORPORATION - AZCONA ST.', 'Barangay V', 9, '2026-07-19', 'PARTIAL', 675000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(46, 'AMXFDF6302', 'AARON B. MIJARES - BRGY. PALAMPAS', 'Palampas', 10, '2026-08-05', 'PARTIAL', 300000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(47, 'YXDKUY9032', 'ABADAJOS, ROSEMARIE M. - FISHING1', 'Barangay I', 10, '2026-08-05', 'PARTIAL', 375000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(48, 'ARKBQJ1163', 'ABANALO SPACE RENTAL', 'Barangay II', 10, '2026-08-05', 'TOTAL', 450000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(49, 'SLFAQP3012', 'ABANALO SPACE RENTAL - REAL ESTATE LESSOR', 'Barangay II', 10, '2026-08-05', 'PARTIAL', 525000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(50, 'WIEKGS7416', 'ABE\'S AUTO AND MOTOR VEHICLE PARTS TRADING - BRGY. CODCOD', 'Codcod', 10, '2026-08-05', 'PARTIAL', 600000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(51, 'ZYMSOX2184', 'ABEGAIL M CORPUZ', 'Barangay VI', 11, '2026-08-21', 'PARTIAL', 225000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(52, 'YQFIRB0170', 'ABEGAIL M. CORPUZ - SARI-SARI', 'Barangay VI', 11, '2026-08-21', 'TOTAL', 300000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(53, 'ERLWOT6175', 'ABEL L. ALLARCE - MOTORCAB', 'Rizal', 11, '2026-08-21', 'PARTIAL', 375000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(54, 'LRZEWS8212', 'ABEL V. MACAPASO - MOTORCAB', 'Rizal', 11, '2026-08-21', 'PARTIAL', 450000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(55, 'MAZDAR1532', 'ABELLY M. BUSTAMANTE - ST. VINCENT SUBD.', 'Barangay II', 11, '2026-08-21', 'TOTAL', 525000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(56, 'MIJCTI6682', 'ABET M. BADIO - PUBLIC MARKET', 'Barangay V', 12, '2026-09-03', 'TOTAL', 150000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(57, 'XTGFMW5172', 'ABET M. BADIO - PUBLIC MARKET', 'Barangay IV', 12, '2026-09-03', 'PARTIAL', 225000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(58, 'AXSSAD7348', 'ABONDIO M. ACABO - MOTORCAB2', 'Punao', 12, '2026-09-03', 'PARTIAL', 300000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(59, 'VOJYSH2729', 'ABONG SARI-SARI STORE - UPPER MAGLAHOS', 'Quezon', 12, '2026-09-03', 'TOTAL', 375000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(60, 'DZHQBG6638', 'ABRAHAM B. MACALUA - PEDICAB', 'Barangay VI', 12, '2026-09-03', 'PARTIAL', 450000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(61, 'XRFPAL1909', 'ABUCAY, ELDE JUN A.', 'Rizal', 13, '2026-09-17', 'PARTIAL', 75000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(62, 'RRSNXU8253', 'ABUNDIO C. GORPIO - MOTORCAB', 'Barangay III', 13, '2026-09-17', 'PARTIAL', 150000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(63, 'IPBXGC3007', 'ABUNDIO I. PASGALA - SARI-SARI', 'Nataban', 13, '2026-09-17', 'TOTAL', 225000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(64, 'SWKQVH8110', 'ABUNDIO I. PASGALA - SARI-SARI', 'Nataban', 13, '2026-09-17', 'PARTIAL', 300000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(65, 'NXBQWD4782', 'ACDI MULTI PURPOSE COOPERATIVE - BRGY. III', 'Barangay III', 13, '2026-09-17', 'PARTIAL', 375000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(66, 'VBGMMK4724', 'ACE HARDWARE PHILIPPINES, INC.- CITY MALL', 'Barangay II', 14, '2026-10-01', 'PARTIAL', 1000000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(67, 'YDZBDA4826', 'ACES ALUMINUM', 'Barangay II', 14, '2026-10-01', 'TOTAL', 75000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(68, 'APTSRZ4503', 'ACGC CONSTRUCTION SERVICES - CONTRACTOR', 'Barangay II', 14, '2026-10-01', 'PARTIAL', 150000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(69, 'IIIJQM9293', 'ACGC CONSTRUCTION SERVICES - CONTRACTOR', 'Barangay II', 14, '2026-10-01', 'PARTIAL', 225000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(70, 'DCISLG1544', 'ACHIEVER\'S FABRICATION - ONYX ST. TEACHERS VILLAGE', 'Barangay II', 14, '2026-10-01', 'TOTAL', 300000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(71, 'ADPGDC7454', 'ACMAD M. MIPANTAO - MOTORCAB1', 'Barangay VI', 15, '2026-10-12', 'TOTAL', 925000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(72, 'SDLDJE2704', 'ACP FOOD CONTRACTING SERVICES - BRGY. III', 'Barangay III', 15, '2026-10-12', 'PARTIAL', 1000000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(73, 'IIWFBZ6951', 'ADDLIFE WATER REFILLING STATION', 'Barangay IV', 15, '2026-10-12', 'PARTIAL', 75000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(74, 'JMLYUA3776', 'ADELA A. CANONIGO - PO. ILANG-ILANG, BRGY QUEZON', 'Quezon', 15, '2026-10-12', 'TOTAL', 150000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(75, 'PCDCNA4874', 'ADELA S. CA?EDO - PUBLIC MARKET', 'Barangay IV', 15, '2026-10-12', 'PARTIAL', 225000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(76, 'UQLJZN2874', 'ADELAIDA R. POTOT - BRGY. RIZAL', 'Rizal', 16, '2026-11-06', 'PARTIAL', 850000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(77, 'PWOQSK6516', 'ADELINA O. EMPOS - SARISARI', 'Codcod', 16, '2026-11-06', 'PARTIAL', 925000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(78, 'WCMEIT8271', 'ADONAI MOTOR PARTS SHOP', 'Barangay II', 16, '2026-11-06', 'TOTAL', 1000000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(79, 'ZEUCPV2692', 'ADONAI MOTOR PARTS SHOP - BRGY. I', 'Barangay I', 16, '2026-11-06', 'PARTIAL', 75000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(80, 'VVTXCQ1323', 'ADONAI MOTOR PARTS SHOP - BRGY. PALAMPAS', 'Barangay I', 16, '2026-11-06', 'PARTIAL', 150000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(81, 'OJGGSI3678', 'ADONAI MOTOR PARTS SHOP - BRGY. RIZAL', 'Rizal', 17, '2026-11-20', 'PARTIAL', 775000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(82, 'EGCQUT6282', 'ADONAI MOTOR PARTS SHOP - SCMCI COMPUND', 'Barangay I', 17, '2026-11-20', 'TOTAL', 850000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(83, 'MXDDPU6971', 'ADONIS B. SANDOVAL - FISHING', 'Buluangan', 17, '2026-11-20', 'PARTIAL', 925000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(84, 'WLNSAG0198', 'ADRIAN MOTORCYCLE PARTS SHOP', 'Barangay II', 17, '2026-11-20', 'PARTIAL', 1000000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(85, 'AESMDC4733', 'ADRIAN MOTORCYCLE PARTS SHOP - BROCE ST.', 'Barangay V', 17, '2026-11-20', 'TOTAL', 75000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(86, 'BOKYLH4874', 'ADRIANA P. ESCOBAR - LOCSIN ST.', 'Barangay II', 18, '2026-12-02', 'TOTAL', 700000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(87, 'VLZMEE7120', 'ADRIANA P. VERSARIO - PROPER GUADALUPE', 'Guadalupe', 18, '2026-12-02', 'PARTIAL', 775000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(88, 'MRSLKA4892', 'ADZ CAL CONSTRUCTION SUPPLY AND HOLLOWBLOCKS MANUFACTURING-HOLLOWBLOCKS', 'Rizal', 18, '2026-12-02', 'PARTIAL', 850000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(89, 'PIKOWG7850', 'AENAMORATA SARI-SARI STORE', 'Ermita', 18, '2026-12-02', 'TOTAL', 925000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(90, 'DUPKXI8085', 'AFG BOOKEEPING SERVICES - CAMPO SIETE', 'Barangay II', 18, '2026-12-02', 'PARTIAL', 1000000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(91, 'GZTLTD1879', 'AFG REAL ESTATE BROKERAGE SERVICES - MANSFIELD SUBD.', 'Barangay II', 19, '2026-12-15', 'PARTIAL', 625000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(92, 'ZWVZRC0714', 'AGAPITO R. FAT - MOTORCAB01', 'Barangay II', 19, '2026-12-15', 'PARTIAL', 700000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(93, 'PAEAJH4204', 'AGENCIA DE SAN JOSE & JEWELRY SHOP - S. CARMONA ST.', 'Barangay III', 19, '2026-12-15', 'TOTAL', 775000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(94, 'NRSHYK3027', 'AGENCIA NEGRENSE PAWNSHOP - S. CARMONA ST.', 'Barangay II', 19, '2026-12-15', 'PARTIAL', 850000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(95, 'ARRMTI8330', 'AGNES M BELARGO- FOOD VENDOR', 'Codcod', 19, '2026-12-15', 'PARTIAL', 925000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(96, 'GQWEBO7054', 'AGNES M. BELARGO - SO. ILIRANAN', 'Codcod', 20, '2026-12-24', 'PARTIAL', 550000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(97, 'VCPLFD7164', 'AGNES MANTALABA BELARGO', 'Codcod', 20, '2026-12-24', 'TOTAL', 625000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(98, 'PRZRAN2039', 'AGOSTIN T. PACALDO - FISHING', 'Ermita', 20, '2026-12-24', 'PARTIAL', 700000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(99, 'FXWNSR0293', 'AGRILIFE AGRIVET SUPPLIES OPC - VALDEVIA ST.', 'Barangay IV', 20, '2026-12-24', 'PARTIAL', 775000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(100, 'EAWHBM3081', 'AGRO-LINK FACTORS, INC. - EMERALD ARCADE', 'Palampas', 20, '2026-12-24', 'TOTAL', 850000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(101, 'SPJVRG1318', 'AGUA DE LEDESMA WATER REFILLING STATION - WATER REFILLING STATION', 'Barangay I', 21, '2026-12-28', 'TOTAL', 475000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(102, 'SNGOTM3616', 'AGUSTIN G. MAHINAY - MOTORCAB', 'Rizal', 21, '2026-12-28', 'PARTIAL', 550000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(103, 'RCRSAI1462', 'AGUSTINO C. ALEGATO - PEDICAB', 'Barangay III', 21, '2026-12-28', 'PARTIAL', 625000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'AID_RELEASED', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(104, 'SIMUNY4503', 'AGUSTINO C. ALEGATO - PEDICAB', 'Barangay III', 21, '2026-12-28', 'TOTAL', 700000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'PENDING_VERIFICATION', '2026-10-24 06:33:41', '2026-10-24 06:33:41'),
(105, 'PCLXUE9785', 'AGUSTINO N. CABUNGCAL - MOTORCAB', 'Rizal', 21, '2026-12-28', 'PARTIAL', 775000.00, 'Sample incident sourced from the SCIMS businesses module.', NULL, 'VERIFIED', '2026-10-24 06:33:41', '2026-10-24 06:33:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calamity_incidents`
--
ALTER TABLE `calamity_incidents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_incident_per_calamity` (`calamity_id`,`juridical_id`),
  ADD KEY `idx_juridical` (`juridical_id`),
  ADD KEY `idx_calamity` (`calamity_id`),
  ADD KEY `idx_damage_date` (`date_occurred`,`nature_of_damage`),
  ADD KEY `idx_incident_barangay` (`barangay`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calamity_incidents`
--
ALTER TABLE `calamity_incidents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
