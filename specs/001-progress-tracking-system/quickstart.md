# Quickstart: Enhanced Progress Tracking System

**Feature**: 001-progress-tracking-system  
**Date**: September 20, 2025

## Quick Validation Scenarios

### 1. Basic Progress Tracking (5 minutes)

**Setup:**
```bash
# Ensure WordPress is running
docker-compose up -d

# Access WordPress admin
open http://localhost:8080/wp-admin
```

**Test Scenario:**
1. Login as test student user
2. Navigate to a lesson page
3. Start lesson (click "Begin Lesson")
4. Wait 30 seconds (simulate study time)
5. Mark lesson as complete
6. Check dashboard for updated progress

**Expected Results:**
- Progress percentage increases
- Time spent recorded (≥30 seconds)
- Domain progress updates correctly
- Dashboard reflects changes within 2 seconds

### 2. Domain-Specific Analytics (3 minutes)

**Test Scenario:**
1. Complete 2 "People" domain lessons
2. Complete 1 "Process" domain lesson  
3. View dashboard analytics section

**Expected Results:**
- People domain: 66% progress (2/3 lessons)
- Process domain: 33% progress (1/3 lessons)
- Overall progress: weighted calculation displayed
- Visual progress circles animate correctly

### 3. Study Streak Tracking (2 minutes)

**Test Scenario:**
1. Complete lesson today (creates session)
2. Manually create yesterday's session in database:
   ```sql
   INSERT INTO wp_study_sessions (user_id, session_date, duration, lessons_completed) 
   VALUES (1, CURDATE() - INTERVAL 1 DAY, 1800, 1);
   ```
3. Refresh dashboard

**Expected Results:**
- "2-day study streak" badge displayed
- Motivational message shown
- Streak counter updates in real-time

## Development Environment Setup

### Prerequisites
```bash
# Required tools
docker --version          # Docker 20.10+
docker-compose --version  # Docker Compose 2.0+
php --version             # PHP 8.1+
node --version            # Node.js 18+
```

### Database Setup
```bash
# Create test data
mysql -h localhost -P 3306 -u wordpress -p wordpress < database/test-data.sql

# Verify tables exist
mysql -h localhost -P 3306 -u wordpress -p -e "SHOW TABLES LIKE 'wp_%progress%';" wordpress
```

### Theme Integration
```bash
# Copy progress tracking files
cp -r includes/class-pmp-progress-*.php wp-content/themes/pmp-dashboard/includes/
cp -r assets/js/progress-*.js wp-content/themes/pmp-dashboard/assets/js/
cp -r template-parts/progress/ wp-content/themes/pmp-dashboard/template-parts/

# Build CSS assets
cd wp-content/themes/pmp-dashboard
npm run build
```

## Testing Checklist

### Unit Tests
```bash
# Run PHP unit tests
./vendor/bin/phpunit tests/unit/ProgressTrackerTest.php

# Run JavaScript tests  
npm test -- progress-tracker.test.js
```

### Integration Tests
```bash
# Test database operations
./vendor/bin/phpunit tests/integration/ProgressDatabaseTest.php

# Test AJAX endpoints
./vendor/bin/phpunit tests/integration/ProgressAPITest.php
```

### Performance Tests
```bash
# Load test progress updates
ab -n 1000 -c 10 http://localhost:8080/wp-admin/admin-ajax.php?action=update_lesson_progress

# Monitor database performance
mysql -e "SHOW PROCESSLIST;" wordpress
```

## Key Validation Points

### ✅ Functional Validation
- [ ] Progress updates save correctly to database
- [ ] Domain calculations match PMI percentages (42%, 50%, 8%)
- [ ] Study streaks calculate consecutive days accurately
- [ ] Time tracking records actual study duration
- [ ] Visual progress circles animate smoothly

### ✅ Performance Validation  
- [ ] Progress updates complete in < 500ms
- [ ] Dashboard loads with data in < 2 seconds
- [ ] Database queries use proper indexes
- [ ] Caching reduces database load by 80%+
- [ ] Mobile performance score > 85

### ✅ Security Validation
- [ ] All AJAX requests use WordPress nonces
- [ ] User input sanitized and validated
- [ ] SQL injection protection via prepared statements
- [ ] XSS prevention on all output
- [ ] User can only access their own progress data

### ✅ Accessibility Validation
- [ ] Progress circles have ARIA labels
- [ ] Keyboard navigation works for all controls
- [ ] Screen reader announces progress updates
- [ ] High contrast mode displays correctly
- [ ] Focus indicators visible and logical

## Common Issues & Solutions

### Issue: Progress not updating
**Symptoms:** Dashboard shows old progress data
**Solution:** 
```bash
# Clear WordPress cache
wp cache flush

# Check database connection
mysql -h localhost -P 3306 -u wordpress -p -e "SELECT 1;" wordpress
```

### Issue: Slow dashboard loading
**Symptoms:** Dashboard takes > 5 seconds to load
**Solution:**
```php
// Enable query debugging
define('WP_DEBUG', true);
define('SAVEQUERIES', true);

// Check slow queries
global $wpdb;
print_r($wpdb->queries);
```

### Issue: Domain percentages incorrect
**Symptoms:** Domain progress doesn't match expected PMI ratios
**Solution:**
```sql
-- Verify lesson domain assignments
SELECT domain_type, COUNT(*) 
FROM wp_postmeta 
WHERE meta_key = 'domain_type' 
GROUP BY meta_value;

-- Should show: people=42%, process=50%, business=8%
```

## Production Deployment

### Pre-deployment Checklist
- [ ] All tests passing
- [ ] Database migrations tested
- [ ] Performance benchmarks met
- [ ] Security scan completed
- [ ] Backup strategy verified

### Deployment Steps
```bash
# 1. Backup current database
mysqldump wordpress > backup_$(date +%Y%m%d).sql

# 2. Run database migrations
mysql wordpress < database/migrations/001_add_domain_tracking.sql

# 3. Deploy theme files
rsync -av wp-content/themes/pmp-dashboard/ production:/var/www/wp-content/themes/pmp-dashboard/

# 4. Clear all caches
wp cache flush --path=/var/www/

# 5. Verify deployment
curl -I https://your-domain.com/dashboard/
```

### Post-deployment Monitoring
```bash
# Monitor error logs
tail -f /var/log/wordpress/error.log

# Check database performance
mysql -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';" wordpress

# Verify progress tracking
curl -X POST https://your-domain.com/wp-admin/admin-ajax.php \
  -d "action=update_lesson_progress&user_id=1&lesson_id=1&progress=50"
```

## Support & Troubleshooting

### Debug Mode
```php
// Enable progress tracking debug mode
define('PMP_PROGRESS_DEBUG', true);

// View debug information
add_action('wp_footer', function() {
    if (defined('PMP_PROGRESS_DEBUG') && PMP_PROGRESS_DEBUG) {
        global $pmp_progress_debug;
        echo '<pre>' . print_r($pmp_progress_debug, true) . '</pre>';
    }
});
```

### Performance Monitoring
```php
// Track progress update performance
add_action('pmp_progress_updated', function($user_id, $lesson_id, $duration) {
    error_log("Progress update took {$duration}ms for user {$user_id}, lesson {$lesson_id}");
});
```

### Health Check Endpoint
```php
// Add health check for progress system
add_action('wp_ajax_nopriv_pmp_health_check', function() {
    $health = [
        'database' => check_database_connection(),
        'cache' => check_cache_status(),
        'performance' => check_query_performance()
    ];
    wp_send_json($health);
});
```
