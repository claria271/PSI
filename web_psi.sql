-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 27, 2025 at 06:38 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web_psi`
--

-- --------------------------------------------------------

--
-- Table structure for table `keluarga`
--

CREATE TABLE `keluarga` (
  `id` int UNSIGNED NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `nik` varchar(32) DEFAULT NULL,
  `no_wa` varchar(32) DEFAULT NULL,
  `alamat` text,
  `dapil` varchar(50) NOT NULL,
  `kecamatan` varchar(50) NOT NULL,
  `jumlah_anggota` int DEFAULT NULL,
  `jumlah_bekerja` int DEFAULT NULL,
  `total_penghasilan` varchar(50) DEFAULT NULL,
  `kenal` varchar(20) DEFAULT NULL,
  `sumber` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keluarga`
--

INSERT INTO `keluarga` (`id`, `nama_lengkap`, `nik`, `no_wa`, `alamat`, `dapil`, `kecamatan`, `jumlah_anggota`, `jumlah_bekerja`, `total_penghasilan`, `kenal`, `sumber`, `created_at`) VALUES
(1, 'Claria Privana Ratna Wati', '256984365987589', '14865159555', 'yos sudarso', 'Kota Surabaya 2', 'Tambaksari', 7, 1, '0', 'Tidak Pernah', NULL, '2025-10-19 23:29:04'),
(2, 'Claria Privana Ratna Wati', '256984365987589', '14865159555', 'yos sudarso', 'Kota Surabaya 2', 'Pabean Cantikan', 7, 1, '0', 'Tidak Pernah', NULL, '2025-10-20 03:58:50'),
(3, 'Claria Privana Ratna Wati', '256984365987589', '14865159555', 'Surabaya', 'Kota Surabaya 2', 'Pabean Cantikan', 7, 1, '0', 'Tidak Pernah', NULL, '2025-10-20 05:26:05'),
(4, 'Claria Privana Ratna Wati', '256984365987589', '14865159555', 'Surabaya', 'Kota Surabaya 3', 'Mulyorejo', 8, 1, '0', 'Ya', 'Lainnya', '2025-10-27 04:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `alamat_lengkap` varchar(255) NOT NULL,
  `nomor_telepon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `alamat_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `nama_lengkap`, `alamat_lengkap`, `nomor_telepon`, `alamat_email`, `password`, `role`) VALUES
(1, 'ardya', 'surabaya', '8921379289182', 'ardya@gmail.com', '$2y$10$pDGjgi7kzIvOKWDOKpRIVe4IEO9MRRvfmtOt3lqg4hDtiyAlX47cq', 'user'),
(2, 'Claria Privana Ratna Wati', 'Surabaya', '8585708680', 'claria@gmail.com', '$2y$10$zesK3EUcLUVjrQOvzhOZreyqJaQWNhemiVqG9Lr3x7Hvr.LRyCrBm', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keluarga`
--
ALTER TABLE `keluarga`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`alamat_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keluarga`
--
ALTER TABLE `keluarga`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
