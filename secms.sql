-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 07:13 AM
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
-- Database: `secms`
--

-- --------------------------------------------------------

--
-- Table structure for table `academics`
--

CREATE TABLE `academics` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_name` varchar(100) NOT NULL,
  `marks_obtained` int(11) DEFAULT 0,
  `total_marks` int(11) DEFAULT 100,
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academics`
--

INSERT INTO `academics` (`id`, `student_id`, `subject_name`, `marks_obtained`, `total_marks`, `semester`) VALUES
(1, 1, 'Software Engineering', 90, 100, 4);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_by`, `created_at`) VALUES
(1, 'Holiday this Friday', 'There will be holiday for both students and staff on this friday', 'admin', '2026-06-21 13:06:39'),
(2, 'Mid Sem Exam of 4th Sem Students', 'The mid semester exam of 4th sem students are scheduled from 29 June', 'admin', '2026-06-21 21:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `date`, `status`, `subject_name`) VALUES
(1, 1, '2026-06-20', 'Present', 'Software Engineering'),
(2, 1, '2026-06-19', 'Absent', 'Software Engineering'),
(3, 10, '2026-06-21', 'Present', ''),
(4, 1, '2026-06-21', 'Present', ''),
(5, 17, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(6, 22, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(7, 18, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(8, 19, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(9, 21, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(10, 26, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(11, 20, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(12, 23, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(13, 24, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(14, 10, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(15, 27, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(16, 1, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(17, 25, '2026-06-21', 'Present', 'Analysis & Design of Algorithms'),
(18, 17, '2026-06-21', 'Present', 'Artificial Intelligence'),
(19, 22, '2026-06-21', 'Present', 'Artificial Intelligence'),
(20, 18, '2026-06-21', 'Present', 'Artificial Intelligence'),
(21, 19, '2026-06-21', 'Present', 'Artificial Intelligence'),
(22, 21, '2026-06-21', 'Present', 'Artificial Intelligence'),
(23, 26, '2026-06-21', 'Present', 'Artificial Intelligence'),
(24, 20, '2026-06-21', 'Present', 'Artificial Intelligence'),
(25, 23, '2026-06-21', 'Present', 'Artificial Intelligence'),
(26, 24, '2026-06-21', 'Present', 'Artificial Intelligence'),
(27, 10, '2026-06-21', 'Present', 'Artificial Intelligence'),
(28, 27, '2026-06-21', 'Present', 'Artificial Intelligence'),
(29, 1, '2026-06-21', 'Present', 'Artificial Intelligence'),
(30, 25, '2026-06-21', 'Present', 'Artificial Intelligence');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `batch_name` varchar(50) NOT NULL,
  `current_semester` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`id`, `batch_name`, `current_semester`) VALUES
(1, '2024-2028', 4),
(2, '2025-2029', 2),
(3, '2023-2029', 6);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`) VALUES
('CD', 'B.Tech CSE - Data Science'),
('CS', 'B.Tech Computer Science and Engineering'),
('IT', 'B.Tech Information Technology'),
('ME', 'Btech in Mechaical Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `status` enum('Paid','Pending','Partially Paid') DEFAULT 'Pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `student_id`, `total_amount`, `amount_paid`, `status`, `updated_at`) VALUES
(1, 1, 50000.00, 45000.00, 'Partially Paid', '2026-06-20 17:58:50'),
(2, 27, 30000.00, 30000.00, 'Paid', '2026-06-21 21:12:09');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `total_marks` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `student_id`, `subject_id`, `marks_obtained`, `total_marks`) VALUES
(1, 1, 16, 100, 100),
(2, 17, 16, 70, 100),
(3, 25, 16, 90, 100);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `course_code`, `semester`) VALUES
(11, 'CS-401', 'Analysis & Design of Algorithms', 'CS', 4),
(12, 'CS-402', 'Database Management Systems', 'CS', 4),
(13, 'CS-403', 'Operating Systems', 'CS', 4),
(14, 'CS-404', 'Theory of Computation', 'CS', 4),
(15, 'CS-405', 'Data Science & Analytics', 'CS', 4),
(16, 'DS-401', 'Introduction to Data Science', 'CD', 4),
(17, 'DS-402', 'Machine Learning Foundations', 'CD', 4),
(18, 'DS-403', 'Statistical Methods for Data Analytics', 'CD', 4),
(19, 'IT-401', 'Web Engineering Technologies', 'IT', 4),
(20, 'IT-402', 'Computer Networks', 'IT', 4),
(21, 'IT-403', 'Software Engineering Core', 'IT', 4),
(22, 'ME-401', 'Fluid Mechanics', 'ME', 4),
(23, 'ME-402', 'Kinematics of Machinery', 'ME', 4),
(24, 'ME-403', 'Manufacturing Processes', 'ME', 4),
(25, 'MA-101', 'Engineering Mathematics - I', 'CS', 1),
(26, 'PH-101', 'Engineering Physics', 'CS', 1),
(27, 'CS-101', 'Fundamentals of Computer Programming', 'CS', 1),
(28, 'MA-101', 'Engineering Mathematics - I', 'CD', 1),
(29, 'PH-101', 'Engineering Physics', 'CD', 1),
(30, 'DS-101', 'Introduction to Data Science & Computing', 'CD', 1),
(31, 'MA-101', 'Engineering Mathematics - I', 'IT', 1),
(32, 'PH-101', 'Engineering Physics', 'IT', 1),
(33, 'IT-101', 'Information Technology Foundations', 'IT', 1),
(34, 'MA-101', 'Engineering Mathematics - I', 'ME', 1),
(35, 'PH-101', 'Engineering Physics', 'ME', 1),
(36, 'ME-101', 'Engineering Graphics & Design', 'ME', 1),
(37, 'MA-201', 'Engineering Mathematics - II', 'CS', 2),
(38, 'EE-201', 'Basic Electrical & Electronics Eng.', 'CS', 2),
(39, 'CS-201', 'Data Structures using C++', 'CS', 2),
(40, 'MA-201', 'Engineering Mathematics - II', 'CD', 2),
(41, 'EE-201', 'Basic Electrical & Electronics Eng.', 'CD', 2),
(42, 'DS-201', 'Python Programming for Data Analytics', 'CD', 2),
(43, 'MA-201', 'Engineering Mathematics - II', 'IT', 2),
(44, 'EE-201', 'Basic Electrical & Electronics Eng.', 'IT', 2),
(45, 'IT-201', 'Digital Logic & System Design', 'IT', 2),
(46, 'MA-201', 'Engineering Mathematics - II', 'ME', 2),
(47, 'CH-201', 'Engineering Chemistry', 'ME', 2),
(48, 'ME-201', 'Basic Mechanical Engineering', 'ME', 2),
(49, 'CS-301', 'Discrete Structures', 'CS', 3),
(50, 'CS-302', 'Object Oriented Programming', 'CS', 3),
(51, 'CS-303', 'Computer Organization & Architecture', 'CS', 3),
(52, 'DS-301', 'Linear Algebra for Data Science', 'CD', 3),
(53, 'DS-302', 'Probability & Inferential Statistics', 'CD', 3),
(54, 'DS-303', 'Advanced Data Structures & Algorithms', 'CD', 3),
(55, 'IT-301', 'Discrete Mathematics', 'IT', 3),
(56, 'IT-302', 'Data Communication Systems', 'IT', 3),
(57, 'IT-303', 'Object Oriented Paradigms', 'IT', 3),
(58, 'ME-301', 'Engineering Thermodynamics', 'ME', 3),
(59, 'ME-302', 'Strength of Materials', 'ME', 3),
(60, 'ME-303', 'Material Science & Metallurgy', 'ME', 3),
(61, 'CS-501', 'Compiler Design', 'CS', 5),
(62, 'CS-502', 'Computer Networks', 'CS', 5),
(63, 'CS-503', 'Artificial Intelligence', 'CS', 5),
(64, 'DS-501', 'Deep Learning Architectures', 'CD', 5),
(65, 'DS-502', 'Data Warehousing & Mining', 'CD', 5),
(66, 'DS-503', 'Big Data Engineering Tools', 'CD', 5),
(67, 'IT-501', 'Web Application Frameworks', 'IT', 5),
(68, 'IT-502', 'Information Coding & Cryptography', 'IT', 5),
(69, 'IT-503', 'Design & Analysis of IT Algorithms', 'IT', 5),
(70, 'ME-501', 'Design of Machine Elements', 'ME', 5),
(71, 'ME-502', 'Dynamics of Machinery', 'ME', 5),
(72, 'ME-503', 'Heat and Mass Transfer', 'ME', 5),
(73, 'CS-601', 'Software Engineering', 'CS', 6),
(74, 'CS-602', 'Cryptography & Network Security', 'CS', 6),
(75, 'CS-603', 'Cloud Computing Architecture', 'CS', 6),
(76, 'DS-601', 'Natural Language Processing', 'CD', 6),
(77, 'DS-602', 'Business Intelligence & Analytics', 'CD', 6),
(78, 'DS-603', 'Computer Vision Foundations', 'CD', 6),
(79, 'IT-601', 'Mobile Computing & Applications', 'IT', 6),
(80, 'IT-602', 'Distributed Systems Control', 'IT', 6),
(81, 'IT-603', 'Software Project Management', 'IT', 6),
(82, 'ME-601', 'Automobile Engineering Structural Design', 'ME', 6),
(83, 'ME-602', 'CAD/CAM & Automation Systems', 'ME', 6),
(84, 'ME-603', 'Refrigeration & Air Conditioning', 'ME', 6),
(85, 'CS-701', 'Minor Project Phase-I', 'CS', 7),
(86, 'CS-702', 'Internet of Things (IoT)', 'CS', 7),
(87, 'CS-703', 'Cybersecurity & Auditing Laws', 'CS', 7),
(88, 'DS-701', 'Data Science Capstone Phase-I', 'CD', 7),
(89, 'DS-702', 'Time Series Analysis & Forecasting', 'CD', 7),
(90, 'DS-703', 'Data Privacy, Ethics & Governance', 'CD', 7),
(91, 'IT-701', 'Project Framework Design Phase-I', 'IT', 7),
(92, 'IT-702', 'Cloud Infrastructure Services', 'IT', 7),
(93, 'IT-703', 'E-Commerce & ERP Systems', 'IT', 7),
(94, 'ME-701', 'Mechanical Design Project Phase-I', 'ME', 7),
(95, 'ME-702', 'Operations Research & Optimization', 'ME', 7),
(96, 'ME-703', 'Power Plant Systems Engineering', 'ME', 7),
(97, 'CS-801', 'Major Comprehensive Project Phase-II', 'CS', 8),
(98, 'CS-802', 'Industrial Internship Evaluation', 'CS', 8),
(99, 'DS-801', 'Enterprise Analytics Capstone Phase-II', 'CD', 8),
(100, 'DS-802', 'Industrial Internship Evaluation', 'CD', 8),
(101, 'IT-801', 'Enterprise Systems Project Phase-II', 'IT', 8),
(102, 'IT-802', 'Industrial Internship Evaluation', 'IT', 8),
(103, 'ME-801', 'Production Plant Design Project Phase-II', 'ME', 8),
(104, 'ME-802', 'Industrial Internship Evaluation', 'ME', 8),
(105, 'CD-402', 'Analysis and Design of Algorithms', 'CD', 4);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `room_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`id`, `day_of_week`, `time_slot`, `subject_name`, `room_number`) VALUES
(1, 'Monday', '10:00 AM- 11:00 AM', 'Introduction to Data Science', 'ADA LAB NRB');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','faculty','student') NOT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_code` varchar(20) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `status`, `created_at`, `course_code`, `batch_id`) VALUES
(1, 'Vishal', '$2y$10$70U8.tmcuxLNsosjIzOf.eAA6APqSwEZbwyoagKBWWA7CcsjBkiJy', '123@gmail.com', 'student', 'approved', '2026-06-20 17:40:03', 'CD', 1),
(3, 'admin', '$2y$10$1E5wLzitlcK/lKlZDQQ4w.e4MUScXtDG01vQfpzLvPaQ2kzd0tctO', 'admin@gmail.com', 'admin', 'approved', '2026-06-20 17:43:55', NULL, NULL),
(4, 'Teacher', '$2y$10$u2xUdkoLQI6jgl96ASrb0uZsWwewcE7H.wugPvcM9ZmJ7EjX.YYwa', 'teach@gmail.com', 'faculty', 'approved', '2026-06-20 17:48:36', NULL, NULL),
(5, 'sai', '$2y$10$xP4dMuiAzZfpg2mdSDLQ..7oxtw54rVcRUyoqLBmiUqOzUEtOFuo.', 'sai@gmail.com', 'student', 'approved', '2026-06-21 08:10:15', NULL, NULL),
(6, 'admin2', '$2y$10$feP.fKF03TmmQnRsJ.l15.aUjUKjM8Pkcno5nd.RyIFx26Dia0M0S', 'admin2@mail.com', 'admin', 'approved', '2026-06-21 16:34:12', NULL, NULL),
(7, 'admin1', '$2y$10$Y/El6yofge7I.3dCK9IxKeSl3oVqZtU/o4lum4lzyEHlhUpgWwhTW', 'admin1@mail.com', 'admin', 'approved', '2026-06-21 16:34:58', NULL, NULL),
(8, 'test', '$2y$10$I3jQD4gamkFbN4FWyM3S3uAn3XvrfCrBSYy230fzEWyadEP0Xgv8m', 'test@gmail.com', 'student', 'approved', '2026-06-21 16:42:09', 'CS', 1),
(9, 'Arjun Sharma', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'arjun.cs4@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'CS', 1),
(10, 'Rohan Verma', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'rohan.cd4@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'CD', 1),
(11, 'Anjali Gupta', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'anjali.it4@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'IT', 1),
(12, 'Vikram Singh', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'vikram.me4@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'ME', 1),
(13, 'Neha Joshi', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'neha.cs2@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'CS', 2),
(14, 'Karan Malhotra', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'karan.cd2@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'CD', 2),
(15, 'Pooja Rao', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'pooja.it2@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'IT', 2),
(16, 'Amit Patel', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'amit.me2@secms.edu', 'student', 'approved', '2026-06-21 17:37:47', 'ME', 2),
(17, 'Aditya Sharma', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'aditya.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(18, 'Divya Kapoor', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'divya.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(19, 'Ishaan Malhotra', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'ishaan.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(20, 'Meera Nair', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'meera.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(21, 'Kabir Joshi', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'kabir.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(22, 'Ananya Mishra', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'ananya.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(23, 'Rahul Choudhary', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'rahul.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(24, 'Riya Saxena', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'riya.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(25, 'Yash Singhal', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'yash.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(26, 'Kriti Deshmukh', '$2y$10$7rHKmCZ72pTj6.f7Xgq9u.lQ6R6xGzK8PpH/J9z5yv3A9mK4lE3Ga', 'kriti.ds4@secms.edu', 'student', 'approved', '2026-06-21 18:13:09', 'CD', 1),
(27, 'Shubam', '$2y$10$HFDtfbIvceFS9vTiF2ftiOkmGqb/UJuCnaxEKW7jOlL1Ndg1Fo1mu', 'shubam@123', 'student', 'approved', '2026-06-21 21:08:18', 'CD', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academics`
--
ALTER TABLE `academics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_code` (`course_code`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academics`
--
ALTER TABLE `academics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academics`
--
ALTER TABLE `academics`
  ADD CONSTRAINT `academics_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
