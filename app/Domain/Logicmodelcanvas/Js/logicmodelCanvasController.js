import '../../Canvas/Js/canvasController.js';

leantime.logicmodelCanvasController = (function () {

    var controller = leantime.canvasController.createController('logicmodel', {});

    /**
     * Currently active stage key (e.g. 'lm_inputs').
     */
    var activeStage = '';

    /**
     * Set row heights — override from base canvas controller (no-op for logic model
     * since the pricing-page layout does not use the standard grid).
     */
    controller.setRowHeights = function () {};

    /**
     * Initialise the board: focus the first stage by default.
     */
    controller.initBoard = function () {
        var stages = document.querySelectorAll('.lm-stage');
        if (stages.length > 0) {
            var firstKey = stages[0].getAttribute('data-stage');
            controller.focusStage(firstKey);
        }
    };

    /**
     * Focus a specific stage — toggle active/inactive CSS classes and swap
     * between full card view and compact dot view.
     *
     * @param {string} stageKey  The box type key, e.g. 'lm_inputs'.
     */
    controller.focusStage = function (stageKey) {
        activeStage = stageKey;
        controller.updateStageStates();
    };

    /**
     * Apply CSS classes and visibility toggles to all stages based on
     * the current `activeStage` value.
     */
    controller.updateStageStates = function () {
        document.querySelectorAll('.lm-stage').forEach(function (el) {
            var key = el.getAttribute('data-stage');
            if (key === activeStage) {
                el.classList.add('lm-stage--active');
                el.classList.remove('lm-stage--inactive');
            } else {
                el.classList.remove('lm-stage--active');
                el.classList.add('lm-stage--inactive');
            }
        });
    };

    return controller;
})();
