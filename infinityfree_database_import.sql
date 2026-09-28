-- InfinityFree Ready Database Import
-- Target Database: if0_42838894_HMS
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `hospital_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `bed_number` (`bed_number`),
  KEY `fk_appt_hospital` (`hospital_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`bed_number`) REFERENCES `beds` (`bed_number`) ON DELETE SET NULL,
  CONSTRAINT `fk_appt_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `type`, `date`, `slot`, `symptoms`, `allergies`, `status`, `stage`, `bed_number`, `doctor_notes`, `created_at`, `hospital_id`) VALUES ('3', 'CP-2026-004', NULL, 'Regular', '2026-09-03', 'Immediate Walk-In', 'testing', 'None recorded', 'Discharged (Normal Medicine)', '5', NULL, '', '2026-09-03 22:36:55', '1');
INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `type`, `date`, `slot`, `symptoms`, `allergies`, `status`, `stage`, `bed_number`, `doctor_notes`, `created_at`, `hospital_id`) VALUES ('4', 'CP-2026-005', NULL, 'Regular', '2026-09-04', 'Walk-in', 'add', NULL, 'Discharged from Bed', '5', NULL, 'Medications testing', '2026-09-04 13:49:30', '1');

DROP TABLE IF EXISTS `beds`;
CREATE TABLE `beds` (
  `id` varchar(50) NOT NULL,
  `bed_number` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `wing` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `patient_id` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bed_number` (`bed_number`),
  KEY `patient_id` (`patient_id`),
  KEY `fk_bed_hospital` (`hospital_id`),
  CONSTRAINT `beds_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bed_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-1', 'OPD-101', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-10', 'ICU-04', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-2', 'OPD-102', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-3', 'OPD-103', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-4', 'OPD-104', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-5', 'OPD-105', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-6', 'OPD-106', 'OPD', 'North Wing, 2nd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-7', 'ICU-01', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-8', 'ICU-02', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, '1');
INSERT INTO `beds` (`id`, `bed_number`, `type`, `wing`, `status`, `patient_id`, `hospital_id`) VALUES ('bed-9', 'ICU-03', 'ICU', 'Critical Care Floor, 3rd Floor', 'Available', NULL, '1');

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Cardiology', 'Cardiology', 'fa-heart-pulse', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Dermatology', 'Dermatology', 'fa-hand-dots', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Emergency Trauma', 'Emergency Trauma', 'fa-truck-medical', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('General Medicine', 'General Medicine', 'fa-user-doctor', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Neurology', 'Neurology', 'fa-brain', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Orthopedics', 'Orthopedics', 'fa-bone', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Pediatrics', 'Pediatrics', 'fa-baby', '1');
INSERT INTO `departments` (`id`, `name`, `icon`, `hospital_id`) VALUES ('Pulmonology', 'Pulmonology', 'fa-lungs', '1');

DROP TABLE IF EXISTS `diagnoses`;
CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `diagnoses` (`id`, `appointment_id`, `description`) VALUES ('3', '4', 'Admit for reports');

DROP TABLE IF EXISTS `doctor_categories`;
CREATE TABLE `doctor_categories` (
  `doctor_id` varchar(50) NOT NULL,
  `department_id` varchar(100) NOT NULL,
  PRIMARY KEY (`doctor_id`,`department_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `doctor_categories_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctor_categories_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `doctor_categories` (`doctor_id`, `department_id`) VALUES ('doc-6a9a8604e31bd', 'General Medicine');

DROP TABLE IF EXISTS `doctor_day_schedules`;
CREATE TABLE `doctor_day_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_day` (`doctor_id`,`day_of_week`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('1', 'doc-6a9a8604e31bd', 'Monday', '1', '09:00 AM', '07:00 PM', '15', '02:00 PM', '05:00 PM', '[\"09:00 AM\",\"09:15 AM\",\"09:30 AM\",\"09:45 AM\",\"10:00 AM\",\"10:15 AM\",\"10:30 AM\",\"10:45 AM\",\"11:00 AM\",\"11:15 AM\",\"11:30 AM\",\"11:45 AM\",\"12:00 PM\",\"12:15 PM\",\"12:30 PM\",\"12:45 PM\",\"01:00 PM\",\"01:15 PM\",\"01:30 PM\",\"01:45 PM\",\"05:00 PM\",\"05:15 PM\",\"05:30 PM\",\"05:45 PM\",\"06:00 PM\",\"06:15 PM\",\"06:30 PM\",\"06:45 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:28:21');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('2', 'doc-6a9a8604e31bd', 'Tuesday', '1', '10:00 AM', '02:00 PM', '20', '01:00 PM', '02:00 PM', '[\"10:00 AM\",\"10:20 AM\",\"10:40 AM\",\"11:00 AM\",\"11:20 AM\",\"11:40 AM\",\"12:00 PM\",\"12:20 PM\",\"12:40 PM\",\"01:00 PM\",\"01:20 PM\",\"01:40 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:27:35');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('3', 'doc-6a9a8604e31bd', 'Wednesday', '1', '09:00 AM', '05:00 PM', '30', '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:27:35');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('4', 'doc-6a9a8604e31bd', 'Thursday', '1', '09:00 AM', '05:00 PM', '30', '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:27:35');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('5', 'doc-6a9a8604e31bd', 'Friday', '1', '09:00 AM', '05:00 PM', '30', '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:27:35');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('6', 'doc-6a9a8604e31bd', 'Saturday', '1', '09:00 AM', '07:00 PM', '20', '02:00 PM', '05:00 PM', '[\"09:00 AM\",\"09:20 AM\",\"09:40 AM\",\"10:00 AM\",\"10:20 AM\",\"10:40 AM\",\"11:00 AM\",\"11:20 AM\",\"11:40 AM\",\"12:00 PM\",\"12:20 PM\",\"12:40 PM\",\"01:00 PM\",\"01:20 PM\",\"01:40 PM\",\"05:00 PM\",\"05:20 PM\",\"05:40 PM\",\"06:00 PM\",\"06:20 PM\",\"06:40 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:50:26');
INSERT INTO `doctor_day_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `duration_minutes`, `break_start`, `break_end`, `custom_slots`, `created_at`, `updated_at`) VALUES ('7', 'doc-6a9a8604e31bd', 'Sunday', '0', '09:00 AM', '05:00 PM', '30', '01:00 PM', '02:00 PM', '[\"09:00 AM\",\"09:30 AM\",\"10:00 AM\",\"10:30 AM\",\"11:00 AM\",\"11:30 AM\",\"12:00 PM\",\"12:30 PM\",\"02:00 PM\",\"02:30 PM\",\"03:00 PM\",\"03:30 PM\",\"04:00 PM\",\"04:30 PM\"]', '2026-09-05 14:22:03', '2026-09-05 14:27:35');

DROP TABLE IF EXISTS `doctor_slots`;
CREATE TABLE `doctor_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` varchar(50) DEFAULT NULL,
  `time_slot` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `doctor_slots_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department_id` varchar(100) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `fk_doctor_hospital` (`hospital_id`),
  CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `fk_doctor_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `doctors` (`id`, `name`, `department_id`, `experience`, `degree`, `hospital_id`, `phone`) VALUES ('doc-6a9a8604e31bd', 'Dr. Ambarish A. Panchasara', NULL, NULL, NULL, '1', '9898989898');

DROP TABLE IF EXISTS `hospitals`;
CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hospitals` (`id`, `name`, `username`, `password`, `created_at`) VALUES ('1', 'Bhoomaa', 'Gopalbhai', '$2y$10$WTNo.ZbHGb7t21XRtq699.vC9jkuvEW1CzfkrAg2Ezjcf4909MOdC', '2026-09-04 14:16:10');

DROP TABLE IF EXISTS `patient_files`;
CREATE TABLE `patient_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(50) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `record_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patient_files` (`id`, `patient_id`, `appointment_id`, `title`, `file_path`, `record_date`, `created_at`) VALUES ('2', 'CP-2026-004', '3', 'testing files', 'uploads/CP-2026-004_6a9a7373dafa5.jpg', '2026-09-04 07:29:00', '2026-09-04 12:59:55');
INSERT INTO `patient_files` (`id`, `patient_id`, `appointment_id`, `title`, `file_path`, `record_date`, `created_at`) VALUES ('3', 'CP-2026-004', '3', 'testing files 2', 'uploads/CP-2026-004_6a9a7373db9e9.jpeg', '2026-09-04 07:29:00', '2026-09-04 12:59:55');
INSERT INTO `patient_files` (`id`, `patient_id`, `appointment_id`, `title`, `file_path`, `record_date`, `created_at`) VALUES ('4', 'CP-2026-005', '4', 'Testing files image', 'uploads/CP-2026-005_6a9bc53fe820a.jpg', '2026-09-05 07:30:00', '2026-09-05 13:01:11');

DROP TABLE IF EXISTS `patients`;
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
  `hospital_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_patient_hospital` (`hospital_id`),
  CONSTRAINT `fk_patient_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patients` (`id`, `name`, `surname`, `father_name`, `phone`, `demographics`, `created_at`, `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`) VALUES ('CP-2026-002', 'dev', 'patel', 'Rameshbhai', '+1 555-WALK', 'Walk-In', '2026-09-04 15:34:28', NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `patients` (`id`, `name`, `surname`, `father_name`, `phone`, `demographics`, `created_at`, `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`) VALUES ('CP-2026-003', 'dev', 'patel', 'Rameshbhai', '+1 555-WALK', 'Walk-In', '2026-09-04 15:34:28', NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `patients` (`id`, `name`, `surname`, `father_name`, `phone`, `demographics`, `created_at`, `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`) VALUES ('CP-2026-004', 'poojan', 'patel', 'Rajeshbhai', '+1 555-WALK', 'Walk-In', '2026-09-03 22:36:55', NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `patients` (`id`, `name`, `surname`, `father_name`, `phone`, `demographics`, `created_at`, `gender`, `blood_group`, `age`, `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`) VALUES ('CP-2026-005', 'Raj', 'Patel', 'Rameshbhai', '7892345678', '34 Y, Male, AB+', '2026-09-04 13:47:21', 'Male', 'AB+', '34', 'time pass', '9988776655', '1');

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `medicine_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prescriptions` (`id`, `appointment_id`, `medicine_name`, `dosage`, `frequency`, `duration`, `instructions`) VALUES ('4', '4', 'Test Medications', '1-0-0-1', '5 days', '', 'testing note');

DROP TABLE IF EXISTS `system_state`;
CREATE TABLE `system_state` (
  `id` int(11) NOT NULL,
  `line_running` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_state` (`id`, `line_running`) VALUES ('1', '1');

DROP TABLE IF EXISTS `timeline_events`;
CREATE TABLE `timeline_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `patient_id` varchar(50) DEFAULT NULL,
  `event_time` varchar(50) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `timeline_events_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  CONSTRAINT `timeline_events_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('8', '3', 'CP-2026-004', '07:06 PM', 'Walk-in registered by staff. Marked Available at Hospital (CP-2026-004).', '2026-09-03 22:36:55');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('9', '3', 'CP-2026-004', '07:23 PM', 'Reverted to checked-in.', '2026-09-03 22:53:25');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('10', '3', 'CP-2026-004', '07:23 PM', 'Patient arrived at hospital desk.', '2026-09-03 22:53:29');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('12', '3', 'CP-2026-004', '09:28 AM', 'Patient transferred to Waiting Lounge.', '2026-09-04 12:58:54');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('13', '3', 'CP-2026-004', '09:28 AM', 'Called into consulting room.', '2026-09-04 12:58:56');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('14', '3', 'CP-2026-004', '09:29 AM', 'Consultation finalized. Discharged with medicine.', '2026-09-04 12:59:55');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('15', '4', 'CP-2026-005', '10:19 AM', 'Patient arrived and was added to the queue.', '2026-09-04 13:49:30');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('16', '4', 'CP-2026-005', '10:19 AM', 'Patient arrived at hospital desk.', '2026-09-04 13:49:56');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('18', '4', 'CP-2026-005', '12:03 PM', 'Patient transferred to Waiting Lounge.', '2026-09-04 15:33:04');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('19', '4', 'CP-2026-005', '12:03 PM', 'Called into consulting room.', '2026-09-04 15:33:25');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('20', '4', 'CP-2026-005', '12:05 PM', 'Reverted to waiting room.', '2026-09-04 15:35:09');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('24', '4', 'CP-2026-005', '12:13 PM', 'Reverted to available.', '2026-09-04 15:43:59');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('27', '4', 'CP-2026-005', '12:16 PM', 'Patient transferred to Waiting Lounge.', '2026-09-04 15:46:05');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('35', '4', 'CP-2026-005', '09:29 AM', 'Called into consulting room.', '2026-09-05 12:59:09');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('36', '4', 'CP-2026-005', '09:31 AM', 'Patient admitted to OPD Bed [OPD-102].', '2026-09-05 13:01:11');
INSERT INTO `timeline_events` (`id`, `appointment_id`, `patient_id`, `event_time`, `event_description`, `created_at`) VALUES ('37', '4', 'CP-2026-005', '09:33 AM', 'Discharged from inpatient bed OPD-102. Ready for home rest.', '2026-09-05 13:03:34');

SET FOREIGN_KEY_CHECKS = 1;
