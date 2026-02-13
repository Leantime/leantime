leantime.ideasController = (function () {

    var closeModal = false;

    //Variables
    var canvasoptions = function () {
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

                }
            }
        }
    };

    //Functions

    var _initModals = function () {
        jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
    };

    var initMasonryWall = function () {

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
                url: leantime.appUrl + '/api/ideas',
                data: {
                    action:"ideaSort",
                    payload: ideaSort
                }

            });
        }


        $grid.on('dragItemPositioned',orderItems);
    };

    var initBoardControlModal = function () {


    };

    var initWallImageModals = function () {

        jQuery('.mainIdeaContent img').each(function () {
            jQuery(this).wrap("<a href='" + jQuery(this).attr("src") + "' class='imageModal'></a>");
        });

        jQuery(".imageModal").nyroModal();

    };


    var toggleMilestoneSelectors = function (trigger) {
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

    var setCloseModal = function () {
        closeModal = true;
    };

    var initUserDropdown = function () {

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
                            url: leantime.appUrl + '/api/ideas',
                            data:
                                {
                                    id : canvasId,
                                    author:userId
                            }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img").attr("src", leantime.appUrl + "/api/users?profileImage=" + userId);

                            jQuery.growl({message: leantime.i18n.__("short_notifications.user_updated")});
                        }
                    );
                }
            }
        );
    };

    var initStatusDropdown = function () {

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
                            url: leantime.appUrl + '/api/ideas',
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
                            jQuery.growl({message: leantime.i18n.__("short_notifications.status_updated")});

                        }
                    );
                }
            }
        );

    };


    var setKanbanHeights = function () {

        var maxHeight = 0;

        var height = jQuery("html").height() - 320;
        jQuery("#sortableIdeaKanban .column .contentInner").css("height", height);

    };

    var initIdeaKanban = function (statusList) {

        jQuery("#sortableIdeaKanban .ticketBox").hover(function () {
            jQuery(this).css("background", "var(--kanban-card-hover)");
        },function () {
            jQuery(this).css("background", "var(--kanban-card-bg)");
        });

        function serializeSortable(containerEl) {
            var items = containerEl.querySelectorAll(':scope > .moveable');
            var parts = [];
            items.forEach(function (item) {
                if (item.id) {
                    var idx = item.id.indexOf('_');
                    if (idx !== -1) {
                        parts.push(item.id.substring(0, idx) + '[]=' + item.id.substring(idx + 1));
                    }
                }
            });
            return parts.join('&');
        }

        document.querySelectorAll("#sortableIdeaKanban .contentInner").forEach(function (contentInner) {
            if (contentInner._sortableInstance) contentInner._sortableInstance.destroy();
            contentInner._sortableInstance = new Sortable(contentInner, {
                group: 'idea-kanban',
                draggable: '.moveable',
                ghostClass: 'ui-state-highlight',
                filter: '.portlet-toggle',
                animation: 150,

                onStart: function (evt) {
                    evt.item.classList.add('tilt');
                    tilt_direction(jQuery(evt.item));
                },

                onEnd: function (evt) {
                    evt.item.classList.remove('tilt');
                    jQuery("html").unbind('mousemove', jQuery(evt.item).data("move_handler"));
                    jQuery(evt.item).removeData("move_handler");

                    var statusPostData = {
                        action: "statusUpdate",
                        payload: {}
                    };

                    for (var i = 0; i < statusList.length; i++) {
                        var col = document.querySelector(".contentInner.status_" + statusList[i]);
                        if (col) {
                            statusPostData.payload[statusList[i]] = serializeSortable(col);
                        }
                    }

                    jQuery.ajax({
                        type: 'POST',
                        url: leantime.appUrl + '/api/ideas',
                        data: statusPostData
                    });
                }
            });
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
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        initMasonryWall:initMasonryWall,
        initBoardControlModal:initBoardControlModal,
        initWallImageModals:initWallImageModals,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        setKanbanHeights:setKanbanHeights,
        initIdeaKanban:initIdeaKanban
    };
})();
