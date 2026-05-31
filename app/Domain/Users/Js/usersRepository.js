leantime.usersRepository = (function () {

    //Functions

    var saveUserPhoto = function (photo) {
        var formData = new FormData();
        formData.append('file', photo);
        jQuery.ajax(
            {
                type: 'POST',
                url: leantime.appUrl + '/api/users',
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

        leantime.rpc('Users.Users.updateUserSettings', { category: 'views', setting: module, value: value })
            .catch(function (e) { console.error('Could not save view setting', e); });

    };

    // Make public what you want to have public, everything else is private
    return {
        saveUserPhoto: saveUserPhoto,
        updateUserViewSettings:updateUserViewSettings
    };
})();
