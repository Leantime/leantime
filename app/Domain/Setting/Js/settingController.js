import jQuery from "jquery";
import { saveLogo } from './settingService';
import { appUrl } from 'js/app/core/instance-info.module.mjs'

// let _uploadResult;
let _croppieInstance = null;

export const readURL = function (input) {
    clearCroppie();
    console.log('Input is: ', input);
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        var profileImg = jQuery('#logoImg');
        reader.onload = function (e) {
            //profileImg.attr('src', e.currentTarget.result);
            
            _croppieInstance = profileImg
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

            _croppieInstance.croppie(
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
    const logoImg = jQuery('#logoImg');
    if (logoImg.data('croppie')) {
        logoImg.croppie('destroy');
    }
    _croppieInstance = null;
    jQuery("#previousImage").show();
};

export const saveCroppie = function () {

    if (!_croppieInstance) {
        console.error('No image has been loaded');
        return;
    }

    jQuery('#save-logo').addClass('running');

    jQuery('#logoImg').attr('src', appUrl + '/images/loaders/loader28.gif');
    _croppieInstance.croppie(
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
export const settingController = {
    readURL: readURL,
    clearCroppie: clearCroppie,
    saveCroppie: saveCroppie
};
