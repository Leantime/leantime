leantime.generalController = (function () {

    //Variables

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initPopOvers();
            }
        );

    })();

    //Functions
    var _initPopOvers = function() {
        jQuery('.popoverlink').popover({trigger: 'hover'});
    }


    // Make public what you want to have public, everything else is private
    return {};

})();
