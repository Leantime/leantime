import '../../Canvas/Js/canvasController.js';

leantime.logicmodelCanvasController = (function () {

    var controller = leantime.canvasController.createController('logicmodel', {});

    /**
     * Currently active stage key (e.g. 'lm_inputs').
     */
    var activeStage = '';

    /**
     * Set row heights — no-op for logic model since the flow layout
     * does not use the standard canvas grid.
     */
    controller.setRowHeights = function () {};

    /**
     * Initialise the board: focus first stage and attach click handlers.
     */
    controller.initBoard = function () {
        var stages = document.querySelectorAll('.sf-stage');
        if (stages.length > 0) {
            var firstKey = stages[0].getAttribute('data-stage');
            controller.focusStage(firstKey);
        }

        // Click handler: clicking an inactive stage focuses it.
        // Active stage clicks pass through to normal link/button handlers.
        stages.forEach(function (s) {
            s.addEventListener('click', function (e) {
                if (this.classList.contains('active')) return;
                controller.focusStage(this.getAttribute('data-stage'));
            });
        });
    };

    /**
     * Focus a specific stage.
     *
     * @param {string} stageKey  The box type key, e.g. 'lm_inputs'.
     */
    controller.focusStage = function (stageKey) {
        activeStage = stageKey;
        controller.updateStageStates();
    };

    /**
     * Apply active/inactive state to all stages based on the current
     * activeStage value.
     */
    controller.updateStageStates = function () {
        document.querySelectorAll('.sf-stage').forEach(function (el) {
            if (el.getAttribute('data-stage') === activeStage) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    };

    return controller;
})();

// Auto-initialise — this module is lazy-loaded after DOM parsing,
// so the DOM is already ready by the time this executes.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        leantime.logicmodelCanvasController.initBoard();
    });
} else {
    leantime.logicmodelCanvasController.initBoard();
}
