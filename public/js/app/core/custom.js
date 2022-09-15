jQuery.noConflict();

jQuery(document).ready(function () {

    // dropdown in leftmenu
    jQuery('.leftmenu .dropdown > a').click(function () {
        if (!jQuery(this).next().is(':visible')) {
            jQuery(this).next().slideDown('fast');
        }
        else {
            jQuery(this).next().slideUp('fast');
        }
        return false;
    });

    if (jQuery('.widgettitle .close').length > 0) {
        jQuery('.widgettitle .close').click(function () {
            jQuery(this).parents('.widgetbox').fadeOut(function () {
                jQuery(this).remove();
            });
        });
    }

});