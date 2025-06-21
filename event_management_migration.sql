-- Database Migration for Event Management Enhancements
-- Date: 2025-06-21
-- Description: Add smart auto-registration, visibility controls, and event management features

-- 1. Add new columns to events table
ALTER TABLE `events` 
ADD COLUMN `event_description` TEXT DEFAULT NULL AFTER `event_venue`,
ADD COLUMN `event_type` ENUM('meeting', 'competition', 'training', 'social', 'other') DEFAULT 'other' AFTER `event_description`,
ADD COLUMN `is_mandatory` TINYINT(1) DEFAULT 0 AFTER `event_type`,
ADD COLUMN `auto_register_members` TINYINT(1) DEFAULT 0 AFTER `is_mandatory`,
ADD COLUMN `visibility` ENUM('public', 'club_only', 'private') DEFAULT 'public' AFTER `auto_register_members`,
ADD COLUMN `max_participants` INT DEFAULT NULL AFTER `visibility`,
ADD COLUMN `created_by` VARCHAR(12) NOT NULL AFTER `max_participants`,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `created_by`,
ADD COLUMN `status` ENUM('active', 'cancelled', 'completed') DEFAULT 'active' AFTER `created_at`;

-- 2. Create event_registrations table for managing registrations
CREATE TABLE `event_registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `student_ic` varchar(12) NOT NULL,
  `registration_type` ENUM('manual', 'auto') DEFAULT 'manual',
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` ENUM('registered', 'present', 'absent', 'late') DEFAULT 'registered',
  `attendance_marked_by` varchar(12) DEFAULT NULL,
  `attendance_marked_at` TIMESTAMP NULL DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `unique_event_student` (`event_id`, `student_ic`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_student_ic` (`student_ic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create event_notifications table for managing notifications
CREATE TABLE `event_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `student_ic` varchar(12) NOT NULL,
  `notification_type` ENUM('event_created', 'event_updated', 'event_cancelled', 'reminder') DEFAULT 'event_created',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_student_ic` (`student_ic`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Add foreign key constraints
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

ALTER TABLE `event_notifications`
  ADD CONSTRAINT `event_notifications_ibfk_1` FOREIGN KEY (`student_ic`) REFERENCES `student` (`student_ic`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_notifications_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

-- 5. Add foreign key for created_by in events table
ALTER TABLE `events`
  ADD CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `teacher` (`teacher_ic`) ON DELETE RESTRICT;

-- 6. Create indexes for better performance
CREATE INDEX `idx_events_group_id` ON `events` (`group_id`);
CREATE INDEX `idx_events_created_by` ON `events` (`created_by`);
CREATE INDEX `idx_events_status` ON `events` (`status`);
CREATE INDEX `idx_events_visibility` ON `events` (`visibility`);
CREATE INDEX `idx_events_dates` ON `events` (`event_start_date`, `event_end_date`);
