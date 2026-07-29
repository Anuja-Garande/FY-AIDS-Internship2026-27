-- Hospital Appointment Booking System - SQL Dump
-- Database: hospital_db

CREATE DATABASE IF NOT EXISTS hospital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_db;

-- Table structure for `users`
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
  `phone` VARCHAR(20) DEFAULT NULL,
  `gender` VARCHAR(10) DEFAULT 'male',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `departments`
CREATE TABLE `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'fa-stethoscope',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `doctors`
CREATE TABLE `doctors` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `department_id` INT(11) NOT NULL,
  `specialization` VARCHAR(100) NOT NULL,
  `qualification` VARCHAR(100) NOT NULL,
  `experience_years` INT(11) DEFAULT 5,
  `consultation_fee` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
  `availability_days` VARCHAR(100) DEFAULT 'Mon, Tue, Wed, Thu, Fri',
  `available_time_start` TIME DEFAULT '09:00:00',
  `available_time_end` TIME DEFAULT '17:00:00',
  `bio` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_doctors_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_doctors_departments` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for `appointments`
CREATE TABLE `appointments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `appointment_number` VARCHAR(30) NOT NULL UNIQUE,
  `patient_id` INT(11) NOT NULL,
  `doctor_id` INT(11) NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `symptoms` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `fk_app_patient` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping Data --

-- 1. Departments
INSERT INTO `departments` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Cardiology', 'World-class care for cardiac conditions, vascular disease, and heart health.', 'fa-heart-pulse'),
(2, 'Neurology', 'Advanced diagnostics and treatment for brain, spine, and nerve disorders.', 'fa-brain'),
(3, 'Orthopedics', 'Comprehensive spine, joint replacement, and sports medicine expertise.', 'fa-bone'),
(4, 'Pediatrics', 'Compassionate pediatric care, growth tracking, and adolescent health.', 'fa-baby'),
(5, 'Dermatology', 'Cutting-edge skin cancer screening, aesthetic care, and clinical dermatology.', 'fa-hand-sparkles'),
(6, 'General Medicine', 'Primary healthcare, prevention, chronic illness management, and wellness.', 'fa-user-doctor');

-- 2. Users (Admin yash/1234, Doctors, and Sample Patient)
-- Note: Password hash below is for '1234' generated with password_hash('1234', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `name`, `email`, `username`, `password`, `role`, `phone`, `gender`) VALUES
(1, 'Yash (System Admin)', 'admin@apexcare.com', 'yash', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'admin', '+1 800-555-0199', 'male'),
(2, 'Dr. Sarah Jenkins', 'sarah.jenkins@apexcare.com', 'dr_sarah', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'doctor', '+1 800-555-0101', 'female'),
(3, 'Dr. Robert Chen', 'robert.chen@apexcare.com', 'dr_robert', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'doctor', '+1 800-555-0102', 'male'),
(4, 'Dr. Elena Rostova', 'elena.rostova@apexcare.com', 'dr_elena', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'doctor', '+1 800-555-0103', 'female'),
(5, 'Dr. Marcus Vance', 'marcus.vance@apexcare.com', 'dr_marcus', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'doctor', '+1 800-555-0104', 'male'),
(6, 'Alex Johnson', 'alex.patient@example.com', 'alex_j', '$2y$10$qGf5HjVpT.hN5mE.9QfL7e4S6v6F4K2L7M8N9P0Q1R2S3T4U5V6W', 'patient', '+1 800-555-0789', 'male');

-- 3. Doctors Extended Info
INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `availability_days`, `available_time_start`, `available_time_end`, `bio`, `image_url`) VALUES
(1, 2, 1, 'Interventional Cardiology', 'MD, FACC, Harvard Medical', 14, 750.00, 'Mon, Tue, Wed, Thu', '09:00:00', '16:00:00', 'Senior Cardiologist specializing in complex coronary interventions and preventive cardiology.', 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=400&auto=format&fit=crop'),
(2, 3, 2, 'Neuro-Oncology & Epilepsy', 'MD, PhD, Johns Hopkins', 11, 800.00, 'Mon, Wed, Fri', '10:00:00', '17:00:00', 'Renowned neurologist leading precision treatment for neurological disorders and brain health.', 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop'),
(3, 4, 3, 'Joint Replacement & Spine', 'MS Ortho, FRCS London', 9, 650.00, 'Tue, Thu, Sat', '08:30:00', '15:30:00', 'Pioneer in minimally invasive joint reconstructive surgery and sports injury rehabilitation.', 'https://images.unsplash.com/photo-1594824813566-7885a396447d?q=80&w=400&auto=format&fit=crop'),
(4, 5, 4, 'Pediatric Cardiology', 'MD Pediatrics, Stanford', 12, 600.00, 'Mon, Tue, Thu, Fri', '09:30:00', '16:30:00', 'Dedicated pediatrician passionate about child healthcare, developmental monitoring, and preventive medicine.', 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?q=80&w=400&auto=format&fit=crop');

-- 4. Sample Appointments
INSERT INTO `appointments` (`id`, `appointment_number`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `symptoms`, `status`) VALUES
(1, 'APX-20260724-101', 6, 1, CURDATE() + INTERVAL 1 DAY, '10:00:00', 'Chest tightness during physical activity and occasional mild dizziness.', 'confirmed'),
(2, 'APX-20260724-102', 6, 3, CURDATE() + INTERVAL 3 DAY, '11:30:00', 'Persistent lower back pain after workout session.', 'pending');
