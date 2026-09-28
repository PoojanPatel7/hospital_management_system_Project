-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2026 at 05:49 PM
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
-- Database: `hospital_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(50) DEFAULT NULL,
  `doctor_id` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `slot` varchar(20) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `stage` int(11) DEFAULT NULL,
  `bed_number` varchar(50) DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `type`, `date`, `slot`, `symptoms`, `allergies`, `status`, `stage`, `bed_number`, `doctor_notes`, `created_at`, `hospital_id`) VALUES
(4, 'CP-2026-005', NULL, 'Regular', '2026-09-04', 'Walk-in', 'add', NULL, 'Discharged from Bed', 5, NULL, 'Medications testing', '2026-09-04 08:19:30', 1),
(32, 'CP-2026-002', 'doc-6a9a8604e31bd', 'General Consultation', '2026-09-28', '09:00 AM', 'testing', NULL, 'Available at Hospital', 2, NULL, NULL, '2026-09-28 10:48:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `id` varchar(50) NOT NULL,
  `bed_number` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `wing` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `patient_id` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beds`
--

INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES
('bed-1', 'OPD-101', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-10', 'ICU-04', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, 1),
('bed-2', 'OPD-102', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-3', 'OPD-103', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-4', 'OPD-104', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-5', 'OPD-105', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-6', 'OPD-106', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, 1),
('bed-7', 'ICU-01', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, 1),
('bed-8', 'ICU-02', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, 1),
('bed-9', 'ICU-03', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES
('Cardiology', 'Cardiology', 'fa-heart-pulse', 1),
('Dermatology', 'Dermatology', 'fa-hand-dots', 1),
('Emergency Trauma', 'Emergency Trauma', 'fa-truck-medical', 1),
('General Medicine', 'General Medicine', 'fa-user-doctor', 1),
('Neurology', 'Neurology', 'fa-brain', 1),
('Orthopedics', 'Orthopedics', 'fa-bone', 1),
('Pediatrics', 'Pediatrics', 'fa-baby', 1),
('Pulmonology', 'Pulmonology', 'fa-lungs', 1);

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnoses`
--

INSERT INTO `diagnoses` (`id`, `appointment_id`, `description`) VALUES
(3, 4, 'Admit for reports');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department_id` varchar(100) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `department_id`, `experience`, `degree`, `hospital_id`, `phone`) VALUES
('doc-6a9a8604e31bd', 'Dr. Ambarish A. Panchasara', NULL, NULL, NULL, 1, '9898989898');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_categories`
--

CREATE TABLE `doctor_categories` (
  `doctor_id` varchar(50) NOT NULL,
  `department_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_categories`
--

INSERT INTO `doctor_categories` (`doctor_id`, `department_id`) VALUES
('doc-6a9a8604e31bd', 'General Medicine');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_day_schedules`
--

CREATE TABLE `doctor_day_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` varchar(50) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `start_time` varchar(10) DEFAULT '09:00 AM',
  `end_time` varchar(10) DEFAULT '05:00 PM',
  `duration_minutes` int(11) DEFAULT 30,
  `break_start` varchar(10) DEFAULT NULL,
  `break_end` varchar(10) DEFAULT NULL,
  `custom_slots` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_day_schedules`
--

INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES
(1, 'doc-6a9a8604e31bd', 'Monday', 1, '09:00 AM', '07:00 PM', 15, '02:00 PM', '05:00 PM', '[\"09:00 AM\",\"09:15 AM\",\"09:30 AM\",\"09:45 AM\",\"10:00 AM\",\"10:15 AM\",\"10:30 AM\",\"10:45 AM\",\"11:00 AM\",\"11:15 AM\",\"11:30 AM\",\"11:45 AM\",\"12:00 PM\",\"12:15 PM\",\"12:30 PM\",\"12:45 PM\",\"01:00 PM\",\"01:15 PM\",\"01:30 PM\",\"01:45 PM\",\"05:00 PM\",\"05:15 PM\",\"05:30 PM\",\"05:45 PM\",\"06:00 PM\",\"06:15 PM\",\"06:30 PM\",\"06:45 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:58:21'),
(2, 'doc-6a9a8604e31bd', 'Tuesday', 1, '10:00 AM', '02:00 PM', 20, '01:00 PM', '02:00 PM', '[\"10:00 AM\",\"10:20 AM\",\"10:40 AM\",\"11:00 AM\",\"11:20 AM\",\"11:40 AM\",\"12:00 PM\",\"12:20 PM\",\"12:40 PM\",\"01:00 PM\",\"01:20 PM\",\"01:40 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:57:35'),
(3, 'doc-6a9a8604e31bd', 'Wednesday', 1, '09:00 AM', '05:00 PM', 30, '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:57:35'),
(4, 'doc-6a9a8604e31bd', 'Thursday', 1, '09:00 AM', '05:00 PM', 30, '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:57:35'),
(5, 'doc-6a9a8604e31bd', 'Friday', 1, '09:00 AM', '05:00 PM', 30, '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:57:35'),
(6, 'doc-6a9a8604e31bd', 'Saturday', 1, '09:00 AM', '07:00 PM', 20, '02:00 PM', '05:00 PM', '[\"09:00 AM\",\"09:20 AM\",\"09:40 AM\",\"10:00 AM\",\"10:20 AM\",\"10:40 AM\",\"11:00 AM\",\"11:20 AM\",\"11:40 AM\",\"12:00 PM\",\"12:20 PM\",\"12:40 PM\",\"01:00 PM\",\"01:20 PM\",\"01:40 PM\",\"05:00 PM\",\"05:20 PM\",\"05:40 PM\",\"06:00 PM\",\"06:20 PM\",\"06:40 PM\"]', '2026-09-05 08:52:03', '2026-09-05 09:20:26'),
(7, 'doc-6a9a8604e31bd', 'Sunday', 0, '09:00 AM', '05:00 PM', 30, '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 08:52:03', '2026-09-05 08:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_slots`
--

CREATE TABLE `doctor_slots` (
  `id` int(11) NOT NULL,
  `doctor_id` varchar(50) DEFAULT NULL,
  `time_slot` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `name`, `username`, `password`, `created_at`) VALUES
(1, 'Bhoomaa', 'Gopalbhai', '$2y$10$WTNo.ZbHGb7t21XRtq699.vC9jkuvEW1CzfkrAg2Ezjcf4909MOdC', '2026-09-04 08:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `demographics` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `surname`, `father_name`, `phone`, `demographics`, `created_at`, `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`) VALUES
('CP-2026-002', 'dev', 'patel', 'Rameshbhai', '9887676545', '34 Y, Male, A+', '2026-09-04 10:04:28', 'Male', 'A+', '34', 'Poojan Panchasara', '7201050500', 1),
('CP-2026-003', 'dev', 'patel', 'Rameshbhai', '7867545446', '44 Y, Male, B+', '2026-09-04 10:04:28', 'Male', 'B+', '44', 'harsh', '9882535637', 1),
('CP-2026-005', 'Raj', 'Patel', 'Rameshbhai', '7892345678', '34 Y, Male, AB+', '2026-09-04 08:17:21', 'Male', 'AB+', '34', 'time pass', '9988776655', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patient_files`
--

CREATE TABLE `patient_files` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(50) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `record_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_files`
--

INSERT INTO `patient_files` (`id`, `patient_id`, `appointment_id`, `title`, `file_path`, `record_date`, `created_at`) VALUES
(4, 'CP-2026-005', 4, 'Testing files image', 'uploads/CP-2026-005_6a9bc53fe820a.jpg', '2026-09-05 07:30:00', '2026-09-05 07:31:11');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medicine_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `appointment_id`, `medicine_name`, `dosage`, `frequency`, `duration`, `instructions`) VALUES
(4, 4, 'Test Medications', '1-0-0-1', '5 days', '', 'testing note');

-- --------------------------------------------------------

--
-- Table structure for table `system_state`
--

CREATE TABLE `system_state` (
  `id` int(11) NOT NULL,
  `line_running` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_state`
--

INSERT INTO `system_state` (`id`, `line_running`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `timeline_events`
--

CREATE TABLE `timeline_events` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `patient_id` varchar(50) DEFAULT NULL,
  `event_time` varchar(50) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_events`
--

INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES
(15, 4, 'CP-2026-005', '10:19 AM', 'Patient arrived and was added to the queue.', '2026-09-04 08:19:30'),
(16, 4, 'CP-2026-005', '10:19 AM', 'Patient arrived at hospital desk.', '2026-09-04 08:19:56'),
(18, 4, 'CP-2026-005', '12:03 PM', 'Patient transferred to Waiting Lounge.', '2026-09-04 10:03:04'),
(19, 4, 'CP-2026-005', '12:03 PM', 'Called into consulting room.', '2026-09-04 10:03:25'),
(20, 4, 'CP-2026-005', '12:05 PM', 'Reverted to waiting room.', '2026-09-04 10:05:09'),
(24, 4, 'CP-2026-005', '12:13 PM', 'Reverted to available.', '2026-09-04 10:13:59'),
(27, 4, 'CP-2026-005', '12:16 PM', 'Patient transferred to Waiting Lounge.', '2026-09-04 10:16:05'),
(35, 4, 'CP-2026-005', '09:29 AM', 'Called into consulting room.', '2026-09-05 07:29:09'),
(36, 4, 'CP-2026-005', '09:31 AM', 'Patient admitted to OPD Bed [OPD-102].', '2026-09-05 07:31:11'),
(37, 4, 'CP-2026-005', '09:33 AM', 'Discharged from inpatient bed OPD-102. Ready for home rest.', '2026-09-05 07:33:34'),
(66, 32, 'CP-2026-002', '12:48 PM', 'Patient arrived and was added to the queue.', '2026-09-28 10:48:36'),
(67, 32, 'CP-2026-002', '12:48 PM', 'Pre-booked patient arrived at hospital and checked in.', '2026-09-28 10:48:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `bed_number` (`bed_number`),
  ADD KEY `fk_appt_hospital` (`hospital_id`);

--
-- Indexes for table `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bed_number` (`bed_number`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `fk_bed_hospital` (`hospital_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `fk_doctor_hospital` (`hospital_id`);

--
-- Indexes for table `doctor_categories`
--
ALTER TABLE `doctor_categories`
  ADD PRIMARY KEY (`doctor_id`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `doctor_day_schedules`
--
ALTER TABLE `doctor_day_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doc_day` (`doctor_id`,`day_of_week`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctor_slots`
--
ALTER TABLE `doctor_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_patient_hospital` (`hospital_id`);

--
-- Indexes for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `system_state`
--
ALTER TABLE `system_state`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timeline_events`
--
ALTER TABLE `timeline_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctor_day_schedules`
--
ALTER TABLE `doctor_day_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `doctor_slots`
--
ALTER TABLE `doctor_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient_files`
--
ALTER TABLE `patient_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `timeline_events`
--
ALTER TABLE `timeline_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`bed_number`) REFERENCES `beds` (`bed_number`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appt_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `beds_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bed_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_doctor_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_categories`
--
ALTER TABLE `doctor_categories`
  ADD CONSTRAINT `doctor_categories_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_categories_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_slots`
--
ALTER TABLE `doctor_slots`
  ADD CONSTRAINT `doctor_slots_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);

--
-- Constraints for table `timeline_events`
--
ALTER TABLE `timeline_events`
  ADD CONSTRAINT `timeline_events_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `timeline_events_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
