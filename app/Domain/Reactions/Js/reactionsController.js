import jQuery from 'jquery';
import { appUrl } from 'js/app/core/instance-info.module';

export const addReactions = function (module, moduleId, reaction, clb) {
    jQuery.ajax({
        type: 'POST',
        url: appUrl + '/api/reactions',
        data: {
            'action': 'add',
            'module': module,
            'moduleId': moduleId,
            'reaction': reaction
        }

    }).done(function () {
        clb();
    });
};

export const removeReaction = function (module, moduleId, reaction, clb) {
    jQuery.ajax({
        type: 'POST',
        url: appUrl + '/api/reactions',
        data: {
            'action': 'remove',
            'module': module,
            'moduleId': moduleId,
            'reaction': reaction
        }
    }).done(function () {
        clb();
    });
};

export default {
    addReactions: addReactions,
    removeReaction: removeReaction
};
