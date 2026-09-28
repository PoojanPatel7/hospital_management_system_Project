CREATE DATABASE IF NOT EXISTS hospital_db;
USE hospital_db;

-- 1. Create Tables
CREATE TABLE IF NOT EXISTS patients (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    demographics VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS doctors (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id VARCHAR(100),
    experience VARCHAR(100),
    degree VARCHAR(100),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE IF NOT EXISTS doctor_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(50),
    time_slot VARCHAR(20),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

CREATE TABLE IF NOT EXISTS beds (
    id VARCHAR(50) PRIMARY KEY,
    bed_number VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(20) NOT NULL,
    wing VARCHAR(100),
    status VARCHAR(20) DEFAULT 'Available',
    patient_id VARCHAR(50),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(50),
    doctor_id VARCHAR(50),
    type VARCHAR(50),
    date DATE,
    slot VARCHAR(20),
    symptoms TEXT,
    allergies TEXT,
    status VARCHAR(50),
    stage INT,
    bed_number VARCHAR(50),
    doctor_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    FOREIGN KEY (bed_number) REFERENCES beds(bed_number) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS timeline_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    patient_id VARCHAR(50),
    event_time VARCHAR(50),
    event_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
);

CREATE TABLE IF NOT EXISTS diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    description TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
);

CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    medicine_name VARCHAR(255),
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    instructions TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
);

CREATE TABLE IF NOT EXISTS system_state (
    id INT PRIMARY KEY,
    line_running BOOLEAN DEFAULT TRUE
);

-- 2. Insert Seed Data

-- System State
INSERT IGNORE INTO system_state (id, line_running) VALUES (1, 1);

-- Departments
INSERT IGNORE INTO departments (id, name, icon) VALUES 
('General Medicine', 'General Medicine', 'fa-user-doctor'),
('Cardiology', 'Cardiology', 'fa-heart-pulse'),
('Orthopedics', 'Orthopedics', 'fa-bone'),
('Pediatrics', 'Pediatrics', 'fa-baby'),
('Neurology', 'Neurology', 'fa-brain'),
('Pulmonology', 'Pulmonology', 'fa-lungs'),
('Dermatology', 'Dermatology', 'fa-hand-dots'),
('Emergency Trauma', 'Emergency Trauma', 'fa-truck-medical');

-- Doctors
INSERT IGNORE INTO doctors (id, name, department_id, experience, degree) VALUES 
('doc-1', 'Dr. Sarah Jenkins', 'General Medicine', '12 Years Exp', 'MBBS, MD'),
('doc-2', 'Dr. Arthur Vance', 'Cardiology', '16 Years Exp', 'MD, DM Cardiology'),
('doc-3', 'Dr. Elena Rostova', 'Orthopedics', '9 Years Exp', 'MS Ortho'),
('doc-4', 'Dr. Ronald Miller', 'Pediatrics', '14 Years Exp', 'MBBS, DCH'),
('doc-5', 'Dr. Emily Zhao', 'Neurology', '11 Years Exp', 'MD Neurology'),
('doc-6', 'Dr. Marcus Holloway', 'General Medicine', '8 Years Exp', 'MBBS, MD');

-- Doctor Slots
INSERT IGNORE INTO doctor_slots (doctor_id, time_slot) VALUES 
('doc-1', '09:00 AM'), ('doc-1', '09:30 AM'), ('doc-1', '10:00 AM'), ('doc-1', '10:30 AM'), ('doc-1', '11:30 AM'), ('doc-1', '02:00 PM'), ('doc-1', '03:00 PM'),
('doc-2', '09:30 AM'), ('doc-2', '10:00 AM'), ('doc-2', '10:30 AM'), ('doc-2', '11:00 AM'), ('doc-2', '03:30 PM'), ('doc-2', '04:00 PM'),
('doc-3', '10:00 AM'), ('doc-3', '10:30 AM'), ('doc-3', '11:00 AM'), ('doc-3', '01:30 PM'), ('doc-3', '02:30 PM'),
('doc-4', '09:00 AM'), ('doc-4', '10:00 AM'), ('doc-4', '11:00 AM'), ('doc-4', '03:00 PM'), ('doc-4', '04:00 PM'),
('doc-5', '10:00 AM'), ('doc-5', '11:30 AM'), ('doc-5', '02:00 PM'), ('doc-5', '03:30 PM'),
('doc-6', '08:30 AM'), ('doc-6', '09:00 AM'), ('doc-6', '10:00 AM'), ('doc-6', '11:00 AM');

-- Beds
INSERT IGNORE INTO beds (id, bed_number, type, wing) VALUES 
('bed-1', 'OPD-101', 'OPD', 'North Wing, 2nd Floor'),
('bed-2', 'OPD-102', 'OPD', 'North Wing, 2nd Floor'),
('bed-3', 'OPD-103', 'OPD', 'North Wing, 2nd Floor'),
('bed-4', 'OPD-104', 'OPD', 'North Wing, 2nd Floor'),
('bed-5', 'OPD-105', 'OPD', 'North Wing, 2nd Floor'),
('bed-6', 'OPD-106', 'OPD', 'North Wing, 2nd Floor'),
('bed-7', 'ICU-01', 'ICU', 'Critical Care Floor, 3rd Floor'),
('bed-8', 'ICU-02', 'ICU', 'Critical Care Floor, 3rd Floor'),
('bed-9', 'ICU-03', 'ICU', 'Critical Care Floor, 3rd Floor'),
('bed-10', 'ICU-04', 'ICU', 'Critical Care Floor, 3rd Floor');
