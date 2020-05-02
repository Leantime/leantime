leantime.settingController = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    var _uploadResult;

    //Constructor
    (function () {

    })();

    //Functions

    var readURL = function (input) {

        clearCroppie();

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            var profileImg = jQuery('#logoImg');
            reader.onload = function (e) {
                //profileImg.attr('src', e.currentTarget.result);

                _uploadResult = profileImg
                    .croppie(
                        {
                            enableExif: true,
                            enforceBoundary: false,
                            viewport:{
                                width: 260,
                                height: 60,
                                type: 'square'
                            },
                            boundary: {
                                width: 400,
                                height: 200
                            }
                        }
                    );

                _uploadResult.croppie(
                    'bind', {
                        url: e.currentTarget.result
                    }
                );

                jQuery("#previousImage").hide();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var clearCroppie = function () {
        jQuery('#logoImg').croppie('destroy');
        jQuery("#previousImage").show();
    };

    var saveCroppie = function () {

        jQuery('#save-logo').addClass('running');

        jQuery('#logoImg').attr('src', leantime.appUrl+'/images/loaders/loader28.gif');
        _uploadResult.croppie(
            'result', {
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
