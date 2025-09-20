/**
 * PMP Dashboard JavaScript
 */

jQuery(document).ready(function($) {
    // Sidebar functionality
    const sidebar = $('#sidebar');
    const sidebarOverlay = $('#sidebarOverlay');
    const openSidebar = $('#openSidebar');
    const closeSidebar = $('#closeSidebar');

    function showSidebar() {
        sidebar.removeClass('-translate-x-full');
        sidebarOverlay.removeClass('hidden');
    }

    function hideSidebar() {
        sidebar.addClass('-translate-x-full');
        sidebarOverlay.addClass('hidden');
    }

    if (openSidebar.length) openSidebar.on('click', showSidebar);
    if (closeSidebar.length) closeSidebar.on('click', hideSidebar);
    if (sidebarOverlay.length) sidebarOverlay.on('click', hideSidebar);

    // User account dropdown
    window.toggleUserDropdown = function() {
        $('#userAccountDropdown').toggleClass('hidden');
    };

    // Close dropdown when clicking outside
    $(document).on('click', function(event) {
        const dropdown = $('#userAccountDropdown');
        const button = $(event.target).closest('button');
        if (!button.length || button.attr('onclick') !== 'toggleUserDropdown()') {
            dropdown.addClass('hidden');
        }
    });

    // Work Groups accordion
    window.toggleAccordion = function(wgId) {
        const content = $('#' + wgId + '-content');
        const icon = $('#' + wgId + '-icon');
        
        if (content.length && icon.length) {
            content.toggleClass('hidden');
            icon.toggleClass('fa-chevron-down fa-chevron-up');
        }
    };

    // In-page navigation
    $('header nav a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        // Update active state
        $('header nav a[href^="#"]').removeClass('text-primary border-b-2 border-primary')
                                   .addClass('text-gray-500 hover:text-primary');
        $(this).removeClass('text-gray-500 hover:text-primary')
               .addClass('text-primary border-b-2 border-primary');
        
        // Smooth scroll to section
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 500);
        }
    });

    // Lesson progress tracking
    window.updateLessonProgress = function(lessonId, progressData) {
        $.post(pmp_ajax.ajax_url, {
            action: 'update_lesson_progress',
            nonce: pmp_ajax.nonce,
            lesson_id: lessonId,
            progress_percentage: progressData.percentage || 0,
            time_spent: progressData.timeSpent || 0,
            status: progressData.status || 'in_progress'
        }, function(response) {
            if (response.success) {
                // Update UI with new progress data
                updateProgressUI(response.data);
                
                // Show success notification
                showNotification('Progress updated successfully!', 'success');
            } else {
                showNotification('Failed to update progress', 'error');
            }
        });
    };

    // Update progress UI elements
    function updateProgressUI(data) {
        if (data.study_streak !== undefined) {
            $('.study-streak').text(data.study_streak + ' days');
        }
        if (data.week_time !== undefined) {
            $('.week-time').text(data.week_time + 'h');
        }
        if (data.completion_percentage !== undefined) {
            $('.overall-progress').text(data.completion_percentage + '%');
            
            // Update progress bars
            $('.progress-bar').each(function() {
                const percentage = $(this).data('percentage') || data.completion_percentage;
                $(this).find('.progress-fill').css('width', percentage + '%');
            });
        }
    }

    // Video progress tracking
    window.trackVideoProgress = function(videoElement, lessonId) {
        let lastUpdate = 0;
        let totalWatched = 0;
        
        $(videoElement).on('timeupdate', function() {
            const currentTime = this.currentTime;
            const duration = this.duration;
            
            if (currentTime - lastUpdate > 5) { // Update every 5 seconds
                totalWatched += 5;
                lastUpdate = currentTime;
                
                const percentage = Math.round((currentTime / duration) * 100);
                
                updateLessonProgress(lessonId, {
                    percentage: percentage,
                    timeSpent: 5,
                    status: percentage >= 90 ? 'completed' : 'in_progress'
                });
            }
        });
        
        $(videoElement).on('ended', function() {
            updateLessonProgress(lessonId, {
                percentage: 100,
                timeSpent: 0,
                status: 'completed'
            });
        });
    };

    // Practice test functionality
    window.submitPracticeTest = function(testId, answers, timeSpent) {
        $.post(pmp_ajax.ajax_url, {
            action: 'submit_practice_test',
            nonce: pmp_ajax.nonce,
            test_id: testId,
            answers: JSON.stringify(answers),
            time_taken: timeSpent
        }, function(response) {
            if (response.success) {
                showTestResults(response.data);
            } else {
                showNotification('Failed to submit test', 'error');
            }
        });
    };

    // Show test results
    function showTestResults(results) {
        const modal = $(`
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 max-w-md mx-4">
                    <h3 class="text-lg font-semibold mb-4">Test Results</h3>
                    <div class="text-center mb-4">
                        <div class="text-3xl font-bold ${results.passed ? 'text-green-600' : 'text-red-600'} mb-2">
                            ${results.percentage}%
                        </div>
                        <p class="text-gray-600">
                            ${results.score} correct answers
                        </p>
                        <p class="text-sm ${results.passed ? 'text-green-600' : 'text-red-600'} mt-2">
                            ${results.passed ? 'Passed!' : 'Keep studying!'}
                        </p>
                    </div>
                    <button onclick="$(this).closest('.fixed').remove()" 
                            class="w-full bg-primary text-white py-2 rounded-lg hover:bg-primary-dark">
                        Continue
                    </button>
                </div>
            </div>
        `);
        
        $('body').append(modal);
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        const notification = $(`
            <div class="fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg z-50 notification">
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 3000);
    }

    // Auto-save functionality for forms
    $('form[data-autosave]').each(function() {
        const form = $(this);
        const formId = form.data('autosave');
        
        // Load saved data
        const savedData = localStorage.getItem('pmp_form_' + formId);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                form.find(`[name="${key}"]`).val(data[key]);
            });
        }
        
        // Save on change
        form.on('change input', function() {
            const formData = {};
            form.serializeArray().forEach(item => {
                formData[item.name] = item.value;
            });
            localStorage.setItem('pmp_form_' + formId, JSON.stringify(formData));
        });
    });

    // Study timer
    let studyTimer = null;
    let studyStartTime = null;
    
    window.startStudySession = function() {
        studyStartTime = Date.now();
        studyTimer = setInterval(updateStudyTime, 1000);
    };
    
    window.endStudySession = function() {
        if (studyTimer) {
            clearInterval(studyTimer);
            const timeSpent = Math.floor((Date.now() - studyStartTime) / 1000);
            
            // Save study session
            $.post(pmp_ajax.ajax_url, {
                action: 'update_study_session',
                nonce: pmp_ajax.nonce,
                time_spent: timeSpent
            });
            
            studyTimer = null;
            studyStartTime = null;
        }
    };
    
    function updateStudyTime() {
        if (studyStartTime) {
            const elapsed = Math.floor((Date.now() - studyStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            
            $('.study-timer').text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
        }
    }

    // Initialize study session on page load
    if ($('body').hasClass('single-lesson') || $('body').hasClass('page-dashboard')) {
        startStudySession();
        
        // End session on page unload
        $(window).on('beforeunload', endStudySession);
    }

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});
