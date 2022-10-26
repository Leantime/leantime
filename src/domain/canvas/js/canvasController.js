leantime.canvasController = (function () {

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

        jQuery(".importCanvasLink").click(function() {

            jQuery('#importCanvas').modal('show');

        });

    };

    // Make public what you want to have public, everything else is private
    return {
        initFilterBar:initFilterBar,
        initCanvasLinks:initCanvasLinks
    };
    
})();
