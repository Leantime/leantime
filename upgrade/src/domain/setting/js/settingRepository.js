leantime.settingRepository = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    //Constructor
    (function () {

    })();

    //Functions

    var saveLogo = function (photo) {
        var formData = new FormData();
        formData.append('file', photo);
        jQuery.ajax(
            {
                type: 'POST',
                url: leantime.appUrl+'/api/setting',
                data: formData,
                processData: false,
                contentType: false,
                success: function (resp) {
                    jQuery('#save-logo').removeClass('running');
                    location.reload();
                },
                error: function (err) {
                    console.log(err);
                }
            }
        );
    };

    // Make public what you want to have public, everything else is private
    return {
        saveLogo: saveLogo
    };
})();
