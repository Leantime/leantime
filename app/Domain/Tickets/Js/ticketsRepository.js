var leantime = leantime || {};

leantime.ticketsRepository = (function () {

    //Functions

    /**
     * Patch a single ticket via JSON-RPC. Returns the underlying promise so
     * callers can chain success/error handling.
     */
    function patchTicket(id, values) {
        return leantime.rpc('Tickets.Tickets.patchTicket', { id: id, values: values });
    }

    /**
     * Shared failure handler for inline ticket updates (auth denied, validation, server error).
     */
    function handlePatchError(id) {
        return function (error) {
            console.error('Could not update ticket ' + id, error);
        };
    }

    var updateMilestoneDates = function (id, start, end, sortIndex) {

        let userDateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "luxon");
        let userTimeFormat = leantime.dateHelper.getFormatFromSettings("timeformat", "luxon");

        let editFrom = luxon.DateTime.fromSQL(start).toFormat(userDateFormat);
        let timeFrom = luxon.DateTime.fromSQL(start).toFormat(userTimeFormat);
        let editTo = luxon.DateTime.fromSQL(end).toFormat(userDateFormat);
        let timeTo = luxon.DateTime.fromSQL(end).toFormat(userTimeFormat);

        //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
        patchTicket(id, {
            editFrom: editFrom,
            editTo: editTo,
            timeFrom: timeFrom,
            timeTo: timeTo,
            sortIndex: sortIndex
        }).catch(handlePatchError(id));

    };

    var updateRemainingHours = function (id, remaining, callbackSuccess) {

        patchTicket(id, { hourRemaining: remaining })
            .then(function () { callbackSuccess(); })
            .catch(handlePatchError(id));

    };

    var updatePlannedHours = function (id, planhours, callbackSuccess) {

        patchTicket(id, { planHours: planhours })
            .then(function () { callbackSuccess(); })
            .catch(handlePatchError(id));

    };

    var updateDueDates = function (id, date, callbackSuccess) {

        patchTicket(id, { dateToFinish: date })
            .then(function () { callbackSuccess(); })
            .catch(handlePatchError(id));

    };

    var updateEditFromDates = function (id, date, callbackSuccess) {

        patchTicket(id, { editFrom: date })
            .then(function () { callbackSuccess(); })
            .catch(handlePatchError(id));

    };

    var updateEditToDates = function (id, date, callbackSuccess) {

        patchTicket(id, { editTo: date })
            .then(function () { callbackSuccess(); })
            .catch(handlePatchError(id));

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
