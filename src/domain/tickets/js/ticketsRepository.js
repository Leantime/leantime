var leantime = leantime || {};

leantime.ticketsRepository = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    //Constructor
    (function () {

    })();

    //Functions

    var updateMilestoneDates = function (id, start, end) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl+'/api/tickets',
                data:
                {
                    id : id,
                    editFrom:start.format("YYYY-MM-DD"),
                    editTo:end.format("YYYY-MM-DD")
                }
            }
        ).done(
            function () {
                    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

            }
        );

    };

    var updateRemainingHours = function (id, remaining, callbackSuccess) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl+'/api/tickets',
                data:
                {
                    id : id,
                    hourRemaining:remaining
                }
            }
        ).done(
            function () {

                    callbackSuccess();
            }
        );

    };

    var updateDueDates = function (id, date, callbackSuccess) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl+'/api/tickets',
                data:
                    {
                        id : id,
                        dateToFinish:date
                    }
            }
        ).done(
            function () {

                callbackSuccess();
            }
        );

    };


    // Make public what you want to have public, everything else is private
    return {
        updateMilestoneDates: updateMilestoneDates,
        updateRemainingHours:updateRemainingHours,
        updateDueDates:updateDueDates
    };
})();
