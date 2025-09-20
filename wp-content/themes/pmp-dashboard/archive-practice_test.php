<?php
/**
 * Practice Tests Archive Template
 */

get_header();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id = get_current_user_id();
?>

<div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:block lg:flex-shrink-0 flex flex-col">
        <?php get_template_part('template-parts/navigation/sidebar-nav'); ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-6">
                <div class="flex items-center">
                    <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="text-gray-500 hover:text-primary mr-4">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Practice Tests</h1>
                        <p class="text-sm text-gray-500">Test your PMP knowledge and track your progress</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Test Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="text-2xl font-bold text-primary mb-2">12</div>
                        <div class="text-sm text-gray-600">Tests Taken</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="text-2xl font-bold text-green-600 mb-2">87%</div>
                        <div class="text-sm text-gray-600">Average Score</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-2">92%</div>
                        <div class="text-sm text-gray-600">Best Score</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="text-2xl font-bold text-orange-600 mb-2">Ready</div>
                        <div class="text-sm text-gray-600">Exam Status</div>
                    </div>
                </div>

                <!-- Practice Tests Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Work Group Tests -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Work Group Assessments</h3>
                        <div class="space-y-4">
                            <?php
                            $wg_tests = array(
                                array('title' => 'WG1: Building A Team', 'questions' => 25, 'time' => 30, 'status' => 'completed', 'score' => 92),
                                array('title' => 'WG2: Starting the Project', 'questions' => 35, 'time' => 45, 'status' => 'completed', 'score' => 88),
                                array('title' => 'WG3: Doing the Work', 'questions' => 40, 'time' => 50, 'status' => 'available', 'score' => null),
                                array('title' => 'WG4: Keeping on Track', 'questions' => 30, 'time' => 40, 'status' => 'locked', 'score' => null),
                                array('title' => 'WG5: Focus on Business', 'questions' => 20, 'time' => 25, 'status' => 'locked', 'score' => null)
                            );
                            
                            foreach ($wg_tests as $test) :
                                $is_completed = $test['status'] === 'completed';
                                $is_available = $test['status'] === 'available';
                                $is_locked = $test['status'] === 'locked';
                            ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg <?php echo $is_locked ? 'opacity-50' : ''; ?>">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900"><?php echo esc_html($test['title']); ?></h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo esc_html($test['questions']); ?> questions • <?php echo esc_html($test['time']); ?> minutes
                                        </p>
                                        <?php if ($is_completed) : ?>
                                            <p class="text-sm text-green-600 font-medium">Score: <?php echo esc_html($test['score']); ?>%</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <?php if ($is_completed) : ?>
                                            <i class="fas fa-check-circle text-green-600"></i>
                                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                                Retake
                                            </button>
                                        <?php elseif ($is_available) : ?>
                                            <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition-colors">
                                                Start Test
                                            </button>
                                        <?php else : ?>
                                            <i class="fas fa-lock text-gray-400"></i>
                                            <button class="bg-gray-400 text-white px-4 py-2 rounded-lg text-sm cursor-not-allowed">
                                                Locked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Full Practice Exams -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Full Practice Exams</h3>
                        <div class="space-y-4">
                            <?php
                            $full_exams = array(
                                array('title' => 'Practice Exam 1', 'questions' => 180, 'time' => 230, 'status' => 'completed', 'score' => 85),
                                array('title' => 'Practice Exam 2', 'questions' => 180, 'time' => 230, 'status' => 'available', 'score' => null),
                                array('title' => 'Practice Exam 3', 'questions' => 180, 'time' => 230, 'status' => 'available', 'score' => null),
                                array('title' => 'Final Mock Exam', 'questions' => 180, 'time' => 230, 'status' => 'locked', 'score' => null)
                            );
                            
                            foreach ($full_exams as $exam) :
                                $is_completed = $exam['status'] === 'completed';
                                $is_available = $exam['status'] === 'available';
                                $is_locked = $exam['status'] === 'locked';
                            ?>
                                <div class="flex items-center justify-between p-4 border-2 border-purple-200 rounded-lg <?php echo $is_locked ? 'opacity-50' : ''; ?>">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900"><?php echo esc_html($exam['title']); ?></h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo esc_html($exam['questions']); ?> questions • <?php echo esc_html($exam['time']); ?> minutes
                                        </p>
                                        <?php if ($is_completed) : ?>
                                            <p class="text-sm text-green-600 font-medium">Score: <?php echo esc_html($exam['score']); ?>%</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <?php if ($is_completed) : ?>
                                            <i class="fas fa-check-circle text-green-600"></i>
                                            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition-colors">
                                                Retake
                                            </button>
                                        <?php elseif ($is_available) : ?>
                                            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition-colors">
                                                Start Exam
                                            </button>
                                        <?php else : ?>
                                            <i class="fas fa-lock text-gray-400"></i>
                                            <button class="bg-gray-400 text-white px-4 py-2 rounded-lg text-sm cursor-not-allowed">
                                                Locked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Practice -->
                <div class="mt-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Quick Practice</h3>
                            <p class="text-blue-100 mb-4">Test specific topics or take a random quiz</p>
                            <div class="flex space-x-4">
                                <button class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-random mr-2"></i>Random Quiz
                                </button>
                                <button class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-blue-600 transition-colors">
                                    <i class="fas fa-filter mr-2"></i>Topic Quiz
                                </button>
                            </div>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-brain text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <?php get_template_part('template-parts/navigation/mobile-nav'); ?>
</div>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<?php get_footer(); ?>
