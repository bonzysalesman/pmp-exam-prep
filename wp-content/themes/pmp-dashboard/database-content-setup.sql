-- Enhanced Content Management System Database Schema
-- Feature: 002-content-management-system
-- Task: T001 - Database Schema Setup

-- Content relationships and sequencing
CREATE TABLE IF NOT EXISTS pmp_content_sequences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    content_id BIGINT UNSIGNED NOT NULL,
    prerequisite_id BIGINT UNSIGNED NULL,
    sequence_order INT NOT NULL DEFAULT 0,
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_content_id (content_id),
    KEY idx_prerequisite_id (prerequisite_id),
    KEY idx_sequence_order (sequence_order),
    UNIQUE KEY unique_content_sequence (content_id, prerequisite_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Practice test questions and answers
CREATE TABLE IF NOT EXISTS pmp_test_questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    test_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'drag_drop', 'scenario') DEFAULT 'multiple_choice',
    correct_answer TEXT NOT NULL,
    explanation TEXT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    domain VARCHAR(50) NULL,
    knowledge_area VARCHAR(100) NULL,
    points INT NOT NULL DEFAULT 1,
    time_limit INT NULL COMMENT 'Time limit in seconds',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_test_id (test_id),
    KEY idx_difficulty_level (difficulty_level),
    KEY idx_domain (domain),
    KEY idx_knowledge_area (knowledge_area),
    KEY idx_question_type (question_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question answer options
CREATE TABLE IF NOT EXISTS pmp_question_options (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    option_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_question_id (question_id),
    KEY idx_option_order (option_order),
    KEY idx_is_correct (is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test attempts and results
CREATE TABLE IF NOT EXISTS pmp_test_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    test_id BIGINT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NULL,
    total_questions INT NOT NULL DEFAULT 0,
    correct_answers INT NOT NULL DEFAULT 0,
    time_spent INT NULL COMMENT 'Time spent in minutes',
    completed_at TIMESTAMP NULL,
    attempt_data JSON NULL COMMENT 'Detailed answers and analytics',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_test_id (test_id),
    KEY idx_completed_at (completed_at),
    KEY idx_score (score),
    KEY idx_user_test (user_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Content bookmarks and favorites
CREATE TABLE IF NOT EXISTS pmp_content_bookmarks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    bookmark_type ENUM('favorite', 'later', 'completed') DEFAULT 'favorite',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_content_id (content_id),
    KEY idx_content_type (content_type),
    KEY idx_bookmark_type (bookmark_type),
    UNIQUE KEY unique_bookmark (user_id, content_id, bookmark_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learning paths and curricula
CREATE TABLE IF NOT EXISTS pmp_learning_paths (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    estimated_hours INT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_is_default (is_default),
    KEY idx_is_active (is_active),
    KEY idx_difficulty_level (difficulty_level),
    KEY idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learning path content mapping
CREATE TABLE IF NOT EXISTS pmp_learning_path_content (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    path_id BIGINT UNSIGNED NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    sequence_order INT NOT NULL DEFAULT 0,
    is_required BOOLEAN NOT NULL DEFAULT TRUE,
    estimated_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_path_id (path_id),
    KEY idx_content_id (content_id),
    KEY idx_content_type (content_type),
    KEY idx_sequence_order (sequence_order),
    UNIQUE KEY unique_path_content (path_id, content_id, content_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints (optional, for data integrity)
-- Note: WordPress typically doesn't use foreign keys, but they're included for reference

-- ALTER TABLE pmp_content_sequences 
-- ADD CONSTRAINT fk_content_sequences_content 
-- FOREIGN KEY (content_id) REFERENCES wp_posts(ID) ON DELETE CASCADE;

-- ALTER TABLE pmp_test_questions 
-- ADD CONSTRAINT fk_test_questions_test 
-- FOREIGN KEY (test_id) REFERENCES wp_posts(ID) ON DELETE CASCADE;

-- ALTER TABLE pmp_question_options 
-- ADD CONSTRAINT fk_question_options_question 
-- FOREIGN KEY (question_id) REFERENCES pmp_test_questions(id) ON DELETE CASCADE;

-- ALTER TABLE pmp_test_attempts 
-- ADD CONSTRAINT fk_test_attempts_user 
-- FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE;

-- ALTER TABLE pmp_content_bookmarks 
-- ADD CONSTRAINT fk_content_bookmarks_user 
-- FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE;

-- ALTER TABLE pmp_learning_paths 
-- ADD CONSTRAINT fk_learning_paths_creator 
-- FOREIGN KEY (created_by) REFERENCES wp_users(ID) ON DELETE SET NULL;

-- ALTER TABLE pmp_learning_path_content 
-- ADD CONSTRAINT fk_learning_path_content_path 
-- FOREIGN KEY (path_id) REFERENCES pmp_learning_paths(id) ON DELETE CASCADE;

-- Insert default learning path
INSERT IGNORE INTO pmp_learning_paths (name, description, is_default, estimated_hours, difficulty_level) 
VALUES (
    'PMP Certification Preparation', 
    'Complete 13-week PMP exam preparation curriculum following PMI standards',
    TRUE,
    120,
    'intermediate'
);

-- Performance optimization indexes
CREATE INDEX idx_content_sequences_lookup ON pmp_content_sequences (content_id, sequence_order);
CREATE INDEX idx_test_questions_domain_difficulty ON pmp_test_questions (domain, difficulty_level);
CREATE INDEX idx_test_attempts_user_date ON pmp_test_attempts (user_id, created_at);
CREATE INDEX idx_bookmarks_user_type ON pmp_content_bookmarks (user_id, content_type, bookmark_type);
CREATE INDEX idx_learning_path_content_order ON pmp_learning_path_content (path_id, sequence_order);
