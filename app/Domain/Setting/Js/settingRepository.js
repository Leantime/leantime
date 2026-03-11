leantime.settingRepository = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    //Constructor
    (function () {

    })();

    //Functions

    var saveLogo = function (photo) {
        var formData = new FormData();
        formData.append('file', photo);
        fetch(leantime.appUrl + '/api/setting', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(function (resp) {
            var saveLogoEl = document.querySelector('#save-logo');
            if (saveLogoEl) {
                saveLogoEl.classList.remove('running');
            }
            location.reload();
        }).catch(function (err) {
            console.log(err);
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        saveLogo: saveLogo
    };
})();
