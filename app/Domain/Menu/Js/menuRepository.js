var leantime = leantime || {};

leantime.menuRepository = (function () {

    //Functions

    var updateUserMenuSettings = function (menuStateValue) {

        leantime.rpc('Api.Api.setMainMenuState', { state: menuStateValue })
            .catch(function (e) { console.error('Could not update menu state', e); });

    };

    // Make public what you want to have public, everything else is private
    return {
        updateUserMenuSettings: updateUserMenuSettings
    };
})();
