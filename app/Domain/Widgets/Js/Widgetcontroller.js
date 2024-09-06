import jQuery from 'jquery';
import { GridStack } from 'gridstack';
import { appUrl } from 'js/app/core/instance-info.module';

let grid = [];

export const initGrid = function () {
    grid = GridStack.init({
        columnOpts: {
            breakpointForWindow: true,  // test window vs grid size
            breakpoints: [{w:700, c:1},{w:850, c:6},{w:950, c:8},{w:1100, c:12}],
        },
        margin: '0px 15px 15px 0px',
        handle: ".grid-handler-top",
        minRow: 2, // don't let it collapse when empty
        cellHeight: '30px',
        float:false,
        animate: false,
        draggable: {
            handle: '.grid-handler-top',
            appendTo: 'body',
            scroll: false
        },
        disableAutoScroll: true,
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

export const saveGrid = function() {
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
            item.w = 1;
        }
        item.gridWidth = item.w;

        if(item.h == undefined) {
            item.h = 1;
        }
        item.gridHeight = item.h;

        item.content = '';

    });

    GridStack.Utils.sort(items);

    jQuery.post(appUrl + "/widgets/widgetManager",
        {
            action: "saveGrid",
            data: items
        },
        function(data, status){

        });
};

export const removeWidget = function (el) {
    el.remove();
    grid.removeWidget(el, true);
    saveGrid();
}

export const resizeWidget = function (el) {
    let grid = document.querySelector('.grid-stack').gridstack;
    grid.resizeToContent(el, false);
    saveGrid();
}

export const toggleWidgetVisibility = function(id, element, widget) {
    let grid = document.querySelector('.grid-stack').gridstack;

    //When we click on a checked checkbox, the checked status will change
    //Then it will get here. So once it is here it is already unchecked
    //which means that we need to check if it is not checked to remove the widget
    if (!jQuery(element).is(":checked")){

        removeWidget(jQuery("#" + id).closest(".grid-stack-item")[0]);

    } else {

        grid.addWidget(buildWidget(widget), {
            w: widget.gridWidth,
            h: widget.gridHeight,
            minW: widget.gridMinWidth,
            minH: widget.gridMinHeight,
            x: widget.gridX,
            y: widget.gridY
        });
        htmx.process(document.body);
        saveGrid();
    }
}

export const buildWidget = function(widget) {
    var widgetHtml = '<div class="grid-stack-item">\n' +
        '    <div class="grid-stack-item-content tw-p-none ' + (widget.widgetBackground == "default" ? "maincontentinner" : widget.background) + '">\n' +
        '        <div class="' + (widget.widgetBackground == "default" ? "tw-pb-l" : "") + '">\n' +
        '            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">\n' +
        '               <div class="grid-handler-top tw-h-[40px] tw-cursor-grab tw-float-left tw-mr-sm">\n' +
        '                    <i class="fa-solid fa-grip-vertical"></i>\n' +
        '                </div>\n' +
        '           ' + (widget.name != '' ? '<h5 class="subtitle tw-pb-m tw-float-left tw-mr-sm">' + widget.name + '</h5>' : '') + '\n' +
        '            <div class="inlineDropDownContainer tw-float-right">\n' +
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
        ' <div class="widgetContent tw-px-l">\n' +
        '             <div hx-get="'+widget.widgetUrl+'" hx-trigger="'+widget.widgetTrigger+'" id="'+widget.id+'"></div>\n' +
        '        </div>\n' +
        '       </div>\n' +
        '        <div class="clear"></div>\n' +
        '    </div>\n' +
        '</div>\n';

    return jQuery(widgetHtml)[0];
}

// Make public what you want to have public, everything else is private
export default {
    resizeWidget: resizeWidget,
    removeWidget: removeWidget,
    saveGrid: saveGrid,
    initGrid: initGrid,
    toggleWidgetVisibility: toggleWidgetVisibility
};
