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

    // Make public what you want to have public, everything else is private
    return {
        copyUrl:copyUrl,
        initConfettiClick:initConfettiClick
    };

})();
