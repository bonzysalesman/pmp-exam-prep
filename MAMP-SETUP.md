# MAMP Setup for PMP WordPress Theme Testing

## Quick Setup Steps

### 1. Copy Theme to MAMP
```bash
# Copy the theme to your MAMP WordPress installation
cp -r /Users/bonzysalesman/pmp-exam-prep/wp-content/themes/pmp-dashboard /Applications/MAMP/htdocs/wordpress/wp-content/themes/

# Or create a symlink for easier development
ln -s /Users/bonzysalesman/pmp-exam-prep/wp-content/themes/pmp-dashboard /Applications/MAMP/htdocs/wordpress/wp-content/themes/pmp-dashboard
```

### 2. Database Setup
1. Start MAMP
2. Go to phpMyAdmin: http://localhost:8888/phpMyAdmin/
3. Create database: `pmp_exam_prep`
4. Import WordPress tables (standard WordPress installation)
5. Run the custom table setup:

```sql
-- Copy and run this in phpMyAdmin
-- From: wp-content/themes/pmp-dashboard/database-setup.sql
```

### 3. WordPress Configuration
1. Access WordPress admin: http://localhost:8888/wordpress/wp-admin/
2. Go to Appearance > Themes
3. Activate "PMP Dashboard Theme"
4. Create test pages and content

## Test Checklist

### ✅ Basic Theme Functionality
- [ ] Theme activates without errors
- [ ] Front page displays correctly
- [ ] Navigation menu works
- [ ] Mobile responsive design
- [ ] Custom post types register

### ✅ Progress Tracking
- [ ] Database tables created
- [ ] AJAX endpoints respond
- [ ] Progress updates save
- [ ] Dashboard displays data

### ✅ Content Management
- [ ] Work Groups display
- [ ] Lessons show correctly
- [ ] Practice tests function
- [ ] Resources page works

## Common Issues & Fixes

### Theme Not Appearing
```bash
# Check file permissions
chmod -R 755 /Applications/MAMP/htdocs/wordpress/wp-content/themes/pmp-dashboard
```

### Database Errors
```sql
-- Verify tables exist
SHOW TABLES LIKE 'wp_%';

-- Check custom tables
SHOW TABLES LIKE 'wp_user_lesson_progress';
SHOW TABLES LIKE 'wp_study_sessions';
```

### JavaScript/CSS Not Loading
- Check file paths in functions.php
- Verify Tailwind CSS is properly enqueued
- Clear browser cache

## URLs to Test
- Homepage: http://localhost:8888/wordpress/
- Dashboard: http://localhost:8888/wordpress/dashboard/
- Lessons: http://localhost:8888/wordpress/lesson/
- Work Groups: http://localhost:8888/wordpress/work-group/
