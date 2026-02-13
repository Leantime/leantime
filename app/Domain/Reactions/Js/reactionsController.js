leantime.reactionsController = (function () {


    //Functions

    let addReactions = function (module, moduleId, reaction, clb) {

        fetch(leantime.appUrl + '/api/reactions', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'add',
                'module': module,
                'moduleId': moduleId,
                'reaction': reaction
            })
        }).then(function () {
            clb();
        });

    };

    let removeReaction = function (module, moduleId, reaction, clb) {

        fetch(leantime.appUrl + '/api/reactions', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'remove',
                'module': module,
                'moduleId': moduleId,
                'reaction': reaction
            })
        }).then(function () {
            clb();
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        addReactions:addReactions,
        removeReaction:removeReaction

    };
})();
