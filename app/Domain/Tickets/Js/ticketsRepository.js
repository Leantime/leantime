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

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                editFrom: editFrom,
                editTo: editTo,
                timeFrom: timeFrom,
                timeTo: timeTo,
                sortIndex: sortIndex
            })
        });

    };

    var updateRemainingHours = function (id, remaining, callbackSuccess) {

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                hourRemaining: remaining
            })
        }).then(function () {
            callbackSuccess();
        });

    };

    var updatePlannedHours = function (id, planhours, callbackSuccess) {

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                planHours: planhours
            })
        }).then(function () {
            callbackSuccess();
        });

    };

    var updateDueDates = function (id, date, callbackSuccess) {

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                dateToFinish: date
            })
        }).then(function () {
            callbackSuccess();
        });

    };

    var updateEditFromDates = function (id, date, callbackSuccess) {

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                editFrom: date
            })
        }).then(function () {
            callbackSuccess();
        });

    };

    var updateEditToDates = function (id, date, callbackSuccess) {

        fetch(leantime.appUrl + '/api/tickets', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                id: id,
                editTo: date
            })
        }).then(function () {
            callbackSuccess();
        });

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
