leantime.snippets = (function () {

    var copyUrl = function (field) {

        // Get the text field
        var copyText = document.getElementById(field);

        // Select the text field
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        jQuery.growl({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

    };

    var initConfettiClick = function() {
        jQuery(".confetti").click(function(){
            confetti.start();
        });
    };

    var accordionToggle = function (id) {

        var currentLink = jQuery("#accordion_toggle_"+id).find("i.fa").first();
        var submenuName = '#accordion_'+id;
        var submenuState = "closed";

        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
            submenuState = "open";

        }else{

            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");

            jQuery('#accordion_'+id).slideUp("fast");
            submenuState = "closed";
        }

        jQuery.ajax({
            type : 'PATCH',
            url  : leantime.appUrl + '/api/submenu',
            data : {
                submenu : submenuName,
                state   : submenuState
            }
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        copyUrl:copyUrl,
        initConfettiClick:initConfettiClick,
        accordionToggle:accordionToggle
    };

})();
