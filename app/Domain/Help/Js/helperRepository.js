var leantime = leantime || {};

leantime.helperRepository = (function () {

    //Functions

    var updateUserModalSettings = function (module, permanent = false) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/users',
                data:
                {
                    settings: module,
                    permanent: permanent ? 1 : 0,
                    patchModalSettings: 1
                }
            }
        ).done(
            function () {
                    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

            }
        );

    };

    var startingTour = function () {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/sessions',
                data:
                {
                    tourActive : 1
                }
            }
        ).done(
            function () {
                    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

            }
        );

    };

    var stopTour = function () {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/sessions',
                data:
                {
                    tourActive : 0
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
        updateUserModalSettings: updateUserModalSettings,
        startingTour:startingTour,
        stopTour:stopTour
    };
})();
