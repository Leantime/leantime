leantime.firstTaskController = (function () {

    /**
     * Initialize the first task form
     */
    var initFirstTaskForm = function() {
        var firstTaskForm = document.querySelector('#firstTaskOnboarding');

        if (firstTaskForm) {
            firstTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var taskInput = document.querySelector('#firstTask');
                var taskText = taskInput.value.trim();

                if (taskText === '') {
                    // Show validation error
                    taskInput.classList.add('error');
                    return false;
                }

                // Remove any error styling
                taskInput.classList.remove('error');

                // Show confetti animation
                leantime.confettiHelper.celebrateSuccess();

                // Wait for confetti animation before submitting
                setTimeout(function() {
                    firstTaskForm.submit();
                }, 1500);
            });

            // Remove error styling when user starts typing
            var firstTaskInput = document.querySelector('#firstTask');
            if (firstTaskInput) {
                firstTaskInput.addEventListener('input', function() {
                    this.classList.remove('error');
                });
            }
        }
    };

    return {
        initFirstTaskForm: initFirstTaskForm
    };
})();

// Initialize when document is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        leantime.firstTaskController.initFirstTaskForm();
    });
} else {
    leantime.firstTaskController.initFirstTaskForm();
}
