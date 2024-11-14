leantime.swotCanvasController = (function () {

    var canvasName = 'swot';

    var setRowHeights = function () {

        var nbRows = 2;
        var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 25;

        var firstRowHeight = rowHeight / nbRows;
        jQuery("#firstRow div.contentInner").each(function () {
            if (jQuery(this).height() > firstRowHeight) {
                firstRowHeight = jQuery(this).height() + 50;
            }
        });
        jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);

        var secondRowHeight = rowHeight / nbRows;
        jQuery("#secondRow div.contentInner").each(function () {
            if (jQuery(this).height() > secondRowHeight) {
                secondRowHeight = jQuery(this).height() + 50;
            }
        });

        jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);

    };

    // --- Internal (not to be changed beyond this point) ---

    var closeModal = false;

    //Variables
    var canvasoptions = function () {
        return {
            sizes: {
                minW:  700,
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
                    window.htmx.process('.nyroModalCont');
                    jQuery("." + canvasName + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions());

                },
                beforeClose: function () {
                    location.reload();
                }
            },
            titleFromIframe: true

        }
    };


    //Functions

    var _initModals = function () {
        jQuery("." + canvasName + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
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
                            url: leantime.appUrl + '/api/' + canvasName + 'canvas',
                            data:
                                {
                                    id : canvasId,
                                    author:userId
                            }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img").attr("src", leantime.appUrl + "/api/users?profileImage=" + userId);
                            jQuery.growl({message: leantime.i18n.__("short_notifications.user_updated"), style: "success"});
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

                var dataValue = jQuery(this).attr("data-value").split("/");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 2) {
                    var canvasItemId = dataValue[0];
                    var status = dataValue[1];
                    var statusClass = jQuery(this).attr('class');


                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl + '/api/' + canvasName + 'canvas',
                            data:
                                {
                                    id : canvasItemId,
                                    status: status
                            }
                        }
                    ).done(
                        function () {
                            jQuery("#statusDropdownMenuLink" + canvasItemId + " span.text").text(dataLabel);
                            jQuery("#statusDropdownMenuLink" + canvasItemId).removeClass().addClass(statusClass + " dropdown-toggle f-left status ");
                            jQuery.growl({message: leantime.i18n.__("short_notifications.status_updated")});

                        }
                    );
                }
            }
        );

    };

    var initRelatesDropdown = function () {

        jQuery("body").on(
            "click",
            ".relatesDropdown .dropdown-menu a",
            function () {

                var dataValue = jQuery(this).attr("data-value").split("/");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 2) {
                    var canvasItemId = dataValue[0];
                    var relates = dataValue[1];
                    var relatesClass = jQuery(this).attr('class');


                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl + '/api/' + canvasName + 'canvas',
                            data:
                                {
                                    id : canvasItemId,
                                    relates: relates
                            }
                        }
                    ).done(
                        function () {
                            jQuery("#relatesDropdownMenuLink" + canvasItemId + " span.text").text(dataLabel);
                            jQuery("#relatesDropdownMenuLink" + canvasItemId).removeClass().addClass(relatesClass + " dropdown-toggle f-left relates ");
                            jQuery.growl({message: leantime.i18n.__("short_notifications.relates_updated")});

                        }
                    );
                }
            }
        );

    };

    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        initRelatesDropdown:initRelatesDropdown,
        setRowHeights:setRowHeights
    };

})();
