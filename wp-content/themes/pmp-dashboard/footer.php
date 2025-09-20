    <!-- Footer -->
    <footer class="bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-2">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mohlomi_institute_logo.png" alt="Mohlomi Institute" class="h-10 mb-4 max-w-60">
                    <p class="text-gray-600 mb-4">Mohlomi Institute provides focused, practical, and ethical training solutions for project management professionals worldwide.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><a href="<?php echo home_url(); ?>" class="hover:text-primary transition-colors">Home</a></li>
                        <li><a href="<?php echo get_post_type_archive_link('work_group'); ?>" class="hover:text-primary transition-colors">Courses</a></li>
                        <li><a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="hover:text-primary transition-colors">My Learning</a></li>
                        <li><a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" class="hover:text-primary transition-colors">Resources</a></li>
                        <li><a href="<?php echo get_permalink(get_page_by_path('community')); ?>" class="hover:text-primary transition-colors">Community</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><a href="#" class="hover:text-primary transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-gray-300 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 mb-4 md:mb-0">© <?php echo date('Y'); ?> Mohlomi Institute. All rights reserved.</p>
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                    <span>PMP® is a registered trademark of PMI</span>
                    <span>•</span>
                    <span>Made with ❤️ for PMP candidates</span>
                </div>
            </div>
        </div>
    </footer>

    <style>
        footer img {
            height: auto;
            max-width: 100%;
        }
    </style>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');

        function showSidebar() {
            if (sidebar) sidebar.classList.remove('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
        }

        function hideSidebar() {
            if (sidebar) sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }

        if (openSidebar) openSidebar.addEventListener('click', showSidebar);
        if (closeSidebar) closeSidebar.addEventListener('click', hideSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', hideSidebar);

        // User account dropdown
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userAccountDropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        // Work Groups accordion
        function toggleAccordion(wgId) {
            const content = document.getElementById(wgId + '-content');
            const icon = document.getElementById(wgId + '-icon');
            
            if (content && icon) {
                content.classList.toggle('hidden');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        }

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
    
    <?php wp_footer(); ?>
</body>
</html>
