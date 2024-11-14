var leantime = leantime || {};

leantime.menuRepository = (function () {

    //Functions

    var updateUserMenuSettings = function (menuStateValue) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/sessions',
                data:
                    {
                        menuState : menuStateValue
                }
            }
        ).done(
            function () {


            }
        );

    };

    // Make public what you want to have public, everything else is private
    return {
        updateUserMenuSettings: updateUserMenuSettings
    };
})();
