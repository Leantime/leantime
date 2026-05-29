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

        leantime.rpc('Api.Api.setTourActive', { tourActive: 1 })
            .catch(function (e) { console.error('Could not start tour', e); });

    };

    var stopTour = function () {

        leantime.rpc('Api.Api.setTourActive', { tourActive: 0 })
            .catch(function (e) { console.error('Could not stop tour', e); });

    };



    // Make public what you want to have public, everything else is private
    return {
        updateUserModalSettings: updateUserModalSettings,
        startingTour:startingTour,
        stopTour:stopTour
    };
})();
