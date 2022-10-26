var leantime = leantime || {};

leantime.helperRepository = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    //Constructor
    (function () {

    })();

    //Functions

    var updateUserModalSettings = function (module) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl+'/api/users',
                data:
                {
                    settings : module,
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
                url: leantime.appUrl+'/api/sessions',
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
                url: leantime.appUrl+'/api/sessions',
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
