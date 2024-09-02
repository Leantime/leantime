import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module.js';

export const closeModal = false;

//Variables
export const canvasoptions = function () {
    return {
        sizes: {
            minW: 700,
            minH: 1000,
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function () {
                jQuery(".showDialogOnLoad").show();
                if (closeModal == true) {
                    closeModal = false;
                    location.reload();
                }
            },
            afterShowCont: function () {

                jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());

                jQuery('textarea.ideaTextEditor').tinymce(
                    {
                        // General options
                        width: "100%",
                        height: "400px",
                        skin_url: appUrl + '/assets/css/libs/tinymceSkin/oxide',
                        content_css: appUrl + '/assets/css/libs/tinymceSkin/oxide/content.css',
                        content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                        plugins: "emoticons,autolink,link,image,lists,table,save,preview,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist",
                        toolbar: "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media | bullist numlist | table | template | emoticons",
                        branding: true,
                        statusbar: true,
                        convert_urls: false,
                        menubar: false,
                        resizable: true,
                        paste_data_images: true,
                        images_upload_handler: function (blobInfo, success, failure) {
                            var xhr, formData;

                            xhr = new XMLHttpRequest();
                            xhr.withCredentials = false;
                            xhr.open('POST', appUrl + '/api/files');

                            xhr.onload = function () {
                                var json;

                                if (xhr.status < 200 || xhr.status >= 300) {
                                    failure('HTTP Error: ' + xhr.status);
                                    return;
                                }

                                success(xhr.responseText);
                            };

                            formData = new FormData();
                            formData.append('file', blobInfo.blob());

                            xhr.send(formData);
                        },
                        file_picker_callback: function (callback, value, meta) {

                            window.filePickerCallback = callback;

                            var shortOptions = {
                                afterShowCont: function () {
                                    jQuery(".fileModal").nyroModal({callbacks: shortOptions});

                                }
                            };

                            jQuery.nmManual(
                                appUrl + '/files/showAll&modalPopUp=true',
                                {
                                    stack: true,
                                    callbacks: shortOptions,
                                    sizes: {
                                        minW: 500,
                                        minH: 500,
                                    }
                                }
                            );
                            jQuery.nmTop().elts.cont.css("zIndex", "1000010");
                            jQuery.nmTop().elts.bg.css("zIndex", "1000010");
                            jQuery.nmTop().elts.load.css("zIndex", "1000010");
                            jQuery.nmTop().elts.all.find('.nyroModalCloseButton').css("zIndex", "1000010");

                        }
                    }
                );

            },
            beforeClose: function () {
                location.reload();
            }
        },
        titleFromIframe: true
    }
};

export const _initModals = function () {
    jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
};

export const openModalManually = function (url) {
    jQuery.nmManual(url, canvasoptions);
};

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

    jQuery(".imageModal").nyroModal();

};

export const toggleMilestoneSelectors = function (trigger) {
    if (trigger == 'existing') {
        jQuery('#newMilestone, #milestoneSelectors').hide('fast');
        jQuery('#existingMilestone').show();
        _initModals();
    }
    if (trigger == 'new') {
        jQuery('#newMilestone').show();
        jQuery('#existingMilestone, #milestoneSelectors').hide('fast');
        _initModals();
    }

    if (trigger == 'hide') {
        jQuery('#newMilestone, #existingMilestone').hide('fast');
        jQuery('#milestoneSelectors').show('fast');
    }
};

export const setCloseModal = function () {
    closeModal = true;
};

export const initUserDropdown = function () {

    jQuery("body").on(
        "click",
        ".userDropdown .dropdown-menu a",
        function () {

            var dataValue = jQuery(this).attr("data-value").split("_");
            var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 3) {
                var canvasId = dataValue[0];
                var userId = dataValue[1];
                var profileImageId = dataValue[2];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: appUrl + '/api/ideas',
                        data:
                            {
                                id : canvasId,
                                author:userId
                        }
                    }
                ).done(
                    function () {
                        jQuery("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img").attr("src", appUrl + "/api/users?profileImage=" + userId);

                        jQuery.growl({message: i18n.__("short_notifications.user_updated")});
                    }
                );
            }
        }
    );
};

export const initStatusDropdown = function () {

    jQuery("body").on(
        "click",
        ".statusDropdown .dropdown-menu a",
        function () {

            var dataValue = jQuery(this).attr("data-value").split("_");
            var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 3) {
                var canvasItemId = dataValue[0];
                var status = dataValue[1];
                var statusClass = dataValue[2];


                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: appUrl + '/api/ideas',
                        data:
                            {
                                id : canvasItemId,
                                box:status
                        }
                    }
                ).done(
                    function () {
                        jQuery("#statusDropdownMenuLink" + canvasItemId + " span.text").text(dataLabel);
                        jQuery("#statusDropdownMenuLink" + canvasItemId).removeClass().addClass("" + statusClass + " dropdown-toggle f-left status ");
                        jQuery.growl({message: i18n.__("short_notifications.status_updated")});

                    }
                );
            }
        }
    );

};

export const setKanbanHeights = function () {

    var maxHeight = 0;

    var height = jQuery("html").height() - 320;
    jQuery("#sortableIdeaKanban .column .contentInner").css("height", height);

};

export const initIdeaKanban = function (statusList) {

    jQuery("#sortableIdeaKanban").disableSelection();

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
        cancel: ".portlet-toggle",
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
    setCloseModal: setCloseModal,
    toggleMilestoneSelectors:  toggleMilestoneSelectors,
    openModalManually: openModalManually,
    initMasonryWall: initMasonryWall,
    initBoardControlModal: initBoardControlModal,
    initWallImageModals: initWallImageModals,
    initUserDropdown: initUserDropdown,
    initStatusDropdown: initStatusDropdown,
    setKanbanHeights: setKanbanHeights,
    initIdeaKanban: initIdeaKanban
};
