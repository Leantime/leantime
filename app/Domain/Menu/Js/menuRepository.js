var leantime = window.leantime || (window.leantime = {});

leantime.menuRepository = (function () {

    //Functions

    var updateUserMenuSettings = function (menuStateValue) {

        fetch(leantime.appUrl + '/api/sessions', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                menuState: menuStateValue
            })
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        updateUserMenuSettings: updateUserMenuSettings
    };
})();
