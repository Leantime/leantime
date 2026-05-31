import '../../Canvas/Js/canvasController.js';

/**
 * Logic Model board controller — stage-flow focus behaviour.
 *
 * Canvas link/modal/dropdown wiring is handled by leantime.canvasController
 * (initialised from the board template). This controller only manages the
 * horizontal stage-flow: clicking an inactive stage expands it.
 */
leantime.logicmodelCanvasController = (function () {

    /**
     * Currently active stage key (e.g. 'lm_inputs').
     */
    var activeStage = '';

    /**
     * Apply active/inactive state to all stages based on activeStage.
     */
    function updateStageStates() {
        document.querySelectorAll('.sf-stage').forEach(function (el) {
            if (el.getAttribute('data-stage') === activeStage) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    /**
     * Focus a specific stage.
     *
     * @param {string} stageKey  The box type key, e.g. 'lm_inputs'.
     */
    function focusStage(stageKey) {
        activeStage = stageKey;
        updateStageStates();
    }

    /**
     * Initialise the board: focus the first stage and attach click handlers.
     * Clicking an inactive stage focuses it; active-stage clicks pass through
     * to the normal link/button handlers.
     */
    function initBoard() {
        var stages = document.querySelectorAll('.sf-stage');
        if (stages.length === 0) {
            return;
        }

        focusStage(stages[0].getAttribute('data-stage'));

        stages.forEach(function (s) {
            s.addEventListener('click', function () {
                if (this.classList.contains('active')) {
                    return;
                }
                focusStage(this.getAttribute('data-stage'));
            });
        });
    }

    return {
        initBoard: initBoard,
        focusStage: focusStage,
        updateStageStates: updateStageStates,
    };
})();

// Auto-initialise. The bundle loads after DOM parsing, but guard anyway.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        leantime.logicmodelCanvasController.initBoard();
    });
} else {
    leantime.logicmodelCanvasController.initBoard();
}
