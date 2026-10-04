/**
 * Touch support for jQuery UI mouse widgets (sortable, draggable, ...).
 *
 * Replaces jquery-ui-touch-punch. Touch-punch called preventDefault() on every
 * touchstart inside a sortable item, so on phones and tablets a swipe over a card
 * could never scroll the page and a tap only worked when the finger did not move
 * at all - otherwise the card was grabbed instead of opened.
 *
 * Here a touch only becomes a drag after a short long-press:
 * - tap (released before the long-press)       -> native click, nothing simulated
 * - swipe (moves before the long-press)        -> native scrolling, drag abandoned
 * - long-press, then move                      -> simulated mouse drag for jQuery UI
 *
 * Mouse input is not touched, so desktop drag & drop behaves as before.
 *
 * Fixes #1357 (can't tap cards), #1465 (tablet kanban drag), #3350 (scrolling the
 * dashboard moves tasks).
 */
(function ($) {
    'use strict';

    if (!$ || !$.ui || !$.ui.mouse || !('ontouchend' in document)) {
        return;
    }

    var LONG_PRESS_MS = 300;
    var MOVE_TOLERANCE_PX = 10;

    var mouseProto = $.ui.mouse.prototype;
    var originalMouseInit = mouseProto._mouseInit;
    var originalMouseDestroy = mouseProto._mouseDestroy;

    // Only one widget handles a touch sequence (innermost wins, events bubble outward)
    var activeTouch = null;

    function pointFromTouch(touch) {
        return {
            screenX: touch.screenX,
            screenY: touch.screenY,
            clientX: touch.clientX,
            clientY: touch.clientY
        };
    }

    function dispatchMouseEvent(target, type, point) {
        var mouseEvent = new MouseEvent(type, {
            bubbles: true,
            cancelable: true,
            view: window,
            detail: 1,
            screenX: point.screenX,
            screenY: point.screenY,
            clientX: point.clientX,
            clientY: point.clientY,
            button: 0,
            buttons: type === 'mouseup' || type === 'mouseout' ? 0 : 1
        });

        target.dispatchEvent(mouseEvent);
    }

    function resetActiveTouch() {
        if (activeTouch && activeTouch.longPressTimer) {
            clearTimeout(activeTouch.longPressTimer);
        }
        activeTouch = null;
    }

    // A second finger (pinch/zoom, two-finger scroll) is never a drag: drop a
    // pending long-press so its timer cannot start a drag mid-gesture.
    function cancelPendingTouchOnMultiTouch(touches) {
        if (touches.length > 1 && activeTouch && !activeTouch.isDragging) {
            resetActiveTouch();
        }
    }

    // The second finger may land outside any sortable, so watch the whole document
    document.addEventListener('touchstart', function (event) {
        cancelPendingTouchOnMultiTouch(event.touches);
    }, { capture: true, passive: true });

    mouseProto._touchStart = function (event) {
        var touches = event.originalEvent.touches;

        if (touches.length > 1) {
            cancelPendingTouchOnMultiTouch(touches);
            return;
        }

        if (activeTouch) {
            return;
        }

        var touch = event.originalEvent.changedTouches[0];

        if (!this._mouseCapture(touch)) {
            return;
        }

        // Links, buttons and inputs listed in the widget's cancel option stay plain taps
        if (this.options.cancel && $(event.target).closest(this.options.cancel).length) {
            return;
        }

        var widget = this;
        var startPoint = pointFromTouch(touch);

        activeTouch = {
            widget: widget,
            target: event.target,
            startPoint: startPoint,
            lastPoint: startPoint,
            isDragging: false,
            longPressTimer: null
        };

        activeTouch.longPressTimer = setTimeout(function () {
            if (!activeTouch || activeTouch.widget !== widget) {
                return;
            }

            activeTouch.isDragging = true;
            activeTouch.longPressTimer = null;

            dispatchMouseEvent(activeTouch.target, 'mouseover', activeTouch.lastPoint);
            dispatchMouseEvent(activeTouch.target, 'mousemove', activeTouch.lastPoint);
            dispatchMouseEvent(activeTouch.target, 'mousedown', activeTouch.lastPoint);
        }, LONG_PRESS_MS);
    };

    mouseProto._touchMove = function (event) {
        if (!activeTouch || activeTouch.widget !== this) {
            return;
        }

        cancelPendingTouchOnMultiTouch(event.originalEvent.touches);
        if (!activeTouch) {
            return;
        }

        var currentPoint = pointFromTouch(event.originalEvent.changedTouches[0]);
        activeTouch.lastPoint = currentPoint;

        if (!activeTouch.isDragging) {
            var movedX = Math.abs(currentPoint.clientX - activeTouch.startPoint.clientX);
            var movedY = Math.abs(currentPoint.clientY - activeTouch.startPoint.clientY);

            // Finger moved before the long-press: the user is scrolling, not dragging
            if (movedX > MOVE_TOLERANCE_PX || movedY > MOVE_TOLERANCE_PX) {
                resetActiveTouch();
            }

            return;
        }

        // Dragging: keep the page from scrolling under the card
        event.preventDefault();
        dispatchMouseEvent(activeTouch.target, 'mousemove', currentPoint);
    };

    mouseProto._touchEnd = function (event) {
        if (!activeTouch || activeTouch.widget !== this) {
            return;
        }

        var wasDragging = activeTouch.isDragging;
        var target = activeTouch.target;
        var lastPoint = activeTouch.lastPoint;

        resetActiveTouch();

        // A tap is left to the browser so links and click handlers work natively
        if (!wasDragging) {
            return;
        }

        // Suppress the click the browser would synthesize after a drag
        if (event.cancelable) {
            event.preventDefault();
        }

        dispatchMouseEvent(target, 'mouseup', lastPoint);
        dispatchMouseEvent(target, 'mouseout', lastPoint);
    };

    // Holding a finger still on a link/image opens the context menu or link preview,
    // which would interrupt a drag that has already started
    mouseProto._touchContextMenu = function (event) {
        if (activeTouch && activeTouch.widget === this && activeTouch.isDragging) {
            event.preventDefault();
        }
    };

    mouseProto._mouseInit = function () {
        this.element.on('touchstart.ltTouchDrag', $.proxy(this, '_touchStart'));
        this.element.on('touchmove.ltTouchDrag', $.proxy(this, '_touchMove'));
        this.element.on('touchend.ltTouchDrag touchcancel.ltTouchDrag', $.proxy(this, '_touchEnd'));
        this.element.on('contextmenu.ltTouchDrag', $.proxy(this, '_touchContextMenu'));

        originalMouseInit.call(this);
    };

    mouseProto._mouseDestroy = function () {
        this.element.off('.ltTouchDrag');

        if (activeTouch && activeTouch.widget === this) {
            resetActiveTouch();
        }

        originalMouseDestroy.call(this);
    };
})(window.jQuery);
