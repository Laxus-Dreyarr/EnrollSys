-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 10:04 AM
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
-- Database: `enrollsys`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL,
  `date_created` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `last_login` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`, `profile`, `date_created`, `user_type`, `is_active`, `last_login`) VALUES
(107568, 'ur_evsumail@evsu.edu.ph', 'ur_password', 'default.jpg', '2025-09-01 19:40:56', 'admin', 1, '2025-09-30 12:34:59am');

-- --------------------------------------------------------

--
-- Table structure for table `admin_info`
--

CREATE TABLE `admin_info` (
  `id` int(100) NOT NULL,
  `admin_id` int(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `birthdate` varchar(100) NOT NULL,
  `age` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_info`
--

INSERT INTO `admin_info` (`id`, `admin_id`, `firstname`, `lastname`, `middlename`, `birthdate`, `age`, `address`) VALUES
(1, 107568, 'ur_firstname', 'ur_lastname', 'ur_middlename', 'ur_birthdate', '0', 'ur_address');

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `access_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('FHE','Prospectus','Receipt','Other') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollmentrequests`
--

CREATE TABLE `enrollmentrequests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `instructor_id` int(11) DEFAULT NULL,
  `processed_date` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `enrollment_date` datetime DEFAULT current_timestamp(),
  `status` enum('Enrolled','Dropped') DEFAULT 'Enrolled',
  `grade` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `instructor_id` int(100) NOT NULL,
  `email5` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL,
  `date_created` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `last_login` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructor_info`
--

CREATE TABLE `instructor_info` (
  `id` int(100) NOT NULL,
  `instructor_id` int(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `birthdate` varchar(100) NOT NULL,
  `age` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications_instructor`
--

CREATE TABLE `notifications_instructor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `org_id` int(100) NOT NULL,
  `email4` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `profile` varchar(100) NOT NULL,
  `date_created` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `last_login` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizationfees`
--

CREATE TABLE `organizationfees` (
  `id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `receipt_url` varchar(500) DEFAULT NULL,
  `red_flag` tinyint(1) DEFAULT 0,
  `red_flag_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orgs_info`
--

CREATE TABLE `orgs_info` (
  `id` int(100) NOT NULL,
  `organization_id` int(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `birthdate` varchar(100) NOT NULL,
  `age` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passkeys`
--

CREATE TABLE `passkeys` (
  `id` int(11) NOT NULL,
  `passkey` varchar(100) NOT NULL,
  `email3` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `expiration_date` datetime DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `user_type` enum('instructor','organization') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('GCash','Cash','Bank Transfer') DEFAULT 'GCash',
  `reference_no` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `receipt_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `subsched_id` int(11) NOT NULL,
  `section_name` varchar(10) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `max_students` int(11) DEFAULT NULL,
  `current_students` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `id_no` varchar(20) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year','NULL') DEFAULT NULL,
  `status` enum('Not Enrolled','Pending','Officially Enrolled','Rejected') DEFAULT 'Not Enrolled',
  `is_regular` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjectprerequisites`
--

CREATE TABLE `subjectprerequisites` (
  `subject_id` int(11) NOT NULL,
  `prerequisite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjectprerequisites`
--

INSERT INTO `subjectprerequisites` (`subject_id`, `prerequisite_id`) VALUES
(59, 51);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `units` int(11) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `semester` enum('1st Sem','2nd Sem','Summer') NOT NULL,
  `max_students` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `units`, `year_level`, `semester`, `max_students`, `created_by`, `date_created`, `is_active`) VALUES
(49, 'IT 323', 'Software Engineering', 'Coding', 3, '3rd Year', '2nd Sem', 100, 107568, '2025-08-29 20:03:11', 1),
(50, 'IT 113', 'Introduction to Computing', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-30 08:06:01', 1),
(51, 'IT 134', 'Computer Programming 1', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-30 09:14:55', 1),
(52, 'GEN. ED. 001', 'Purposive Communication', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:13:42', 1),
(53, 'GEN. ED. 002', 'Understanding  the Self', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:23:56', 1),
(54, 'GEN. ED. 004', 'Mathematics in the Modern World', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:26:55', 1),
(55, 'DRR 113', 'Disaster Risk Reduction and Education in Emergencies', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:41:02', 1),
(56, 'MATH ENHANCE 1', 'College Algebra & Trigonometry', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:51:55', 1),
(57, 'PATHFIT 112', 'Movement Competency Training', '', 2, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:55:30', 1),
(58, 'NSTP 113', 'CWTS, LTS, MTS,(Naval or Air Force)', '', 3, '1st Year', '1st Sem', 100, 107568, '2025-08-31 07:58:24', 1),
(59, 'IT 123', 'Introduction to Human Computer Interaction (*)', '', 3, '1st Year', '2nd Sem', 100, 107568, '2025-08-31 08:02:11', 1),
(60, 'IT 143', 'Discrete Mathematics', '', 3, '1st Year', '2nd Sem', 100, 107568, '2025-08-31 08:09:36', 1),
(61, 'IT 163', 'Computer Programming 2(*)', '', 3, '1st Year', '2nd Sem', 98, 107568, '2025-08-31 08:13:18', 1),
(62, 'GEN. ED. 003', 'Readings in Philippine History', '', 3, '1st Year', '2nd Sem', 100, 107568, '2025-08-31 08:16:09', 1),
(63, 'GEN. ED. 006', 'Ethics', '', 3, '1st Year', '2nd Sem', 100, 107568, '2025-08-31 08:17:43', 1),
(64, 'GEN. ED. 007', 'The Contemporary Wold', '', 3, '1st Year', '2nd Sem', 100, 107568, '2025-08-31 08:20:28', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subjectschedules`
--

CREATE TABLE `subjectschedules` (
  `id` int(11) NOT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `Type` varchar(100) DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjectschedules`
--

INSERT INTO `subjectschedules` (`id`, `subject_id`, `Section`, `Type`, `day`, `start_time`, `end_time`, `room`) VALUES
(166, 49, 'A', 'Lecture', 'Monday', '09:30:00', '11:00:00', '1'),
(167, 49, 'B', 'Lecture', 'Tuesday', '08:00:00', '10:00:00', '2'),
(172, 51, 'C', 'Lecture', 'Monday', '09:30:00', '11:00:00', '5'),
(173, 51, 'C', 'Laboratory', 'Monday', '14:00:00', '15:30:00', ''),
(174, 52, 'C', 'Lecture', 'Monday', '09:30:00', '10:30:00', '4'),
(175, 52, 'A', 'Lecture', 'Tuesday', '09:30:00', '10:30:00', '4'),
(176, 52, 'b', 'Lecture', 'Wednesday', '09:30:00', '11:00:00', '4'),
(177, 50, 'B', 'Lecture', 'Thursday', '13:00:00', '14:30:00', '3'),
(178, 50, 'B', 'Laboratory', 'Friday', '14:00:00', '16:30:00', '2'),
(179, 50, 'A', 'Lecture', 'Monday', '08:40:00', '10:00:00', '3'),
(180, 50, 'A', 'Laboratory', 'Monday', '14:00:00', '16:00:00', '2'),
(181, 50, 'C', 'Lecture', 'Tuesday', '10:00:00', '11:30:00', '3'),
(182, 50, 'C', 'Laboratory', 'Tuesday', '14:00:00', '16:30:00', '2'),
(183, 53, 'A', 'Lecture', 'Tuesday', '14:00:00', '15:30:00', '1'),
(184, 53, 'b', 'Lecture', 'Tuesday', '09:00:00', '10:30:00', '1'),
(185, 54, 'A', 'Lecture', 'Friday', '10:00:00', '11:30:00', '5'),
(186, 54, 'b', 'Lecture', 'Friday', '13:00:00', '14:30:00', '5'),
(187, 55, 'A', 'Lecture', 'Friday', '15:00:00', '16:30:00', '5'),
(188, 56, 'A', 'Lecture', 'Monday', '08:30:00', '10:00:00', '5'),
(189, 57, 'A', 'Lecture', 'Sunday', '14:00:00', '16:00:00', '1'),
(190, 58, 'A', 'Lecture', 'Saturday', '09:00:00', '10:30:00', '5'),
(193, 60, 'A', 'Lecture', 'Wednesday', '13:00:00', '14:30:00', '2'),
(194, 61, 'A', 'Lecture', 'Wednesday', '09:00:00', '10:30:00', '2'),
(195, 61, 'A', 'Laboratory', 'Wednesday', '01:30:00', '15:30:00', '3'),
(196, 62, 'A', 'Lecture', 'Wednesday', '10:00:00', '11:30:00', '6'),
(197, 63, 'A', 'Lecture', 'Monday', '10:00:00', '11:30:00', '5'),
(198, 64, 'A', 'Lecture', 'Thursday', '09:30:00', '11:00:00', '1'),
(199, 59, 'A', 'Laboratory', 'Tuesday', '09:00:00', '11:00:00', '3'),
(200, 59, 'A', 'Lecture', 'Monday', '16:00:00', '17:30:00', '4'),
(226, 87, 'A', 'Lecture', 'Monday', '02:07:00', '07:07:00', '1'),
(227, 88, 'A', 'Lecture', 'Monday', '02:37:00', '07:37:00', '1'),
(228, 89, 'A', 'Lecture', 'Monday', '02:38:00', '06:38:00', '1'),
(229, 90, 'A', 'Lecture', 'Monday', '13:13:00', '17:13:00', '1'),
(231, 92, 'A', 'Lecture', 'Monday', '18:21:00', '22:21:00', '1'),
(237, 93, 'A', 'Lecture', 'Monday', '13:59:00', '17:59:00', '1'),
(242, 94, 'A', 'Lecture', 'Monday', '18:59:00', '22:59:00', '1'),
(243, 95, 'A', 'Lecture', 'Monday', '16:23:00', '20:23:00', '1'),
(246, 96, 'A', 'Lecture', 'Monday', '15:53:00', '18:53:00', '1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email2` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile` varchar(100) NOT NULL,
  `date_created` varchar(100) DEFAULT NULL,
  `user_type` enum('student','instructor','organization','admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `birthdate` varchar(100) DEFAULT NULL,
  `age` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `access_by` (`access_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `enrollmentrequests`
--
ALTER TABLE `enrollmentrequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`instructor_id`);

--
-- Indexes for table `instructor_info`
--
ALTER TABLE `instructor_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications_instructor`
--
ALTER TABLE `notifications_instructor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`org_id`);

--
-- Indexes for table `organizationfees`
--
ALTER TABLE `organizationfees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `orgs_info`
--
ALTER TABLE `orgs_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `passkeys`
--
ALTER TABLE `passkeys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subsched_id` (`subsched_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`student_id`);

--
-- Indexes for table `subjectprerequisites`
--
ALTER TABLE `subjectprerequisites`
  ADD PRIMARY KEY (`subject_id`,`prerequisite_id`),
  ADD KEY `prerequisite_id` (`prerequisite_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_subjects_code` (`code`);

--
-- Indexes for table `subjectschedules`
--
ALTER TABLE `subjectschedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email2`),
  ADD KEY `idx_users_email` (`email2`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_info`
--
ALTER TABLE `admin_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollmentrequests`
--
ALTER TABLE `enrollmentrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructor_info`
--
ALTER TABLE `instructor_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications_instructor`
--
ALTER TABLE `notifications_instructor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizationfees`
--
ALTER TABLE `organizationfees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orgs_info`
--
ALTER TABLE `orgs_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `subjectschedules`
--
ALTER TABLE `subjectschedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD CONSTRAINT `admin_info_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `auditlogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `auditlogs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `orgs_info` (`id`),
  ADD CONSTRAINT `auditlogs_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `instructor_info` (`id`),
  ADD CONSTRAINT `auditlogs_ibfk_4` FOREIGN KEY (`access_by`) REFERENCES `admin_info` (`id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `enrollmentrequests`
--
ALTER TABLE `enrollmentrequests`
  ADD CONSTRAINT `enrollmentrequests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications_instructor`
--
ALTER TABLE `notifications_instructor`
  ADD CONSTRAINT `notifications_instructor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `instructor` (`instructor_id`);

--
-- Constraints for table `organizationfees`
--
ALTER TABLE `organizationfees`
  ADD CONSTRAINT `organizationfees_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `orgs_info` (`id`),
  ADD CONSTRAINT `organizationfees_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `passkeys`
--
ALTER TABLE `passkeys`
  ADD CONSTRAINT `passkeys_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_info` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`subsched_id`) REFERENCES `subjectschedules` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `user_info` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
