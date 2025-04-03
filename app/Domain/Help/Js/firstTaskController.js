leantime.firstTaskController = (function () {

    /**
     * Initialize the first task form
     */
    var initFirstTaskForm = function() {
        jQuery(document).ready(function() {
            const firstTaskForm = jQuery('#firstTaskOnboarding');

            if (firstTaskForm.length > 0) {
                firstTaskForm.on('submit', function(e) {
                    e.preventDefault();

                    const taskInput = jQuery('#firstTask');
                    const taskText = taskInput.val().trim();

                    if (taskText === '') {
                        // Show validation error
                        taskInput.addClass('error');
                        return false;
                    }

                    // Remove any error styling
                    taskInput.removeClass('error');

                    // Show confetti animation
                    leantime.confettiHelper.celebrateSuccess();

                    // Wait for confetti animation before submitting
                    setTimeout(function() {
                        firstTaskForm[0].submit();
                    }, 1500);
                });

                // Remove error styling when user starts typing
                jQuery('#firstTask').on('input', function() {
                    jQuery(this).removeClass('error');
                });
            }
        });
    };

    return {
        initFirstTaskForm: initFirstTaskForm
    };
})();

// Initialize when document is ready
jQuery(document).ready(function() {
    leantime.firstTaskController.initFirstTaskForm();
});
