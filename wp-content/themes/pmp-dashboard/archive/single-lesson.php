<?php
/**
 * Single Lesson Template
 */

get_header();

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id = get_current_user_id();
$lesson_id = get_the_ID();
$lesson_progress = pmp_get_lesson_progress($user_id, $lesson_id);

// Get lesson meta
$video_url = get_post_meta($lesson_id, 'lesson_video_url', true);
$duration = get_post_meta($lesson_id, 'lesson_duration', true);
$objectives = get_post_meta($lesson_id, 'lesson_objectives', true);
$work_group_id = get_post_meta($lesson_id, 'work_group_id', true);

// Get Work Group for breadcrumbs
$work_group = $work_group_id ? get_post($work_group_id) : null;
?>

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:block lg:flex-shrink-0 flex flex-col">
        <div class="flex items-center justify-between h-16 px-4 lg:px-6 border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" alt="PMP Exam Prep" class="w-16 lg:w-20 h-auto">
                <span class="ml-2 lg:ml-3 text-base lg:text-lg font-semibold text-secondary hidden sm:block">LESSON</span>
            </div>
            <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto" style="background: #FFF;">
            <!-- Lesson Progress -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="bg-primary bg-opacity-10 rounded-lg p-3 sm:p-4 border-l-4 border-primary">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs sm:text-sm font-semibold text-primary">Day 43</h4>
                        <span class="text-xs text-primary font-medium">5 of 9 lessons</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div class="bg-primary h-2 rounded-full" style="width: <?php echo esc_attr($lesson_progress['progress_percentage']); ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-600"><?php the_title(); ?></p>
                </div>
            </div>

            <!-- Lesson Navigation -->
            <nav class="mt-4">
                <div class="px-6 mb-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <?php echo $work_group ? esc_html($work_group->post_title) : 'WG3: Doing the Work'; ?>
                    </h3>
                </div>
                
                <?php
                // Get all lessons in this Work Group
                $all_lessons = get_posts(array(
                    'post_type' => 'lesson',
                    'meta_query' => array(
                        array(
                            'key' => 'work_group_id',
                            'value' => $work_group_id ?: 3,
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'posts_per_page' => -1
                ));
                
                if (empty($all_lessons)) {
                    // Default lesson structure for demo
                    $default_lessons = array(
                        'Assess & Manage Risks' => 'completed',
                        'Execute for Value' => 'completed',
                        'Manage Communications' => 'completed',
                        'Engage Stakeholders' => 'completed',
                        'Manage Project Artefacts' => 'current',
                        'Manage Project Changes' => 'upcoming',
                        'Manage Project Issues' => 'upcoming',
                        'Knowledge Transfer' => 'upcoming',
                        'Manage Suppliers' => 'upcoming'
                    );
                    
                    echo '<div class="space-y-1">';
                    foreach ($default_lessons as $lesson_title => $status) :
                        $is_current = $lesson_title === get_the_title();
                        $link_class = $is_current ? 'text-primary font-medium bg-primary bg-opacity-10 border-r-2 border-primary' : 'text-gray-600';
                        
                        if ($status === 'completed') {
                            $icon = 'fas fa-check-circle text-green-600';
                        } elseif ($status === 'current') {
                            $icon = 'fas fa-play-circle text-primary';
                        } else {
                            $icon = 'fas fa-circle text-gray-400';
                        }
                    ?>
                        <a href="#" class="flex items-center px-6 py-2 text-sm <?php echo esc_attr($link_class); ?>">
                            <i class="<?php echo esc_attr($icon); ?> w-4 mr-3"></i>
                            <?php echo esc_html($lesson_title); ?>
                        </a>
                    <?php endforeach;
                    echo '</div>';
                } else {
                    echo '<div class="space-y-1">';
                    foreach ($all_lessons as $lesson) :
                        $is_current = $lesson->ID === $lesson_id;
                        $lesson_prog = pmp_get_lesson_progress($user_id, $lesson->ID);
                        $link_class = $is_current ? 'text-primary font-medium bg-primary bg-opacity-10 border-r-2 border-primary' : 'text-gray-600';
                        
                        if ($lesson_prog['status'] === 'completed') {
                            $icon = 'fas fa-check-circle text-green-600';
                        } elseif ($lesson_prog['status'] === 'in_progress' || $is_current) {
                            $icon = 'fas fa-play-circle text-primary';
                        } else {
                            $icon = 'fas fa-circle text-gray-400';
                        }
                    ?>
                        <a href="<?php echo get_permalink($lesson->ID); ?>" class="flex items-center px-6 py-2 text-sm <?php echo esc_attr($link_class); ?>">
                            <i class="<?php echo esc_attr($icon); ?> w-4 mr-3"></i>
                            <?php echo esc_html($lesson->post_title); ?>
                        </a>
                    <?php endforeach;
                    echo '</div>';
                }
                ?>
            </nav>

            <!-- Quick Actions -->
            <div class="px-6 mt-8 pt-6 border-t border-gray-200 pb-6">
                <div class="space-y-2">
                    <button onclick="bookmarkLesson(<?php echo $lesson_id; ?>)" class="w-full bg-primary text-white py-2 px-4 rounded-lg text-sm hover:bg-primary-dark transition-colors">
                        <i class="fas fa-bookmark mr-2"></i>Bookmark
                    </button>
                    <button onclick="openNotes()" class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                        <i class="fas fa-note-sticky mr-2"></i>Take Notes
                    </button>
                    <button onclick="askQuestion()" class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                        <i class="fas fa-question-circle mr-2"></i>Ask Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-6">
                <div class="flex items-center">
                    <a href="<?php echo $work_group ? get_permalink($work_group->ID) : get_post_type_archive_link('work_group'); ?>" class="text-gray-500 hover:text-primary mr-4">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900"><?php the_title(); ?></h1>
                        <p class="text-sm text-gray-500">
                            <?php echo $work_group ? esc_html($work_group->post_title) : 'WG3: Doing the Work'; ?> • Day 43 • <?php echo esc_html($duration ?: '15'); ?> min read
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="text-gray-500 hover:text-primary">
                        <i class="fas fa-print text-lg"></i>
                    </button>
                    <button onclick="shareLesson()" class="text-gray-500 hover:text-primary">
                        <i class="fas fa-share text-lg"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Lesson Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Breadcrumbs -->
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                <nav class="flex text-sm text-gray-600">
                    <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="hover:text-primary">Dashboard</a>
                    <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                    <a href="<?php echo $work_group ? get_permalink($work_group->ID) : '#'; ?>" class="hover:text-primary">
                        <?php echo $work_group ? esc_html($work_group->post_title) : 'WG3: Doing the Work'; ?>
                    </a>
                    <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                    <span class="text-gray-900"><?php the_title(); ?></span>
                </nav>
            </div>

            <div class="max-w-4xl mx-auto px-6 py-8">
                <!-- Video Section -->
                <?php if ($video_url) : ?>
                    <div class="mb-8">
                        <div class="aspect-w-16 aspect-h-9 bg-gray-900 rounded-lg overflow-hidden">
                            <iframe 
                                src="<?php echo esc_url($video_url); ?>" 
                                title="<?php the_title(); ?> - PMP Lesson"
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                class="w-full h-full"
                                style="aspect-ratio: 16/9; min-height: 400px;"
                                onloadstart="trackVideoProgress(this, <?php echo $lesson_id; ?>)">
                            </iframe>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Lesson Video</h2>
                                <p class="text-sm text-gray-600">Duration: <?php echo esc_html($duration ?: '12:34'); ?> • Watch at your own pace</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button class="text-gray-500 hover:text-primary">
                                    <i class="fas fa-closed-captioning text-lg"></i>
                                </button>
                                <button class="text-gray-500 hover:text-primary">
                                    <i class="fas fa-cog text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Learning Objectives -->
                <?php if ($objectives) : ?>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-8">
                        <h2 class="text-lg font-semibold text-blue-900 mb-3">Learning Objectives</h2>
                        <ul class="space-y-2 text-blue-800">
                            <?php foreach ($objectives as $objective) : ?>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-blue-600 mt-1 mr-3 flex-shrink-0"></i>
                                    <?php echo esc_html($objective); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Main Content -->
                <div class="prose prose-lg max-w-none">
                    <?php the_content(); ?>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                    <a href="#" class="flex items-center text-gray-600 hover:text-primary transition-colors">
                        <i class="fas fa-chevron-left mr-2"></i>
                        Previous Lesson
                    </a>
                    <button onclick="markLessonComplete(<?php echo $lesson_id; ?>)" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition-colors font-medium">
                        Mark Complete & Continue
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<script>
function markLessonComplete(lessonId) {
    updateLessonProgress(lessonId, {
        percentage: 100,
        timeSpent: 0,
        status: 'completed'
    });
    
    // Redirect to next lesson or Work Group page
    setTimeout(() => {
        window.location.href = '<?php echo $work_group ? get_permalink($work_group->ID) : get_post_type_archive_link('work_group'); ?>';
    }, 1000);
}

function bookmarkLesson(lessonId) {
    // Add bookmark functionality
    showNotification('Lesson bookmarked!', 'success');
}

function openNotes() {
    // Open notes modal or redirect to notes page
    showNotification('Notes feature coming soon!', 'info');
}

function askQuestion() {
    // Open question modal or redirect to community
    showNotification('Question feature coming soon!', 'info');
}

function shareLesson() {
    if (navigator.share) {
        navigator.share({
            title: '<?php the_title(); ?>',
            url: window.location.href
        });
    } else {
        // Fallback to copy URL
        navigator.clipboard.writeText(window.location.href);
        showNotification('Link copied to clipboard!', 'success');
    }
}
</script>

<?php get_footer(); ?>
