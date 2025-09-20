# PMP Dashboard WordPress Theme Conversion Specification

**Version:** 1.0  
**Date:** September 19, 2025  
**Author:** Development Team  
**Status:** Draft

## 1. Overview

### 1.1 Purpose
Convert static HTML templates for PMP exam preparation dashboard into a fully functional WordPress theme with learning management capabilities.

### 1.2 Scope
- Transform 8 static HTML pages into dynamic WordPress templates
- Implement user progress tracking and authentication
- Create custom post types for educational content
- Maintain responsive design and PWA functionality
- Add interactive learning features

### 1.3 Success Criteria
- [ ] All static templates converted to WordPress PHP
- [ ] User registration and progress tracking functional
- [ ] Mobile-responsive design maintained
- [ ] PWA features operational
- [ ] Performance benchmarks met (< 3s load time)

## 2. Technical Requirements

### 2.1 WordPress Environment
- **WordPress Version:** 6.4+
- **PHP Version:** 8.1+
- **MySQL Version:** 8.0+
- **Required Plugins:** Advanced Custom Fields Pro, WP Rocket (optional)

### 2.2 Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

### 2.3 Performance Targets
- **Page Load Time:** < 3 seconds
- **First Contentful Paint:** < 1.5 seconds
- **Lighthouse Score:** > 90
- **Mobile Performance:** > 85

## 3. Architecture

### 3.1 Custom Post Types

#### 3.1.1 Work Group (`work_group`)
```php
'supports' => ['title', 'editor', 'thumbnail', 'custom-fields']
'hierarchical' => false
'public' => true
'show_in_rest' => true
```

**Custom Fields:**
- `wg_number` (number): Work Group sequence (1-5)
- `wg_domain` (select): People, Process, Business Environment
- `wg_duration` (text): Estimated completion time
- `wg_prerequisites` (relationship): Required previous WGs

#### 3.1.2 Lesson (`lesson`)
```php
'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'comments']
'hierarchical' => true
'public' => true
'show_in_rest' => true
```

**Custom Fields:**
- `lesson_video_url` (url): YouTube/Vimeo embed
- `lesson_duration` (number): Minutes
- `lesson_objectives` (repeater): Learning objectives
- `lesson_resources` (file): Downloadable materials
- `lesson_order` (number): Sequence within Work Group

#### 3.1.3 Practice Test (`practice_test`)
```php
'supports' => ['title', 'editor', 'custom-fields']
'hierarchical' => false
'public' => true
'show_in_rest' => true
```

**Custom Fields:**
- `test_questions` (repeater): Question bank
- `test_time_limit` (number): Minutes
- `test_passing_score` (number): Percentage
- `test_attempts_allowed` (number): Max attempts

### 3.2 Custom Taxonomies

#### 3.2.1 Domain (`domain`)
- People
- Process  
- Business Environment

#### 3.2.2 Difficulty (`difficulty`)
- Beginner
- Intermediate
- Advanced

### 3.3 User Roles

#### 3.3.1 PMP Student (`pmp_student`)
**Capabilities:**
- `read`
- `access_lessons`
- `take_tests`
- `view_progress`

#### 3.3.2 PMP Instructor (`pmp_instructor`)
**Capabilities:**
- All student capabilities
- `edit_lessons`
- `view_student_progress`
- `manage_tests`

## 4. Database Schema

### 4.1 Progress Tracking Tables

#### 4.1.1 User Lesson Progress
```sql
CREATE TABLE wp_user_lesson_progress (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    lesson_id BIGINT(20) UNSIGNED NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    progress_percentage TINYINT(3) UNSIGNED DEFAULT 0,
    time_spent INT(11) UNSIGNED DEFAULT 0,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    last_accessed DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    KEY idx_user_id (user_id),
    KEY idx_lesson_id (lesson_id),
    KEY idx_status (status)
);
```

#### 4.1.2 User Test Results
```sql
CREATE TABLE wp_user_test_results (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    test_id BIGINT(20) UNSIGNED NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    time_taken INT(11) UNSIGNED NOT NULL,
    attempt_number TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
    answers JSON,
    taken_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_test (user_id, test_id),
    KEY idx_percentage (percentage)
);
```

#### 4.1.3 Study Sessions
```sql
CREATE TABLE wp_study_sessions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    session_date DATE NOT NULL,
    duration INT(11) UNSIGNED NOT NULL,
    lessons_completed TINYINT(3) UNSIGNED DEFAULT 0,
    tests_taken TINYINT(3) UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_date (user_id, session_date),
    KEY idx_session_date (session_date)
);
```

## 5. Template Structure

### 5.1 Template Hierarchy
```
wp-content/themes/pmp-dashboard/
├── style.css
├── index.php
├── functions.php
├── header.php
├── footer.php
├── sidebar.php
├── single-work_group.php
├── single-lesson.php
├── single-practice_test.php
├── page-dashboard.php
├── page-progress.php
├── template-parts/
│   ├── dashboard/
│   │   ├── hero-section.php
│   │   ├── progress-cards.php
│   │   ├── work-groups-accordion.php
│   │   └── activity-feed.php
│   ├── lesson/
│   │   ├── video-player.php
│   │   ├── lesson-navigation.php
│   │   └── lesson-content.php
│   ├── navigation/
│   │   ├── sidebar-nav.php
│   │   └── mobile-nav.php
│   └── components/
│       ├── progress-bar.php
│       └── user-dropdown.php
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── fonts/
├── inc/
│   ├── custom-post-types.php
│   ├── user-progress.php
│   ├── ajax-handlers.php
│   ├── enqueue-scripts.php
│   └── theme-setup.php
└── languages/
```

### 5.2 Key Templates

#### 5.2.1 Dashboard (page-dashboard.php)
**Requirements:**
- Display user's current progress
- Show Work Groups with completion status
- Recent activity feed
- Study statistics
- Mobile-responsive sidebar

**Data Sources:**
- `wp_user_lesson_progress`
- `wp_study_sessions`
- `wp_user_test_results`

#### 5.2.2 Lesson Template (single-lesson.php)
**Requirements:**
- Video player with progress tracking
- Lesson navigation (prev/next)
- Learning objectives display
- Progress update via AJAX
- Breadcrumb navigation

**JavaScript Events:**
- Video play/pause tracking
- Completion marking
- Time spent calculation

#### 5.2.3 Work Group Template (single-work_group.php)
**Requirements:**
- List all lessons in order
- Show completion status per lesson
- Progress overview
- Prerequisites checking

## 6. Functionality Specifications

### 6.1 User Progress Tracking

#### 6.1.1 Lesson Progress
```php
function update_lesson_progress($user_id, $lesson_id, $progress_data) {
    // Validate input
    // Update database
    // Trigger hooks
    // Return status
}
```

**AJAX Endpoint:** `wp_ajax_update_lesson_progress`
**Parameters:**
- `lesson_id` (required)
- `progress_percentage` (0-100)
- `time_spent` (seconds)
- `status` (not_started|in_progress|completed)

#### 6.1.2 Study Streak Calculation
```php
function calculate_study_streak($user_id) {
    // Query study sessions
    // Calculate consecutive days
    // Update user meta
    // Return streak count
}
```

### 6.2 Assessment System

#### 6.2.1 Quiz Engine
**Features:**
- Timed assessments
- Multiple choice questions
- Instant feedback
- Score calculation
- Attempt tracking

**Question Types:**
- Single choice
- Multiple choice
- True/False
- Scenario-based

#### 6.2.2 Progress Analytics
```php
function get_user_analytics($user_id) {
    return [
        'total_time_spent' => calculate_total_time($user_id),
        'lessons_completed' => count_completed_lessons($user_id),
        'average_test_score' => calculate_average_score($user_id),
        'study_streak' => get_study_streak($user_id),
        'exam_readiness' => calculate_readiness($user_id)
    ];
}
```

### 6.3 PWA Implementation

#### 6.3.1 Service Worker
**Cache Strategy:**
- Static assets: Cache first
- API calls: Network first with fallback
- User data: Network only

**Offline Features:**
- Cached lesson content
- Progress sync when online
- Offline indicator

#### 6.3.2 Manifest Configuration
```json
{
    "name": "PMP Exam Prep Dashboard",
    "short_name": "PMP Prep",
    "start_url": "/dashboard/",
    "display": "standalone",
    "theme_color": "#5b28b3",
    "background_color": "#ffffff"
}
```

## 7. Performance Optimization

### 7.1 Caching Strategy
- **Object Cache:** User progress data (1 hour TTL)
- **Transients:** Expensive queries (24 hours TTL)
- **Page Cache:** Static content with dynamic exclusions

### 7.2 Database Optimization
- Proper indexing on frequently queried columns
- Query optimization for progress calculations
- Pagination for large datasets

### 7.3 Asset Optimization
- CSS/JS minification and concatenation
- Image optimization and WebP support
- Lazy loading for non-critical content

## 8. Security Requirements

### 8.1 User Authentication
- WordPress native authentication
- Nonce verification for AJAX calls
- Capability checks for all actions

### 8.2 Data Protection
- Sanitize all user inputs
- Escape all outputs
- Secure AJAX endpoints

### 8.3 Progress Data Integrity
- Validate progress updates
- Prevent manipulation of completion status
- Audit trail for critical actions

## 9. Testing Requirements

### 9.1 Unit Tests
- Progress calculation functions
- User role capabilities
- Database operations

### 9.2 Integration Tests
- AJAX functionality
- User registration flow
- Progress tracking accuracy

### 9.3 Performance Tests
- Page load times
- Database query performance
- Mobile responsiveness

## 10. Deployment Specifications

### 10.1 Environment Requirements
- **Staging:** Mirror production environment
- **Production:** WordPress hosting with PHP 8.1+
- **CDN:** CloudFlare or similar for static assets

### 10.2 Migration Strategy
1. Set up WordPress environment
2. Install and configure theme
3. Import content structure
4. Test all functionality
5. Performance optimization
6. Go-live checklist

### 10.3 Monitoring
- Error logging and monitoring
- Performance metrics tracking
- User engagement analytics

## 11. Maintenance Plan

### 11.1 Regular Updates
- WordPress core updates
- Plugin compatibility checks
- Security patches

### 11.2 Content Management
- Lesson content updates
- New Work Group additions
- Test question bank maintenance

### 11.3 Performance Monitoring
- Monthly performance audits
- Database optimization
- Cache effectiveness review

---

**Approval Required From:**
- [ ] Technical Lead
- [ ] UX/UI Designer  
- [ ] Product Owner
- [ ] QA Lead

**Estimated Timeline:** 8-10 weeks
**Budget Estimate:** $15,000 - $20,000
