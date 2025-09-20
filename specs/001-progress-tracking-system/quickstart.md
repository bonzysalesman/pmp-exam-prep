# Quickstart Guide: Enhanced Progress Tracking System

**Feature**: 001-progress-tracking-system  
**Date**: September 20, 2025

## Testing Scenarios

### 1. Dashboard Access Test
**Objective**: Verify dashboard loads with progress components

**Steps**:
1. Navigate to `http://localhost:8888/mi/dashboard/`
2. Login as a user with lessons available
3. Verify all components load:
   - Progress cards for 3 domains (People, Process, Business Environment)
   - Study streak display with current streak
   - Analytics charts with tabs (Weekly, Domain, Time)
   - Recent activity sidebar

**Expected Results**:
- Dashboard loads without errors
- All progress data displays correctly
- Charts render with Chart.js
- Logo appears in top left

### 2. Lesson Progress Update Test
**Objective**: Test real-time progress updates

**Steps**:
1. Complete a lesson using the "Complete Lesson" button
2. Observe progress bar updates
3. Check domain progress percentage changes
4. Verify study streak increments (if daily)

**Expected Results**:
- Progress updates immediately without page refresh
- Domain percentages recalculate correctly
- Success notification appears
- Study streak updates if applicable

### 3. API Endpoints Test
**Objective**: Verify REST API functionality

**Test URLs**:
```bash
# Get user progress (replace {user_id} with actual ID)
GET /wp-json/pmp/v1/progress/{user_id}

# Get analytics data
GET /wp-json/pmp/v1/analytics/{user_id}?period=month

# Get study streak
GET /wp-json/pmp/v1/streak/{user_id}

# Update lesson progress (POST with nonce)
POST /wp-json/pmp/v1/progress/lesson
```

**Expected Results**:
- All endpoints return valid JSON
- Proper authentication required
- Data structure matches API contracts

### 4. Domain Progress Calculation Test
**Objective**: Verify weighted progress calculation

**Test Data**:
- Complete 5 People domain lessons (should show ~13% of People domain)
- Complete 10 Process domain lessons (should show ~22% of Process domain)
- Complete 2 Business Environment lessons (should show ~25% of Business domain)

**Expected Results**:
- Domain percentages calculate correctly
- Overall progress uses proper weighting (42% + 50% + 8%)
- Time tracking accumulates properly

### 5. Study Streak Test
**Objective**: Test streak logic and milestones

**Steps**:
1. Complete lessons on consecutive days
2. Skip a day and resume (streak should reset)
3. Reach milestone (7, 14, 30 days)
4. Check motivational messages

**Expected Results**:
- Streak increments for consecutive days
- Streak resets after gaps
- Milestone badges appear at correct intervals
- Messages update based on streak length

### 6. Analytics Charts Test
**Objective**: Verify Chart.js visualizations

**Steps**:
1. Switch between chart tabs (Weekly, Domain, Time)
2. Change period filter (week, month, all)
3. Verify data accuracy in charts

**Expected Results**:
- Charts render without errors
- Data matches user's actual progress
- Responsive design works on mobile
- Interactive tooltips function

### 7. Performance Test
**Objective**: Verify performance targets

**Metrics to Check**:
- Page load time < 3 seconds
- API response time < 200ms
- Chart rendering < 1 second
- Database queries optimized

**Tools**:
- Browser DevTools Network tab
- WordPress Query Monitor plugin
- Lighthouse performance audit

### 8. Accessibility Test
**Objective**: Verify WCAG 2.1 AA compliance

**Checks**:
- Progress bars have proper ARIA labels
- Charts include text alternatives
- Keyboard navigation works
- Screen reader compatibility
- Color contrast ratios meet standards

### 9. Mobile Responsiveness Test
**Objective**: Test mobile experience

**Devices to Test**:
- iPhone (375px width)
- iPad (768px width)
- Android phone (360px width)

**Expected Results**:
- All components stack properly
- Touch interactions work
- Charts remain readable
- Navigation stays accessible

### 10. Error Handling Test
**Objective**: Test error scenarios

**Test Cases**:
- Invalid lesson ID in API call
- Network timeout during progress update
- Missing user permissions
- Malformed API requests

**Expected Results**:
- Graceful error messages
- No JavaScript console errors
- Fallback content displays
- User can recover from errors

## Quick Validation Checklist

### Database
- [ ] All 4 tables created with proper indexes
- [ ] Domain data initialized for users
- [ ] Foreign key relationships work
- [ ] Query performance < 200ms

### API
- [ ] All 4 endpoints respond correctly
- [ ] Authentication works properly
- [ ] Input validation prevents errors
- [ ] Error responses are helpful

### Frontend
- [ ] Progress cards display domain data
- [ ] Study streak shows current status
- [ ] Charts render with real data
- [ ] Real-time updates work

### Integration
- [ ] Dashboard page loads completely
- [ ] JavaScript dependencies load
- [ ] AJAX calls succeed
- [ ] No PHP errors in logs

### Performance
- [ ] Page load < 3 seconds
- [ ] API calls < 200ms
- [ ] Charts render quickly
- [ ] Mobile performance good

## Troubleshooting

### Common Issues

**Charts not rendering**:
- Check Chart.js is loaded
- Verify analytics-charts.js loads after Chart.js
- Check browser console for errors

**Progress not updating**:
- Verify nonce is valid
- Check user permissions
- Confirm lesson exists and is published

**Database errors**:
- Run theme activation to create tables
- Check database user permissions
- Verify table structure matches schema

**Performance issues**:
- Check database indexes exist
- Monitor query execution time
- Optimize image sizes

### Debug Mode
Enable WordPress debug mode in wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at `/wp-content/debug.log` for errors.

## Success Criteria

✅ **All test scenarios pass**  
✅ **Performance targets met**  
✅ **No accessibility violations**  
✅ **Mobile experience excellent**  
✅ **Error handling graceful**  
✅ **Real-time updates work**  
✅ **Data accuracy verified**
