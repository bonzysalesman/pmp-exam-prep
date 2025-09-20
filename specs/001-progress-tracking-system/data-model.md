# Data Model: Enhanced Progress Tracking System

**Feature**: 001-progress-tracking-system  
**Date**: September 20, 2025

## Entity Relationship Diagram

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   wp_users      │    │   wp_posts      │    │  wp_postmeta    │
│                 │    │   (lessons)     │    │                 │
│ • ID            │    │ • ID            │    │ • post_id       │
│ • user_login    │    │ • post_title    │    │ • meta_key      │
│ • user_email    │    │ • post_type     │    │ • meta_value    │
│ • display_name  │    │ • post_status   │    │   - domain_type │
└─────────────────┘    └─────────────────┘    │   - duration    │
         │                       │             └─────────────────┘
         │                       │                      │
         │              ┌────────┴────────┐            │
         │              │                 │            │
         ▼              ▼                 ▼            ▼
┌─────────────────────────────────────────────────────────────┐
│              wp_user_lesson_progress                        │
│                                                             │
│ • id (BIGINT PRIMARY KEY)                                   │
│ • user_id (BIGINT, FK to wp_users.ID)                      │
│ • lesson_id (BIGINT, FK to wp_posts.ID)                    │
│ • domain_type (ENUM: 'people', 'process', 'business')      │
│ • status (ENUM: 'not_started', 'in_progress', 'completed') │
│ • progress_percentage (TINYINT 0-100)                      │
│ • time_spent (INT seconds)                                 │
│ • started_at (DATETIME)                                    │
│ • completed_at (DATETIME)                                  │
│ • last_accessed (DATETIME, auto-update)                   │
└─────────────────────────────────────────────────────────────┘
         │
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│                   wp_study_sessions                         │
│                                                             │
│ • id (BIGINT PRIMARY KEY)                                   │
│ • user_id (BIGINT, FK to wp_users.ID)                      │
│ • session_date (DATE)                                      │
│ • duration (INT seconds)                                   │
│ • lessons_completed (TINYINT)                              │
│ • domain_focus (ENUM: 'people', 'process', 'business',     │
│                       'mixed')                             │
│ • engagement_score (DECIMAL 0.0-10.0)                     │
│ • created_at (DATETIME)                                    │
│ • updated_at (DATETIME, auto-update)                       │
└─────────────────────────────────────────────────────────────┘
```

## Table Specifications

### wp_user_lesson_progress (Enhanced)

```sql
CREATE TABLE wp_user_lesson_progress (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NOT NULL,
    domain_type ENUM('people', 'process', 'business') NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress_percentage TINYINT UNSIGNED DEFAULT 0,
    time_spent INT UNSIGNED DEFAULT 0,
    quiz_score DECIMAL(5,2) NULL,
    attempts_count TINYINT UNSIGNED DEFAULT 0,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    last_accessed DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    KEY idx_user_domain_status (user_id, domain_type, status),
    KEY idx_user_completion (user_id, completed_at),
    KEY idx_domain_progress (domain_type, progress_percentage),
    KEY idx_lesson_stats (lesson_id, status),
    
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES wp_posts(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### wp_study_sessions (New)

```sql
CREATE TABLE wp_study_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    session_date DATE NOT NULL,
    duration INT UNSIGNED DEFAULT 0,
    lessons_completed TINYINT UNSIGNED DEFAULT 0,
    domain_focus ENUM('people', 'process', 'business', 'mixed') DEFAULT 'mixed',
    engagement_score DECIMAL(3,1) DEFAULT 0.0,
    device_type ENUM('desktop', 'tablet', 'mobile') DEFAULT 'desktop',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_date (user_id, session_date),
    KEY idx_user_sessions (user_id, session_date DESC),
    KEY idx_session_duration (session_date, duration),
    KEY idx_engagement (engagement_score, session_date),
    
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### wp_progress_milestones (New)

```sql
CREATE TABLE wp_progress_milestones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    milestone_type ENUM('streak', 'completion', 'domain_mastery', 'time_goal') NOT NULL,
    milestone_value INT UNSIGNED NOT NULL,
    domain_type ENUM('people', 'process', 'business', 'overall') DEFAULT 'overall',
    achieved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notified BOOLEAN DEFAULT FALSE,
    
    PRIMARY KEY (id),
    KEY idx_user_milestones (user_id, achieved_at DESC),
    KEY idx_milestone_type (milestone_type, milestone_value),
    KEY idx_notifications (notified, achieved_at),
    
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Data Access Patterns

### Common Queries

#### User Progress Overview
```sql
-- Get user's overall progress with domain breakdown
SELECT 
    domain_type,
    COUNT(*) as total_lessons,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lessons,
    AVG(progress_percentage) as avg_progress,
    SUM(time_spent) as total_time
FROM wp_user_lesson_progress 
WHERE user_id = ? 
GROUP BY domain_type;
```

#### Study Streak Calculation
```sql
-- Get consecutive study days for streak calculation
SELECT session_date, duration
FROM wp_study_sessions 
WHERE user_id = ? AND duration > 0
ORDER BY session_date DESC
LIMIT 365;
```

#### Domain Performance Analysis
```sql
-- Identify weakest domain for recommendations
SELECT 
    domain_type,
    AVG(progress_percentage) as avg_progress,
    AVG(time_spent) as avg_time,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) / COUNT(*) * 100 as completion_rate
FROM wp_user_lesson_progress 
WHERE user_id = ?
GROUP BY domain_type
ORDER BY completion_rate ASC;
```

### Caching Strategy

#### WordPress Transients
```php
// Cache user progress for 15 minutes
$cache_key = "pmp_progress_{$user_id}";
$progress_data = get_transient($cache_key);

if (false === $progress_data) {
    $progress_data = calculate_user_progress($user_id);
    set_transient($cache_key, $progress_data, 15 * MINUTE_IN_SECONDS);
}
```

#### Object Cache Integration
```php
// Use WordPress object cache for frequently accessed data
wp_cache_set("user_streak_{$user_id}", $streak_data, 'pmp_progress', HOUR_IN_SECONDS);
$streak_data = wp_cache_get("user_streak_{$user_id}", 'pmp_progress');
```

## Data Validation Rules

### Input Validation
```php
class PMP_Progress_Validator {
    public static function validate_progress_update($data) {
        $rules = [
            'user_id' => 'required|integer|exists:wp_users,ID',
            'lesson_id' => 'required|integer|exists:wp_posts,ID',
            'progress_percentage' => 'integer|min:0|max:100',
            'time_spent' => 'integer|min:0|max:86400', // Max 24 hours
            'status' => 'in:not_started,in_progress,completed',
            'domain_type' => 'in:people,process,business'
        ];
        
        return self::validate($data, $rules);
    }
}
```

### Business Logic Constraints
- Progress percentage cannot decrease unless lesson is reset
- Completion time must be after start time
- Time spent cannot exceed lesson duration by more than 200%
- Domain type must match lesson's assigned domain

## Migration Strategy

### Phase 1: Schema Updates
```sql
-- Add domain_type column to existing table
ALTER TABLE wp_user_lesson_progress 
ADD COLUMN domain_type ENUM('people', 'process', 'business') AFTER lesson_id;

-- Populate domain_type from lesson metadata
UPDATE wp_user_lesson_progress ulp
JOIN wp_postmeta pm ON ulp.lesson_id = pm.post_id
SET ulp.domain_type = pm.meta_value
WHERE pm.meta_key = 'domain_type';
```

### Phase 2: Index Creation
```sql
-- Add performance indexes
CREATE INDEX idx_user_domain_status ON wp_user_lesson_progress (user_id, domain_type, status);
CREATE INDEX idx_user_completion ON wp_user_lesson_progress (user_id, completed_at);
CREATE INDEX idx_domain_progress ON wp_user_lesson_progress (domain_type, progress_percentage);
```

### Phase 3: New Tables
```sql
-- Create new supporting tables
CREATE TABLE wp_study_sessions (...);
CREATE TABLE wp_progress_milestones (...);
```

## Performance Considerations

### Query Optimization
- Use covering indexes for common query patterns
- Implement query result caching with WordPress transients
- Use EXPLAIN to analyze query performance
- Consider read replicas for analytics queries

### Data Archival
- Archive completed sessions older than 2 years
- Maintain summary statistics for historical analysis
- Implement soft deletes for audit trail
- Regular cleanup of orphaned progress records

### Monitoring
- Track query execution times
- Monitor cache hit rates
- Alert on unusual progress patterns
- Log performance metrics for optimization
