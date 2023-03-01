var leantime = leantime || {};

leantime.ticketsRepository = (function () {

    // Variables (underscore for private variables)

    //Constructor
    (function () {

    })();

    //Functions

    var updateMilestoneDates = function (id, start, end, sortIndex) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/tickets',
                data:
                {
                    id : id,
                    editFrom:start,
                    editTo:end,
                    sortIndex: sortIndex
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
                url: leantime.appUrl + '/api/tickets',
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

    var updatePlannedHours = function (id, planhours, callbackSuccess) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/tickets',
                data:
                    {
                        id : id,
                        planHours:planhours
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
                url: leantime.appUrl + '/api/tickets',
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

    var updateEditFromDates = function (id, date, callbackSuccess) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/tickets',
                data:
                    {
                        id : id,
                        editFrom:date
                }
            }
        ).done(
            function () {

                callbackSuccess();
            }
        );

    };

    var updateEditToDates = function (id, date, callbackSuccess) {

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/tickets',
                data:
                    {
                        id : id,
                        editTo:date
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
        updatePlannedHours:updatePlannedHours,
        updateDueDates:updateDueDates,
        updateEditFromDates:updateEditFromDates,
        updateEditToDates:updateEditToDates

    };
})();
