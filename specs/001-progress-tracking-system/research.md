# Research: Enhanced Progress Tracking System

**Date**: September 20, 2025  
**Feature**: 001-progress-tracking-system

## Chart.js Integration Patterns for WordPress

### Decision: Chart.js 4.x with WordPress Enqueue System
**Rationale**: 
- Chart.js 4.x provides excellent responsive charts with accessibility features
- WordPress wp_enqueue_script() ensures proper dependency management
- CDN delivery for performance with local fallback

**Implementation Pattern**:
```php
// In functions.php or theme file
wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js', array(), '4.4.0', true);
wp_enqueue_script('progress-charts', get_template_directory_uri() . '/assets/js/analytics-charts.js', array('chartjs'), '1.0', true);
```

**Alternatives Considered**:
- Google Charts: Rejected due to privacy concerns and external dependency
- D3.js: Rejected due to complexity and learning curve
- Canvas native: Rejected due to accessibility and maintenance overhead

## WordPress Custom Table Best Practices

### Decision: Custom Tables with Proper Indexing
**Rationale**:
- WordPress meta tables become slow with large datasets (>10k records)
- Custom tables allow proper indexing for <200ms query performance
- Direct SQL queries for analytics calculations

**Table Design Pattern**:
```sql
CREATE TABLE wp_user_progress (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id bigint(20) UNSIGNED NOT NULL,
    domain varchar(50) NOT NULL,
    completion_percentage decimal(5,2) DEFAULT 0.00,
    lessons_completed int(11) DEFAULT 0,
    total_lessons int(11) DEFAULT 0,
    last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_domain (user_id, domain),
    KEY idx_user_id (user_id),
    KEY idx_domain (domain)
) ENGINE=InnoDB;
```

**Alternatives Considered**:
- WordPress meta tables: Rejected due to performance at scale
- JSON fields: Rejected due to limited query capabilities
- External database: Rejected due to complexity and WordPress integration

## Real-time Progress Update Strategies

### Decision: WordPress REST API with AJAX
**Rationale**:
- WordPress REST API provides standardized endpoints with authentication
- AJAX allows real-time updates without page refresh
- Nonce validation ensures security

**Update Pattern**:
```javascript
// Frontend update
fetch('/wp-json/pmp/v1/progress/lesson', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': pmp_ajax.nonce
    },
    body: JSON.stringify({
        lesson_id: lessonId,
        time_spent: timeSpent
    })
});
```

**Alternatives Considered**:
- WordPress AJAX hooks: Rejected due to less standardized approach
- WebSockets: Rejected due to server complexity and hosting requirements
- Server-sent events: Rejected due to limited browser support

## PWA Offline Storage Strategies

### Decision: IndexedDB with Service Worker Caching
**Rationale**:
- IndexedDB provides structured storage for progress data
- Service Worker enables offline functionality
- Sync when connection restored

**Storage Pattern**:
```javascript
// Store progress offline
const request = indexedDB.open('pmp-progress', 1);
request.onsuccess = function(event) {
    const db = event.target.result;
    const transaction = db.transaction(['progress'], 'readwrite');
    const store = transaction.objectStore('progress');
    store.put({
        user_id: userId,
        lesson_id: lessonId,
        completed_at: new Date(),
        synced: false
    });
};
```

**Alternatives Considered**:
- LocalStorage: Rejected due to 5MB limit and synchronous API
- WebSQL: Rejected due to deprecation
- Cache API only: Rejected due to limited structured data support

## Performance Optimization Strategies

### Decision: Database Indexing + Query Optimization
**Rationale**:
- Proper indexing ensures <200ms query response
- Aggregate tables for complex analytics
- Caching for frequently accessed data

**Optimization Techniques**:
1. Composite indexes on (user_id, domain) for progress queries
2. Separate analytics table with pre-calculated aggregates
3. WordPress transient caching for dashboard data (5-minute TTL)
4. Pagination for large datasets

## Security Considerations

### Decision: WordPress Nonces + Capability Checks
**Rationale**:
- WordPress nonces prevent CSRF attacks
- Capability checks ensure proper authorization
- Data sanitization prevents SQL injection

**Security Pattern**:
```php
// API endpoint security
if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'pmp_progress_update')) {
    wp_die('Security check failed');
}

if (!current_user_can('read')) {
    wp_die('Insufficient permissions');
}

$user_id = absint($_POST['user_id']);
$lesson_id = sanitize_text_field($_POST['lesson_id']);
```

## Accessibility Requirements

### Decision: WCAG 2.1 AA Compliance
**Rationale**:
- Screen reader compatibility for progress data
- Keyboard navigation for all interactive elements
- High contrast colors for visual indicators

**Implementation Requirements**:
- ARIA labels for progress bars and charts
- Alt text for visual progress indicators
- Keyboard-accessible chart interactions
- Focus management for dynamic updates
