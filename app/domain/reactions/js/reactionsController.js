leantime.reactionsController = (function () {


    //Functions

    let addReactions = function (module, moduleId, reaction) {

        jQuery.ajax({
            type: 'POST',
            url: leantime.appUrl + '/api/reactions',
            data: {
                'action': 'add',
                'module': module,
                'moduleId': moduleId,
                'reaction': reaction
            }

        });

    };

    let removeReaction = function (module, moduleId, reaction) {

        jQuery.ajax({
            type: 'POST',
            url: leantime.appUrl + '/api/reactions',
            data: {
                'action': 'add',
                'module': module,
                'moduleId': moduleId,
                'reaction': reaction
            }

        });

    };

    // Make public what you want to have public, everything else is private
    return {
        addReactions:addReactions,
        removeReaction:removeReaction

    };
})();
