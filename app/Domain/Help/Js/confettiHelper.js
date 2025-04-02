leantime.confettiHelper = (function () {

    /**
     * Trigger a confetti animation
     * @param {Object} options - Confetti options
     */
    var triggerConfetti = function(options) {
        const defaultOptions = {
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 },
            disableForReducedMotion: true
        };

        // Merge default options with provided options
        const confettiOptions = {...defaultOptions, ...options};

        // Check if confetti library is loaded
        if (typeof confetti === 'function') {
            confetti(confettiOptions);
        } else {
            console.error('Confetti library not loaded');
        }
    };

    /**
     * Trigger a success celebration with confetti
     */
    var celebrateSuccess = function() {
        triggerConfetti({
            particleCount: 150,
            spread: 90,
            origin: { y: 0.8 },
            colors: ['#26a69a', '#00bcd4', '#4caf50', '#8bc34a', '#cddc39']
        });

        // Add a second burst for more effect
        setTimeout(function() {
            triggerConfetti({
                particleCount: 80,
                spread: 120,
                origin: { y: 0.7 },
                colors: ['#ff9800', '#ff5722', '#f44336', '#e91e63', '#9c27b0']
            });
        }, 500);
    };

    return {
        triggerConfetti: triggerConfetti,
        celebrateSuccess: celebrateSuccess
    };
})();
