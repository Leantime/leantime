leantime.reactionsController = (function () {


    //Functions

    let addReactions = function (module, moduleId, reaction, clb) {

        leantime.rpc('Reactions.Reactions.react', {
            module: module,
            moduleId: moduleId,
            reaction: reaction
        }).then(function () {
            clb();
        }).catch(function (e) { console.error('Could not add reaction', e); });

    };

    let removeReaction = function (module, moduleId, reaction, clb) {

        leantime.rpc('Reactions.Reactions.unreact', {
            module: module,
            moduleId: moduleId,
            reaction: reaction
        }).then(function () {
            clb();
        }).catch(function (e) { console.error('Could not remove reaction', e); });

    };

    // Make public what you want to have public, everything else is private
    return {
        addReactions:addReactions,
        removeReaction:removeReaction

    };
})();
