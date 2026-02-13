var leantime = leantime || {};

leantime.helperRepository = (function () {

    //Functions

    var updateUserModalSettings = function (module, permanent = false) {

        fetch(leantime.appUrl + '/api/users', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                settings: module,
                permanent: permanent ? 1 : 0,
                patchModalSettings: 1
            })
        });

    };

    var startingTour = function () {

        fetch(leantime.appUrl + '/api/sessions', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                tourActive: 1
            })
        });

    };

    var stopTour = function () {

        fetch(leantime.appUrl + '/api/sessions', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                tourActive: 0
            })
        });

    };



    // Make public what you want to have public, everything else is private
    return {
        updateUserModalSettings: updateUserModalSettings,
        startingTour:startingTour,
        stopTour:stopTour
    };
})();
