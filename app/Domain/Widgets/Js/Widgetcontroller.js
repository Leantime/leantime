leantime.widgetController = (function () {

    var grid = [];


    var initGrid = function () {

        grid = GridStack.init({
            margin: 5,
            handle: ".grid-handler-top",
            minRow: 2, // don't let it collapse when empty
            cellHeight: '30px',
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
        })

    };

    var saveGrid = function() {

        let items = grid.save();

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
                item.w = 2;
            }
            item.gridWidth = item.w;

            if(item.h == undefined) {
                item.h = 2;
            }
            item.gridHeight = item.h;

            item.content = '';

        });

        GridStack.Utils.sort(items);

        jQuery.post(leantime.appUrl+"/widgets/widgetManager",
            {
                action: "saveGrid",
                data: items
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

        grid.resizeToContent(el, false);
        saveGrid();
    }




    // Make public what you want to have public, everything else is private
    return {
        resizeWidget: resizeWidget,
        removeWidget: removeWidget,
        saveGrid: saveGrid,
        initGrid:initGrid
    };
})();
