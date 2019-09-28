//Lets get this party started.

var leantime = leantime || {};


jQuery(document).on('click', function (e) {
    jQuery('[data-toggle="popover"],[data-original-title]').each(function () {
        //the 'is' for buttons that trigger popups
        //the 'has' for icons within a button that triggers a popup
        if (!jQuery(this).is(e.target) && jQuery(this).has(e.target).length === 0 && jQuery('.popover').has(e.target).length === 0) {
            ((jQuery(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false;  // fix for BS 3.3.6
        }

    });
});
