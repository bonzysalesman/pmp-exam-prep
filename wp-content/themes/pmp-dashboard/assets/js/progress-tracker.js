/**
 * Progress Tracker JavaScript
 * 
 * Handles real-time progress updates and user interactions
 */

class ProgressTracker {
    constructor() {
        this.apiBase = '/wp-json/pmp/v1';
        this.nonce = pmp_ajax.nonce;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadUserProgress();
    }
    
    bindEvents() {
        // Lesson completion buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.complete-lesson-btn')) {
                e.preventDefault();
                this.handleLessonCompletion(e.target);
            }
        });
        
        // Progress update forms
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.lesson-progress-form')) {
                e.preventDefault();
                this.handleProgressForm(e.target);
            }
        });
    }
    
    async handleLessonCompletion(button) {
        const lessonId = button.dataset.lessonId;
        const timeSpent = parseInt(button.dataset.timeSpent) || 20;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        try {
            const response = await this.updateLessonProgress(lessonId, 'completed', 100, timeSpent);
            
            if (response.success) {
                this.showNotification('Lesson completed! Great job!', 'success');
                button.innerHTML = '<i class="fas fa-check"></i> Completed';
                button.classList.remove('bg-primary');
                button.classList.add('bg-green-500');
                
                // Update progress displays
                this.refreshProgressDisplays();
            } else {
                throw new Error(response.message || 'Failed to update progress');
            }
        } catch (error) {
            console.error('Error updating lesson progress:', error);
            this.showNotification('Failed to update progress. Please try again.', 'error');
            button.disabled = false;
            button.innerHTML = 'Complete Lesson';
        }
    }
    
    async handleProgressForm(form) {
        const formData = new FormData(form);
        const lessonId = formData.get('lesson_id');
        const status = formData.get('status');
        const progressPercentage = parseFloat(formData.get('progress_percentage')) || 0;
        const timeSpent = parseInt(formData.get('time_spent')) || 0;
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        try {
            const response = await this.updateLessonProgress(lessonId, status, progressPercentage, timeSpent);
            
            if (response.success) {
                this.showNotification('Progress saved successfully!', 'success');
                this.refreshProgressDisplays();
            } else {
                throw new Error(response.message || 'Failed to save progress');
            }
        } catch (error) {
            console.error('Error saving progress:', error);
            this.showNotification('Failed to save progress. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
    
    async updateLessonProgress(lessonId, status, progressPercentage = 0, timeSpent = 0) {
        const response = await fetch(`${this.apiBase}/progress/lesson`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': this.nonce
            },
            body: JSON.stringify({
                lesson_id: parseInt(lessonId),
                status: status,
                progress_percentage: progressPercentage,
                time_spent_minutes: timeSpent
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    async loadUserProgress() {
        try {
            const userId = this.getCurrentUserId();
            if (!userId) return;
            
            const response = await fetch(`${this.apiBase}/progress/${userId}`, {
                headers: {
                    'X-WP-Nonce': this.nonce
                }
            });
            
            if (response.ok) {
                const progressData = await response.json();
                this.updateProgressDisplays(progressData);
            }
        } catch (error) {
            console.error('Error loading user progress:', error);
        }
    }
    
    async refreshProgressDisplays() {
        // Reload progress data and update displays
        await this.loadUserProgress();
        
        // Refresh streak data
        this.loadStreakData();
    }
    
    async loadStreakData() {
        try {
            const userId = this.getCurrentUserId();
            if (!userId) return;
            
            const response = await fetch(`${this.apiBase}/streak/${userId}`, {
                headers: {
                    'X-WP-Nonce': this.nonce
                }
            });
            
            if (response.ok) {
                const streakData = await response.json();
                this.updateStreakDisplay(streakData);
            }
        } catch (error) {
            console.error('Error loading streak data:', error);
        }
    }
    
    updateProgressDisplays(progressData) {
        // Update progress bars
        document.querySelectorAll('[data-progress-bar]').forEach(bar => {
            const domain = bar.dataset.domain;
            if (progressData.domains[domain]) {
                const percentage = progressData.domains[domain].completion_percentage;
                bar.style.width = `${percentage}%`;
                bar.setAttribute('aria-valuenow', percentage);
            }
        });
        
        // Update progress text
        document.querySelectorAll('[data-progress-text]').forEach(text => {
            const domain = text.dataset.domain;
            if (progressData.domains[domain]) {
                const percentage = progressData.domains[domain].completion_percentage;
                text.textContent = `${percentage.toFixed(1)}%`;
            }
        });
        
        // Update overall progress
        const overallProgress = document.querySelector('[data-overall-progress]');
        if (overallProgress) {
            overallProgress.textContent = `${progressData.overall_progress.toFixed(1)}%`;
        }
    }
    
    updateStreakDisplay(streakData) {
        // Update streak counter
        const streakCounter = document.querySelector('[data-streak-counter]');
        if (streakCounter) {
            streakCounter.textContent = streakData.current_streak;
        }
        
        // Update streak message
        const streakMessage = document.querySelector('[data-streak-message]');
        if (streakMessage) {
            streakMessage.textContent = streakData.streak_message;
        }
        
        // Update milestone progress
        const milestoneProgress = document.querySelector('[data-milestone-progress]');
        if (milestoneProgress && streakData.next_milestone) {
            const progress = (streakData.current_streak / streakData.next_milestone) * 100;
            milestoneProgress.style.width = `${progress}%`;
        }
    }
    
    getCurrentUserId() {
        // Get current user ID from global variable or data attribute
        return window.pmpCurrentUserId || document.body.dataset.userId;
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
        
        // Set notification style based on type
        switch (type) {
            case 'success':
                notification.classList.add('bg-green-500', 'text-white');
                break;
            case 'error':
                notification.classList.add('bg-red-500', 'text-white');
                break;
            default:
                notification.classList.add('bg-blue-500', 'text-white');
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-2"></i>
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof pmp_ajax !== 'undefined') {
        window.progressTracker = new ProgressTracker();
    }
});

// Export for use in other scripts
window.ProgressTracker = ProgressTracker;
