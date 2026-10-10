leantime.blueprintsController = (function () {

    var canvasName = '';

    var setCanvasName = function (name) {
        canvasName = name;
    };

    var setRowHeights = function () {
        // Collect all unique row IDs from .canvas-row elements
        var rowIds = [];
        jQuery(".canvas-row[id]").each(function () {
            var id = jQuery(this).attr("id");
            if (id && rowIds.indexOf(id) === -1) {
                rowIds.push(id);
            }
        });

        if (rowIds.length === 0) {
            return;
        }

        var nbRows = rowIds.length;
        var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 25;
        var perRowHeight = rowHeight / nbRows;

        // For each row, find the tallest content and set all columns to that height
        for (var i = 0; i < rowIds.length; i++) {
            var rowSelector = "#" + rowIds[i];
            var maxHeight = perRowHeight;

            jQuery(rowSelector + " div.contentInner").each(function () {
                if (jQuery(this).height() > maxHeight) {
                    maxHeight = jQuery(this).height() + 50;
                }
            });

            jQuery(rowSelector + " .column .contentInner").css("height", maxHeight);
        }
    };

    var initFilterBar = function () {

        jQuery(window).bind("load", function () {
            jQuery(".loading").fadeOut();
            jQuery(".filterBar .row-fluid").css("opacity", "1");
        });

    };

    var initCanvasLinks = function () {

        jQuery(".addCanvasLink").nyroModal();

        jQuery(".editCanvasLink").click(function () {
            jQuery('#editCanvas').modal('show');
        });

        jQuery(".cloneCanvasLink").click(function () {
            jQuery('#cloneCanvas').modal('show');
        });

        jQuery(".mergeCanvasLink").click(function () {
            jQuery('#mergeCanvas').modal('show');
        });

        jQuery(".importCanvasLink").click(function () {
            jQuery('#importCanvas').modal('show');
        });

    };

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
                    jQuery(".blueprintsCanvasModal, #commentForm, .blueprintsCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
                },
                beforeClose: function () {
                    location.reload();
                }
            },
            titleFromIframe: true
        };
    };

    //Functions

    var _initModals = function () {
        jQuery(".blueprintsCanvasModal, #commentForm, .blueprintsCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions());
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

    return {
        setCanvasName: setCanvasName,
        setRowHeights: setRowHeights,
        initFilterBar: initFilterBar,
        initCanvasLinks: initCanvasLinks,
        setCloseModal: setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually: openModalManually
    };

})();
