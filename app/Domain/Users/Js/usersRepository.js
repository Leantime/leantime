leantime.usersRepository = (function () {

    //Functions

    var saveUserPhoto = function (photo) {
        var formData = new FormData();
        formData.append('file', photo);
        fetch(leantime.appUrl + '/api/users', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(function (resp) {
            var savePictureEl = document.querySelector('#save-picture');
            if (savePictureEl) {
                savePictureEl.classList.remove('running');
            }
            location.reload();
        }).catch(function (err) {
            console.log(err);
        });
    };

    var updateUserViewSettings = function (module, value) {

        fetch(leantime.appUrl + '/api/users', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                patchViewSettings: module,
                value: value
            })
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        saveUserPhoto: saveUserPhoto,
        updateUserViewSettings:updateUserViewSettings
    };
})();
