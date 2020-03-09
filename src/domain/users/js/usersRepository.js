leantime.usersRepository = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    //Constructor
    (function () {

    })();

    //Functions

    var saveUserPhoto = function (photo) {
        var formData = new FormData();
        formData.append('file', photo);
        jQuery.ajax(
            {
                type: 'POST',
                url: leantime.appUrl+'/api/users',
                data: formData,
                processData: false,
                contentType: false,
                success: function (resp) {

                    jQuery('#save-picture').removeClass('running');

                    location.reload();
                },
                error:  function (err) {
                    console.log(err);
                }
            }
        );
    };

    var updateUserViewSettings = function (module, value) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl+'/api/users',
                data:
                {
                    patchViewSettings : module,
                    value: value
                }
            }
        ).done(
            function () {
                    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

            }
        );

    };

    // Make public what you want to have public, everything else is private
    return {
        saveUserPhoto: saveUserPhoto,
        updateUserViewSettings:updateUserViewSettings
    };
})();
