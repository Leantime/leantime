import { appUrl } from "../core/instance-info.module.mjs";
import {getFormatFromSettings} from "./dates/dateHelper.module.mjs";
import jQuery from 'jquery';
import { DateTime } from 'luxon';
import flatpickr from 'flatpickr';


    function getBaseDatePickerConfig(callback)
    {

        return {
            numberOfMonths: 1,
            dateFormat: getFormatFromSettings("dateformat", "jquery"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            currentText: leantime.i18n.__("language.currentText"),
            closeText: leantime.i18n.__("language.closeText"),
            buttonText: leantime.i18n.__("language.buttonText"),
            isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
            nextText: leantime.i18n.__("language.nextText"),
            prevText: leantime.i18n.__("language.prevText"),
            weekHeader: leantime.i18n.__("language.weekHeader"),
            firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            onSelect: callback

        };
    }

    function getDate( element )
    {

        var dateFormat =  getFormatFromSettings("dateformat", "jquery");
        var date;

        try {
            date = jQuery.datepicker.parseDate(dateFormat, element.value);
        } catch ( error ) {
            date = null;
            console.log(error);
        }

        return date;
    }

export const initDateRangePicker = function (fromElement, toElement, minDistance) {

    console.log("initDateRangePicker");
        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };

        //Check for readonly status and disable datepicker if readonly
        jQuery.datepicker.setDefaults({
            beforeShow: function (i) {
                if (jQuery(i).attr('readonly')) {
                    return false;
                }
            }
        });

        var from = jQuery(fromElement).datepicker(getBaseDatePickerConfig())
                   .on(
                       "change",
                       function (date) {
                           to.datepicker("option", "minDate", getDate(this));

                           if (jQuery(toElement).val() == '') {
                               jQuery(toElement).val(jQuery(fromElement).val());
                           }
                       }
                   );

        var to = jQuery(toElement).datepicker(getBaseDatePickerConfig())
                 .on(
                     "change",
                        function () {
                            from.datepicker("option", "maxDate", getDate(this));
                        }
                 );
    };

export const initDatePicker = function (element, callback) {
        jQuery(element).datepicker(
            getBaseDatePickerConfig(callback)
        );
    }

let datepickerInstance = false;

let dateTimePickerConfig = {
    enableTime: false,
    allowInput: true,
    inline: true,
    allowInvalidPreload: true,
    altInput: true,
    altFormat: getFormatFromSettings("dateformat", "flatpickr"),
    dateFormat:  "Y-m-d H:i:s",
    shorthandCurrentMonth: true,
    "locale": {
        weekdays: {
            shorthand: leantime.i18n.__("language.dayNamesMin").split(","),
            longhand: leantime.i18n.__("language.dayNames").split(","),
        },
        months: {
            shorthand: leantime.i18n.__("language.monthNamesShort").split(","),
            longhand: leantime.i18n.__("language.monthNames").split(","),
        },
        firstDayOfWeek: leantime.i18n.__("language.firstDayOfWeek"),
        weekAbbreviation: leantime.i18n.__("language.weekHeader"),
        rangeSeparator: " " + leantime.i18n.__("language.until") + " ",
        scrollTitle: leantime.i18n.__("language.scroll_to_change"),
        toggleTitle: leantime.i18n.__("language.buttonText"),
        time_24hr: !leantime.i18n.__("language.timeformat").includes("A"),
    },
    onChange: function(selectedDates, dateStr, instance) {
        console.log(instance);

        let userDateFormat = getFormatFromSettings("dateformat", "luxon");

        let formattedDate = DateTime.fromJSDate(instance.latestSelectedDateObj).toFormat(userDateFormat);

        if(formattedDate == "Invalid DateTime")
            formattedDate = leantime.i18n.__("language.anytime");

        jQuery(instance.element).parent().parent().find(".dateField").text(formattedDate);

        if(instance.config.enableTime) {
            let userTimeFormat = getFormatFromSettings("timeformat", "luxon");
            let formattedTime = DateTime.fromJSDate(instance.latestSelectedDateObj).toFormat(userTimeFormat);
            jQuery(instance.element).parent().parent().find(".timeField").text(formattedTime);
        }
    }
};

export const initDateTimePicker = function (element, callback) {

    datepickerInstance = jQuery(element).flatpickr(dateTimePickerConfig);

}

export const toggleTime = function (datePickerElement, toggleElement) {

    let datepickerInstance = jQuery(datePickerElement)[0]._flatpickr;

    if(datepickerInstance.config.enableTime) {
        datepickerInstance.destroy();
        dateTimePickerConfig.enableTime = false;
        dateTimePickerConfig.altFormat = getFormatFromSettings("dateformat", "flatpickr");
        datepickerInstance = jQuery(datePickerElement).flatpickr(dateTimePickerConfig);
        jQuery(toggleElement).removeClass("active");
    }else{
        datepickerInstance.destroy();
        dateTimePickerConfig.enableTime = true;
        dateTimePickerConfig.altFormat = getFormatFromSettings("dateformat", "flatpickr") + " | " + getFormatFromSettings("timeformat", "flatpickr");
        datepickerInstance = jQuery(datePickerElement).flatpickr(dateTimePickerConfig);
        jQuery(toggleElement).addClass("active");
    }

}

export const clearInstance = function (datePickerElement, toggleElement) {

    let datepickerInstance = jQuery(datePickerElement)[0]._flatpickr;
    datepickerInstance.clear();
}

    // Make public what you want to have public, everything else is private
export const datePickers = {
    initDateRangePicker:initDateRangePicker,
    initDatePicker: initDatePicker,
    initDateTimePicker: initDateTimePicker,
    toggleTime: toggleTime
};

export default datePickers;
