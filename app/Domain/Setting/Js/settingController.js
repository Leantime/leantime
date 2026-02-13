leantime.settingController = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    var _uploadResult;

    //Functions

    var readURL = function (input) {

        clearCroppie();

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            var profileImg = jQuery('#logoImg');
            reader.onload = function (e) {

                _uploadResult = profileImg
                    .croppie(
                        {
                            enableExif: true,
                            enforceBoundary: false,
                            viewport:{
                                width: 220,
                                height: 40,
                                type: 'square'
                            },
                            boundary: {
                                width: 400,
                                height: 200
                            }
                        }
                    );

                _uploadResult.croppie(
                    'bind',
                    {
                        url: e.currentTarget.result
                    }
                );

                var previousImageEl = document.querySelector('#previousImage');
                if (previousImageEl) {
                    previousImageEl.style.display = 'none';
                }
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var clearCroppie = function () {
        jQuery('#logoImg').croppie('destroy');
        var previousImageEl = document.querySelector('#previousImage');
        if (previousImageEl) {
            previousImageEl.style.display = '';
        }
    };

    var saveCroppie = function () {

        var saveLogoEl = document.querySelector('#save-logo');
        if (saveLogoEl) {
            saveLogoEl.classList.add('running');
        }

        var logoImgEl = document.querySelector('#logoImg');
        if (logoImgEl) {
            logoImgEl.setAttribute('src', leantime.appUrl + '/images/loaders/loader28.gif');
        }
        _uploadResult.croppie(
            'result',
            {
                type: "blob",
                circle: false,
                size: "original",
                quality:1

            }
        ).then(
            function (result) {
                    leantime.settingService.saveLogo(result);
            }
        );
    };

    // Make public what you want to have public, everything else is private
    return {
        readURL: readURL,
        clearCroppie: clearCroppie,
        saveCroppie: saveCroppie
    };
})();
