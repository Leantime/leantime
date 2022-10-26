leantime.retroController = (function () {

    var closeModal = false;

    //Variables
    var canvasoptions = {
        sizes: {
            minW:  700,
            minH: 1000,
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function() {
                jQuery(".showDialogOnLoad").show();
                if(closeModal == true){
                    closeModal = false;
                    location.reload();
                }
            },
            afterShowCont: function () {

                jQuery(".retroModal, #commentForm, #commentForm .deleteComment, .retroMilestone .deleteMilestone").nyroModal(canvasoptions);


            },
            beforeClose: function () {
                location.reload();
            }
        },
        titleFromIframe: true

    };


    //Constructor
    (function () {
        jQuery(document).ready(
            function () {

            }
        );

    })();

    //Functions

    var initModals = function () {
        jQuery(".retroModal, #commentForm, #commentForm .deleteComment, .retroMilestone .deleteMilestone").nyroModal(canvasoptions);
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
    };

    var initBoardControlModal = function () {

        jQuery(".addItem").click(function () {
            jQuery("#box").val(jQuery(this).attr("id"));
            jQuery('#addItem').modal('show');

        });

        jQuery(".addCanvasLink").click(function () {

            jQuery('#addCanvas').modal('show');

        });

        jQuery(".editCanvasLink").click(function () {

            jQuery('#editCanvas').modal('show');

        });

    };

    var initWallImageModals = function () {

        jQuery('.mainIdeaContent img').each(function () {
            jQuery(this).wrap("<a href='" + jQuery(this).attr("src") + "' class='imageModal'></a>");
        });

        jQuery(".imageModal").nyroModal();

    };


    var toggleMilestoneSelectors = function (trigger) {
        if(trigger == 'existing') {
            jQuery('#newMilestone, #milestoneSelectors').hide('fast');
            jQuery('#existingMilestone').show();
            _initModals();

        }
        if(trigger == 'new') {
            jQuery('#newMilestone').show();
            jQuery('#existingMilestone, #milestoneSelectors').hide('fast');
            _initModals();
        }

        if(trigger == 'hide') {
            jQuery('#newMilestone, #existingMilestone').hide('fast');
            jQuery('#milestoneSelectors').show('fast');
        }
    };

    var setCloseModal = function() {
        closeModal = true;
    };

    var initUserDropdown = function () {

        jQuery("body").on(
            "click", ".userDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 3) {

                    var canvasId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/retrospectives',
                            data:
                                {
                                    id : canvasId,
                                    author:userId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink"+canvasId+" span.text span#userImage"+canvasId+" img").attr("src", leantime.appUrl+"/api/users?profileImage="+profileImageId);

                            jQuery.jGrowl(leantime.i18n.__("short_notifications.user_updated"));
                        }
                    );

                }
            }
        );
    };



    var setKanbanHeights = function () {

        var maxHeight = 0;

        var height = jQuery("html").height()-320;
        jQuery("#sortableRetroKanban .column .contentInner").css("height", height);

    };

    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        initModals:initModals,
        initBoardControlModal:initBoardControlModal,
        initUserDropdown:initUserDropdown,
        setKanbanHeights:setKanbanHeights,
    };
})();
