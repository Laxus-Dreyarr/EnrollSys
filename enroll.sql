-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 31, 2025 at 05:04 PM
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
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `street_no` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `brgy` varchar(255) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`id`, `user_id`, `street_no`, `region`, `province`, `municipality`, `brgy`, `zipcode`) VALUES
(24, 220595, 'Proper', 'Region VIII', 'Leyte', 'Merida', 'Cambalong', '6540'),
(25, 316085, 'Proper', 'Region VIII', 'Leyte', 'Merida', 'Cambalong', '6540');

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
(107568, 'carljames.duallo@evsu.edu.ph', '$2y$10$.vsAz0x8qVqMWKTeDBhhAufqaTrXDKkOF4G5aRdgRV3MXO/gr5DNy', 'profile/admin/default.png', '2025-09-01 19:40:56', 'admin', 1, '2025-09-30 12:34:59am');

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
(1, 107568, 'Carl James', 'Duallo', 'Piol', 'March 08, 2000', '25', 'Cambalong, Merida, Leyte');

-- --------------------------------------------------------

--
-- Table structure for table `api_access_logs`
--

CREATE TABLE `api_access_logs` (
  `id` int(11) NOT NULL,
  `api_key` varchar(32) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `request_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_params`)),
  `response_code` int(11) DEFAULT NULL,
  `response_time` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `key` varchar(64) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `key`, `client_name`, `email`, `created_by`, `expires_at`, `is_active`, `created_at`, `updated_at`) VALUES
(6, 'b5d63d23dd6953d04a16de6aecf374de704172b0b20667093e43e7a5fda47e88', 'Retention Alert System', 'classmate@evsu.edu.ph', 107568, '2026-03-19 06:23:03', 1, '2025-12-19 06:23:03', '2025-12-19 06:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `id` int(100) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `date` varchar(255) DEFAULT NULL,
  `access_by` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditlogs`
--

INSERT INTO `auditlogs` (`id`, `user_id`, `action`, `details`, `ip_address`, `date`, `access_by`) VALUES
(632, '220595', 'New Student Account Created carljames.duallo@evsu.edu.ph', 'Windows/desktop/Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '127.0.0.1', '2025-12-31 12:48:56am', 107568),
(633, '316085', 'New Student Account Created carljamesduallo661@gmail.com', 'Windows/desktop/Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '127.0.0.1', '2025-12-31 06:11:06pm', 107568);

-- --------------------------------------------------------

--
-- Table structure for table `csv`
--

CREATE TABLE `csv` (
  `id` int(100) NOT NULL,
  `application_number` varchar(255) DEFAULT NULL,
  `preferred_program` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `csv`
--

INSERT INTO `csv` (`id`, `application_number`, `preferred_program`, `lastname`, `firstname`, `middlename`, `email`, `contact_number`) VALUES
(12, 'APP-2024-001', 'BSIT', 'Satoru', 'Gojo', '', 's.duallo@evsu.ph', '09171234567'),
(13, 'APP-2024-002', 'BSIT', 'Dragneel', 'Zeref', '', 'carljamesduallo661@gmail.com', '09171234569'),
(14, 'APP-2024-0199', 'BSIT', 'Ryomen', ' Sukuna', '', 'carljames.duallo@evsu.edu.ph', '091712345900');

-- --------------------------------------------------------

--
-- Table structure for table `csv_uploads`
--

CREATE TABLE `csv_uploads` (
  `id` int(100) NOT NULL,
  `admin_id` int(100) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `stored_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `total_records` varchar(255) DEFAULT NULL,
  `inserted_records` varchar(255) DEFAULT NULL,
  `updated_records` varchar(255) DEFAULT NULL,
  `failed_records` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `error_log` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `csv_uploads`
--

INSERT INTO `csv_uploads` (`id`, `admin_id`, `file_name`, `stored_name`, `file_path`, `total_records`, `inserted_records`, `updated_records`, `failed_records`, `status`, `error_log`, `created_at`) VALUES
(7, 107568, 'applications.csv', 'applications_1765936352_69420ce01fda8.csv', 'documents/csv/applications_1765936352_69420ce01fda8.csv', '3', '3', '0', '0', 'success', NULL, '2025-12-17 09:52:32'),
(8, 107568, 'applications.csv', 'applications_1765939271_694218474e36e.csv', 'documents/csv/applications_1765939271_694218474e36e.csv', '3', '0', '3', '0', 'success', NULL, '2025-12-17 10:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum`
--

CREATE TABLE `curriculum` (
  `id` int(100) NOT NULL,
  `curriculum_year` varchar(100) DEFAULT NULL,
  `is_active` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curriculum`
--

INSERT INTO `curriculum` (`id`, `curriculum_year`, `is_active`) VALUES
(5, '2018', 1),
(6, '2025', 1);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('FHE','Prospectus','Receipt','Other','Payment Notice') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrolled_sub`
--

CREATE TABLE `enrolled_sub` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `units` int(11) NOT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `semester` enum('1st Sem','2nd Sem','Summer') NOT NULL,
  `grade` varchar(100) DEFAULT NULL,
  `date_enrolled` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolled_sub`
--

INSERT INTO `enrolled_sub` (`id`, `student_id`, `subject_id`, `subject_code`, `subject_name`, `units`, `year_level`, `semester`, `grade`, `date_enrolled`) VALUES
(3728, 110, 433, 'IT 113', 'Information Technology Fundementals', 3, '1st Year', '1st Sem', '1.6', '2025-12-31 00:48:56'),
(3729, 110, 434, 'IT 133', 'Computer Programming 1', 3, '1st Year', '1st Sem', '1.9', '2025-12-31 00:48:56'),
(3730, 110, 437, 'IT 153', 'Discrete Structures', 3, '1st Year', '1st Sem', '2.3', '2025-12-31 00:48:56'),
(3731, 110, 438, 'GEN ED 001', 'Purposive Communication', 3, '1st Year', '1st Sem', '1.0', '2025-12-31 00:48:56'),
(3732, 110, 439, 'GEN ED 002', 'Understanding the Self', 3, '1st Year', '1st Sem', '1.6', '2025-12-31 00:48:56'),
(3733, 110, 440, 'GEN ED 006', 'Ethics', 3, '1st Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3734, 110, 441, 'GEN EL 001', 'GE Elective 1', 3, '1st Year', '1st Sem', '2.1', '2025-12-31 00:48:56'),
(3735, 110, 442, 'PE 112', '(PATHFIT) Movement Competency Training', 2, '1st Year', '1st Sem', '1.7', '2025-12-31 00:48:56'),
(3736, 110, 443, 'NSTP 113', 'CWTS, LTS, MTS (Naval or Air Force)', 3, '1st Year', '1st Sem', '1.5', '2025-12-31 00:48:56'),
(3737, 110, 444, 'IT 123', 'Human Computer Interaction', 3, '1st Year', '2nd Sem', '1.4', '2025-12-31 00:48:56'),
(3738, 110, 445, 'IT 143', 'Computer Programming 2', 3, '1st Year', '2nd Sem', '1.4', '2025-12-31 00:48:56'),
(3739, 110, 446, 'IT 163', 'Statistics and Probability', 3, '1st Year', '2nd Sem', '1.6', '2025-12-31 00:48:56'),
(3740, 110, 447, 'GEN ED 003', 'Readings in Philippine History', 3, '1st Year', '2nd Sem', '1.2', '2025-12-31 00:48:56'),
(3741, 110, 448, 'Rizal 001', 'Rizal, Life and Works', 3, '1st Year', '2nd Sem', '2.0', '2025-12-31 00:48:56'),
(3742, 110, 449, 'GEN EL 002', 'GE Elective 2', 3, '1st Year', '2nd Sem', '1.1', '2025-12-31 00:48:56'),
(3743, 110, 450, 'PE 122', '(PATHFIT) Fitness Training', 2, '1st Year', '2nd Sem', '1.8', '2025-12-31 00:48:56'),
(3744, 110, 451, 'NSTP 123', 'CWTS, LTS, MTS (Naval or Airforce)', 3, '1st Year', '2nd Sem', '1.7', '2025-12-31 00:48:56'),
(3745, 110, 452, 'IT 213', 'Data Structures and Algorithms', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3746, 110, 453, 'IT 233', 'Object Oriented Programming', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3747, 110, 454, 'IT 253', 'Platform Technologies', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3748, 110, 455, 'NET 01', 'Introduction to Networking and Network Protocols: Fundamental, Concept, and Services', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3749, 110, 456, 'GEN ED 004', 'Mathematics in the Modern World', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3750, 110, 457, 'GEN ED 007', 'The Contemporary World', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3751, 110, 461, 'GE EL 003', 'GE Elective 3', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3752, 110, 462, 'PE 212', '(PATHFIT) Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 1', 2, '2nd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3753, 110, 463, 'IT 223', 'Fundamental of Information Management', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3754, 110, 464, 'IT 243', 'Quantitative Methods', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3755, 110, 465, 'IT 283', 'Event-Driven Programming', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3756, 110, 466, 'IT 200', 'Fundamentals of Artificial Intelligence', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3757, 110, 467, 'NET 02', 'Routing, Switching, and Wireless Networks Concept Configuration and Management', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3758, 110, 468, 'GEN ED 005', 'Art Appreciation', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3759, 110, 469, 'GEN ED 008', 'Science, Technology and Society', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3760, 110, 470, 'PE 222', 'Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 2', 2, '2nd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3761, 110, 471, 'IT 313', 'Advanced Database Systems', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3762, 110, 472, 'IT 333', 'Data Mining and Analytics', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3763, 110, 473, 'IT 353', 'Systems Integration and Architecture', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3764, 110, 474, 'IT 363', 'Information Assurance and Security', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3765, 110, 475, 'IT 373', 'Web Systems and Technology', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3766, 110, 476, 'IT 383', 'Integrative Programming and Technologies', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3767, 110, 477, 'NET 03', 'Enterprise Networking Security and Automation', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3768, 110, 478, 'IT 323', 'Project Management for IT', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3769, 110, 479, 'IT 343', 'Multimedia Systems', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3770, 110, 480, 'IT 353A', 'Virtualization and Cloud Platforms', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3771, 110, 481, 'IT 363A', 'Information Technology Strategy', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3772, 110, 482, 'IT 373A', 'Innovation Technopreneurship', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3773, 110, 483, 'IT 383A', 'Mobile Application Development and Emerging Technologies', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3774, 110, 484, 'IT 393', 'IT Electives', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3775, 110, 485, 'IT 413', 'System Administration and Maintenance', 3, '4th Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3776, 110, 486, 'IT 433', 'Capstone Project and Research 2', 3, '4th Year', '1st Sem', NULL, '2025-12-31 00:48:56'),
(3777, 110, 487, 'IT 426', 'Practicum (486 hours)', 6, '4th Year', '2nd Sem', NULL, '2025-12-31 00:48:56'),
(3778, 110, 488, 'IT 303', 'Social and Professional Issues', 3, '3rd Year', 'Summer', NULL, '2025-12-31 00:48:56'),
(3779, 110, 489, 'IT 303A', 'Capstone Project and Research 1', 3, '3rd Year', 'Summer', NULL, '2025-12-31 00:48:56'),
(3780, 111, 433, 'IT 113', 'Information Technology Fundementals', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3781, 111, 434, 'IT 133', 'Computer Programming 1', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3782, 111, 437, 'IT 153', 'Discrete Structures', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3783, 111, 438, 'GEN ED 001', 'Purposive Communication', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3784, 111, 439, 'GEN ED 002', 'Understanding the Self', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3785, 111, 440, 'GEN ED 006', 'Ethics', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3786, 111, 441, 'GEN EL 001', 'GE Elective 1', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3787, 111, 442, 'PE 112', '(PATHFIT) Movement Competency Training', 2, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3788, 111, 443, 'NSTP 113', 'CWTS, LTS, MTS (Naval or Air Force)', 3, '1st Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3789, 111, 444, 'IT 123', 'Human Computer Interaction', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3790, 111, 445, 'IT 143', 'Computer Programming 2', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3791, 111, 446, 'IT 163', 'Statistics and Probability', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3792, 111, 447, 'GEN ED 003', 'Readings in Philippine History', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3793, 111, 448, 'Rizal 001', 'Rizal, Life and Works', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3794, 111, 449, 'GEN EL 002', 'GE Elective 2', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3795, 111, 450, 'PE 122', '(PATHFIT) Fitness Training', 2, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3796, 111, 451, 'NSTP 123', 'CWTS, LTS, MTS (Naval or Airforce)', 3, '1st Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3797, 111, 452, 'IT 213', 'Data Structures and Algorithms', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3798, 111, 453, 'IT 233', 'Object Oriented Programming', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3799, 111, 454, 'IT 253', 'Platform Technologies', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3800, 111, 455, 'NET 01', 'Introduction to Networking and Network Protocols: Fundamental, Concept, and Services', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3801, 111, 456, 'GEN ED 004', 'Mathematics in the Modern World', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3802, 111, 457, 'GEN ED 007', 'The Contemporary World', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3803, 111, 461, 'GE EL 003', 'GE Elective 3', 3, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3804, 111, 462, 'PE 212', '(PATHFIT) Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 1', 2, '2nd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3805, 111, 463, 'IT 223', 'Fundamental of Information Management', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3806, 111, 464, 'IT 243', 'Quantitative Methods', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3807, 111, 465, 'IT 283', 'Event-Driven Programming', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3808, 111, 466, 'IT 200', 'Fundamentals of Artificial Intelligence', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3809, 111, 467, 'NET 02', 'Routing, Switching, and Wireless Networks Concept Configuration and Management', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3810, 111, 468, 'GEN ED 005', 'Art Appreciation', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3811, 111, 469, 'GEN ED 008', 'Science, Technology and Society', 3, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3812, 111, 470, 'PE 222', 'Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 2', 2, '2nd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3813, 111, 471, 'IT 313', 'Advanced Database Systems', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3814, 111, 472, 'IT 333', 'Data Mining and Analytics', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3815, 111, 473, 'IT 353', 'Systems Integration and Architecture', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3816, 111, 474, 'IT 363', 'Information Assurance and Security', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3817, 111, 475, 'IT 373', 'Web Systems and Technology', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3818, 111, 476, 'IT 383', 'Integrative Programming and Technologies', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3819, 111, 477, 'NET 03', 'Enterprise Networking Security and Automation', 3, '3rd Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3820, 111, 478, 'IT 323', 'Project Management for IT', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3821, 111, 479, 'IT 343', 'Multimedia Systems', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3822, 111, 480, 'IT 353A', 'Virtualization and Cloud Platforms', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3823, 111, 481, 'IT 363A', 'Information Technology Strategy', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3824, 111, 482, 'IT 373A', 'Innovation Technopreneurship', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3825, 111, 483, 'IT 383A', 'Mobile Application Development and Emerging Technologies', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3826, 111, 484, 'IT 393', 'IT Electives', 3, '3rd Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3827, 111, 485, 'IT 413', 'System Administration and Maintenance', 3, '4th Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3828, 111, 486, 'IT 433', 'Capstone Project and Research 2', 3, '4th Year', '1st Sem', NULL, '2025-12-31 18:11:06'),
(3829, 111, 487, 'IT 426', 'Practicum (486 hours)', 6, '4th Year', '2nd Sem', NULL, '2025-12-31 18:11:06'),
(3830, 111, 488, 'IT 303', 'Social and Professional Issues', 3, '3rd Year', 'Summer', NULL, '2025-12-31 18:11:06'),
(3831, 111, 489, 'IT 303A', 'Capstone Project and Research 1', 3, '3rd Year', 'Summer', NULL, '2025-12-31 18:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `enrollmentrequests`
--

CREATE TABLE `enrollmentrequests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `year_level` varchar(255) DEFAULT NULL,
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
  `status` enum('Enrolled','Dropped','Pending') DEFAULT NULL,
  `grade` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_date`
--

CREATE TABLE `enrollment_date` (
  `id` int(100) NOT NULL,
  `admin_id` int(100) NOT NULL,
  `semester` varchar(100) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_date`
--

INSERT INTO `enrollment_date` (`id`, `admin_id`, `semester`, `academic_year`, `is_active`) VALUES
(185, 107568, '1st Sem', '2024-2025', 0),
(186, 107568, '1st Sem', '2025-2026', 0),
(187, 107568, '1st Sem', '2025-2026', 1);

-- --------------------------------------------------------

--
-- Table structure for table `failed_subjects`
--

CREATE TABLE `failed_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade` varchar(100) NOT NULL,
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `failed_subjects`
--

INSERT INTO `failed_subjects` (`id`, `student_id`, `subject_id`, `grade`, `date`) VALUES
(15, 110, 439, 'INC', '2025-12-31 12:28:07'),
(16, 110, 438, 'INC', '2025-12-31 13:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `important_documents`
--

CREATE TABLE `important_documents` (
  `id` int(100) NOT NULL,
  `student_id` int(100) NOT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `important_documents`
--

INSERT INTO `important_documents` (`id`, `student_id`, `year_level`, `type`, `file_path`, `upload_date`) VALUES
(72, 110, '1st Year', 'FORM138A', 'documents/requirements/form138a_110_1767149987_3093.pdf', '2025-12-31 10:59:47'),
(77, 110, '1st Year', 'TOR', 'documents/requirements/tor_110_1767150715_6954947b699c9.jpg', '2025-12-31 11:11:55'),
(78, 110, '1st Year', 'GOOD_MORAL', 'documents/requirements/good_moral_110_1767151483_6954977bc08bc.PNG', '2025-12-31 11:24:44'),
(79, 110, '1st Year', 'PSA_NSO', 'documents/requirements/psa_nso_110_1767151639_69549817daa5c.pdf', '2025-12-31 11:27:19'),
(86, 111, '1st Year', 'FORM138A', 'documents/requirements/form138a_111_1767175940_2823.pdf', '2025-12-31 18:12:20'),
(87, 111, '1st Year', 'GOOD_MORAL', 'documents/requirements/good_moral_111_1767175940_2550.pdf', '2025-12-31 18:12:20'),
(88, 111, '1st Year', 'PSA_NSO', 'documents/requirements/psa_nso_111_1767175940_8477.docx', '2025-12-31 18:12:20'),
(90, 110, '2nd Year', 'ID_PICTURE', 'documents/requirements/id_picture.png', '2025-12-31 18:25:58'),
(107, 111, '1st Year', 'ID_PICTURE', 'documents/requirements/id_picture.jpg', '2025-12-31 19:10:52');

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

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`instructor_id`, `email5`, `password`, `profile`, `date_created`, `user_type`, `is_active`, `last_login`) VALUES
(448735, 'carljames.duallo@evsu.edu.ph', '$2y$10$RVLW29bIYMBBCYxNpX9QVeBgiXzHL9YobTIWFS.sEHRXgXIkK4OYu', 'default.png', '2025-12-29 04:58:13', 'instructor', 1, '2025-12-29 04:58:13');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_info`
--

CREATE TABLE `instructor_info` (
  `id` int(100) NOT NULL,
  `instructor_id` int(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `birthdate` varchar(100) NOT NULL,
  `age` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `office` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_info`
--

INSERT INTO `instructor_info` (`id`, `instructor_id`, `firstname`, `lastname`, `middlename`, `birthdate`, `age`, `address`, `department`, `office`, `phone_number`, `bio`) VALUES
(8, 448735, 'Carl James', 'Duallo', 'Piol', 'null', 'null', 'null', 'Computer Studies Department', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` varchar(255) DEFAULT NULL
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
  `created_at` varchar(255) DEFAULT NULL
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

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`org_id`, `email4`, `password`, `profile`, `date_created`, `user_type`, `is_active`, `last_login`) VALUES
(809924, 'carljames.duallo@evsu.edu.ph', '$2y$10$MGcH4KUvmVe1Nv5VGzEudO66h1j2s.hA//eAP6fadSXiQCHGoksde', 'default.png', '2025-12-06 09:22:30', 'organization', 1, '2025-12-06 09:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `organizationfees`
--

CREATE TABLE `organizationfees` (
  `id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `receipt_url` varchar(500) DEFAULT NULL,
  `red_flag_reason` varchar(100) DEFAULT NULL,
  `uploaded_date` varchar(255) DEFAULT NULL
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
  `middlename` varchar(100) DEFAULT NULL,
  `birthdate` varchar(100) NOT NULL,
  `age` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orgs_info`
--

INSERT INTO `orgs_info` (`id`, `organization_id`, `firstname`, `lastname`, `middlename`, `birthdate`, `age`, `address`) VALUES
(3, 809924, 'Carl James', 'Duallo', 'Piol', 'null', 'null', 'null');

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
  `expiration_date` datetime DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `user_type` enum('instructor','organization') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(100) NOT NULL,
  `student_id` int(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `file_path` varchar(100) NOT NULL,
  `upload_date` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `created_at` varchar(100) NOT NULL,
  `updated_at` varchar(100) NOT NULL
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

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `subsched_id`, `section_name`, `instructor_id`, `max_students`, `current_students`) VALUES
(75, 254, 'A', NULL, 50, 6),
(76, 256, 'A', NULL, 50, 7),
(77, 258, 'A', NULL, 50, 7),
(78, 260, 'A', NULL, 50, 7),
(79, 262, 'A', NULL, 50, 7),
(80, 497, '', NULL, 50, 7),
(81, 498, '', NULL, 50, 7),
(82, 499, '', NULL, 50, 5),
(83, 500, '', NULL, 50, 5),
(84, 501, '', NULL, 50, 7),
(133, 268, 'A', NULL, 50, 17),
(134, 456, 'A', NULL, 50, 17),
(135, 457, 'A', NULL, 50, 17),
(136, 458, 'A', NULL, 50, 17),
(137, 459, 'A', NULL, 50, 17),
(138, 316, 'ONLINE', NULL, 50, 17),
(139, 337, 'A', NULL, 50, 17),
(140, 460, 'A', NULL, 50, 17),
(141, 276, 'A', NULL, 50, 1),
(142, 503, 'A', NULL, 50, 6),
(143, 506, 'S', NULL, 50, 2),
(144, 504, 'A', NULL, 50, 5),
(145, 513, 'A', NULL, 50, 3),
(146, 514, 'A', NULL, 50, 11),
(147, 515, 'A', NULL, 50, 3),
(148, 516, 'A', NULL, 50, 3),
(149, 517, 'A', NULL, 50, 3),
(150, 518, 'A', NULL, 50, 3),
(151, 519, 'A', NULL, 50, 3),
(152, 522, 'A', NULL, 50, 1),
(153, 523, 'A', NULL, 50, 1),
(154, 524, 'A', NULL, 50, 1),
(155, 525, 'A', NULL, 50, 1),
(156, 526, 'A', NULL, 50, 1),
(157, 541, 'A', NULL, 50, 1),
(158, 542, 'A', NULL, 50, 1),
(159, 543, 'A', NULL, 50, 1),
(160, 587, 'A', NULL, 50, 1),
(161, 588, 'A', NULL, 50, 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `id_no` varchar(255) DEFAULT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year','NONE') DEFAULT NULL,
  `status` enum('Not Enrolled','Pending','Officially Enrolled','Rejected','None') DEFAULT 'Not Enrolled',
  `curriculum` varchar(100) DEFAULT NULL,
  `is_regular` varchar(255) DEFAULT NULL,
  `enrolled` int(11) DEFAULT NULL,
  `sy` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `id_no`, `year_level`, `status`, `curriculum`, `is_regular`, `enrolled`, `sy`) VALUES
(110, 128, '2025-30617', '2nd Year', 'Not Enrolled', '2025', '2', 0, '2025-2026'),
(111, 129, '2025-30615', '1st Year', 'Not Enrolled', '2025', '1', 1, '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `student_count_year`
--

CREATE TABLE `student_count_year` (
  `id` int(100) NOT NULL,
  `student_id` int(100) NOT NULL,
  `total_year` int(100) NOT NULL,
  `is_active` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_files`
--

CREATE TABLE `student_files` (
  `id` int(100) NOT NULL,
  `student_id` int(100) NOT NULL,
  `year_level` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `file_path` varchar(100) NOT NULL,
  `upload_date` varchar(100) NOT NULL
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
(49, 405),
(49, 406),
(380, 371),
(382, 371),
(387, 379),
(388, 371),
(389, 382),
(390, 382),
(393, 370),
(396, 382),
(397, 381),
(397, 382),
(398, 389),
(398, 390),
(399, 393),
(404, 396),
(405, 396),
(406, 397),
(407, 398),
(408, 391),
(409, 388),
(411, 399),
(414, 407),
(415, 404),
(416, 411),
(417, 398),
(418, 407),
(419, 414),
(421, 419),
(422, 420),
(423, 423),
(444, 434),
(445, 434),
(451, 443),
(452, 434),
(453, 445),
(454, 445),
(455, 433),
(463, 452),
(464, 446),
(465, 445),
(465, 453),
(466, 433),
(466, 437),
(467, 455),
(471, 463),
(472, 464),
(472, 465),
(473, 465),
(474, 454),
(475, 465),
(476, 453),
(477, 467),
(478, 471),
(480, 473),
(481, 474),
(482, 475),
(483, 476),
(485, 477),
(486, 489);

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
  `year_level` varchar(255) DEFAULT NULL,
  `semester` enum('1st Sem','2nd Sem','Summer') NOT NULL,
  `max_students` int(11) DEFAULT NULL,
  `curriculum_id` int(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `units`, `year_level`, `semester`, `max_students`, `curriculum_id`, `created_by`, `date_created`, `is_active`) VALUES
(49, 'IT 323', 'Software Engineering', 'Coding', 3, '3rd Year', '2nd Sem', 100, 5, 107568, '2025-08-29 20:03:11', 1),
(370, 'IT 113', 'Introduction to Computing', 'Fundamentals of computing concepts and applications', 3, '1st Year', '1st Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(371, 'IT 134', 'Computer Programming 1', 'Introduction to programming concepts and logic', 4, '1st Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(372, 'GEN. ED. 001', 'Purposive Communication', 'Effective communication in various contexts', 3, '1st Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(373, 'GEN. ED. 002', 'Understanding the Self', 'Psychological and philosophical perspectives on self', 3, '1st Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(374, 'GEN. ED. 004', 'Mathematics in the Modern World', 'Application of mathematics in real-world scenarios', 3, '1st Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(375, 'FIL 001', 'Akademiko sa Wikang Filipino', 'Academic writing and communication in Filipino', 3, '1st Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(376, 'DRR 113', 'Disaster Risk Reduction and Education in Emergencies', 'Disaster preparedness and management', 3, '1st Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(377, 'MATH ENHANCE 1', 'College Algebra & Trigonometry', 'Advanced algebraic and trigonometric concepts', 3, '1st Year', '1st Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(378, 'PATHFIT 112', 'Movement Competency Training', 'Fundamental movement skills and physical fitness', 2, '1st Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(379, 'NSTP 113', 'CWTS, LTS, MST (Naval or Air Force)', 'National Service Training Program', 0, '1st Year', '1st Sem', 100, 5, 107568, '2025-10-23 01:31:20', 1),
(380, 'IT 123', 'Introduction to Human Computer Interaction', 'Principles of user interface design', 3, '1st Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(381, 'IT 143', 'Discrete Mathematics', 'Mathematical structures for computer science', 3, '1st Year', '2nd Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(382, 'IT 163', 'Computer Programming 2', 'Advanced programming concepts and techniques', 3, '1st Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(383, 'GEN. ED. 003', 'Readings in Philippine History', 'Critical analysis of Philippine historical documents', 3, '1st Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(384, 'GEN. ED. 006', 'Ethics', 'Moral principles and ethical decision making', 3, '1st Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(385, 'GEN. ED. 007', 'The Contemporary World', 'Globalization and contemporary world issues', 3, '1st Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(386, 'PATHFIT 122', 'Fitness Training', 'Physical fitness and wellness programs', 2, '1st Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(387, 'NSTP 123', 'CWTS, LTS, MST (Naval or Air Force)', 'Advanced National Service Training', 0, '1st Year', '2nd Sem', 100, 5, 107568, '2025-10-23 01:31:20', 1),
(388, 'IT 213', 'Data Structures and Algorithms', 'Organization and manipulation of data', 3, '2nd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(389, 'IT 233', 'Object Oriented Programming', 'Principles and practices of OOP', 3, '2nd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(390, 'IT 253', 'Platform Technologies', 'Computer platform architectures and technologies', 3, '2nd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(391, 'IT 273', 'Web Systems and Technologies 1', 'Fundamentals of web development', 3, '2nd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(392, 'IT 293', 'Statistics and Probability', 'Statistical methods and probability theory', 3, '2nd Year', '1st Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(393, 'CCNA 213', 'Introduction to Networks', 'Cisco networking fundamentals', 3, '2nd Year', '1st Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(394, 'RIZAL 001', 'Rizals Life and Works', 'Study of Jose Rizals life and literary works', 3, '2nd Year', '1st Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(395, 'PATHFIT 212', 'Dance, Sports, Group Exercise, Outdoor and Adventure Activities', 'Various physical activities and sports', 2, '2nd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(396, 'IT 223', 'Information Management', 'Database systems and information management', 3, '2nd Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(397, 'IT 243', 'Quantitative Methods', 'Mathematical modeling and quantitative analysis', 3, '2nd Year', '2nd Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(398, 'IT 263', 'Integrative Programming and Technologies 1', 'Integration of various programming technologies', 3, '2nd Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(399, 'CCNA 223', 'Routing and Switching Essentials', 'Cisco routing and switching technologies', 3, '2nd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(400, 'GEN. ED. 005', 'Art Appreciation', 'Understanding and appreciation of various art forms', 3, '2nd Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(401, 'GEN. ED. 008', 'Science, Technology, and Society', 'Interrelationship of science, technology and society', 3, '2nd Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(402, 'LIT 001', 'Panitikan ng Pilipinas', 'Philippine literature studies', 3, '2nd Year', '2nd Sem', 60, 5, 107568, '2025-10-23 01:31:20', 1),
(403, 'PATHFIT 222', 'Dance, Sports, Group Exercise, Outdoor and Adventure Activities', 'Advanced physical activities and sports', 2, '2nd Year', '2nd Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(404, 'IT 313', 'Advanced Database Systems', 'Advanced database concepts and administration', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(405, 'IT 333', 'Systems Analysis and Design', 'System development methodologies and design', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(406, 'IT 353', 'Data Mining and Analytics', 'Data analysis and knowledge discovery', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(407, 'IT 353A', 'Systems Integration and Architecture 1', 'System integration principles and architectures', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(408, 'IT 373', 'Web Systems and Technology 2', 'Advanced web development technologies', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(409, 'IT 373A', 'Event-Driven Programming', 'GUI and event-based programming', 3, '3rd Year', '1st Sem', 35, 5, 107568, '2025-10-23 01:31:20', 1),
(410, 'IT 393', 'Social and Professional Issues', 'Ethical and professional issues in IT', 3, '3rd Year', '1st Sem', 40, 5, 107568, '2025-10-23 01:31:20', 1),
(411, 'CCNA 313', 'Scaling Networks', 'Advanced network scaling techniques', 3, '3rd Year', '1st Sem', 25, 5, 107568, '2025-10-23 01:31:20', 1),
(412, 'IT 343', 'Multimedia Systems', 'Multimedia technologies and applications', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(413, 'IT 343A', 'IT Electives', 'Specialized IT elective courses', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(414, 'IT 363', 'Information Assurance and Security 1', 'Fundamentals of information security', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(415, 'IT 363A', 'Application Development and Emerging Technologies', 'Modern application development with new technologies', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(416, 'CCNA 323', 'Connecting Networks', 'Network connectivity and integration', 3, '3rd Year', '2nd Sem', 25, 5, 107568, '2025-10-23 01:31:20', 1),
(417, 'IT 383', 'Integrative Programming and Technologies 2', 'Advanced integration of programming technologies', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(418, 'IT 383A', 'Systems Integration and Architecture 2', 'Advanced system integration and architecture', 3, '3rd Year', '2nd Sem', 30, 5, 107568, '2025-10-23 01:31:20', 1),
(419, 'IT 303', 'Information Assurance and Security 2', 'Advanced information security concepts', 3, '3rd Year', 'Summer', 100, 5, 107568, '2025-10-23 01:31:20', 1),
(420, 'IT 303A', 'Capstone Project and Research 1', 'Initial phase of capstone project development', 3, '3rd Year', 'Summer', 20, 5, 107568, '2025-10-23 01:31:20', 1),
(421, 'IT 413', 'System Administration and Maintenance', 'System administration and maintenance techniques', 3, '4th Year', '1st Sem', 100, 5, 107568, '2025-10-23 01:31:20', 1),
(422, 'IT 433', 'Capstone Project and Research 2', 'Final phase of capstone project development', 3, '4th Year', '1st Sem', 20, 5, 107568, '2025-10-23 01:31:20', 1),
(423, 'IT 429', 'Practicum (min.486 hrs)', 'Industry immersion and practical application', 9, '4th Year', '2nd Sem', 50, 5, 107568, '2025-10-23 01:31:20', 1),
(424, 'FIL 002', 'Pagbasa at Pagsulat sa Iba\'t-Ibang Disiplina', 'Akademiko sa Wikang Filipino', 3, '1st Year', '2nd Sem', 100, 5, 107568, '2025-10-23 01:20:20', 1),
(433, 'IT 113', 'Information Technology Fundementals', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-13 05:20:39', 1),
(434, 'IT 133', 'Computer Programming 1', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-16 01:54:31', 1),
(437, 'IT 153', 'Discrete Structures', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 08:54:52', 1),
(438, 'GEN ED 001', 'Purposive Communication', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 08:56:52', 1),
(439, 'GEN ED 002', 'Understanding the Self', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 08:58:15', 1),
(440, 'GEN ED 006', 'Ethics', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 08:59:32', 1),
(441, 'GEN EL 001', 'GE Elective 1', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:00:57', 1),
(442, 'PE 112', '(PATHFIT) Movement Competency Training', '', 2, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:03:01', 1),
(443, 'NSTP 113', 'CWTS, LTS, MTS (Naval or Air Force)', '', 3, '1st Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:05:22', 1),
(444, 'IT 123', 'Human Computer Interaction', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:06:52', 1),
(445, 'IT 143', 'Computer Programming 2', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:08:00', 1),
(446, 'IT 163', 'Statistics and Probability', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:09:14', 1),
(447, 'GEN ED 003', 'Readings in Philippine History', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:10:32', 1),
(448, 'Rizal 001', 'Rizal, Life and Works', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:11:50', 1),
(449, 'GEN EL 002', 'GE Elective 2', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:12:51', 1),
(450, 'PE 122', '(PATHFIT) Fitness Training', '', 2, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:13:57', 1),
(451, 'NSTP 123', 'CWTS, LTS, MTS (Naval or Airforce)', '', 3, '1st Year', '2nd Sem', 50, 6, 107568, '2025-12-17 09:15:44', 1),
(452, 'IT 213', 'Data Structures and Algorithms', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:17:20', 1),
(453, 'IT 233', 'Object Oriented Programming', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:23:54', 1),
(454, 'IT 253', 'Platform Technologies', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:25:11', 1),
(455, 'NET 01', 'Introduction to Networking and Network Protocols: Fundamental, Concept, and Services', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:27:24', 1),
(456, 'GEN ED 004', 'Mathematics in the Modern World', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:28:48', 1),
(457, 'GEN ED 007', 'The Contemporary World', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 09:30:54', 1),
(461, 'GE EL 003', 'GE Elective 3', '', 3, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 14:33:50', 1),
(462, 'PE 212', '(PATHFIT) Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 1', '', 2, '2nd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:17:32', 1),
(463, 'IT 223', 'Fundamental of Information Management', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:19:32', 1),
(464, 'IT 243', 'Quantitative Methods', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:20:54', 1),
(465, 'IT 283', 'Event-Driven Programming', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:22:44', 1),
(466, 'IT 200', 'Fundamentals of Artificial Intelligence', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:24:16', 1),
(467, 'NET 02', 'Routing, Switching, and Wireless Networks Concept Configuration and Management', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:27:11', 1),
(468, 'GEN ED 005', 'Art Appreciation', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:28:26', 1),
(469, 'GEN ED 008', 'Science, Technology and Society', '', 3, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:30:17', 1),
(470, 'PE 222', 'Dance, Sport, Group Exercise, Outdoor and Adventure Activities - 2', '', 2, '2nd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:32:18', 1),
(471, 'IT 313', 'Advanced Database Systems', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:35:37', 1),
(472, 'IT 333', 'Data Mining and Analytics', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:36:33', 1),
(473, 'IT 353', 'Systems Integration and Architecture', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:38:06', 1),
(474, 'IT 363', 'Information Assurance and Security', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:39:54', 1),
(475, 'IT 373', 'Web Systems and Technology', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:40:58', 1),
(476, 'IT 383', 'Integrative Programming and Technologies', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:42:21', 1),
(477, 'NET 03', 'Enterprise Networking Security and Automation', '', 3, '3rd Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:44:28', 1),
(478, 'IT 323', 'Project Management for IT', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:46:03', 1),
(479, 'IT 343', 'Multimedia Systems', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:47:38', 1),
(480, 'IT 353A', 'Virtualization and Cloud Platforms', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:49:07', 1),
(481, 'IT 363A', 'Information Technology Strategy', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:52:03', 1),
(482, 'IT 373A', 'Innovation Technopreneurship', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:54:21', 1),
(483, 'IT 383A', 'Mobile Application Development and Emerging Technologies', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:56:18', 1),
(484, 'IT 393', 'IT Electives', '', 3, '3rd Year', '2nd Sem', 50, 6, 107568, '2025-12-17 16:57:41', 1),
(485, 'IT 413', 'System Administration and Maintenance', '', 3, '4th Year', '1st Sem', 50, 6, 107568, '2025-12-17 16:59:34', 1),
(486, 'IT 433', 'Capstone Project and Research 2', '', 3, '4th Year', '1st Sem', 50, 6, 107568, '2025-12-17 17:01:49', 1),
(487, 'IT 426', 'Practicum (486 hours)', '', 6, '4th Year', '2nd Sem', 50, 6, 107568, '2025-12-17 17:03:38', 1),
(488, 'IT 303', 'Social and Professional Issues', '', 3, '3rd Year', 'Summer', 50, 6, 107568, '2025-12-28 01:07:03', 1),
(489, 'IT 303A', 'Capstone Project and Research 1', '', 3, '3rd Year', 'Summer', 50, 6, 107568, '2025-12-28 01:14:27', 1);

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
(254, 370, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'Comp Lab 1'),
(255, 370, 'A', 'Laboratory', 'Wednesday', '08:00:00', '11:00:00', 'Comp Lab 1'),
(256, 371, 'A', 'Lecture', 'Tuesday', '09:30:00', '11:00:00', 'Room 201'),
(257, 371, 'A', 'Laboratory', 'Thursday', '09:30:00', '12:30:00', 'Comp Lab 2'),
(258, 372, 'A', 'Lecture', 'Monday', '11:00:00', '12:30:00', 'Room 101'),
(259, 372, 'A', 'Lecture', 'Wednesday', '11:00:00', '12:30:00', 'Room 101'),
(260, 373, 'A', 'Lecture', 'Tuesday', '13:00:00', '14:30:00', 'Room 102'),
(261, 373, 'A', 'Lecture', 'Thursday', '13:00:00', '14:30:00', 'Room 102'),
(262, 374, 'A', 'Lecture', 'Monday', '14:30:00', '16:00:00', 'Room 103'),
(263, 374, 'A', 'Lecture', 'Wednesday', '14:30:00', '16:00:00', 'Room 103'),
(268, 381, 'A', 'Lecture', 'Tuesday', '11:00:00', '12:30:00', 'Room 104'),
(269, 381, 'A', 'Lecture', 'Thursday', '11:00:00', '12:30:00', 'Room 104'),
(276, 391, 'A', 'Lecture', 'Thursday', '13:00:00', '14:30:00', 'Room 304'),
(277, 391, 'A', 'Laboratory', 'Friday', '13:00:00', '16:00:00', 'Comp Lab 2'),
(298, 412, 'A', 'Lecture', 'Wednesday', '09:30:00', '11:00:00', 'Multimedia Lab'),
(299, 412, 'A', 'Laboratory', 'Friday', '09:30:00', '12:30:00', 'Multimedia Lab'),
(316, 400, 'ONLINE', 'Lecture', 'Sunday', '09:00:00', '12:00:00', 'Online'),
(317, 392, 'ONLINE', 'Lecture', 'Sunday', '13:00:00', '16:00:00', 'Online'),
(318, 410, 'ONLINE', 'Lecture', 'Sunday', '16:00:00', '19:00:00', 'Online'),
(319, 371, 'B', 'Lecture', 'Wednesday', '09:30:00', '11:00:00', 'Room 201'),
(322, 391, 'B', 'Lecture', 'Friday', '13:00:00', '14:30:00', 'Room 304'),
(328, 424, 'A', 'Lecture', 'Thursday', '09:00:00', '11:00:00', '1'),
(329, 424, 'B', 'Lecture', 'Saturday', '13:00:00', '15:00:00', '1'),
(335, 394, 'A', 'Lecture', 'Tuesday', '13:00:00', '15:00:00', '2'),
(336, 394, 'B', 'Lecture', 'Monday', '13:00:00', '15:00:00', '1'),
(337, 401, 'A', 'Lecture', 'Wednesday', '08:00:00', '11:00:00', '2'),
(338, 401, 'B', 'Lecture', 'Saturday', '13:50:00', '16:00:00', '1'),
(349, 413, 'A', 'Lecture', 'Friday', '13:00:00', '16:00:00', '1'),
(350, 413, 'B', 'Lecture', 'Saturday', '13:00:00', '16:00:00', '1'),
(370, 380, 'A', 'Lecture', 'Tuesday', '08:00:00', '09:30:00', 'Room 202'),
(371, 380, 'A', 'Laboratory', 'Thursday', '08:00:00', '11:00:00', 'Comp Lab 3'),
(372, 382, 'A', 'Lecture', 'Monday', '09:30:00', '11:00:00', 'Room 203'),
(373, 382, 'A', 'Laboratory', 'Wednesday', '13:00:00', '16:00:00', 'Comp Lab 2'),
(374, 382, 'B', 'Lecture', 'Tuesday', '09:30:00', '11:00:00', 'Room 203'),
(375, 387, 'A', 'Lecture', 'Saturday', '13:30:00', '16:00:00', '1'),
(376, 387, 'B', 'Lecture', 'Sunday', '13:30:00', '16:00:00', '1'),
(377, 388, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'Room 301'),
(378, 388, 'A', 'Laboratory', 'Wednesday', '08:00:00', '11:00:00', 'Comp Lab 4'),
(379, 389, 'A', 'Lecture', 'Tuesday', '09:30:00', '11:00:00', 'Room 302'),
(380, 389, 'A', 'Laboratory', 'Thursday', '09:30:00', '12:30:00', 'Comp Lab 5'),
(381, 389, 'B', 'Lecture', 'Wednesday', '09:30:00', '11:00:00', 'Room 302'),
(382, 390, 'A', 'Lecture', 'Wednesday', '11:00:00', '12:30:00', 'Room 303'),
(383, 390, 'A', 'Laboratory', 'Friday', '08:00:00', '11:00:00', 'Comp Lab 1'),
(384, 393, 'A', 'Lecture', 'Monday', '14:30:00', '16:00:00', 'Networking Lab'),
(385, 393, 'A', 'Laboratory', 'Wednesday', '14:30:00', '17:30:00', 'Networking Lab'),
(386, 396, 'A', 'Lecture', 'Tuesday', '08:00:00', '09:30:00', 'Room 401'),
(387, 396, 'A', 'Laboratory', 'Thursday', '08:00:00', '11:00:00', 'DB Lab'),
(388, 397, 'A', 'Lecture', 'Tuesday', '08:00:00', '11:00:00', '1'),
(389, 397, 'B', 'Lecture', 'Friday', '13:00:00', '16:00:00', '1'),
(390, 398, 'A', 'Lecture', 'Wednesday', '09:30:00', '11:00:00', 'Room 402'),
(391, 398, 'A', 'Laboratory', 'Friday', '09:30:00', '12:30:00', 'Comp Lab 3'),
(392, 399, 'A', 'Lecture', 'Monday', '11:00:00', '12:30:00', 'Networking Lab'),
(393, 399, 'A', 'Laboratory', 'Wednesday', '11:00:00', '14:00:00', 'Networking Lab'),
(394, 404, 'A', 'Lecture', 'Tuesday', '13:00:00', '14:30:00', 'Room 501'),
(395, 404, 'A', 'Laboratory', 'Thursday', '13:00:00', '16:00:00', 'DB Lab'),
(396, 405, 'A', 'Lecture', 'Monday', '14:30:00', '16:00:00', 'Room 502'),
(397, 405, 'A', 'Laboratory', 'Wednesday', '14:30:00', '17:30:00', 'Project Lab'),
(398, 406, 'A', 'Lecture', 'Tuesday', '08:00:00', '11:00:00', '1'),
(399, 406, 'B', 'Lecture', 'Wednesday', '08:00:00', '11:00:00', '1'),
(400, 407, 'A', 'Lecture', 'Tuesday', '16:00:00', '17:30:00', 'Room 503'),
(401, 407, 'A', 'Laboratory', 'Thursday', '16:00:00', '19:00:00', 'Systems Lab'),
(402, 408, 'A', 'Lecture', 'Wednesday', '16:00:00', '17:30:00', 'Room 504'),
(403, 408, 'A', 'Laboratory', 'Friday', '16:00:00', '19:00:00', 'Web Lab'),
(404, 409, 'A', 'Lecture', 'Monday', '08:00:00', '10:00:00', '1'),
(405, 409, 'A', 'Laboratory', 'Wednesday', '13:00:00', '16:00:00', '2'),
(406, 409, 'B', 'Lecture', 'Tuesday', '08:00:00', '10:00:00', '1'),
(407, 409, 'B', 'Laboratory', 'Friday', '13:00:00', '16:00:00', '2'),
(408, 411, 'A', 'Lecture', 'Monday', '17:30:00', '19:00:00', 'Networking Lab'),
(409, 411, 'A', 'Laboratory', 'Wednesday', '17:30:00', '20:30:00', 'Networking Lab'),
(410, 49, 'A', 'Lecture', 'Monday', '09:30:00', '11:00:00', '1'),
(411, 49, 'B', 'Lecture', 'Tuesday', '08:00:00', '10:00:00', '2'),
(412, 49, 'A', 'Laboratory', 'Friday', '13:00:00', '15:00:00', '2'),
(413, 49, 'B', 'Laboratory', 'Saturday', '13:00:00', '15:00:00', '1'),
(414, 49, 'C', 'Lecture', 'Tuesday', '08:00:00', '09:30:00', 'Room 601'),
(415, 49, 'C', 'Laboratory', 'Thursday', '08:00:00', '11:00:00', 'SE Lab'),
(416, 49, 'B', 'Lecture', 'Tuesday', '09:30:00', '11:00:00', '1'),
(417, 49, 'C', 'Lecture', 'Wednesday', '08:00:00', '10:00:00', '2'),
(418, 49, 'D', 'Lecture', 'Wednesday', '08:00:00', '09:30:00', 'Room 601'),
(419, 414, 'A', 'Lecture', 'Monday', '11:00:00', '12:30:00', 'Security Lab'),
(420, 414, 'A', 'Laboratory', 'Wednesday', '11:00:00', '14:00:00', 'Security Lab'),
(421, 415, 'A', 'Lecture', 'Thursday', '14:30:00', '16:00:00', 'App Dev Lab'),
(422, 415, 'A', 'Laboratory', 'Saturday', '13:00:00', '16:00:00', 'App Dev Lab'),
(423, 415, 'S', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'Security Lab'),
(424, 415, 'S', 'Lecture', 'Wednesday', '08:00:00', '09:30:00', 'Security Lab'),
(425, 415, 'S', 'Laboratory', 'Friday', '08:00:00', '11:00:00', 'Security Lab'),
(426, 416, 'A', 'Lecture', 'Tuesday', '16:00:00', '17:30:00', 'Networking Lab'),
(427, 416, 'A', 'Laboratory', 'Thursday', '16:00:00', '19:00:00', 'Networking Lab'),
(428, 417, 'A', 'Lecture', 'Wednesday', '13:00:00', '15:00:00', '1'),
(429, 417, 'A', 'Laboratory', 'Thursday', '13:00:00', '16:00:00', '2'),
(430, 417, 'B', 'Lecture', 'Thursday', '08:00:00', '10:00:00', '1'),
(431, 417, 'B', 'Laboratory', 'Friday', '13:00:00', '16:00:00', '2'),
(432, 418, 'A', 'Lecture', 'Monday', '09:00:00', '11:00:00', '1'),
(433, 418, 'A', 'Laboratory', 'Wednesday', '13:00:00', '16:00:00', '2'),
(434, 418, 'B', 'Lecture', 'Tuesday', '09:00:00', '11:00:00', '1'),
(435, 418, 'B', 'Laboratory', 'Thursday', '13:00:00', '16:00:00', '2'),
(450, 421, 'A', 'Lecture', 'Monday', '13:00:00', '14:30:00', 'Sys Admin Lab'),
(451, 421, 'A', 'Laboratory', 'Wednesday', '13:00:00', '16:00:00', 'Sys Admin Lab'),
(452, 421, 'B', 'Lecture', 'Tuesday', '14:30:00', '16:00:00', 'Project Room'),
(453, 421, 'B', 'Laboratory', 'Thursday', '14:30:00', '17:30:00', 'Project Room'),
(455, 422, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(456, 383, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(457, 384, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(458, 385, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(459, 386, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(460, 402, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(461, 403, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(462, 395, 'A', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(467, 426, 'A', 'Lecture', 'Monday', '01:19:00', '05:53:00', '1'),
(468, 425, 'A', 'Lecture', 'Monday', '06:00:00', '10:15:00', '1'),
(469, 427, 'A', 'Lecture', 'Monday', '07:49:00', '10:50:00', '1'),
(470, 428, 'A', 'Lecture', 'Monday', '01:49:00', '04:55:00', '1'),
(471, 429, 'A', 'Lecture', 'Monday', '09:18:00', '21:18:00', '1'),
(473, 430, 'A', 'Lecture', 'Tuesday', '09:34:00', '21:34:00', '3'),
(474, 430, 'A', 'Laboratory', 'Friday', '09:36:00', '21:36:00', '1'),
(476, 431, 'A', 'Lecture', 'Monday', '09:45:00', '21:45:00', '1'),
(481, 432, 'A', 'Lecture', 'Tuesday', '09:49:00', '21:49:00', '1'),
(497, 375, '', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(498, 376, '', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(499, 377, '', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(500, 378, '', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(501, 379, '', 'Lecture', 'Monday', '08:00:00', '09:30:00', 'TBA'),
(502, 423, 'A', 'Lecture', 'Monday', '13:31:00', '17:31:00', '5'),
(503, 433, 'A', 'Lecture', 'Monday', '08:00:00', '10:00:00', '1'),
(504, 434, 'A', 'Lecture', 'Monday', '08:00:00', '11:30:00', '5'),
(505, 436, 'A', 'Lecture', 'Monday', '13:00:00', '15:30:00', '5'),
(506, 420, 'S', 'Lecture', 'Tuesday', '09:30:00', '11:00:00', 'Project Room'),
(507, 420, 'S', 'Lecture', 'Thursday', '09:30:00', '11:00:00', 'Project Room'),
(508, 420, 'S', 'Laboratory', 'Saturday', '08:00:00', '11:00:00', 'Project Room'),
(509, 419, 'A', 'Lecture', 'Wednesday', '13:00:00', '15:00:00', '1'),
(510, 419, 'A', 'Laboratory', 'Thursday', '14:00:00', '17:00:00', '2'),
(511, 419, 'B', 'Lecture', 'Friday', '09:00:00', '11:00:00', '1'),
(512, 419, 'B', 'Laboratory', 'Saturday', '13:00:00', '16:00:00', '2'),
(513, 437, 'A', 'Lecture', 'Tuesday', '08:00:00', '10:30:00', '1'),
(514, 438, 'A', 'Lecture', 'Tuesday', '08:56:00', '11:56:00', '1'),
(515, 439, 'A', 'Lecture', 'Wednesday', '08:57:00', '11:58:00', '3'),
(516, 440, 'A', 'Lecture', 'Wednesday', '13:59:00', '15:59:00', '5'),
(517, 441, 'A', 'Lecture', 'Wednesday', '16:00:00', '18:00:00', '1'),
(518, 442, 'A', 'Lecture', 'Friday', '09:02:00', '11:02:00', '1'),
(519, 443, 'A', 'Lecture', 'Monday', '09:05:00', '10:05:00', '1'),
(522, 446, 'A', 'Lecture', 'Thursday', '09:08:00', '12:08:00', '1'),
(523, 447, 'A', 'Lecture', 'Saturday', '09:10:00', '11:10:00', '1'),
(524, 448, 'A', 'Lecture', 'Tuesday', '09:11:00', '11:11:00', '1'),
(525, 449, 'A', 'Lecture', 'Thursday', '09:12:00', '11:12:00', '5'),
(526, 450, 'A', 'Lecture', 'Friday', '09:13:00', '11:13:00', '1'),
(532, 456, 'A', 'Lecture', 'Tuesday', '09:28:00', '11:28:00', '5'),
(533, 457, 'A', 'Lecture', 'Friday', '09:30:00', '10:30:00', '1'),
(534, 461, 'A', 'Lecture', 'Tuesday', '10:33:00', '12:33:00', '1'),
(535, 462, 'A', 'Lecture', 'Wednesday', '14:17:00', '15:17:00', '1'),
(541, 468, 'A', 'Lecture', 'Thursday', '15:28:00', '17:28:00', '1'),
(542, 469, 'A', 'Lecture', 'Friday', '13:29:00', '14:30:00', '1'),
(543, 470, 'A', 'Lecture', 'Saturday', '14:32:00', '15:32:00', '1'),
(552, 479, 'A', 'Lecture', 'Tuesday', '09:47:00', '10:47:00', '1'),
(557, 484, 'A', 'Lecture', 'Saturday', '15:57:00', '16:57:00', '1'),
(560, 487, 'A', 'Lecture', 'Wednesday', '13:03:00', '16:03:00', '1'),
(561, 488, 'A', 'Lecture', 'Monday', '09:00:00', '11:00:00', '1'),
(562, 489, 'A', 'Lecture', 'Monday', '13:00:00', '17:00:00', '5'),
(563, 444, 'A', 'Lecture', 'Thursday', '09:06:00', '11:06:00', '4'),
(564, 445, 'A', 'Lecture', 'Thursday', '09:07:00', '11:07:00', '1'),
(565, 451, 'A', 'Lecture', 'Saturday', '10:15:00', '12:15:00', '1'),
(566, 452, 'A', 'Lecture', 'Monday', '09:17:00', '11:17:00', '1'),
(567, 453, 'A', 'Lecture', 'Thursday', '09:23:00', '11:23:00', '1'),
(568, 454, 'A', 'Lecture', 'Monday', '09:24:00', '11:25:00', '1'),
(569, 455, 'A', 'Lecture', 'Friday', '09:27:00', '11:27:00', '1'),
(570, 463, 'A', 'Lecture', 'Thursday', '09:19:00', '11:19:00', '1'),
(571, 464, 'A', 'Lecture', 'Thursday', '09:20:00', '11:20:00', '1'),
(572, 465, 'A', 'Lecture', 'Monday', '08:22:00', '10:22:00', '1'),
(573, 466, 'A', 'Lecture', 'Tuesday', '10:23:00', '12:23:00', '1'),
(574, 467, 'A', 'Lecture', 'Thursday', '13:26:00', '15:26:00', '1'),
(575, 471, 'A', 'Lecture', 'Monday', '08:35:00', '10:35:00', '1'),
(576, 472, 'A', 'Lecture', 'Tuesday', '09:36:00', '11:36:00', '1'),
(577, 473, 'A', 'Lecture', 'Wednesday', '10:37:00', '12:37:00', '1'),
(578, 474, 'A', 'Lecture', 'Wednesday', '14:39:00', '16:39:00', '1'),
(579, 475, 'A', 'Lecture', 'Thursday', '15:40:00', '17:40:00', '1'),
(580, 476, 'A', 'Lecture', 'Thursday', '13:42:00', '15:42:00', '1'),
(581, 477, 'A', 'Lecture', 'Friday', '15:44:00', '17:44:00', '1'),
(582, 478, 'A', 'Lecture', 'Monday', '09:45:00', '11:45:00', '1'),
(583, 480, 'A', 'Lecture', 'Wednesday', '14:48:00', '15:48:00', '1'),
(584, 481, 'A', 'Lecture', 'Thursday', '14:51:00', '15:51:00', '1'),
(585, 482, 'A', 'Lecture', 'Thursday', '14:54:00', '15:54:00', '1'),
(586, 483, 'A', 'Lecture', 'Saturday', '13:56:00', '15:56:00', '1'),
(587, 485, 'A', 'Lecture', 'Monday', '09:59:00', '11:59:00', '1'),
(588, 486, 'A', 'Lecture', 'Tuesday', '14:01:00', '16:01:00', '1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email2` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile` varchar(100) DEFAULT NULL,
  `date_created` varchar(100) DEFAULT NULL,
  `user_type` enum('student','instructor','organization','admin') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email2`, `password`, `profile`, `date_created`, `user_type`, `is_active`, `last_login`) VALUES
(220595, 'carljames.duallo@evsu.edu.ph', '$2y$10$y9sIITrS520YaZ1U4lRM9uIJcB.ymztm.zQxHG.R41xgeGGcVShp2', 'documents/requirements/id_picture.png', '2025-12-31 00:48:56', 'student', 1, NULL),
(316085, 'carljamesduallo661@gmail.com', '$2y$10$uoAGE.KFahs8zOZ2KrGrzuk8uJD82d9d/6yjTJEVgys.SijJtx2Jq', 'documents/requirements/id_picture.jpg', '2025-12-31 18:11:05', 'student', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `birthdate` varchar(100) DEFAULT NULL,
  `age` varchar(100) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `relationship_status` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`id`, `user_id`, `firstname`, `lastname`, `middlename`, `birthdate`, `age`, `sex`, `address`, `relationship_status`, `phone_number`) VALUES
(128, 220595, ' sukuna', 'Ryomen', NULL, '2000-03-08', '25', 'Male', 'Proper, Cambalong, Merida, Leyte', 'Single', '091712345900'),
(129, 316085, 'Zeref', 'Dragneel', NULL, '2000-03-08', '25', 'Male', 'Proper, Cambalong, Merida, Leyte', 'Single', '09171234569');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `api_access_logs`
--
ALTER TABLE `api_access_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `access_by` (`access_by`);

--
-- Indexes for table `csv`
--
ALTER TABLE `csv`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `csv_uploads`
--
ALTER TABLE `csv_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `enrolled_sub`
--
ALTER TABLE `enrolled_sub`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

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
-- Indexes for table `enrollment_date`
--
ALTER TABLE `enrollment_date`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `failed_subjects`
--
ALTER TABLE `failed_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `important_documents`
--
ALTER TABLE `important_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `student_count_year`
--
ALTER TABLE `student_count_year`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_files`
--
ALTER TABLE `student_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

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
  ADD UNIQUE KEY `unique_code_curriculum` (`code`,`curriculum_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_subjects_code` (`code`),
  ADD KEY `fk_subjects_curriculum` (`curriculum_id`);

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
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `admin_info`
--
ALTER TABLE `admin_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `api_access_logs`
--
ALTER TABLE `api_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=634;

--
-- AUTO_INCREMENT for table `csv`
--
ALTER TABLE `csv`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `csv_uploads`
--
ALTER TABLE `csv_uploads`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `curriculum`
--
ALTER TABLE `curriculum`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `enrolled_sub`
--
ALTER TABLE `enrolled_sub`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3832;

--
-- AUTO_INCREMENT for table `enrollmentrequests`
--
ALTER TABLE `enrollmentrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1133;

--
-- AUTO_INCREMENT for table `enrollment_date`
--
ALTER TABLE `enrollment_date`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `failed_subjects`
--
ALTER TABLE `failed_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `important_documents`
--
ALTER TABLE `important_documents`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `instructor_info`
--
ALTER TABLE `instructor_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `notifications_instructor`
--
ALTER TABLE `notifications_instructor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `organizationfees`
--
ALTER TABLE `organizationfees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orgs_info`
--
ALTER TABLE `orgs_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `student_count_year`
--
ALTER TABLE `student_count_year`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_files`
--
ALTER TABLE `student_files`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=490;

--
-- AUTO_INCREMENT for table `subjectschedules`
--
ALTER TABLE `subjectschedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=589;

--
-- AUTO_INCREMENT for table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD CONSTRAINT `admin_info_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `auditlogs_ibfk_1` FOREIGN KEY (`access_by`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `csv_uploads`
--
ALTER TABLE `csv_uploads`
  ADD CONSTRAINT `csv_uploads_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

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
-- Constraints for table `enrollment_date`
--
ALTER TABLE `enrollment_date`
  ADD CONSTRAINT `enrollment_date_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `failed_subjects`
--
ALTER TABLE `failed_subjects`
  ADD CONSTRAINT `failed_subjects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `failed_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `important_documents`
--
ALTER TABLE `important_documents`
  ADD CONSTRAINT `important_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

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
  ADD CONSTRAINT `organizationfees_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

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

--
-- Constraints for table `student_count_year`
--
ALTER TABLE `student_count_year`
  ADD CONSTRAINT `student_count_year_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_files`
--
ALTER TABLE `student_files`
  ADD CONSTRAINT `student_files_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subjects_curriculum` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
