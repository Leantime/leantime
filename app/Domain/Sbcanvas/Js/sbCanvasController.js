leantime.sbCanvasController = (function () {

    // To be set
    var canvasName = 'sb';

    // To be implemented
    var setRowHeights = function () {

        var stakeholderRowHeight = 0;
        jQuery("#stakeholderRow div.contentInner").each(function () {
            if (jQuery(this).height() > stakeholderRowHeight) {
                stakeholderRowHeight = jQuery(this).height() + 35;
            }
        });
        var financialsRowHeight = 0;
        jQuery("#financialsRow div.contentInner").each(function () {
            if (jQuery(this).height() > financialsRowHeight) {
                financialsRowHeight = jQuery(this).height() + 35;
            }
        });
        var culturechangeRowHeight = 0;
        jQuery("#culturechangeRow div.contentInner").each(function () {
            if (jQuery(this).height() > culturechangeRowHeight) {
                culturechangeRowHeight = jQuery(this).height() + 35;
            }
        });
        jQuery("#stakeholderRow .column .contentInner").css("height", stakeholderRowHeight);
        jQuery("#financialsRow .column .contentInner").css("height", financialsRowHeight);
        jQuery("#culturechangeRow .column .contentInner").css("height", culturechangeRowHeight);

    };

    // Make public what you want to have public, everything else is private
    return {
        setRowHeights:setRowHeights
    };

})();
