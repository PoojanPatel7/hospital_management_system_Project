-- --------------------------------------------------------
-- Update Script for Hospital Accounts & Multi-Specialty Doctors
-- --------------------------------------------------------

-- 1. Create hospitals table
CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create doctor_categories table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `doctor_categories` (
  `doctor_id` VARCHAR(50) NOT NULL,
  `department_id` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`doctor_id`, `department_id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Add new columns to existing tables
ALTER TABLE `doctors` ADD COLUMN `hospital_id` INT DEFAULT NULL;
ALTER TABLE `doctors` ADD COLUMN `phone` VARCHAR(50) DEFAULT NULL;
ALTER TABLE `patients` ADD COLUMN `hospital_id` INT DEFAULT NULL;
ALTER TABLE `appointments` ADD COLUMN `hospital_id` INT DEFAULT NULL;
ALTER TABLE `beds` ADD COLUMN `hospital_id` INT DEFAULT NULL;
ALTER TABLE `departments` ADD COLUMN `hospital_id` INT DEFAULT NULL;

-- Note: We add foreign keys for hospital_id to ensure data integrity
ALTER TABLE `doctors` ADD CONSTRAINT `fk_doctor_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE;
ALTER TABLE `patients` ADD CONSTRAINT `fk_patient_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE;
ALTER TABLE `appointments` ADD CONSTRAINT `fk_appt_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE;
ALTER TABLE `beds` ADD CONSTRAINT `fk_bed_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE;

-- 4. Migrate existing doctors to doctor_categories (optional fallback if department_id exists)
INSERT IGNORE INTO `doctor_categories` (`doctor_id`, `department_id`)
SELECT `id`, `department_id` FROM `doctors` WHERE `department_id` IS NOT NULL;
