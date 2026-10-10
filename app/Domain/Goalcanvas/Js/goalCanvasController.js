leantime.goalCanvasController = (function () {

    var canvasName = 'goal';


    var setRowHeights = function () {

        var nbRows = 2;
        var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 25;

        /*
        var firstRowHeight = rowHeight / nbRows;
        jQuery("#firstRow div.contentInner").each(function(){
            if(jQuery(this).height() > firstRowHeight){
                firstRowHeight = jQuery(this).height() + 50;
            }
        });
        jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);

        var secondRowHeight = rowHeight / nbRows;
        jQuery("#secondRow div.contentInner").each(function(){
            if(jQuery(this).height() > secondRowHeight){
                secondRowHeight = jQuery(this).height() + 50;
            }
        });

        jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);

         */

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
                    jQuery("." + canvasName + "CanvasModal, #commentForm, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions());

                },
                beforeClose: function () {
                    location.reload();
                }
            },
            titleFromIframe: true

        }
    };

    var _initModals = function () {
        jQuery("." + canvasName + "CanvasModal, #commentForm, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
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

    var initProgressChart = function (chartId, complete, incomplete ) {
        var config = {
            type: 'doughnut',

            data: {
                datasets: [{
                    data: [
                        complete,
                        incomplete

                    ],
                    backgroundColor: [
                        leantime.dashboardController.chartColors.green,
                        leantime.dashboardController.chartColors.grey

                    ],
                    label: leantime.i18n.__("label.project_done")
                }],
                labels: [
                    complete + '%',
                ]
            },
            options: {
                maintainAspectRatio : true,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'none',
                    },
                    title: {
                        display: false,
                        text: 'Complete'
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById(chartId).getContext('2d');
        _progressChart = new Chart(ctx, config);
    };


    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        setRowHeights:setRowHeights,
        initProgressChart:initProgressChart
    };

})();
