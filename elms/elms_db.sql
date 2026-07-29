-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 12:50 PM
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
-- Database: `elms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `status` enum('present','absent') DEFAULT 'present',
  `clocked_in_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `work_date`, `status`, `clocked_in_at`) VALUES
(1, 9, '2026-07-19', 'present', '2026-07-19 10:02:22'),
(4, 11, '2026-07-19', 'present', '2026-07-19 10:42:29'),
(5, 8, '2026-07-20', 'present', '2026-07-20 05:49:08'),
(6, 12, '2026-07-20', 'present', '2026-07-20 08:22:03'),
(7, 13, '2026-07-20', 'present', '2026-07-20 08:24:54'),
(8, 11, '2026-07-20', 'present', '2026-07-20 08:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `applied_at`) VALUES
(1, 4, 'Casual Leave', '2026-07-18', '2026-07-20', 'qwerty', 'rejected', '2026-07-18 12:29:13'),
(3, 4, 'Casual Leave', '2026-07-18', '2026-07-20', 'im attending weddind', 'rejected', '2026-07-18 17:17:57'),
(4, 7, 'Medical Leave', '2026-07-18', '2026-07-31', 'im sick', 'approved', '2026-07-18 17:24:27'),
(5, 8, 'Casual Leave', '2026-07-21', '2026-07-24', 'i want leave', 'approved', '2026-07-18 17:33:18'),
(6, 9, 'Unpaid Sabbatical', '2026-07-20', '2026-07-24', 'travel', 'rejected', '2026-07-18 17:52:31'),
(7, 9, 'Casual Leave', '2026-07-19', '2026-07-21', 'qwertyu', 'approved', '2026-07-19 10:36:35'),
(8, 11, 'Casual Leave', '2026-07-19', '2026-07-21', 'werty', 'rejected', '2026-07-19 10:39:04'),
(9, 9, 'Casual Leave', '2026-07-19', '2026-07-21', 'qwertyui', 'rejected', '2026-07-19 13:04:58'),
(10, 11, 'Medical Leave', '2026-07-14', '2027-04-09', 'sdfgj', 'rejected', '2026-07-19 14:06:30'),
(11, 11, 'Casual Leave', '2026-07-19', '2027-08-19', 'just leave', 'rejected', '2026-07-19 14:48:20'),
(12, 12, 'Casual Leave', '2026-07-21', '2026-07-24', 'just leave', 'approved', '2026-07-20 08:21:50'),
(13, 11, 'Medical Leave', '2026-07-23', '2026-07-20', 'mmm', 'approved', '2026-07-20 08:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL DEFAULT 'employee',
  `emp_type` enum('full-time','part-time','temporary','contract','freelancer','intern','head') NOT NULL DEFAULT 'full-time',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.png',
  `joining_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `emp_type`, `created_at`, `contact_number`, `address`, `profile_photo`, `joining_date`) VALUES
(4, 'Vedant Alurkar', 'vedant@gmail.com', '$2y$10$EqCMQYv8QOkEj7DcikoUpOuG/ZcJdmgEMKs5xmSvlvKbfWsYUqmFG', 'employee', 'full-time', '2026-07-18 12:25:29', NULL, NULL, 'default.png', NULL),
(7, 'aditya', 'aditya@gmail.com', '$2y$10$t12mBbiXWZO3BewhDrTIGOBiUPrPYAPYEaiyvHcnddMWKzV5qEgdq', 'employee', 'part-time', '2026-07-18 17:23:09', '12345678', 'chatrapati sanbhaji nagar', 'default.png', NULL),
(8, 'Soham Rathod', 'sohamrathod@gmail.com', '$2y$10$h.JheG9Y6WgJjALqtTDUleIwEvJHCcMd1D0HEcv36KMZQZ0pePONG', 'employee', 'contract', '2026-07-18 17:32:11', '1234567899', 'nanded city', 'default.png', NULL),
(9, 'sahil', 'sahil@gmail.com', '$2y$10$S1eg/Nsd7UVkBw8vD9tcCeWnWogj6Kzi/UmjckWF89BairZYB8rbe', 'employee', 'intern', '2026-07-18 17:50:35', '1234567890', 'Satara', 'default.png', NULL),
(10, 'Ruturaj', 'ruturaj@gmail.com', '$2y$10$x51T9LUsNeaNbseSChVNf.isu4371Wzp1XjkHYnmxu90mAtAo3ySe', 'admin', 'head', '2026-07-19 10:15:46', '23456789', 'fssdhtd', 'default.png', NULL),
(11, 'Purvshri', 'Purvshri@gmail.com', '$2y$10$2QKpOjeWv1yR9a.DVJfrHuz7YIjRG8slffD9DlDf8I0p58gEQmcQ6', 'admin', 'full-time', '2026-07-19 10:22:43', NULL, NULL, 'default.png', NULL),
(12, 'vishesh', 'vishesh@gmail.com', '$2y$10$2GwwwPE7AOHzmeOBDcBC5.HayiGUPebTlziTp3kvGC69Cs.uTkQ2S', 'employee', 'full-time', '2026-07-20 08:20:21', '', '', 'default.png', NULL),
(13, 'lokesh', 'lokesh@gmail.com', '$2y$10$udXlCqX7fAnnb3H2nF/52.dIdbCF3P9M4uW8tany95OpOPKnKnTja', 'admin', 'full-time', '2026-07-20 08:24:13', NULL, NULL, 'default.png', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`work_date`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
