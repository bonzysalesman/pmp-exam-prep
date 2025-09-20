<?php
/**
 * Enhanced Lesson Viewer
 * Task: T011 - Lesson Viewer Enhancement
 */

// Get lesson data
$lesson_id = get_the_ID();
$current_user_id = get_current_user_id();

// Get lesson metadata
$estimated_minutes = get_post_meta($lesson_id, '_estimated_minutes', true) ?: 15;
$difficulty_level = get_post_meta($lesson_id, '_difficulty_level', true) ?: 'intermediate';
$lesson_type = get_post_meta($lesson_id, '_lesson_type', true) ?: 'text';
$video_url = get_post_meta($lesson_id, '_video_url', true);
$downloadable_resources = get_post_meta($lesson_id, '_downloadable_resources', true);

// Get user progress
$user_progress = 0;
$lesson_status = 'not_started';
if ($current_user_id) {
    global $wpdb;
    $progress_data = $wpdb->get_row($wpdb->prepare(
        "SELECT progress_percentage, status, time_spent FROM {$wpdb->prefix}pmp_lesson_progress 
         WHERE user_id = %d AND lesson_id = %d",
        $current_user_id, $lesson_id
    ));
    
    if ($progress_data) {
        $user_progress = intval($progress_data->progress_percentage);
        $lesson_status = $progress_data->status;
    }
}

// Get navigation
$next_lesson = PMP_Lesson_Manager::get_next_lesson($lesson_id, $current_user_id);
$prev_lesson = PMP_Lesson_Manager::get_previous_lesson($lesson_id);

// Get related content
$related_content = PMP_Content_Manager::get_related_content($lesson_id, 4);

// Get domains and knowledge areas
$domains = wp_get_post_terms($lesson_id, 'pmp_domain');
$knowledge_areas = wp_get_post_terms($lesson_id, 'pmp_knowledge_area');
?>

<div class="lesson-viewer bg-gray-50 min-h-screen">
    
    <!-- Lesson Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="<?php echo home_url(); ?>" class="hover:text-primary">Home</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="<?php echo get_post_type_archive_link('lesson'); ?>" class="hover:text-primary">Lessons</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900"><?php the_title(); ?></span>
            </nav>
            
            <!-- Lesson Title & Meta -->
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
                    
                    <!-- Meta Information -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <span class="flex items-center">
                            <i class="fas fa-clock mr-2 text-primary"></i>
                            <?php echo $estimated_minutes; ?> minutes
                        </span>
                        
                        <span class="flex items-center">
                            <i class="fas fa-signal mr-2 text-primary"></i>
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                <?php echo $difficulty_level === 'beginner' ? 'bg-green-100 text-green-800' : 
                                          ($difficulty_level === 'advanced' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo ucfirst($difficulty_level); ?>
                            </span>
                        </span>
                        
                        <?php if (!empty($domains)): ?>
                            <span class="flex items-center">
                                <i class="fas fa-tag mr-2 text-primary"></i>
                                <?php echo esc_html($domains[0]->name); ?>
                            </span>
                        <?php endif; ?>
                        
                        <span class="flex items-center">
                            <i class="fas fa-<?php echo $lesson_type === 'video' ? 'play' : ($lesson_type === 'interactive' ? 'mouse-pointer' : 'book'); ?> mr-2 text-primary"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $lesson_type)); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Progress Circle -->
                <div class="ml-6">
                    <div class="relative w-20 h-20">
                        <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none" 
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="text-primary" stroke="currentColor" stroke-width="3" fill="none" 
                                  stroke-dasharray="<?php echo $user_progress; ?>, 100" 
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-semibold text-gray-900"><?php echo $user_progress; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span class="text-sm text-gray-500">
                        <?php echo $lesson_status === 'completed' ? 'Completed' : 
                                  ($lesson_status === 'in_progress' ? 'In Progress' : 'Not Started'); ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500" 
                         style="width: <?php echo $user_progress; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lesson Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    
                    <!-- Video Content -->
                    <?php if ($lesson_type === 'video' && $video_url): ?>
                        <div class="mb-6">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-lg overflow-hidden">
                                <?php if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false): ?>
                                    <?php
                                    // Extract YouTube video ID
                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $video_url, $matches);
                                    $video_id = $matches[1] ?? '';
                                    ?>
                                    <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
                                            frameborder="0" allowfullscreen 
                                            class="w-full h-full"></iframe>
                                <?php else: ?>
                                    <video controls class="w-full h-full">
                                        <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Lesson Content -->
                    <div class="prose prose-lg max-w-none">
                        <?php the_content(); ?>
                    </div>
                    
                    <!-- Downloadable Resources -->
                    <?php if ($downloadable_resources): ?>
                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">
                                <i class="fas fa-download mr-2"></i>Downloadable Resources
                            </h3>
                            <div class="space-y-2">
                                <?php 
                                $resources = array_filter(explode("\n", $downloadable_resources));
                                foreach ($resources as $resource): 
                                    $resource = trim($resource);
                                    if (!empty($resource)):
                                ?>
                                    <a href="<?php echo esc_url($resource); ?>" 
                                       target="_blank" 
                                       class="flex items-center text-blue-700 hover:text-blue-900 transition-colors">
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        <?php echo basename(parse_url($resource, PHP_URL_PATH)) ?: 'Download Resource'; ?>
                                    </a>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        
                        <!-- Mark Complete Button -->
                        <?php if ($lesson_status !== 'completed'): ?>
                            <button id="mark-complete-btn" 
                                    class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-check mr-2"></i>Mark as Complete
                            </button>
                        <?php else: ?>
                            <div class="flex-1 px-6 py-3 bg-green-100 text-green-800 rounded-lg text-center">
                                <i class="fas fa-check-circle mr-2"></i>Completed
                            </div>
                        <?php endif; ?>
                        
                        <!-- Bookmark Button -->
                        <button id="bookmark-btn" 
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                                data-content-id="<?php echo $lesson_id; ?>" 
                                data-content-type="lesson">
                            <i class="fas fa-heart mr-2"></i>Bookmark
                        </button>
                        
                        <!-- Share Button -->
                        <button id="share-btn" 
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                            <i class="fas fa-share mr-2"></i>Share
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- Navigation -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Navigation</h3>
                    
                    <!-- Previous Lesson -->
                    <?php if ($prev_lesson): ?>
                        <a href="<?php echo get_permalink($prev_lesson->ID); ?>" 
                           class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors mb-3">
                            <i class="fas fa-chevron-left mr-3 text-primary"></i>
                            <div>
                                <div class="text-xs text-gray-500">Previous</div>
                                <div class="font-medium text-gray-900"><?php echo esc_html($prev_lesson->post_title); ?></div>
                            </div>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Next Lesson -->
                    <?php if ($next_lesson): ?>
                        <a href="<?php echo get_permalink($next_lesson->ID); ?>" 
                           class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex-1">
                                <div class="text-xs text-gray-500">Next</div>
                                <div class="font-medium text-gray-900"><?php echo esc_html($next_lesson->post_title); ?></div>
                            </div>
                            <i class="fas fa-chevron-right ml-3 text-primary"></i>
                        </a>
                    <?php else: ?>
                        <div class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="text-sm text-gray-500 text-center">
                                <i class="fas fa-trophy mr-2"></i>You've reached the end!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Knowledge Areas -->
                <?php if (!empty($knowledge_areas)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Knowledge Areas</h3>
                        <div class="space-y-2">
                            <?php foreach ($knowledge_areas as $ka): ?>
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    <?php echo esc_html($ka->name); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Related Content -->
                <?php if (!empty($related_content)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Content</h3>
                        <div class="space-y-3">
                            <?php foreach ($related_content as $related): ?>
                                <a href="<?php echo get_permalink($related->ID); ?>" 
                                   class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="font-medium text-gray-900 text-sm mb-1">
                                        <?php echo esc_html($related->post_title); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo ucfirst($related->post_type); ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Track lesson start time
let lessonStartTime = Date.now();

// Mark as complete functionality
document.getElementById('mark-complete-btn')?.addEventListener('click', function() {
    const timeSpent = Math.round((Date.now() - lessonStartTime) / 60000); // Convert to minutes
    
    fetch('/wp-json/pmp/v1/progress/lesson', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify({
            lesson_id: <?php echo $lesson_id; ?>,
            status: 'completed',
            progress_percentage: 100,
            time_spent: timeSpent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});

// Bookmark functionality
document.getElementById('bookmark-btn').addEventListener('click', function() {
    const contentId = this.dataset.contentId;
    const contentType = this.dataset.contentType;
    
    fetch('/wp-json/pmp/v1/bookmark', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify({
            content_id: contentId,
            content_type: contentType,
            bookmark_type: 'favorite'
        })
    })
    .then(response => response.json())
    .then(data => {
        const icon = this.querySelector('i');
        if (data.action === 'added') {
            icon.classList.remove('far');
            icon.classList.add('fas');
            this.classList.add('text-red-600', 'border-red-300');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            this.classList.remove('text-red-600', 'border-red-300');
        }
    });
});

// Share functionality
document.getElementById('share-btn').addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo esc_js(get_the_title()); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
});

// Auto-save progress every 30 seconds
setInterval(() => {
    const timeSpent = Math.round((Date.now() - lessonStartTime) / 60000);
    const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
    
    fetch('/wp-json/pmp/v1/progress/lesson', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify({
            lesson_id: <?php echo $lesson_id; ?>,
            status: 'in_progress',
            progress_percentage: Math.min(scrollPercent, 95), // Max 95% until marked complete
            time_spent: timeSpent
        })
    });
}, 30000);
</script>

<style>
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
}

.aspect-w-16 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

.prose {
    color: #374151;
    line-height: 1.75;
}

.prose h1, .prose h2, .prose h3, .prose h4 {
    color: #111827;
    font-weight: 600;
}

.prose p {
    margin-bottom: 1.25em;
}

.prose ul, .prose ol {
    margin-bottom: 1.25em;
    padding-left: 1.625em;
}

.prose li {
    margin-bottom: 0.5em;
}
</style>
