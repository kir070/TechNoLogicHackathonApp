-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2026 at 05:24 PM
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
-- Database: `scheduling_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `code`, `created_at`) VALUES
(1, 'College of Computer Science', 'CCS', '2026-04-21 12:41:09'),
(2, 'College of Accountancy', 'COA', '2026-04-21 12:41:09'),
(3, 'College of Business Administration', 'CBA', '2026-04-21 12:41:09'),
(4, 'College of Entrepreneurship', 'COE', '2026-04-21 12:41:09'),
(5, 'College of Education', 'CED', '2026-04-21 12:41:09');

-- --------------------------------------------------------

--
-- Table structure for table `conflict_log`
--

CREATE TABLE `conflict_log` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `conflict_type` enum('room_double_book','teacher_double_book','capacity_exceeded','equipment_mismatch') NOT NULL,
  `description` text DEFAULT NULL,
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `college_id` int(11) DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `max_units` int(11) DEFAULT 21,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `employment_type` enum('full-time','part-time') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `first_name`, `last_name`, `email`, `phone`, `college_id`, `specialization`, `max_units`, `is_active`, `created_at`, `employment_type`) VALUES
(1, 'hulk', 'inc', 'hulm@gmail.com', '0914541474', 5, '', 21, 1, '2026-04-26 05:05:40', 'part-time'),
(2, 'Elaine Joy', 'Inzon', 'inzonelaine@gmail.com', '09123456968', 1, 'Leadership', 21, 1, '2026-04-22 08:44:18', NULL),
(3, 'Ricardo', 'Dalisay', 'r.dalisay@school.edu.ph', '0912-345-6789', 1, 'Computing, Hardware, Troubleshooting (ITC/ITP1)', 21, 1, '2026-04-22 17:41:33', ''),
(4, 'Liza', 'Soberano', 'l.soberano@school.edu.ph', '0912-345-6790', 1, 'Programming Fundamentals', 21, 1, '2026-04-22 17:41:33', NULL),
(5, 'Jose', 'Rizal', 'j.rizal@school.edu.ph', '0912-345-6791', 1, 'Philippine History (PHILHIST), Leadership (LEAD 1)', 21, 1, '2026-04-22 17:41:33', NULL),
(6, 'Juan', 'Luna', 'j.luna@school.edu.ph', '0912-345-6792', 1, 'Mathematics in the Modern World (MATHWRLD)', 21, 1, '2026-04-22 17:41:33', NULL),
(7, 'Hidilyn', 'Diaz', 'h.diaz@school.edu.ph', '0912-345-6793', 1, 'Physical Education (PE 1), NSTP 1', 21, 1, '2026-04-22 17:41:33', NULL),
(8, 'Maria', 'Clara', 'm.clara@school.edu.ph', '0912-345-6794', 1, 'Understanding the Self (UNDSELF), Filipino (KOMFIL)', 21, 1, '2026-04-22 17:41:33', 'full-time'),
(9, 'Toni', 'Stark', 't.stark@school.edu.ph', '0912-345-6795', 1, '', 21, 1, '2026-04-22 17:41:33', NULL),
(10, 'Boss', 'Mafia', 'mafiaboss@gmail.com', '095601234567', 3, '', 21, 1, '2026-04-22 18:35:34', NULL),
(11, 'ai', 'lyn', 'lyn07@gmail.com', '0912-345-6794', 3, '', 21, 1, '2026-04-26 14:01:45', 'part-time'),
(12, 'chickden', 'winbgs', 'ckdihvud@gmail.com', '095601234567', 1, '', 21, 1, '2026-04-26 14:24:20', 'full-time');

-- --------------------------------------------------------

--
-- Table structure for table `professor_availability`
--

CREATE TABLE `professor_availability` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `professor_availability`
--

INSERT INTO `professor_availability` (`id`, `professor_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 'Monday', '08:00:00', '10:00:00'),
(2, 2, 'Tuesday', '10:00:00', '12:00:00'),
(3, 3, 'Wednesday', '13:00:00', '15:00:00'),
(4, 8, 'Wednesday', '08:15:00', '20:00:00'),
(5, 8, 'Monday', '08:20:00', '19:30:00'),
(8, 8, 'Thursday', '09:00:00', '20:00:00'),
(9, 11, 'Saturday', '10:30:00', '20:30:00'),
(10, 12, 'Monday', '06:00:00', '20:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `professor_expertise`
--

CREATE TABLE `professor_expertise` (
  `professor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `professor_expertise`
--

INSERT INTO `professor_expertise` (`professor_id`, `subject_id`) VALUES
(0, 14),
(0, 15),
(2, 1),
(2, 15),
(3, 2),
(3, 3),
(3, 8),
(3, 16),
(4, 6),
(4, 7),
(7, 11),
(7, 12),
(8, 3),
(8, 10),
(8, 13),
(9, 14),
(10, 9),
(11, 10),
(11, 12),
(11, 13),
(12, 4),
(12, 11),
(12, 14);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `room_type` enum('lab','lecture') NOT NULL DEFAULT 'lecture',
  `capacity` int(11) DEFAULT 40,
  `has_projector` tinyint(1) DEFAULT 0,
  `has_computers` tinyint(1) DEFAULT 0,
  `has_ac` tinyint(1) DEFAULT 1,
  `floor_level` int(11) DEFAULT 3,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `room_type`, `capacity`, `has_projector`, `has_computers`, `has_ac`, `floor_level`, `is_active`, `created_at`) VALUES
(1, 'Lab1', 'lab', 35, 1, 1, 1, 1, 1, '2026-04-21 12:41:10'),
(2, 'Lab2', 'lab', 35, 1, 1, 1, 1, 1, '2026-04-21 12:41:10'),
(3, 'Lab3', 'lab', 35, 1, 1, 0, 1, 1, '2026-04-21 12:41:10'),
(4, 'Lab4', 'lab', 35, 1, 1, 0, 1, 1, '2026-04-21 12:41:10'),
(5, '3A', 'lecture', 45, 1, 0, 1, 3, 1, '2026-04-21 12:41:10'),
(6, '3B', 'lecture', 45, 1, 0, 1, 3, 1, '2026-04-21 12:41:10'),
(7, '3C', 'lecture', 45, 0, 0, 1, 3, 1, '2026-04-21 12:41:10'),
(8, '3D', 'lecture', 45, 0, 0, 0, 3, 1, '2026-04-21 12:41:10'),
(9, '3E', 'lecture', 45, 0, 0, 0, 3, 1, '2026-04-21 12:41:10'),
(10, '4A', 'lecture', 45, 1, 0, 1, 4, 1, '2026-04-21 12:41:10'),
(11, '4B', 'lecture', 45, 1, 0, 1, 4, 1, '2026-04-21 12:41:10'),
(12, '4C', 'lecture', 45, 0, 0, 1, 4, 1, '2026-04-21 12:41:10'),
(13, '4D', 'lecture', 45, 0, 0, 0, 4, 1, '2026-04-21 12:41:10'),
(14, '4E', 'lecture', 45, 0, 0, 0, 4, 1, '2026-04-21 12:41:10'),
(15, '5A', 'lecture', 45, 1, 0, 1, 5, 1, '2026-04-21 12:41:10'),
(16, '5B', 'lecture', 45, 1, 0, 1, 5, 1, '2026-04-21 12:41:10'),
(17, '5C', 'lecture', 45, 0, 0, 1, 5, 1, '2026-04-21 12:41:10'),
(18, '5D', 'lecture', 45, 0, 0, 0, 5, 1, '2026-04-21 12:41:10'),
(19, '5E', 'lecture', 45, 0, 0, 0, 5, 1, '2026-04-21 12:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `semester` varchar(20) DEFAULT '1st Semester',
  `school_year` varchar(10) DEFAULT '2025-2026',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `professor_id`, `subject_id`, `room_id`, `day_of_week`, `start_time`, `end_time`, `semester`, `school_year`, `is_active`, `created_at`) VALUES
(1, 2, 1, 18, 'Tuesday', '13:00:00', '14:00:00', '1st Semester', '2025-2026', 0, '2026-04-22 15:51:26'),
(2, 2, 1, 19, 'Wednesday', '12:00:00', '13:00:00', '1st Semester', '2025-2026', 0, '2026-04-22 16:28:35'),
(3, 7, 11, 5, 'Tuesday', '12:30:00', '15:30:00', '1st Semester', '2025-2026', 1, '2026-04-22 17:44:46'),
(4, 8, 7, 5, 'Monday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-22 18:18:39'),
(5, 3, 2, 5, 'Tuesday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-22 18:32:41'),
(6, 8, 10, 6, 'Tuesday', '12:00:00', '15:00:00', '1st Semester', '2025-2026', 0, '2026-04-22 18:33:19'),
(7, 6, 8, 14, 'Monday', '07:30:00', '10:30:00', '1st Semester', '2025-2026', 1, '2026-04-22 18:34:01'),
(8, 3, 8, 5, 'Monday', '10:30:00', '13:30:00', '1st Semester', '2025-2026', 0, '2026-04-26 03:10:09'),
(9, 4, 9, 1, 'Tuesday', '11:30:00', '13:30:00', '1st Semester', '2025-2026', 0, '2026-04-26 09:41:28'),
(10, 7, 11, 6, 'Wednesday', '08:00:00', '11:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 11:19:16'),
(11, 7, 11, 1, 'Wednesday', '18:00:00', '21:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 11:19:19'),
(12, 2, 13, 14, 'Saturday', '17:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:04:20'),
(13, 6, 5, 1, 'Saturday', '13:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:04:48'),
(14, 8, 1, 12, 'Saturday', '12:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:11'),
(15, 9, 8, 11, 'Monday', '16:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:12'),
(16, 8, 14, 1, 'Friday', '14:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:13'),
(17, 3, 15, 2, 'Friday', '18:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:13'),
(18, 2, 8, 7, 'Monday', '13:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:13'),
(19, 10, 11, 16, 'Thursday', '17:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:13'),
(20, 8, 16, 9, 'Tuesday', '17:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:13'),
(21, 2, 8, 12, 'Monday', '13:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:14'),
(22, 9, 10, 13, 'Tuesday', '10:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:14'),
(23, 8, 16, 17, 'Monday', '09:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:14'),
(24, 9, 10, 14, 'Friday', '16:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:35'),
(25, 0, 14, 8, 'Tuesday', '15:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:35'),
(26, 0, 5, 1, 'Friday', '11:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:36'),
(27, 5, 8, 8, 'Monday', '15:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:36'),
(28, 9, 5, 14, 'Friday', '14:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:05:36'),
(29, 6, 13, 11, 'Saturday', '19:00:00', '00:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 12:06:31'),
(30, 2, 1, 1, 'Monday', '14:00:00', '15:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(31, 2, 1, 1, 'Monday', '15:00:00', '16:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:51'),
(32, 2, 1, 1, 'Monday', '16:00:00', '17:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(33, 2, 1, 1, 'Monday', '17:00:00', '18:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(34, 2, 1, 1, 'Monday', '18:00:00', '19:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(35, 2, 1, 1, 'Monday', '19:00:00', '20:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(36, 2, 1, 1, 'Tuesday', '09:00:00', '10:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(37, 2, 1, 1, 'Tuesday', '10:00:00', '11:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:51'),
(38, 2, 1, 1, 'Tuesday', '16:00:00', '17:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:51'),
(39, 2, 1, 1, 'Tuesday', '17:00:00', '18:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(40, 2, 1, 1, 'Tuesday', '18:00:00', '19:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(41, 2, 1, 1, 'Tuesday', '19:00:00', '20:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(42, 2, 1, 1, 'Wednesday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(43, 2, 1, 1, 'Wednesday', '11:00:00', '12:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:52'),
(44, 2, 1, 1, 'Wednesday', '13:00:00', '14:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:52'),
(45, 2, 1, 1, 'Wednesday', '14:00:00', '15:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:52'),
(46, 2, 1, 1, 'Wednesday', '15:00:00', '16:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(47, 2, 1, 1, 'Wednesday', '16:00:00', '17:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:52'),
(48, 2, 1, 1, 'Wednesday', '17:00:00', '18:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(49, 2, 1, 1, 'Thursday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(50, 2, 1, 1, 'Thursday', '08:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(51, 2, 1, 1, 'Thursday', '09:00:00', '10:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:52'),
(52, 2, 1, 1, 'Thursday', '10:00:00', '11:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(53, 2, 1, 1, 'Thursday', '11:00:00', '12:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:52'),
(54, 2, 1, 1, 'Thursday', '12:00:00', '13:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(55, 2, 1, 1, 'Thursday', '13:00:00', '14:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(56, 2, 1, 1, 'Thursday', '14:00:00', '15:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(57, 2, 1, 1, 'Thursday', '15:00:00', '16:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(58, 2, 1, 1, 'Thursday', '16:00:00', '17:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:53'),
(59, 2, 1, 1, 'Thursday', '17:00:00', '18:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(60, 2, 1, 1, 'Thursday', '18:00:00', '19:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(61, 2, 1, 1, 'Thursday', '19:00:00', '20:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(62, 2, 1, 1, 'Friday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(63, 2, 1, 1, 'Friday', '08:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(64, 2, 1, 1, 'Friday', '09:00:00', '10:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(65, 2, 1, 1, 'Friday', '10:00:00', '11:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(66, 2, 1, 1, 'Friday', '11:00:00', '12:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:53'),
(67, 2, 1, 1, 'Friday', '12:00:00', '13:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(68, 2, 1, 1, 'Friday', '13:00:00', '14:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 13:07:53'),
(69, 2, 1, 1, 'Friday', '14:00:00', '15:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:53'),
(70, 2, 1, 1, 'Friday', '15:00:00', '16:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:54'),
(71, 2, 1, 1, 'Friday', '16:00:00', '17:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:54'),
(72, 2, 1, 1, 'Friday', '17:00:00', '18:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:54'),
(73, 2, 1, 1, 'Friday', '18:00:00', '19:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:54'),
(74, 2, 1, 1, 'Friday', '19:00:00', '20:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 13:07:54'),
(75, 11, 2, 12, 'Monday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:26:47'),
(76, 2, 1, 1, 'Monday', '11:00:00', '12:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:56'),
(77, 3, 2, 1, 'Monday', '12:00:00', '14:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:56'),
(78, 3, 3, 1, 'Monday', '18:00:00', '19:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:56'),
(79, 12, 4, 1, 'Monday', '19:00:00', '21:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:57'),
(80, 4, 6, 1, 'Tuesday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:57'),
(81, 4, 7, 1, 'Tuesday', '11:00:00', '12:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:57'),
(82, 3, 8, 1, 'Tuesday', '18:00:00', '21:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:57'),
(83, 2, 1, 1, 'Wednesday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:57'),
(84, 10, 9, 1, 'Wednesday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:58'),
(85, 3, 2, 1, 'Wednesday', '09:00:00', '11:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:58'),
(86, 8, 10, 1, 'Thursday', '18:00:00', '21:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:58'),
(87, 8, 3, 1, 'Thursday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:58'),
(88, 12, 11, 1, 'Friday', '17:00:00', '20:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:58'),
(89, 12, 4, 1, 'Friday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:34:58'),
(90, 4, 7, 1, 'Thursday', '08:00:00', '09:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:34:58'),
(91, 2, 1, 1, 'Thursday', '12:00:00', '13:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:42:47'),
(92, 8, 3, 1, 'Friday', '10:00:00', '11:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:42:47'),
(93, 4, 7, 1, 'Friday', '15:00:00', '16:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:42:48'),
(94, 2, 1, 1, 'Wednesday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:43:13'),
(95, 3, 2, 1, 'Friday', '07:00:00', '09:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:43:14'),
(96, 3, 3, 1, 'Wednesday', '08:00:00', '09:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:43:14'),
(97, 4, 7, 1, 'Thursday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:43:14'),
(98, 2, 1, 1, 'Tuesday', '07:00:00', '08:00:00', '1st Semester', '2025-2026', 0, '2026-04-26 14:55:52'),
(99, 3, 3, 1, 'Tuesday', '08:00:00', '09:00:00', '1st Semester', '2025-2026', 1, '2026-04-26 14:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `units` int(11) NOT NULL DEFAULT 3,
  `year_level` int(11) NOT NULL CHECK (`year_level` between 1 and 4),
  `section` varchar(10) NOT NULL,
  `college_id` int(11) DEFAULT NULL,
  `room_type_required` enum('lab','lecture','any') DEFAULT 'any',
  `requires_projector` tinyint(1) DEFAULT 0,
  `requires_computers` tinyint(1) DEFAULT 0,
  `requires_ac` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `units`, `year_level`, `section`, `college_id`, `room_type_required`, `requires_projector`, `requires_computers`, `requires_ac`, `description`, `is_active`, `created_at`) VALUES
(1, 'LEAD1', 'Leadership 1', 1, 1, '101', 1, 'lecture', 0, 0, 0, '', 1, '2026-04-22 08:41:03'),
(2, 'ITC', 'Introduction to Computing LEC', 2, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(3, 'ITCL', 'Introduction to Computing LAB', 1, 1, '101', 1, 'lab', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(4, 'ITP1', 'Computer Hardware and Troubleshooting LEC', 2, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(5, 'ITP1L', 'Computer Hardware and Troubleshooting LAB', 1, 1, '101', 1, 'lab', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(6, 'ITP2', 'Fundamentals of Programming LEC', 2, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(7, 'ITP2L', 'Fundamentals of Programming LAB', 1, 1, '101', 1, 'lab', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(8, 'KOMFIL', 'Kontekstwalisadong Komunikasyon sa Filipino', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(9, 'LEAD 1', 'Leadership 1', 2, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(10, 'MATHWRLD', 'Mathematics in the Modern World', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(11, 'NSTP 1', 'National Service Training Program 1', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(12, 'PE 1', 'Physical Education 1', 2, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(13, 'PHILHIST', 'Readings in Philippine History', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(14, 'PURPCOM', 'Purposive Communication', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(15, 'UNDSELF', 'Understanding the Self', 3, 1, '101', 1, 'lecture', 0, 0, 0, NULL, 1, '2026-04-22 17:27:45'),
(16, 'PE2', 'Physical Education 2', 3, 2, '202', 3, 'lecture', 1, 0, 0, '', 1, '2026-04-22 18:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `slot_label` varchar(30) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `slot_label`, `start_time`, `end_time`) VALUES
(1, '7:00 AM - 8:00 AM', '07:00:00', '08:00:00'),
(2, '8:00 AM - 9:00 AM', '08:00:00', '09:00:00'),
(3, '9:00 AM - 10:00 AM', '09:00:00', '10:00:00'),
(4, '10:00 AM - 11:00 AM', '10:00:00', '11:00:00'),
(5, '11:00 AM - 12:00 PM', '11:00:00', '12:00:00'),
(6, '12:00 PM - 1:00 PM', '12:00:00', '13:00:00'),
(7, '1:00 PM - 2:00 PM', '13:00:00', '14:00:00'),
(8, '2:00 PM - 3:00 PM', '14:00:00', '15:00:00'),
(9, '3:00 PM - 4:00 PM', '15:00:00', '16:00:00'),
(10, '4:00 PM - 5:00 PM', '16:00:00', '17:00:00'),
(11, '5:00 PM - 6:00 PM', '17:00:00', '18:00:00'),
(12, '6:00 PM - 7:00 PM', '18:00:00', '19:00:00'),
(13, '7:00 PM - 8:00 PM', '19:00:00', '20:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `conflict_log`
--
ALTER TABLE `conflict_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `professor_availability`
--
ALTER TABLE `professor_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `professor_expertise`
--
ALTER TABLE `professor_expertise`
  ADD PRIMARY KEY (`professor_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `conflict_log`
--
ALTER TABLE `conflict_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `professor_availability`
--
ALTER TABLE `professor_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conflict_log`
--
ALTER TABLE `conflict_log`
  ADD CONSTRAINT `conflict_log_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `professors`
--
ALTER TABLE `professors`
  ADD CONSTRAINT `professors_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);

--
-- Constraints for table `professor_expertise`
--
ALTER TABLE `professor_expertise`
  ADD CONSTRAINT `professor_expertise_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professor_expertise_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
