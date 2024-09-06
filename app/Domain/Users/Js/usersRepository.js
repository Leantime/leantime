import { appUrl } from 'js/app/core/instance-info.module';
import jQuery from 'jquery';

export const saveUserPhoto = function (photo) {
    var formData = new FormData();
    formData.append('file', photo);
    jQuery.ajax(
        {
            type: 'POST',
            url: appUrl + '/api/users',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {

                jQuery('#save-picture').removeClass('running');

                location.reload();
            },
            error:  function (err) {
                console.log(err);
            }
        }
    );
};

export const updateUserViewSettings = function (module, value) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/users',
            data:
            {
                patchViewSettings : module,
                value: value
            }
        }
    ).done(
        function () {
            //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
        }
    );
};

// Make public what you want to have public, everything else is private
export default {
    saveUserPhoto: saveUserPhoto,
    updateUserViewSettings: updateUserViewSettings
};
