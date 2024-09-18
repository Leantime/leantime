import jQuery from 'jquery';
import { appUrl } from 'js/app/core/instance-info.module.mjs';

export const updateUserModalSettings = function (module) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/users',
            data:
            {
                settings : module,
                patchModalSettings: 1
            }
        }
    ).done(
        function () {
                //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

        }
    );
};

export const startingTour = function () {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/sessions',
            data:
            {
                tourActive : 1
            }
        }
    ).done(
        function () {
                //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

        }
    );
};

export const stopTour = function () {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/sessions',
            data:
            {
                tourActive : 0
            }
        }
    ).done(
        function () {
                //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

        }
    );
};

export default {
    updateUserModalSettings: updateUserModalSettings,
    startingTour: startingTour,
    stopTour: stopTour,
};
