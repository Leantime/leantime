import jQuery from 'jquery';

export const updateUserMenuSettings = function (menuStateValue) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: leantime.appUrl + '/api/sessions',
            data:
                {
                    menuState : menuStateValue
            }
        }
    ).done(
        function () {


        }
    );
};

// Make public what you want to have public, everything else is private
export default {
    updateUserMenuSettings: updateUserMenuSettings
};
