leantime.bmcanvasController = (function () {

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

                jQuery(".bmCanvasModal, #commentForm, #commentForm .deleteComment, .bmCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);

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
                _initModals();
            }
        );

    })();

    //Functions

    var _initModals = function () {
        jQuery(".bmCanvasModal, #commentForm, #commentForm .deleteComment, .bmCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
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

    var setDbmCanvasHeights = function () {

        var rowHeight = (jQuery("html").height()- 320 - 40);

		var firstRowHeight = rowHeight * 0.375;
        jQuery("#firstRow div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeight){
                firstRowHeight = jQuery(this).height()+25;
            }
        });
		var secondRowHeight = rowHeight * 0.375;
        jQuery("#secondRow div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeight){
                secondRowHeight = jQuery(this).height()+25;
            }
        });
		var secondRowHeightTop = secondRowHeight * 0.5;
        jQuery("#secondRowTop div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeightTop){
                secondRowHeightTop = jQuery(this).height()+25;
            }
        });
		var secondRowHeightBottom = secondRowHeight * 0.5;
        jQuery("#secondRowBottom div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeightBottom){
                secondRowHeightBottom = jQuery(this).height()+25;
            }
        });
		if(secondRowHeightTop + secondRowHeightBottom+25> secondRowHeight) {
			secondRowHeight = secondRowHeightTop + secondRowHeightBottom + 50;
		}
		var thirdRowHeight = rowHeight * 0.25;
        jQuery("#thirdRow div.contentInner").each(function(){
            if(jQuery(this).height() > thirdRowHeight){
                thirdRowHeight = jQuery(this).height()+25;
            }
        });

        jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);
        jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);
        jQuery("#secondRowTop .column .contentInner").css("height", secondRowHeightTop);
        jQuery("#secondRowBottom .column .contentInner").css("height", secondRowHeightBottom);
        jQuery("#thirdRow .column .contentInner").css("height", thirdRowHeight);
    };

    var setObmCanvasHeights = function () {
        var rowHeight = (jQuery("html").height()- 320 - 60);

		var firstRowHeight = rowHeight * 0.666;
        jQuery("#firstRow div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeight){
                firstRowHeight = jQuery(this).height()+25;
            }
        });
		var firstRowHeightTop = firstRowHeight * 0.5;
        jQuery("#firstRowTop div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeightTop){
                firstRowHeightTop = jQuery(this).height()+25;
            }
        });
		var firstRowHeightBottom = firstRowHeight * 0.5;
        jQuery("#firstRowBottom div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeightBottom){
                firstRowHeightBottom = jQuery(this).height()+25;
            }
        });
		if(firstRowHeightTop + firstRowHeightBottom+25 > firstRowHeight) {
			firstRowHeight = firstRowHeightTop + firstRowHeightBottom + 50;
		}
		var secondRowHeight = rowHeight * 0.333;
        jQuery("#secondRow div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeight){
                secondRowHeight = jQuery(this).height()+25;
            }
        });

        jQuery("#firstRow div.contentInner").css("height", firstRowHeight);
        jQuery("#firstRowTop div.contentInner").css("height", firstRowHeightTop);
        jQuery("#firstRowBottom div.contentInner").css("height", firstRowHeightBottom);
        jQuery("#secondRow div.contentInner").css("height", secondRowHeight);
    };

    var setLbmCanvasHeights = function () {

        var firstRowHeight = (jQuery("html").height()- 320 - 20) * .6;
        jQuery("#firstRow div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeight){
                firstRowHeight = jQuery(this).height()+25;
            }
        });
        var secondRowHeight = (jQuery("html").height()- 320 - 20) * .4;
        jQuery("#secondRow div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeight){
                secondRowHeight = jQuery(this).height()+25;
            }
        });
        jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);
        jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);

    };

	var setRowHeights() = function () {
	};

    var initFilterBar = function () {

        jQuery(window).bind("load", function () {
            jQuery(".loading").fadeOut();
            jQuery(".filterBar .row-fluid").css("opacity", "1");


        });

    };

    var initCanvasLinks = function () {

        jQuery(".addCanvasLink").click(function() {

            jQuery('#addCanvas').modal('show');

        });

        jQuery(".editCanvasLink").click(function() {

            jQuery('#editCanvas').modal('show');

        });
		
        jQuery(".cloneCanvasLink").click(function() {

            jQuery('#cloneCanvas').modal('show');

        });

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
                            url: leantime.appUrl+'/api/bmcanvas',
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

    var initStatusDropdown = function () {

        jQuery("body").on(
            "click", ".statusDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 2) {

                    var canvasItemId = dataValue[0];
                    var status = dataValue[1];


                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/bmcanvas',
                            data:
                                {
                                    id : canvasItemId,
                                    status:status
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#statusDropdownMenuLink"+canvasItemId+" span.text").text(dataLabel);
                            jQuery("#statusDropdownMenuLink"+canvasItemId).removeClass().addClass("label-"+status+" dropdown-toggle f-left status ");
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.status_updated"));

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
        initFilterBar:initFilterBar,
        initCanvasLinks:initCanvasLinks,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        setLbmCanvasHeights:setLbmCanvasHeights,
        setDbmCanvasHeights:setDbmCanvasHeights,
        setObmCanvasHeights:setObmCanvasHeights
    };
	
})();
