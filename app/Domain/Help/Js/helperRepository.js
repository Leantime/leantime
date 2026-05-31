var leantime = leantime || {};

leantime.helperRepository = (function () {

    //Functions

    var updateUserModalSettings = function (module, permanent = false) {

        leantime.rpc('Users.Users.saveModalDismissal', { modalKey: module, permanent: !!permanent })
            .catch(function (e) { console.error('Could not save modal setting', e); });

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
