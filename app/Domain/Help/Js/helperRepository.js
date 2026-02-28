var leantime = window.leantime || (window.leantime = {});

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
        }).then(function (response) {
            if (!response.ok) {
                console.error('Failed to update modal settings:', response.status);
            }
        }).catch(function (error) {
            console.error('Error updating modal settings:', error);
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
        }).then(function (response) {
            if (!response.ok) {
                console.error('Failed to start tour:', response.status);
            }
        }).catch(function (error) {
            console.error('Error starting tour:', error);
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
        }).then(function (response) {
            if (!response.ok) {
                console.error('Failed to stop tour:', response.status);
            }
        }).catch(function (error) {
            console.error('Error stopping tour:', error);
        });

    };



    // Make public what you want to have public, everything else is private
    return {
        updateUserModalSettings: updateUserModalSettings,
        startingTour:startingTour,
        stopTour:stopTour
    };
})();
