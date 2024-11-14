leantime.usersService = (function () {

    //Functions

    var saveUserPhoto = function (photo) {
        leantime.usersRepository.saveUserPhoto(photo);
    };

    // Make public what you want to have public, everything else is private
    return {
        saveUserPhoto: saveUserPhoto
    };
})();
