/**
 * layout.content-box: resize a sidebar by dragging its divider, or focus the divider and use the
 * arrow keys (Home / End: narrowest / widest). Double-click resets the default width. With an id
 * (data-lt-content) the width is saved per user in the accordion-state store, and the component
 * renders it on the next load.
 *
 * Delegated on document, so boxes added by htmx or dialogs need no setup.
 */
leantime.contentBox = (function () {

    var MIN_WIDTH = 160;
    var MAX_WIDTH = 800;
    var MAIN_MIN_WIDTH = 320;
    var KEY_STEP = 16;

    var drag = null;
    var saveTimers = {};

    var boxOf = function (resizer) {
        return resizer.closest('.lt-content');
    };

    var sidebarOf = function (box, side) {
        return box.querySelector(':scope > .lt-content-side--' + side);
    };

    var widthOf = function (element) {
        return element ? element.getBoundingClientRect().width : 0;
    };

    // Dragging towards the main content widens the sidebar. Works for both sides and in RTL,
    // where the "left" sidebar is drawn on the right.
    var growthDirection = function (box, side) {
        var sidebar = sidebarOf(box, side);
        var main = box.querySelector(':scope > .lt-content-main');
        return sidebar.getBoundingClientRect().left < main.getBoundingClientRect().left ? 1 : -1;
    };

    var clamp = function (box, side, width) {
        var otherSide = side === 'left' ? 'right' : 'left';
        var room = box.clientWidth - widthOf(sidebarOf(box, otherSide)) - MAIN_MIN_WIDTH;
        var max = Math.max(MIN_WIDTH, Math.min(MAX_WIDTH, room));
        return Math.round(Math.max(MIN_WIDTH, Math.min(max, width)));
    };

    var apply = function (box, side, width, resizer) {
        box.style.setProperty('--lt-content-' + side + '-w', width + 'px');
        resizer.setAttribute('aria-valuenow', String(width));
    };

    var resizerOf = function (box, side) {
        return box.querySelector(':scope > .lt-content-resizer[data-lt-side="' + side + '"]');
    };

    // A width saved in a wider window renders narrower (the sidebars shrink to keep the main
    // pane usable). Before a resize, pin both sidebars to what is shown, so flex doesn't hand the
    // space around again, and save the pinned widths, so the next load shows the same layout.
    var pinRenderedWidths = function (box) {
        // Measure both before changing either: pinning one sidebar re-flows the other.
        var pins = [];
        ['left', 'right'].forEach(function (side) {
            var sidebar = sidebarOf(box, side);
            var resizer = resizerOf(box, side);
            if (!sidebar || !resizer) {
                return;
            }
            // Rounded down, so the pinned widths never add up to more than the box holds.
            var rendered = Math.floor(widthOf(sidebar));
            var preferred = parseInt(box.style.getPropertyValue('--lt-content-' + side + '-w'), 10);
            if (rendered !== preferred) {
                pins.push({ side: side, resizer: resizer, width: rendered });
            }
        });

        pins.forEach(function (pin) {
            apply(box, pin.side, pin.width, pin.resizer);
            save(box, pin.side, pin.width);
        });
    };

    var save = function (box, side, width) {
        // Charts, calendars and DataTables re-measure on resize.
        window.dispatchEvent(new Event('resize'));

        var id = box.getAttribute('data-lt-content');
        if (!id) {
            return;
        }
        var key = 'content-box_' + id + '_' + side;
        clearTimeout(saveTimers[key]);
        saveTimers[key] = setTimeout(function () {
            leantime.rpc('Api.Api.setSubmenuState', {
                submenu: key,
                state: String(width)
            }).catch(function (e) { console.error('Could not save the sidebar width', e); });
        }, 300);
    };

    document.addEventListener('pointerdown', function (event) {
        var resizer = event.target.closest && event.target.closest('.lt-content-resizer');
        if (!resizer || event.button !== 0) {
            return;
        }
        var box = boxOf(resizer);
        var side = resizer.getAttribute('data-lt-side');
        pinRenderedWidths(box);
        drag = {
            resizer: resizer,
            box: box,
            side: side,
            direction: growthDirection(box, side),
            startX: event.clientX,
            startWidth: widthOf(sidebarOf(box, side)),
            width: null
        };
        resizer.setPointerCapture(event.pointerId);
        box.classList.add('lt-content--resizing');
        event.preventDefault();
    });

    document.addEventListener('pointermove', function (event) {
        if (!drag) {
            return;
        }
        var width = clamp(drag.box, drag.side, drag.startWidth + (event.clientX - drag.startX) * drag.direction);
        drag.width = width;
        apply(drag.box, drag.side, width, drag.resizer);
    });

    var endDrag = function () {
        if (!drag) {
            return;
        }
        var finished = drag;
        drag = null;
        finished.box.classList.remove('lt-content--resizing');
        if (finished.width !== null && finished.width !== Math.round(finished.startWidth)) {
            save(finished.box, finished.side, finished.width);
        }
    };
    document.addEventListener('pointerup', endDrag);
    document.addEventListener('pointercancel', endDrag);

    document.addEventListener('keydown', function (event) {
        var resizer = event.target.closest && event.target.closest('.lt-content-resizer');
        if (!resizer || ['ArrowRight', 'ArrowLeft', 'Home', 'End'].indexOf(event.key) === -1) {
            return;
        }
        var box = boxOf(resizer);
        var side = resizer.getAttribute('data-lt-side');
        pinRenderedWidths(box);
        var current = widthOf(sidebarOf(box, side));
        var direction = growthDirection(box, side);
        var width;

        if (event.key === 'ArrowRight') {
            width = current + KEY_STEP * direction;
        } else if (event.key === 'ArrowLeft') {
            width = current - KEY_STEP * direction;
        } else if (event.key === 'Home') {
            width = MIN_WIDTH;
        } else {
            width = MAX_WIDTH;
        }
        event.preventDefault();
        width = clamp(box, side, width);
        apply(box, side, width, resizer);
        save(box, side, width);
    });

    document.addEventListener('dblclick', function (event) {
        var resizer = event.target.closest && event.target.closest('.lt-content-resizer');
        if (!resizer) {
            return;
        }
        var box = boxOf(resizer);
        var side = resizer.getAttribute('data-lt-side');
        var width = clamp(box, side, parseInt(resizer.getAttribute('data-lt-default'), 10) || MIN_WIDTH);
        apply(box, side, width, resizer);
        save(box, side, width);
    });

    // Keep each divider's aria-valuenow equal to its sidebar's rendered width: the sidebars shrink
    // (CSS) when the box is narrower than the saved widths allow.
    var syncValues = function (root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('.lt-content-resizer').forEach(function (resizer) {
            var box = boxOf(resizer);
            var sidebar = box ? sidebarOf(box, resizer.getAttribute('data-lt-side')) : null;
            // Stacked (narrow screens) the handle is hidden and the sidebar is full width.
            if (sidebar && resizer.offsetParent !== null) {
                resizer.setAttribute('aria-valuenow', String(Math.round(widthOf(sidebar))));
            }
        });
    };

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { syncValues(document); }, 150);
    });
    document.addEventListener('DOMContentLoaded', function () { syncValues(document); });
    if (window.htmx) {
        htmx.onLoad(syncValues);
    }

    return {};
})();
