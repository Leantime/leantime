import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module.mjs';



export const initMasonryWall = function () {

    var $grid = jQuery('#ideaMason').packery({
        // options
        itemSelector: '.ticketBox',
        columnWidth: 260,
        isResizable: true
    });

    $grid.imagesLoaded().progress(function () {
        $grid.packery('layout');
    });

    var $items = $grid.find('.ticketBox').draggable({
        start: function (event, ui) {
            ui.helper.addClass('tilt');
            tilt_direction(ui.helper);
        },
        stop: function (event, ui) {
            ui.helper.removeClass("tilt");
            jQuery("html").unbind('mousemove', ui.helper.data("move_handler"));
            ui.helper.removeData("move_handler");
        },
    });

    function tilt_direction(item)
    {
        var left_pos = item.position().left,
            move_handler = function (e) {
                if (e.pageX >= left_pos) {
                    item.addClass("right");
                    item.removeClass("left");
                } else {
                    item.addClass("left");
                    item.removeClass("right");
                }
                left_pos = e.pageX;
            };
        jQuery("html").bind("mousemove", move_handler);
        item.data("move_handler", move_handler);
    }
    // bind drag events to Packery
    $grid.packery('bindUIDraggableEvents', $items);

    function orderItems()
    {
        var ideaSort = [];

        var itemElems = $grid.packery('getItemElements');
        jQuery(itemElems).each(function ( i, itemElem ) {
            var sortIndex = i + 1;
            var ideaId = jQuery(itemElem).attr("data-value");
            ideaSort.push({"id":ideaId, "sortIndex":sortIndex});
        });

        // POST to server using $.post or $.ajax
        jQuery.ajax({
            type: 'POST',
            url: appUrl + '/api/ideas',
            data: {
                action:"ideaSort",
                payload: ideaSort
            }

        });
    }


    $grid.on('dragItemPositioned',orderItems);
};

export const initBoardControlModal = function () {

};

export const initWallImageModals = function () {

    jQuery('.mainIdeaContent img').each(function () {
        jQuery(this).wrap("<a href='" + jQuery(this).attr("src") + "' class='imageModal'></a>");
    });

};

export const setKanbanHeights = function () {

    var maxHeight = 0;

    var height = jQuery("html").height() - 320;
    jQuery("#sortableIdeaKanban .column .contentInner").css("height", height);

};

export const initIdeaKanban = function (statusList) {

    // jQuery("#sortableIdeaKanban").disableSelection();
    console.log('update');
    jQuery("#sortableIdeaKanban .ticketBox").hover(function () {
        jQuery(this).css("background", "var(--kanban-card-hover)");
    },function () {
        jQuery(this).css("background", "var(--kanban-card-bg)");
    });

    jQuery("#sortableIdeaKanban .contentInner").sortable({
        connectWith: ".contentInner",
        items: "> .moveable",
        tolerance: 'pointer',
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        cancel: ".portlet-toggle, .dropdown-toggle, .dropdown-menu, .inlineDropDownContainer, .dropdown-bottom",
        start: function (event, ui) {
            ui.item.addClass('tilt');
            tilt_direction(ui.item);
        },
        stop: function (event, ui) {
            ui.item.removeClass("tilt");
            jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
            ui.item.removeData("move_handler");
        },
        update: function (event, ui) {


            var statusPostData = {
                action: "statusUpdate",
                payload: {}
            };

            for (var i = 0; i < statusList.length; i++) {
                if (jQuery(".contentInner.status_" + statusList[i]).length) {
                    statusPostData.payload[statusList[i]] = jQuery(".contentInner.status_" + statusList[i]).sortable('serialize');
                }
            }

            // POST to server using $.post or $.ajax
            jQuery.ajax({
                type: 'POST',
                url: appUrl + '/api/ideas',
                data:statusPostData
            });

        }
    });

    function tilt_direction(item)
    {
        var left_pos = item.position().left,
            move_handler = function (e) {
                if (e.pageX >= left_pos) {
                    item.addClass("right");
                    item.removeClass("left");
                } else {
                    item.addClass("left");
                    item.removeClass("right");
                }
                left_pos = e.pageX;
            };
        jQuery("html").bind("mousemove", move_handler);
        item.data("move_handler", move_handler);
    }

    jQuery(".portlet")
        .addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
        .find(".portlet-header")
        .addClass("ui-widget-header ui-corner-all")
        .prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

    jQuery(".portlet-toggle").click(function () {
        var icon = jQuery(this);
        icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
        icon.closest(".portlet").find(".portlet-content").toggle();
    });
};

// Make public what you want to have public, everything else is private
export default {
    initMasonryWall: initMasonryWall,
    initBoardControlModal: initBoardControlModal,
    initWallImageModals: initWallImageModals,
    setKanbanHeights: setKanbanHeights,
    initIdeaKanban: initIdeaKanban
};
