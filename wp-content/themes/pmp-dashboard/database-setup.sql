-- PMP Dashboard Theme Database Tables
-- Run these queries in your WordPress database

-- User lesson progress table
CREATE TABLE IF NOT EXISTS `wp_user_lesson_progress` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `lesson_id` bigint(20) UNSIGNED NOT NULL,
    `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
    `progress_percentage` tinyint(3) UNSIGNED DEFAULT 0,
    `time_spent` int(11) UNSIGNED DEFAULT 0,
    `started_at` datetime NULL,
    `completed_at` datetime NULL,
    `last_accessed` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_lesson` (`user_id`, `lesson_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_lesson_id` (`lesson_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User test results table
CREATE TABLE IF NOT EXISTS `wp_user_test_results` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `test_id` bigint(20) UNSIGNED NOT NULL,
    `score` decimal(5,2) NOT NULL,
    `percentage` decimal(5,2) NOT NULL,
    `time_taken` int(11) UNSIGNED NOT NULL,
    `attempt_number` tinyint(3) UNSIGNED DEFAULT 1,
    `taken_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_test` (`user_id`, `test_id`),
    KEY `idx_percentage` (`percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Study sessions table
CREATE TABLE IF NOT EXISTS `wp_study_sessions` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `session_date` date NOT NULL,
    `duration` int(11) UNSIGNED NOT NULL,
    `lessons_completed` tinyint(3) UNSIGNED DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_date` (`user_id`, `session_date`),
    KEY `idx_session_date` (`session_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
