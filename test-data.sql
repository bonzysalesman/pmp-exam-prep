-- Test Data for PMP WordPress Theme
-- Run this after WordPress installation and theme activation

-- Insert Work Groups
INSERT INTO wp_posts (post_title, post_content, post_status, post_type, post_date, post_date_gmt, post_modified, post_modified_gmt) VALUES
('WG1: Building A Team', 'Learn the fundamentals of team building and leadership in project management.', 'publish', 'work_group', NOW(), NOW(), NOW(), NOW()),
('WG2: Starting the Project', 'Master project initiation and planning processes.', 'publish', 'work_group', NOW(), NOW(), NOW(), NOW()),
('WG3: Doing the Work', 'Execute project work and manage deliverables effectively.', 'publish', 'work_group', NOW(), NOW(), NOW(), NOW());

-- Get the IDs for meta data
SET @wg1_id = (SELECT ID FROM wp_posts WHERE post_title = 'WG1: Building A Team' AND post_type = 'work_group');
SET @wg2_id = (SELECT ID FROM wp_posts WHERE post_title = 'WG2: Starting the Project' AND post_type = 'work_group');
SET @wg3_id = (SELECT ID FROM wp_posts WHERE post_title = 'WG3: Doing the Work' AND post_type = 'work_group');

-- Add Work Group metadata
INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
(@wg1_id, 'wg_number', '1'),
(@wg1_id, 'wg_domain', 'people'),
(@wg1_id, 'wg_duration', '3 weeks'),
(@wg2_id, 'wg_number', '2'),
(@wg2_id, 'wg_domain', 'process'),
(@wg2_id, 'wg_duration', '2 weeks'),
(@wg3_id, 'wg_number', '3'),
(@wg3_id, 'wg_domain', 'process'),
(@wg3_id, 'wg_duration', '3 weeks');

-- Insert Sample Lessons
INSERT INTO wp_posts (post_title, post_content, post_status, post_type, post_date, post_date_gmt, post_modified, post_modified_gmt) VALUES
('Day 1: PMP Overview and Mindset', 'Introduction to PMP certification and the right mindset for success.', 'publish', 'lesson', NOW(), NOW(), NOW(), NOW()),
('Day 2: Understanding Teams', 'Learn about team dynamics and formation in project environments.', 'publish', 'lesson', NOW(), NOW(), NOW(), NOW()),
('Day 3: Leadership Fundamentals', 'Core leadership principles for project managers.', 'publish', 'lesson', NOW(), NOW(), NOW(), NOW()),
('Day 4: Project Initiation', 'How to properly start a project with stakeholder alignment.', 'publish', 'lesson', NOW(), NOW(), NOW(), NOW()),
('Day 5: Planning Essentials', 'Essential planning techniques and tools for project success.', 'publish', 'lesson', NOW(), NOW(), NOW(), NOW());

-- Get lesson IDs
SET @lesson1_id = (SELECT ID FROM wp_posts WHERE post_title = 'Day 1: PMP Overview and Mindset' AND post_type = 'lesson');
SET @lesson2_id = (SELECT ID FROM wp_posts WHERE post_title = 'Day 2: Understanding Teams' AND post_type = 'lesson');
SET @lesson3_id = (SELECT ID FROM wp_posts WHERE post_title = 'Day 3: Leadership Fundamentals' AND post_type = 'lesson');
SET @lesson4_id = (SELECT ID FROM wp_posts WHERE post_title = 'Day 4: Project Initiation' AND post_type = 'lesson');
SET @lesson5_id = (SELECT ID FROM wp_posts WHERE post_title = 'Day 5: Planning Essentials' AND post_type = 'lesson');

-- Add Lesson metadata
INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES
(@lesson1_id, 'lesson_duration', '20'),
(@lesson1_id, 'domain_type', 'people'),
(@lesson1_id, 'work_group_id', @wg1_id),
(@lesson2_id, 'lesson_duration', '25'),
(@lesson2_id, 'domain_type', 'people'),
(@lesson2_id, 'work_group_id', @wg1_id),
(@lesson3_id, 'lesson_duration', '22'),
(@lesson3_id, 'domain_type', 'people'),
(@lesson3_id, 'work_group_id', @wg1_id),
(@lesson4_id, 'lesson_duration', '18'),
(@lesson4_id, 'domain_type', 'process'),
(@lesson4_id, 'work_group_id', @wg2_id),
(@lesson5_id, 'lesson_duration', '30'),
(@lesson5_id, 'domain_type', 'process'),
(@lesson5_id, 'work_group_id', @wg2_id);

-- Create required pages
INSERT INTO wp_posts (post_title, post_content, post_status, post_type, post_name, post_date, post_date_gmt, post_modified, post_modified_gmt) VALUES
('Dashboard', 'Welcome to your PMP learning dashboard!', 'publish', 'page', 'dashboard', NOW(), NOW(), NOW(), NOW()),
('Resources', 'Access study materials and resources here.', 'publish', 'page', 'resources', NOW(), NOW(), NOW(), NOW());

-- Create test user (password: testpass123)
INSERT INTO wp_users (user_login, user_pass, user_nicename, user_email, user_registered, display_name) VALUES
('testuser', '$P$B7TLakAEeE6DNhHKRTcmGsKUra/8R5/', 'testuser', 'test@example.com', NOW(), 'Test User');

SET @user_id = LAST_INSERT_ID();

-- Add user meta
INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES
(@user_id, 'wp_capabilities', 'a:1:{s:11:"pmp_student";b:1;}'),
(@user_id, 'wp_user_level', '0');

-- Add some test progress data
INSERT INTO wp_user_lesson_progress (user_id, lesson_id, domain_type, status, progress_percentage, time_spent, started_at, completed_at) VALUES
(@user_id, @lesson1_id, 'people', 'completed', 100, 1200, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@user_id, @lesson2_id, 'people', 'completed', 100, 1500, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(@user_id, @lesson3_id, 'people', 'in_progress', 65, 800, NOW(), NULL);

-- Add study sessions
INSERT INTO wp_study_sessions (user_id, session_date, duration, lessons_completed, domain_focus) VALUES
(@user_id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1200, 1, 'people'),
(@user_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1500, 1, 'people'),
(@user_id, CURDATE(), 800, 0, 'people');
