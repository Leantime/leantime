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

    // dropdown menu for profile image
    jQuery('.userloggedinfo img').click(function () {
        if (jQuery(window).width() < 480) {
            var dm = jQuery('.userloggedinfo .userinfo');
            if (dm.is(':visible')) {
                dm.hide();
            } else {
                dm.show();
            }
        }
    });
    // dynamic table
    jQuery('#dyntable2').dataTable({
        "sPaginationType": "full_numbers",
        "aaSortingFixed": [[0, 'asc']],
        "fnDrawCallback": function (oSettings) {

        }
    });

    jQuery('#dyntable').dataTable({
        "bScrollInfinite": true,
        "bScrollCollapse": true,
        "sScrollY": "300px"
    });


});