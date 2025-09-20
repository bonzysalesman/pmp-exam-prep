# Data Model: Enhanced Progress Tracking System

**Date**: September 20, 2025  
**Feature**: 001-progress-tracking-system

## Core Entities

### 1. UserProgress
**Purpose**: Track user progress across PMP domains (People 42%, Process 50%, Business Environment 8%)

**Fields**:
- `id` (bigint, PK, auto-increment)
- `user_id` (bigint, FK to wp_users.ID)
- `domain` (varchar(50), enum: 'people', 'process', 'business_environment')
- `completion_percentage` (decimal(5,2), 0.00-100.00)
- `lessons_completed` (int, count of completed lessons)
- `total_lessons` (int, total lessons in domain)
- `time_spent_minutes` (int, total study time in minutes)
- `last_updated` (datetime, auto-update timestamp)

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `unique_user_domain` (`user_id`, `domain`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_domain` (`domain`)

**Validation Rules**:
- `completion_percentage` must be between 0.00 and 100.00
- `domain` must be one of: 'people', 'process', 'business_environment'
- `user_id` must exist in wp_users table
- `lessons_completed` cannot exceed `total_lessons`

### 2. StudySession
**Purpose**: Track individual study sessions for streak calculation and analytics

**Fields**:
- `id` (bigint, PK, auto-increment)
- `user_id` (bigint, FK to wp_users.ID)
- `session_date` (date, study session date)
- `duration_minutes` (int, session length in minutes)
- `lessons_completed` (tinyint, lessons completed in session)
- `domain_focus` (varchar(50), primary domain studied)
- `created_at` (datetime, session creation timestamp)

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `unique_user_date` (`user_id`, `session_date`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_session_date` (`session_date`)

**Validation Rules**:
- `session_date` cannot be in the future
- `duration_minutes` must be positive integer
- `lessons_completed` must be between 0 and 255
- One session per user per day (enforced by unique constraint)

### 3. LessonProgress
**Purpose**: Track individual lesson completion with detailed metrics

**Fields**:
- `id` (bigint, PK, auto-increment)
- `user_id` (bigint, FK to wp_users.ID)
- `lesson_id` (bigint, FK to wp_posts.ID where post_type='lesson')
- `status` (varchar(20), enum: 'not_started', 'in_progress', 'completed')
- `progress_percentage` (decimal(5,2), 0.00-100.00)
- `time_spent_minutes` (int, time spent on lesson)
- `completed_at` (datetime, completion timestamp, nullable)
- `last_accessed` (datetime, last access timestamp)
- `attempts` (tinyint, number of attempts/views)

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `unique_user_lesson` (`user_id`, `lesson_id`)
- KEY `idx_user_id` (`user_id`)
- KEY `idx_lesson_id` (`lesson_id`)
- KEY `idx_status` (`status`)

**Validation Rules**:
- `status` must be one of: 'not_started', 'in_progress', 'completed'
- `progress_percentage` must be between 0.00 and 100.00
- `completed_at` required when status is 'completed'
- `lesson_id` must exist in wp_posts with post_type='lesson'

### 4. StudyStreak
**Purpose**: Track study streaks for motivation and gamification

**Fields**:
- `id` (bigint, PK, auto-increment)
- `user_id` (bigint, FK to wp_users.ID)
- `current_streak` (int, current consecutive study days)
- `longest_streak` (int, longest streak achieved)
- `last_study_date` (date, last day user studied)
- `streak_start_date` (date, when current streak started)
- `total_study_days` (int, total days studied)
- `updated_at` (datetime, last update timestamp)

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `unique_user` (`user_id`)
- KEY `idx_current_streak` (`current_streak`)
- KEY `idx_last_study_date` (`last_study_date`)

**Validation Rules**:
- `current_streak` must be non-negative integer
- `longest_streak` must be >= `current_streak`
- `last_study_date` cannot be in the future
- `streak_start_date` must be <= `last_study_date`

## Entity Relationships

### UserProgress ↔ StudySession
- **Relationship**: One-to-Many (User has many sessions)
- **Business Rule**: StudySession updates trigger UserProgress recalculation
- **Cascade**: Delete user sessions when user is deleted

### LessonProgress ↔ UserProgress  
- **Relationship**: Many-to-One (Many lessons contribute to domain progress)
- **Business Rule**: Lesson completion updates domain progress percentage
- **Calculation**: `completion_percentage = (lessons_completed / total_lessons) * 100`

### StudySession ↔ StudyStreak
- **Relationship**: One-to-One per day (Session creates/updates streak)
- **Business Rule**: Daily session updates streak counter
- **Logic**: 
  - Consecutive days increment streak
  - Gap > 1 day resets streak to 1
  - Update longest_streak if current exceeds it

## State Transitions

### LessonProgress Status Flow
```
not_started → in_progress → completed
     ↑            ↓
     └────────────┘ (can restart)
```

### StudyStreak Calculation Logic
```
IF last_study_date = yesterday:
    current_streak += 1
ELIF last_study_date = today:
    // No change (already studied today)
ELSE:
    current_streak = 1
    streak_start_date = today

IF current_streak > longest_streak:
    longest_streak = current_streak
```

## Domain Mapping

### PMP Domain Distribution
- **People Domain**: 42% of total content
  - Team building, leadership, conflict resolution
  - Lessons tagged with 'people' domain
  
- **Process Domain**: 50% of total content  
  - Project lifecycle, methodologies, tools
  - Lessons tagged with 'process' domain
  
- **Business Environment**: 8% of total content
  - Organizational strategy, compliance, benefits
  - Lessons tagged with 'business_environment' domain

### Progress Calculation Formula
```php
// Domain progress calculation
$domain_progress = ($completed_lessons / $total_domain_lessons) * 100;

// Overall progress calculation  
$overall_progress = (
    ($people_progress * 0.42) + 
    ($process_progress * 0.50) + 
    ($business_progress * 0.08)
);
```

## Performance Considerations

### Query Optimization
- Use composite indexes for user-specific queries
- Aggregate calculations cached in UserProgress table
- Batch updates for multiple lesson completions

### Data Archival
- Archive completed sessions older than 2 years
- Maintain streak data indefinitely for motivation
- Soft delete lesson progress (status flag vs hard delete)
