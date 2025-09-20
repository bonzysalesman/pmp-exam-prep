<?php
/**
 * Test Taking Interface
 * Task: T020 - Test Taking Interface
 */

// Get test and session data
$test_id = get_the_ID();
$session_id = $_GET['session'] ?? null;
$current_user_id = get_current_user_id();

if (!$current_user_id) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Initialize or get existing session
if (!$session_id) {
    // Start new test session
    $test_data = PMP_Test_Engine::generate_test($test_id, $current_user_id);
    
    if (is_wp_error($test_data)) {
        echo '<div class="error">Error: ' . $test_data->get_error_message() . '</div>';
        return;
    }
    
    $session_id = $test_data['session_id'];
    $questions = $test_data['questions'];
    $time_limit = $test_data['time_limit'];
} else {
    // Get existing session
    $session_status = PMP_Test_Engine::get_session_status($session_id);
    
    if (!$session_status) {
        echo '<div class="error">Invalid test session.</div>';
        return;
    }
    
    if ($session_status['is_completed']) {
        wp_redirect(add_query_arg(['results' => $session_id], get_permalink()));
        exit;
    }
    
    if ($session_status['is_expired']) {
        echo '<div class="error">Test session has expired.</div>';
        return;
    }
    
    // Get session questions
    global $wpdb;
    $session = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
        $session_id
    ));
    
    $session_data = json_decode($session->attempt_data, true);
    $question_ids = $session_data['questions'] ?? [];
    $time_limit = $session_data['time_limit'] ?? 180;
    
    // Get questions with options
    $questions = [];
    foreach ($question_ids as $q_id) {
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_test_questions WHERE id = %d",
            $q_id
        ));
        
        if ($question) {
            $question->options = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pmp_question_options 
                 WHERE question_id = %d ORDER BY option_order",
                $q_id
            ));
            $questions[] = $question;
        }
    }
}

$current_question = intval($_GET['q'] ?? 1);
$current_question = max(1, min($current_question, count($questions)));
$question = $questions[$current_question - 1] ?? null;

if (!$question) {
    echo '<div class="error">Question not found.</div>';
    return;
}
?>

<div class="test-interface bg-gray-50 min-h-screen">
    
    <!-- Test Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                
                <!-- Test Info -->
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold text-gray-900"><?php the_title(); ?></h1>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        Question <?php echo $current_question; ?> of <?php echo count($questions); ?>
                    </span>
                </div>
                
                <!-- Timer -->
                <div class="flex items-center space-x-4">
                    <div id="timer" class="flex items-center text-lg font-mono">
                        <i class="fas fa-clock mr-2 text-primary"></i>
                        <span id="timer-display">--:--</span>
                    </div>
                    
                    <!-- Menu Button -->
                    <button id="test-menu-btn" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                         style="width: <?php echo ($current_question / count($questions)) * 100; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Test Menu Dropdown -->
    <div id="test-menu" class="fixed top-16 right-4 bg-white rounded-lg shadow-lg border z-50 hidden">
        <div class="p-4 w-64">
            <h3 class="font-semibold text-gray-900 mb-3">Test Navigation</h3>
            
            <!-- Question Grid -->
            <div class="grid grid-cols-5 gap-2 mb-4">
                <?php for ($i = 1; $i <= count($questions); $i++): ?>
                    <a href="?session=<?php echo $session_id; ?>&q=<?php echo $i; ?>" 
                       class="w-8 h-8 flex items-center justify-center text-xs rounded
                              <?php echo $i === $current_question 
                                  ? 'bg-primary text-white' 
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            
            <div class="border-t pt-3 space-y-2">
                <button id="flag-question" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                    <i class="fas fa-flag mr-2"></i>Flag Question
                </button>
                <button id="submit-test" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                    <i class="fas fa-check-circle mr-2"></i>Submit Test
                </button>
            </div>
        </div>
    </div>
    
    <!-- Question Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm p-8">
            
            <!-- Question Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                            <?php echo ucfirst(str_replace('_', ' ', $question->question_type)); ?>
                        </span>
                        
                        <?php if ($question->domain): ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                <?php echo ucfirst($question->domain); ?>
                            </span>
                        <?php endif; ?>
                        
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                            <?php echo ucfirst($question->difficulty_level); ?>
                        </span>
                    </div>
                </div>
                
                <button id="question-flag" class="p-2 text-gray-400 hover:text-yellow-500 transition-colors">
                    <i class="fas fa-flag"></i>
                </button>
            </div>
            
            <!-- Question Text -->
            <div class="prose prose-lg max-w-none mb-8">
                <div class="text-lg leading-relaxed text-gray-900">
                    <?php echo nl2br(esc_html($question->question_text)); ?>
                </div>
            </div>
            
            <!-- Answer Options -->
            <form id="question-form" class="space-y-4">
                <?php foreach ($question->options as $index => $option): ?>
                    <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors answer-option">
                        <input type="radio" 
                               name="answer" 
                               value="<?php echo esc_attr($option->option_text); ?>" 
                               class="mt-1 mr-4 text-primary focus:ring-primary"
                               data-option-id="<?php echo $option->id; ?>">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 rounded-full text-sm font-medium mr-3">
                                    <?php echo chr(65 + $index); ?>
                                </span>
                                <span class="font-medium text-gray-900">Option <?php echo chr(65 + $index); ?></span>
                            </div>
                            <div class="text-gray-700 leading-relaxed">
                                <?php echo nl2br(esc_html($option->option_text)); ?>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </form>
        </div>
        
        <!-- Navigation -->
        <div class="flex items-center justify-between mt-8">
            <div>
                <?php if ($current_question > 1): ?>
                    <a href="?session=<?php echo $session_id; ?>&q=<?php echo $current_question - 1; ?>" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-left mr-2"></i>Previous
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-4">
                <button id="save-answer" 
                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Save Answer
                </button>
                
                <?php if ($current_question < count($questions)): ?>
                    <a href="?session=<?php echo $session_id; ?>&q=<?php echo $current_question + 1; ?>" 
                       id="next-question"
                       class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        Next<i class="fas fa-chevron-right ml-2"></i>
                    </a>
                <?php else: ?>
                    <button id="finish-test" 
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Finish Test
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Test interface JavaScript
let sessionId = <?php echo $session_id; ?>;
let questionId = <?php echo $question->id; ?>;
let timeLimit = <?php echo $time_limit * 60; ?>; // Convert to seconds
let questionStartTime = Date.now();

// Timer functionality
let timeRemaining = timeLimit;
let timerInterval;

function startTimer() {
    timerInterval = setInterval(() => {
        timeRemaining--;
        updateTimerDisplay();
        
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            autoSubmitTest();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const hours = Math.floor(timeRemaining / 3600);
    const minutes = Math.floor((timeRemaining % 3600) / 60);
    const seconds = timeRemaining % 60;
    
    const display = hours > 0 
        ? `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        : `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    document.getElementById('timer-display').textContent = display;
    
    // Change color when time is running low
    const timerElement = document.getElementById('timer');
    if (timeRemaining < 300) { // 5 minutes
        timerElement.classList.add('text-red-600');
    } else if (timeRemaining < 900) { // 15 minutes
        timerElement.classList.add('text-yellow-600');
    }
}

// Menu functionality
document.getElementById('test-menu-btn').addEventListener('click', function() {
    const menu = document.getElementById('test-menu');
    menu.classList.toggle('hidden');
});

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('test-menu');
    const button = document.getElementById('test-menu-btn');
    
    if (!menu.contains(e.target) && !button.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

// Save answer functionality
document.getElementById('save-answer').addEventListener('click', function() {
    const selectedAnswer = document.querySelector('input[name="answer"]:checked');
    
    if (!selectedAnswer) {
        alert('Please select an answer before saving.');
        return;
    }
    
    const timeSpent = Math.round((Date.now() - questionStartTime) / 1000);
    
    fetch('/wp-json/pmp/v1/test/answer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify({
            session_id: sessionId,
            question_id: questionId,
            answer: selectedAnswer.value,
            time_spent: timeSpent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Visual feedback
            this.innerHTML = '<i class="fas fa-check mr-2"></i>Saved';
            this.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            this.classList.add('bg-green-600', 'hover:bg-green-700');
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-save mr-2"></i>Save Answer';
                this.classList.remove('bg-green-600', 'hover:bg-green-700');
                this.classList.add('bg-gray-600', 'hover:bg-gray-700');
            }, 2000);
        } else {
            alert('Error saving answer. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving answer. Please try again.');
    });
});

// Auto-save when selecting answer
document.querySelectorAll('input[name="answer"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Auto-save after 2 seconds
        setTimeout(() => {
            document.getElementById('save-answer').click();
        }, 2000);
    });
});

// Finish test functionality
document.getElementById('finish-test')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to finish the test? This action cannot be undone.')) {
        finishTest();
    }
});

document.getElementById('submit-test')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to submit the test? This action cannot be undone.')) {
        finishTest();
    }
});

function finishTest() {
    // Save current answer first
    const selectedAnswer = document.querySelector('input[name="answer"]:checked');
    if (selectedAnswer) {
        document.getElementById('save-answer').click();
    }
    
    // Submit test
    fetch('/wp-json/pmp/v1/test/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify({
            session_id: sessionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?results=' + sessionId;
        } else {
            alert('Error submitting test. Please try again.');
        }
    });
}

function autoSubmitTest() {
    alert('Time is up! The test will be submitted automatically.');
    finishTest();
}

// Flag question functionality
document.getElementById('question-flag').addEventListener('click', function() {
    this.classList.toggle('text-yellow-500');
    this.classList.toggle('text-gray-400');
    
    // Store flag status in localStorage
    const flaggedQuestions = JSON.parse(localStorage.getItem('flagged_questions') || '[]');
    const questionIndex = flaggedQuestions.indexOf(questionId);
    
    if (questionIndex > -1) {
        flaggedQuestions.splice(questionIndex, 1);
    } else {
        flaggedQuestions.push(questionId);
    }
    
    localStorage.setItem('flagged_questions', JSON.stringify(flaggedQuestions));
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    startTimer();
    
    // Check if question is flagged
    const flaggedQuestions = JSON.parse(localStorage.getItem('flagged_questions') || '[]');
    if (flaggedQuestions.includes(questionId)) {
        document.getElementById('question-flag').classList.add('text-yellow-500');
        document.getElementById('question-flag').classList.remove('text-gray-400');
    }
    
    // Auto-focus on first option for keyboard navigation
    document.querySelector('input[name="answer"]')?.focus();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Number keys 1-4 for selecting options
    if (e.key >= '1' && e.key <= '4') {
        const optionIndex = parseInt(e.key) - 1;
        const option = document.querySelectorAll('input[name="answer"]')[optionIndex];
        if (option) {
            option.checked = true;
        }
    }
    
    // Arrow keys for navigation
    if (e.key === 'ArrowLeft' && <?php echo $current_question; ?> > 1) {
        window.location.href = '?session=<?php echo $session_id; ?>&q=<?php echo $current_question - 1; ?>';
    }
    
    if (e.key === 'ArrowRight' && <?php echo $current_question; ?> < <?php echo count($questions); ?>) {
        window.location.href = '?session=<?php echo $session_id; ?>&q=<?php echo $current_question + 1; ?>';
    }
    
    // S key to save
    if (e.key === 's' || e.key === 'S') {
        e.preventDefault();
        document.getElementById('save-answer').click();
    }
});

// Prevent accidental page refresh
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>

<style>
.answer-option:hover {
    border-color: #3b82f6;
}

.answer-option:has(input:checked) {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1em;
}

#timer.text-red-600 {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
