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

