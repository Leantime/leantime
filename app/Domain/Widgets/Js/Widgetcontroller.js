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
                breakpoints: [{w:700, c:1},{w:950, c:6}]
            },
        });

        grid.on('dragstop', function(event, item) {
            saveGrid();
        });

        grid.on('resizestop', function(Event, item) {
            saveGrid();
        });

        jQuery(".grid-stack-item").each(function(){
            jQuery(this).find(".removeWidget").click(function(){
                removeWidget(jQuery(this).closest(".grid-stack-item")[0]);
            });
            jQuery(this).find(".fitContent").click(function(){
                resizeWidget(jQuery(this).closest(".grid-stack-item")[0]);
            });
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
            removeWidget(jQuery("#" + id).closest(".grid-stack-item")[0]);
        } else {
            // Create the widget structure using DOM methods
            const widgetNode = document.createElement('div');
            widgetNode.className = 'grid-stack-item';

            // Create the content container
            const contentDiv = document.createElement('div');
            contentDiv.className = `grid-stack-item-content tw:p-none ${
                widget.widgetBackground == "default" ? "maincontentinner" : widget.background
            }`;

            // Set the inner structure
            contentDiv.innerHTML = buildWidget(widget);
            widgetNode.appendChild(contentDiv);

            // Add to grid and make it a widget
            grid.el.appendChild(widgetNode);
            grid.makeWidget(widgetNode, {
                x: widget.gridX || 0,
                y: widget.gridY || 50,
                w: widget.gridWidth || 2,
                h: widget.gridHeight || 2
            });

            // Initialize HTMX
            htmx.process(widgetNode);

            saveGrid({action: "toggleWidget", widgetId: id, visible: visible});
        }
    }

    var buildWidget = function(widget) {
        return '<div class="widgetInner">' +
            '        <div class="' + (widget.widgetBackground == "default" ? "tw:pb-l" : "") + '">\n' +
            '            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">\n' +
            '               <div class="grid-handler-top tw:h-[40px] tw:cursor-grab tw:float-left tw:mr-sm">\n' +
            '                    <i class="fa-solid fa-grip-vertical"></i>\n' +
            '                </div>\n' +
            '           ' + (widget.name != '' ? '<h5 class="subtitle tw:pb-m tw:float-left tw:mr-sm">' + widget.name + '</h5>' : '') + '\n' +
            '            <div class="inlineDropDownContainer tw:float-right">\n' +
            '                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">\n' +
            '                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>\n' +
            '                </a>\n' +
            '                <ul class="dropdown-menu">\n' +
            '                    <li><a href="javascript:void(0)" class="fitContent"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content</a></li>\n' +
            '                        <li><a href="javascript:void(0)" class="removeWidget"><i class="fa fa-eye-slash"></i> Hide</a></li>\n' +
            '                </ul>\n' +
            '            </div>\n' +
            '\n' +
            '        </div>\n' +
            ' <div class="widgetContent tw:px-l">\n' +
            '             <div hx-get="'+widget.widgetUrl+'" hx-trigger="'+widget.widgetTrigger+'" id="'+widget.id+'"></div>\n' +
            '        </div>\n' +
            '       </div>\n' +
            '        <div class="clear"></div>\n' +
            '    </div>\n';
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
