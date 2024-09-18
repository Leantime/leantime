import jQuery from 'jquery';
import i18n from 'i18n';
import { getFormatFromSettings } from './dateHelper.module.mjs';
import { DateTime } from 'luxon';
import flatpickr from 'flatpickr';

export const getBaseDatePickerConfig = (callback) => ({
    numberOfMonths: 1,
    dateFormat: getFormatFromSettings("dateformat", "jquery"),
    dayNames: i18n.__("language.dayNames").split(","),
    dayNamesMin:  i18n.__("language.dayNamesMin").split(","),
    dayNamesShort: i18n.__("language.dayNamesShort").split(","),
    monthNames: i18n.__("language.monthNames").split(","),
    currentText: i18n.__("language.currentText"),
    closeText: i18n.__("language.closeText"),
    buttonText: i18n.__("language.buttonText"),
    isRTL: i18n.__("language.isRTL") === "true" ? 1 : 0,
    nextText: i18n.__("language.nextText"),
    prevText: i18n.__("language.prevText"),
    weekHeader: i18n.__("language.weekHeader"),
    firstDay: i18n.__("language.firstDayOfWeek"),
    onSelect: callback
});

export const getDate = function ( element )
{
    var dateFormat = getFormatFromSettings("dateformat", "jquery");
    var date;

    try {
        date = jQuery.datepicker.parseDate(dateFormat, element.value);
    } catch ( error ) {
        date = null;
        console.log(error);
    }

    return date;
}

export const initDateRangePicker = function (fromElement, toElement, minDistance)
{
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

export const initDatePicker = function (element, callback)
{
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
            shorthand: i18n.__("language.dayNamesMin").split(","),
            longhand: i18n.__("language.dayNames").split(","),
        },
        months: {
            shorthand: i18n.__("language.monthNamesShort").split(","),
            longhand: i18n.__("language.monthNames").split(","),
        },
        firstDayOfWeek: i18n.__("language.firstDayOfWeek"),
        weekAbbreviation: i18n.__("language.weekHeader"),
        rangeSeparator: " " + i18n.__("language.until") + " ",
        scrollTitle: i18n.__("language.scroll_to_change"),
        toggleTitle: i18n.__("language.buttonText"),
        time_24hr: !i18n.__("language.timeformat").includes("A"),
    },
    onChange: function(selectedDates, dateStr, instance) {
        console.log(instance);

        let userDateFormat = getFormatFromSettings("dateformat", "luxon");

        let formattedDate = DateTime.fromJSDate(instance.latestSelectedDateObj).toFormat(userDateFormat);

        if(formattedDate == "Invalid DateTime")
            formattedDate = i18n.__("language.anytime");

        jQuery(instance.element).parents(".date-dropdown").find(".dateField").text(formattedDate);

        if(instance.config.enableTime) {
            let userTimeFormat = getFormatFromSettings("timeformat", "luxon");
            let formattedTime = DateTime.fromJSDate(instance.latestSelectedDateObj).toFormat(userTimeFormat);
            jQuery(instance.element).parents(".date-dropdown").find(".dateField").text(formattedTime);
        }
    }
};

export const initDateTimePicker = function (element, callback)
{
    datepickerInstance = flatpickr(element, dateTimePickerConfig);
}

export const toggleTime = function (datePickerElement, toggleElement)
{
    if(datepickerInstance.config.enableTime) {
        datepickerInstance.destroy();
        dateTimePickerConfig.enableTime = false;
        dateTimePickerConfig.altFormat = getFormatFromSettings("dateformat", "flatpickr");
        datepickerInstance = flatpickr(datePickerElement, dateTimePickerConfig);
        jQuery(toggleElement).removeClass("active");
    }else{
        datepickerInstance.destroy();
        dateTimePickerConfig.enableTime = true;
        dateTimePickerConfig.altFormat = getFormatFromSettings("dateformat", "flatpickr") + " | " + getFormatFromSettings("timeformat", "flatpickr");
        datepickerInstance = flatpickr(datePickerElement, dateTimePickerConfig);
        jQuery(toggleElement).addClass("active");
    }
}

// Make public what you want to have public, everything else is private
export default
{
    initDateRangePicker: initDateRangePicker,
    initDatePicker: initDatePicker,
    initDateTimePicker: initDateTimePicker,
    toggleTime: toggleTime,
};
