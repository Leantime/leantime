var leantime = leantime || {};

leantime.ticketsRepository = (function () {

    //Functions

    var updateMilestoneDates = function (id, start, end, sortIndex) {

        let userDateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "luxon");
        let userTimeFormat = leantime.dateHelper.getFormatFromSettings("timeformat", "luxon");

        let editFrom = luxon.DateTime.fromSQL(start).toFormat(userDateFormat);
        let timeFrom = luxon.DateTime.fromSQL(start).toFormat(userTimeFormat);
        let editTo = luxon.DateTime.fromSQL(end).toFormat(userDateFormat);
        let timeTo = luxon.DateTime.fromSQL(end).toFormat(userTimeFormat);

        jQuery.ajax(
            {
                type: 'PATCH',
                url: leantime.appUrl + '/api/tickets',
                data:
                {
                    id : id,
                    editFrom:editFrom,
                    editTo:editTo,
                    timeFrom: timeFrom,
                    timeTo: timeTo,
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
