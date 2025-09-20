# PMP Dashboard Theme Setup Instructions

## 1. Database Tables Setup

Run the following SQL queries in your WordPress database (phpMyAdmin or similar):

```sql
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
```

## 2. User Roles Setup

Go to WordPress Admin → Users → Add New User and assign the "PMP Student" role.

Or run this PHP code in WordPress admin (Tools → Site Health → Info → Copy site info to clipboard):

```php
// Add PMP Student role to existing user
$user = get_user_by('email', 'your-email@example.com');
if ($user) {
    $user->set_role('pmp_student');
}
```

## 3. Create Required Pages

Create these pages in WordPress Admin → Pages → Add New:

1. **Dashboard** (slug: dashboard) - Use template: Dashboard
2. **Resources** (slug: resources) - Use template: Resources

## 4. Test AJAX Functionality

1. Login as PMP Student
2. Go to Dashboard page
3. Check browser console for any JavaScript errors
4. Test sidebar navigation and mobile menu

## 5. Add Sample Content

Create some Work Groups and Lessons:

1. Go to Work Groups → Add New
2. Create WG1: Building A Team, WG2: Starting the Project, etc.
3. Go to Lessons → Add New
4. Add lessons and link them to Work Groups using custom fields

## 6. Production Tailwind CSS

For production, replace the CDN with a proper Tailwind build:

1. Install Tailwind CSS: `npm install tailwindcss`
2. Create tailwind.config.js
3. Build CSS file: `npx tailwindcss -i ./src/input.css -o ./assets/css/tailwind.css --watch`
4. Enqueue the built CSS file instead of CDN

## Troubleshooting

- **Database tables not created**: Run SQL manually in phpMyAdmin
- **User roles missing**: Check if theme activation hook ran properly
- **AJAX not working**: Check nonce and user permissions
- **Tailwind styles missing**: Temporarily re-enable CDN for testing
