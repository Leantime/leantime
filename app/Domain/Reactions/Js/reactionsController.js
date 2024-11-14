leantime.reactionsController = (function () {


    //Functions

    let addReactions = function (module, moduleId, reaction, clb) {

        jQuery.ajax({
            type: 'POST',
            url: leantime.appUrl + '/api/reactions',
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

    let removeReaction = function (module, moduleId, reaction, clb) {

        jQuery.ajax({
            type: 'POST',
            url: leantime.appUrl + '/api/reactions',
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

    // Make public what you want to have public, everything else is private
    return {
        addReactions:addReactions,
        removeReaction:removeReaction

    };
})();
