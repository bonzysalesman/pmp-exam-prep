<?php
/**
 * Homepage Template
 */

get_header(); ?>

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-6 py-2 flex items-center justify-between border-b border-border-color">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" alt="PMP Exam Prep" class="w-20 h-auto">
        <div class="hidden md:flex space-x-8">
            <a href="<?php echo home_url(); ?>" class="text-primary font-medium text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2">Home</a>
            <a href="<?php echo get_post_type_archive_link('work_group'); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Courses</a>
            <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">My Learning</a>
            <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Resources</a>
            <a href="<?php echo get_permalink(get_page_by_path('community')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 border-r border-dotted border-black border-opacity-10 pr-2 transition-colors duration-300">Community</a>
            <a href="<?php echo get_permalink(get_page_by_path('about')); ?>" class="text-secondary hover:text-primary font-light text-lg px-4 py-2 transition-colors duration-300">About</a>
        </div>
        <div class="flex items-center space-x-2">
            <?php if (is_user_logged_in()) : ?>
                <div class="relative">
                    <button class="flex items-center text-secondary hover:text-primary px-3 py-2 transition-colors duration-300" onclick="toggleDropdown()">
                        <span class="font-bold">MY ACCOUNT</span>
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div id="accountDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">Dashboard</a>
                        <a href="<?php echo get_edit_user_link(); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <hr class="my-1">
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">Logout</a>
                    </div>
                </div>
            <?php else : ?>
                <a href="<?php echo wp_login_url(); ?>" class="text-secondary hover:text-primary px-3 py-2 transition-colors duration-300">Login</a>
                <a href="<?php echo wp_registration_url(); ?>" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors duration-300">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- Hero Section -->
<section class="min-h-screen flex items-center relative overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/pmp_one.jpeg'); mask-image: linear-gradient(to left, rgba(0, 0, 0, 1), rgba(0, 0, 0, 0));"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-100 opacity-90"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
        <h1 class="text-5xl xl:text-6xl font-black leading-tight text-secondary mb-6 uppercase" style="line-height: 1.1618;">
            Pass Your PMP® Exam the First Time: 
            <span class="text-primary">Practical, ECO-Focused Prep</span>
        </h1>
        <p class="text-xl font-light text-secondary mb-8 max-w-2xl mx-auto leading-normal">
            Stop wasting time and money. Get the clear, structured guidance you need, based *directly* on the official PMP® Exam Content Outline (ECO). Designed for serious first-time passers by a fellow PMP® candidate.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="bg-primary text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-primary-dark transition-colors duration-300 shadow-lg">
                    Continue Learning
                </a>
            <?php else : ?>
                <a href="<?php echo wp_registration_url(); ?>" class="bg-primary text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-primary-dark transition-colors duration-300 shadow-lg">
                    Start Your Journey
                </a>
            <?php endif; ?>
            <a href="#features" class="border-2 border-primary text-primary px-8 py-4 rounded-lg text-lg font-semibold hover:bg-primary hover:text-white transition-colors duration-300">
                Learn More
            </a>
        </div>
        
        <!-- Trust Indicators -->
        <div class="flex flex-wrap justify-center items-center gap-8 text-sm text-gray-600">
            <div class="flex items-center">
                <i class="fas fa-users text-primary mr-2"></i>
                <span>1,000+ Students</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                <span>4.9/5 Rating</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-certificate text-primary mr-2"></i>
                <span>95% Pass Rate</span>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-secondary mb-4">Why Choose Our PMP Prep?</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Our comprehensive approach combines the latest ECO guidelines with practical, real-world application.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-open text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">ECO-Aligned Content</h3>
                <p class="text-gray-600">
                    Every lesson directly maps to the official PMP Exam Content Outline for focused, relevant study.
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">Progress Tracking</h3>
                <p class="text-gray-600">
                    Monitor your learning journey with detailed analytics and personalized study recommendations.
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-mobile-alt text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">Mobile Learning</h3>
                <p class="text-gray-600">
                    Study anywhere with our responsive design and offline-capable progressive web app.
                </p>
            </div>
            
            <!-- Feature 4 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">Practice Tests</h3>
                <p class="text-gray-600">
                    Comprehensive practice exams with detailed explanations and performance analytics.
                </p>
            </div>
            
            <!-- Feature 5 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">Community Support</h3>
                <p class="text-gray-600">
                    Connect with fellow PMP candidates and get support from experienced project managers.
                </p>
            </div>
            
            <!-- Feature 6 -->
            <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-2xl text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary mb-3">13-Week Plan</h3>
                <p class="text-gray-600">
                    Structured 13-week study plan with daily goals and milestone tracking.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Work Groups Preview -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-secondary mb-4">Our 5 Work Groups</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Comprehensive coverage of all PMP domains through structured Work Groups.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $work_groups = get_posts(array(
                'post_type' => 'work_group',
                'posts_per_page' => 5,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ));
            
            $wg_colors = array('bg-green-500', 'bg-blue-500', 'bg-blue-600', 'bg-green-600', 'bg-orange-500');
            $wg_icons = array('fas fa-users', 'fas fa-rocket', 'fas fa-cogs', 'fas fa-chart-line', 'fas fa-building');
            
            foreach ($work_groups as $index => $wg) :
                $color = $wg_colors[$index] ?? 'bg-primary';
                $icon = $wg_icons[$index] ?? 'fas fa-circle';
            ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 <?php echo esc_attr($color); ?> rounded-full flex items-center justify-center mr-4">
                            <i class="<?php echo esc_attr($icon); ?> text-white text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-secondary"><?php echo esc_html($wg->post_title); ?></h3>
                    </div>
                    <p class="text-gray-600 mb-4"><?php echo esc_html(wp_trim_words($wg->post_excerpt ?: $wg->post_content, 20)); ?></p>
                    <a href="<?php echo get_permalink($wg->ID); ?>" class="text-primary hover:text-primary-dark font-medium">
                        Learn More <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-primary text-white">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-4">Ready to Pass Your PMP Exam?</h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Join thousands of successful PMP candidates who chose our proven study system.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="bg-white text-primary px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors duration-300">
                    Go to Dashboard
                </a>
            <?php else : ?>
                <a href="<?php echo wp_registration_url(); ?>" class="bg-white text-primary px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors duration-300">
                    Start Free Trial
                </a>
                <a href="<?php echo wp_login_url(); ?>" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary transition-colors duration-300">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function toggleDropdown() {
    document.getElementById('accountDropdown').classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('accountDropdown');
    const button = event.target.closest('button');
    if (!button || button.getAttribute('onclick') !== 'toggleDropdown()') {
        if (dropdown) dropdown.classList.add('hidden');
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php get_footer(); ?>
