import jQuery from 'jquery';
import { appUrl } from 'js/app/core/instance-info.module';
import { getFormatFromSettings } from 'js/app/core/dateHelper.module';
import { DateTime } from 'luxon';

export const updateMilestoneDates = function (id, start, end, sortIndex) {
    let userDateFormat = getFormatFromSettings("dateformat", "luxon");
    let userTimeFormat = getFormatFromSettings("timeformat", "luxon");

    let editFrom = DateTime.fromSQL(start).toFormat(userDateFormat);
    let timeFrom = DateTime.fromSQL(start).toFormat(userTimeFormat);
    let editTo = DateTime.fromSQL(end).toFormat(userDateFormat);
    let timeTo = DateTime.fromSQL(end).toFormat(userTimeFormat);

    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
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

export const updateRemainingHours = function (id, remaining, callbackSuccess) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
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

export const updatePlannedHours = function (id, planhours, callbackSuccess) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
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

export const updateDueDates = function (id, date, callbackSuccess) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
            data: {
                id: id,
                dateToFinish:date
            }
        }
    ).done(
        function () {
            callbackSuccess();
        }
    );
};

export const updateEditFromDates = function (id, date, callbackSuccess) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
            data: {
                id: id,
                editFrom:date
            }
        }
    ).done(
        function () {
            callbackSuccess();
        }
    );
};

export const updateEditToDates = function (id, date, callbackSuccess) {
    jQuery.ajax(
        {
            type: 'PATCH',
            url: appUrl + '/api/tickets',
            data: {
                id: id,
                editTo:date
            }
        }
    ).done(
        function () {
            callbackSuccess();
        }
    );
};

export default {
    updateMilestoneDates: updateMilestoneDates,
    updateRemainingHours: updateRemainingHours,
    updatePlannedHours: updatePlannedHours,
    updateDueDates: updateDueDates,
    updateEditFromDates: updateEditFromDates,
    updateEditToDates: updateEditToDates
};
