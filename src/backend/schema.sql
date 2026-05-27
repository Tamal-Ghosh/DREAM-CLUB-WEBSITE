-- SQL schema for users table (based on registration form fields)
CREATE TABLE IF NOT EXISTS `users` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL,
	`email` VARCHAR(100) UNIQUE NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	`role` ENUM('donor','patient','volunteer','admin') DEFAULT 'patient',
	`blood_group` VARCHAR(5) DEFAULT NULL,
	`phone` VARCHAR(20) DEFAULT NULL,
	`availability_status` ENUM('Available','Unavailable') DEFAULT 'Available',
	`status` ENUM('Active','Blocked') DEFAULT 'Active',
	`remember_token` VARCHAR(255) DEFAULT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requests table: stores blood requests created by patients
CREATE TABLE IF NOT EXISTS `requests` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`user_id` INT NOT NULL COMMENT 'patient user id who created the request',
	`patient_name` VARCHAR(150) NOT NULL,
	`contact_number` VARCHAR(30) NOT NULL,
	`blood_group` VARCHAR(5) NOT NULL,
	`units_required` INT DEFAULT 1,
	`hospital` VARCHAR(255) DEFAULT NULL,
	`location` VARCHAR(255) DEFAULT NULL,
	`urgency` ENUM('Normal','Urgent','Critical') DEFAULT 'Normal',
	`details` TEXT DEFAULT NULL,
	`status` ENUM('Pending','Donor Review','Donor Assigned','Completed','Failed','Rejected') DEFAULT 'Pending',
	`donor_id` INT DEFAULT NULL COMMENT 'user id of assigned donor (if known)',
	`donor_name` VARCHAR(150) DEFAULT NULL,
	`donor_phone` VARCHAR(30) DEFAULT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	INDEX (`user_id`),
	INDEX (`donor_id`),
	INDEX (`status`),
	CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_requests_donor` FOREIGN KEY (`donor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

