-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 07:07 AM
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
-- Database: `chungchi_ngoaingu`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(10) UNSIGNED NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `language` varchar(50) NOT NULL,
  `level` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration_weeks` int(10) UNSIGNED DEFAULT NULL,
  `total_hours` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `max_students` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `teacher_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `language`, `level`, `description`, `duration_weeks`, `total_hours`, `price`, `start_date`, `end_date`, `max_students`, `status`, `image`, `created_at`, `updated_at`, `teacher_id`) VALUES
(7, 'IELTS_ACA', 'IELTS Academic', 'Tiếng Anh', 'Mọi cấp độ', 'Đối tượng phù hợp: Du học sinh, học thuật. Mục tiêu đánh giá: Năng lực tiếng Anh học thuật.', 12, NULL, 4664000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1455390582262-044cdead277a', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL),
(8, 'IELTS_GEN', 'IELTS General Training', 'Tiếng Anh', 'Mọi cấp độ', 'Đối tượng phù hợp: Định cư, làm việc nước ngoài.', 12, NULL, 4664000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL),
(9, 'TOEIC_2', 'TOEIC 2 kỹ năng', 'Tiếng Anh', 'Mọi cấp độ', 'Đối tượng phù hợp: Sinh viên, định hướng giao tiếp công sở.', 8, NULL, 1700000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL),
(10, 'TOEIC_4', 'TOEIC 4 kỹ năng', 'Tiếng Anh', 'Mọi cấp độ', 'Đối tượng phù hợp: Đi làm, giao tiếp chuyên nghiệp.', 10, NULL, 3500000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL),
(11, 'VSTEP_B1', 'Khóa học VSTEP B1-B2', 'Tiếng Anh', 'B1-B2', 'Luyện thi chứng chỉ VSTEP dành cho sinh viên và giáo viên.', 14, NULL, 4200000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL),
(12, 'HSK_4', 'Khóa học HSK 4', 'Tiếng Trung', 'HSK4', 'Luyện thi HSK dành cho du học và giao tiếp.', 16, NULL, 3900000.00, NULL, NULL, NULL, 'upcoming', 'https://images.unsplash.com/photo-1513258496099-48168024aec0', '2026-05-16 16:18:19', '2026-05-16 16:18:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','completed','cancelled','rejected') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `payment_method` enum('cash','bank_transfer','momo','vnpay','other') DEFAULT NULL,
  `amount_paid` decimal(12,2) DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `change_request` text DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `transfer_course_id` int(11) DEFAULT NULL,
  `request_date` timestamp NULL DEFAULT current_timestamp(),
  `request_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('student','admin','staff','teacher') DEFAULT 'student',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `phone`, `date_of_birth`, `role`, `status`, `avatar`, `created_at`, `updated_at`) VALUES
(2, 'nguyenvana', 'nguyenvana@gmail.com', '$2y$10$Pi0Vus4nGW7KkuGvshzgMuW77vrMV.921zE0bLhDacO9MvCL.lll2', 'Đinh Văn Quốc', NULL, NULL, 'admin', 'active', NULL, '2026-05-13 08:52:11', '2026-05-13 09:27:26'),
(3, 'dinhvanquoc994', 'dinhvanquoc994@gmail.com', '$2y$10$4CMh9oTwztt.IwWqnzyQb.MFrLF2Qch4Hwk6md1ImygPZkS4he/lK', 'ĐV Quốc', NULL, NULL, 'admin', 'active', NULL, '2026-05-13 08:57:05', '2026-05-18 04:16:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `idx_course_language` (`language`),
  ADD KEY `idx_course_status` (`status`),
  ADD KEY `fk_course_teacher` (`teacher_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_registration_status` (`status`),
  ADD KEY `idx_registration_payment` (`payment_status`),
  ADD KEY `idx_reg_user_course` (`user_id`,`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_course_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
