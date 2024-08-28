import jQuery from 'jquery';
import { appUrl } from 'js/app/core/instance-info.module';

export const saveLogo = function (photo) {
    var formData = new FormData();
    formData.append('file', photo);
    jQuery.ajax(
        {
            type: 'POST',
            url: appUrl + '/api/setting',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {
                jQuery('#save-logo').removeClass('running');
                location.reload();
            },
            error: function (err) {
                console.log(err);
            }
        }
    );
};

// Make public what you want to have public, everything else is private
export default {
    saveLogo: saveLogo
};
