leantime.widgetController = (function () {
    var grid = [];

    // Helper function to find next available position
    var findAvailablePosition = function(widget, grid) {
        let x = widget.gridX || 0;
        let y = widget.gridY || 0;
        let width = widget.gridWidth || 2;
        let height = widget.gridHeight || 2;

        // Try the preferred position first
        if (grid.willItFit({x, y, width, height})) {
            return { x: x, y: y };
        }

        // If preferred position is occupied, find next available spot
        let maxY = Math.max(...grid.engine.nodes.map(n => n.y + n.h), 0);

        // Try positions from top to bottom
        for (let newY = 0; newY <= maxY + 1; newY++) {
            for (let newX = 0; newX <= 12 - width; newX++) {
                if (grid.willItFit({newX, newY, width, height})) {
                    return { x: newX, y: newY };
                }
            }
        }
        return { x: 0, y: maxY + 1 }; // Fallback to bottom
    };

    // Implement safe HTML rendering callback
    GridStack.renderCB = function(el, w) {
        if (w.content) {
            // Using DOMPurify to sanitize content if available
            if (typeof DOMPurify !== 'undefined') {
                el.innerHTML = DOMPurify.sanitize(w.content);
            }
        }
    };


    var initGrid = function () {
        grid = GridStack.init({
            margin: '0px 15px 15px 0px',
            handle: ".grid-handler-top",
            minRow: 2,
            cellHeight: '30px',
            float: true,
            draggable: {
                handle: '.grid-handler-top',
                appendTo: 'body',
                // scroll: true,
                // scrollSensitivity: 20,
                // scrollSpeed: 10
            },
            lazyLoad: false,
            columnOpts: {
                breakpointForWindow: true,  // test window vs grid size
                breakpoints: [{w:1199, c:1}]
            },
        });

        grid.on('dragstop', function(event, item) {
            saveGrid();
        });

        grid.on('resizestop', function(Event, item) {
            saveGrid();
        });

        // Mobile/tablet (<1200px): force a static single-column grid so that
        // scrolling or tapping a widget doesn't drag/reshuffle it (#3350). The
        // 1-column layout comes from columnOpts.breakpoints above; setStatic
        // disables drag/resize (so saveGrid never fires here and the desktop
        // layout is never overwritten). Restore the draggable grid on resize up.
        var welcomeRemoved = false;
        var applyResponsiveGridMode = function () {
            if (!grid) return;
            var isMobile = window.innerWidth < 1200;
            grid.setStatic(isMobile);
            grid.margin(isMobile ? '0px 5px 5px 0px' : '0px 15px 15px 0px');

            // Hide the low-value welcome/stats widget on mobile. Removing it from
            // the grid engine (keeping the DOM, which CSS hides) and compacting
            // closes the gap its grid rows would otherwise leave behind.
            if (isMobile && !welcomeRemoved) {
                var welcome = document.getElementById('widget_wrapper_welcome');
                if (welcome) {
                    try {
                        grid.removeWidget(welcome, false);
                        grid.compact();
                    } catch (e) { /* grid not ready; CSS still hides it */ }
                    welcomeRemoved = true;
                }
            }
        };
        applyResponsiveGridMode();
        var gridResizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(gridResizeTimer);
            gridResizeTimer = setTimeout(applyResponsiveGridMode, 200);
        });

        // Delegated, so widgets added later from the widget manager get a working menu too.
        jQuery(grid.el).on("click", ".removeWidget", function(){
            removeWidget(jQuery(this).closest(".grid-stack-item")[0]);
        });
        jQuery(grid.el).on("click", ".fitContent", function(){
            resizeWidget(jQuery(this).closest(".grid-stack-item")[0]);
        });

        jQuery(document).ready(function(){
            jQuery("#gridBoard").css("opacity", 1);
        });

    };

    var saveGrid = function() {
        let items = grid.save();

        // Sort items by Y position first, then X position
        items.sort((a, b) => {
            return a.y === b.y ? a.x - b.x : a.y - b.y;
        });

        let visibilityData = null;

        if(arguments.length > 0 && arguments[0].action === "toggleWidget") {
            visibilityData = {
                widgetId: arguments[0].widgetId,
                visible: arguments[0].visible
            };
        }

        items.forEach(function(item) {
            //get hx links
            let htmxElement = jQuery(item.content).find("[hx-get]").first();

            item.id = htmxElement.attr("id");
            item.widgetUrl = htmxElement.attr("hx-get");
            item.widgetTrigger = htmxElement.attr("hx-trigger");

            if(item.x == undefined) {
                item.x = 0;
            }
            item.gridX = item.x;

            if(item.y == undefined) {
                item.y = 0;
            }
            item.gridY = item.y;

            if(item.w == undefined) {
                item.w = 1;
            }
            item.gridWidth = item.w;

            if(item.h == undefined) {
                item.h = 1;
            }
            item.gridHeight = item.h;

            item.content = '';
        });


        jQuery.post(leantime.appUrl+"/widgets/widgetManager",
            {
                action: "saveGrid",
                data: items,
                visibilityData: visibilityData
            },
            function(data, status){
            });
    };


    var removeWidget = function (el) {
        el.remove();
        grid.removeWidget(el, true);
        saveGrid();
    }

    var resizeWidget = function (el) {
        let grid = document.querySelector('.grid-stack').gridstack;
        grid.resizeToContent(el, false);
        saveGrid();
    }

    var toggleWidgetVisibility = function(id, element, widget) {
        let grid = document.querySelector('.grid-stack').gridstack;
        let visible = jQuery(element).is(":checked");

        // Find the next available position
        let position = findAvailablePosition(widget, grid);

        if (!visible) {
            const widgetItem = jQuery("#" + id).closest(".grid-stack-item")[0];
            if (widgetItem) {
                removeWidget(widgetItem);
            }
        } else {
            // The server renders the widget's grid item (same partial as the dashboard). The checkbox
            // stays disabled until it is on the grid, so it can't be switched off mid-request.
            element.disabled = true;
            fetch(leantime.appUrl + '/hx/widgets/widgetShell/get?id=' + encodeURIComponent(id), {
                credentials: 'include',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'HX-Request': 'true' }
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    const template = document.createElement('template');
                    template.innerHTML = html.trim();
                    const widgetNode = template.content.querySelector('.grid-stack-item');
                    if (!widgetNode || document.getElementById(widgetNode.id)) {
                        return;
                    }

                    grid.el.appendChild(widgetNode);
                    grid.makeWidget(widgetNode, {
                        x: widget.gridX || 0,
                        y: widget.gridY || 50,
                        w: widget.gridWidth || 2,
                        h: widget.gridHeight || 2
                    });

                    htmx.process(widgetNode);
                    saveGrid({action: "toggleWidget", widgetId: id, visible: visible});
                })
                .catch(function (error) {
                    element.checked = false;
                    console.error('[widgets] Could not add widget ' + id, error);
                })
                .finally(function () { element.disabled = false; });
        }
    }

    // Make public what you want to have public, everything else is private
    return {
        resizeWidget: resizeWidget,
        removeWidget: removeWidget,
        saveGrid: saveGrid,
        initGrid:initGrid,
        toggleWidgetVisibility:toggleWidgetVisibility
    };
})();
