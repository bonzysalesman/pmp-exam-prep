# API Contracts: Progress Tracking System

**Feature**: 001-progress-tracking-system  
**Date**: September 20, 2025

## AJAX Endpoints

### Update Lesson Progress

**Endpoint**: `wp-admin/admin-ajax.php`  
**Action**: `update_lesson_progress`  
**Method**: POST  
**Authentication**: WordPress nonce required

#### Request
```javascript
{
    action: 'update_lesson_progress',
    nonce: 'wp_nonce_value',
    user_id: 123,
    lesson_id: 456,
    progress_percentage: 75,
    time_spent: 1800,  // seconds
    status: 'in_progress'  // 'not_started', 'in_progress', 'completed'
}
```

#### Response - Success
```javascript
{
    success: true,
    data: {
        progress_percentage: 75,
        time_spent: 1800,
        status: 'in_progress',
        domain_progress: {
            people: 65,
            process: 42,
            business: 88
        },
        overall_progress: 58,
        study_streak: 7,
        updated_at: '2025-09-20T10:30:00Z'
    }
}
```

#### Response - Error
```javascript
{
    success: false,
    data: {
        error_code: 'INVALID_LESSON',
        message: 'Lesson not found or access denied',
        details: {
            user_id: 123,
            lesson_id: 456
        }
    }
}
```

### Get User Progress

**Endpoint**: `wp-admin/admin-ajax.php`  
**Action**: `get_user_progress`  
**Method**: GET  
**Authentication**: WordPress nonce required

#### Request
```javascript
{
    action: 'get_user_progress',
    nonce: 'wp_nonce_value',
    user_id: 123,
    include_details: true  // optional, default false
}
```

#### Response
```javascript
{
    success: true,
    data: {
        user_id: 123,
        overall_progress: 58,
        domain_progress: {
            people: {
                percentage: 65,
                completed_lessons: 13,
                total_lessons: 20,
                time_spent: 14400,
                avg_score: 87.5
            },
            process: {
                percentage: 42,
                completed_lessons: 21,
                total_lessons: 50,
                time_spent: 28800,
                avg_score: 82.1
            },
            business: {
                percentage: 88,
                completed_lessons: 7,
                total_lessons: 8,
                time_spent: 7200,
                avg_score: 91.3
            }
        },
        study_streak: {
            current: 7,
            longest: 14,
            last_study_date: '2025-09-20'
        },
        milestones: [
            {
                type: 'streak',
                value: 7,
                achieved_at: '2025-09-20T08:00:00Z',
                title: 'One week strong!'
            }
        ],
        recommendations: [
            {
                type: 'focus_domain',
                domain: 'process',
                reason: 'Lowest completion rate',
                suggested_lessons: [101, 102, 103]
            }
        ]
    }
}
```

### Get Study Analytics

**Endpoint**: `wp-admin/admin-ajax.php`  
**Action**: `get_study_analytics`  
**Method**: GET  
**Authentication**: WordPress nonce required

#### Request
```javascript
{
    action: 'get_study_analytics',
    nonce: 'wp_nonce_value',
    user_id: 123,
    period: 'week',  // 'week', 'month', 'quarter', 'year'
    start_date: '2025-09-01',  // optional
    end_date: '2025-09-20'     // optional
}
```

#### Response
```javascript
{
    success: true,
    data: {
        period: 'week',
        total_study_time: 7200,  // seconds
        sessions_count: 5,
        avg_session_duration: 1440,  // seconds
        lessons_completed: 8,
        domains_studied: ['people', 'process'],
        daily_breakdown: [
            {
                date: '2025-09-14',
                duration: 1800,
                lessons: 2,
                domains: ['people']
            },
            {
                date: '2025-09-15',
                duration: 1200,
                lessons: 1,
                domains: ['process']
            }
        ],
        performance_trends: {
            completion_rate: 85,  // percentage
            avg_quiz_score: 87.5,
            improvement_rate: 12  // percentage increase
        }
    }
}
```

## PHP Class Interfaces

### PMP_Progress_Tracker

```php
interface PMP_Progress_Tracker_Interface {
    /**
     * Update lesson progress for a user
     */
    public function update_lesson_progress(
        int $user_id, 
        int $lesson_id, 
        array $progress_data
    ): array;
    
    /**
     * Get comprehensive user progress
     */
    public function get_user_progress(int $user_id, bool $include_details = false): array;
    
    /**
     * Calculate domain-specific progress
     */
    public function get_domain_progress(int $user_id, string $domain = null): array;
    
    /**
     * Get current study streak
     */
    public function get_study_streak(int $user_id): int;
    
    /**
     * Record study session
     */
    public function record_study_session(int $user_id, array $session_data): bool;
    
    /**
     * Get study recommendations
     */
    public function get_recommendations(int $user_id): array;
}
```

### PMP_Domain_Analytics

```php
interface PMP_Domain_Analytics_Interface {
    /**
     * Calculate weighted overall progress
     */
    public function calculate_overall_progress(int $user_id): float;
    
    /**
     * Get domain performance comparison
     */
    public function get_domain_comparison(int $user_id): array;
    
    /**
     * Identify weakest domain
     */
    public function get_weakest_domain(int $user_id): string;
    
    /**
     * Get domain-specific recommendations
     */
    public function get_domain_recommendations(int $user_id, string $domain): array;
}
```

### PMP_Study_Streaks

```php
interface PMP_Study_Streaks_Interface {
    /**
     * Calculate current study streak
     */
    public function calculate_streak(int $user_id): int;
    
    /**
     * Get streak history
     */
    public function get_streak_history(int $user_id, int $days = 30): array;
    
    /**
     * Check if streak is at risk
     */
    public function is_streak_at_risk(int $user_id): bool;
    
    /**
     * Get motivational message for current streak
     */
    public function get_streak_message(int $streak_count): string;
}
```

## JavaScript Component Interfaces

### ProgressTracker

```javascript
class ProgressTracker {
    /**
     * Initialize progress tracking
     */
    constructor(config) {
        this.userId = config.userId;
        this.ajaxUrl = config.ajaxUrl;
        this.nonce = config.nonce;
    }
    
    /**
     * Update lesson progress
     */
    async updateProgress(lessonId, progressData) {
        // Returns Promise<ProgressResponse>
    }
    
    /**
     * Get current user progress
     */
    async getProgress(includeDetails = false) {
        // Returns Promise<UserProgressResponse>
    }
    
    /**
     * Start lesson timer
     */
    startLessonTimer(lessonId) {
        // Starts time tracking
    }
    
    /**
     * Stop lesson timer and save progress
     */
    async stopLessonTimer(lessonId, completed = false) {
        // Returns Promise<ProgressResponse>
    }
}
```

### ProgressVisualizer

```javascript
class ProgressVisualizer {
    /**
     * Render circular progress indicator
     */
    renderCircularProgress(element, percentage, options = {}) {
        // Animates SVG progress circle
    }
    
    /**
     * Update domain progress bars
     */
    updateDomainProgress(progressData) {
        // Updates all domain progress indicators
    }
    
    /**
     * Display study streak badge
     */
    displayStreakBadge(streakCount, container) {
        // Shows animated streak badge
    }
    
    /**
     * Animate progress changes
     */
    animateProgressChange(fromValue, toValue, element) {
        // Smooth progress animation
    }
}
```

## Error Codes

### Progress Update Errors
- `INVALID_USER`: User ID not found or access denied
- `INVALID_LESSON`: Lesson ID not found or not accessible
- `INVALID_PROGRESS`: Progress percentage out of range (0-100)
- `INVALID_TIME`: Time spent value invalid or excessive
- `INVALID_STATUS`: Status not in allowed values
- `DATABASE_ERROR`: Database operation failed
- `CACHE_ERROR`: Cache operation failed
- `PERMISSION_DENIED`: User lacks required permissions

### Authentication Errors
- `INVALID_NONCE`: WordPress nonce verification failed
- `SESSION_EXPIRED`: User session has expired
- `INSUFFICIENT_PERMISSIONS`: User role lacks required capabilities

### Validation Errors
- `MISSING_REQUIRED_FIELD`: Required parameter not provided
- `INVALID_DATA_TYPE`: Parameter type mismatch
- `VALUE_OUT_OF_RANGE`: Parameter value exceeds allowed range
- `INVALID_ENUM_VALUE`: Enum parameter has invalid value

## Rate Limiting

### Progress Updates
- **Limit**: 60 requests per minute per user
- **Burst**: 10 requests per 10 seconds
- **Response**: HTTP 429 with Retry-After header

### Analytics Requests
- **Limit**: 30 requests per minute per user
- **Burst**: 5 requests per 10 seconds
- **Response**: HTTP 429 with Retry-After header

## Caching Strategy

### Response Caching
- **User Progress**: 15 minutes (invalidated on update)
- **Domain Analytics**: 30 minutes (invalidated on progress change)
- **Study Streaks**: 1 hour (invalidated on session update)
- **Recommendations**: 2 hours (invalidated on significant progress change)

### Cache Keys
```php
// WordPress transient keys
"pmp_progress_{$user_id}"           // User progress data
"pmp_domain_{$user_id}"             // Domain analytics
"pmp_streak_{$user_id}"             // Study streak data
"pmp_recommendations_{$user_id}"    // Study recommendations
```
