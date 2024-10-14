import jQuery from "jquery";
import { saveLogo } from './settingService';
import { appUrl } from 'js/app/core/instance-info.module.mjs'

let _uploadResult;

export const readURL = function (input) {
    clearCroppie();

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        var profileImg = jQuery('#logoImg');
        reader.onload = function (e) {
            //profileImg.attr('src', e.currentTarget.result);

            this._uploadResult = profileImg
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

            this._uploadResult.croppie(
                'bind',
                {
                    url: e.currentTarget.result
                }
            );

            jQuery("#previousImage").hide();
        };

        reader.readAsDataURL(input.files[0]);
    }
};

export const clearCroppie = function () {
    jQuery('#logoImg').croppie('destroy');
    jQuery("#previousImage").show();
};

export const saveCroppie = function () {
    jQuery('#save-logo').addClass('running');

    jQuery('#logoImg').attr('src', appUrl + '/images/loaders/loader28.gif');
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
                saveLogo(result);
        }
    );
};

// Make public what you want to have public, everything else is private
export default {
    readURL: readURL,
    clearCroppie: clearCroppie,
    saveCroppie: saveCroppie
};

