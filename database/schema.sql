-- Phase 1: Database Schema for Digital Skills Portal

-- 1. Roles Table
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles
INSERT INTO `roles` (`name`, `description`) VALUES
('Super Administrator', 'Full system control'),
('Head of Admin/Accounts', 'Payment management and financial reporting'),
('Programme Coordinator', 'Programme and Facilitator management'),
('Facilitator', 'Student assessment, grading, attendance'),
('Student', 'TSU internal students'),
('External Candidate', 'Non-university students'),
('University Management', 'View-only access to statistical reports');

-- 2. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `type` ENUM('tsu_student', 'external', 'staff') NOT NULL,
    `reg_number` VARCHAR(50) UNIQUE DEFAULT NULL, -- Only for TSU students
    `passport_path` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_users_email ON `users`(`email`);
CREATE INDEX idx_users_reg_number ON `users`(`reg_number`);

-- 3. Departments Table (For Academic Mapping)
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL UNIQUE,
    `faculty` VARCHAR(150) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Programmes Table
CREATE TABLE `programmes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `duration_weeks` INT NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Courses Table
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `programme_id` INT NOT NULL,
    `course_code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`programme_id`) REFERENCES `programmes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Mapping Rules Table (Intelligent Course Mapping)
CREATE TABLE `mapping_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `priority_level` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    UNIQUE (`department_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Enrollments Table
CREATE TABLE `enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `programme_id` INT NOT NULL,
    `status` ENUM('pending', 'active', 'completed', 'dropped') DEFAULT 'pending',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`programme_id`) REFERENCES `programmes`(`id`) ON DELETE CASCADE,
    UNIQUE (`user_id`, `programme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Payments Table
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `reference` VARCHAR(100) NOT NULL UNIQUE, -- Paystack reference or manual receipt
    `method` ENUM('paystack', 'offline_teller') NOT NULL,
    `teller_path` VARCHAR(255) DEFAULT NULL, -- If offline payment
    `status` ENUM('pending', 'approved', 'failed') DEFAULT 'pending',
    `approved_by` INT DEFAULT NULL, -- Admin who approved (if offline)
    `paid_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_payments_reference ON `payments`(`reference`);

-- 9. Assessments Table
CREATE TABLE `assessments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `facilitator_id` INT NOT NULL,
    `score` DECIMAL(5, 2) DEFAULT NULL,
    `remarks` TEXT,
    `graded_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`facilitator_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    UNIQUE (`enrollment_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Facilitator Courses Mapping Table
CREATE TABLE `facilitator_courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `facilitator_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`facilitator_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    UNIQUE (`facilitator_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
