/**
 * Logic Model board controller.
 *
 * The board renders every stage expanded so the full causal chain
 * (inputs → activities → outputs → outcomes → impact) is visible and each stage
 * is independently editable. Canvas link/modal/dropdown wiring is handled by
 * leantime.canvasController (initialised from the board template), so no
 * stage-focus behaviour is needed here. Kept as a thin, stable namespace for
 * plugin extension points (e.g. StrategyPro health/narrative overlays).
 */
leantime.logicmodelCanvasController = (function () {
    return {};
})();
