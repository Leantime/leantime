leantime.dateController = (function () {

    function getBaseDatePickerConfig(callback)
    {

        return {
            numberOfMonths: 1,
            dateFormat:  leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),
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

        var dateFormat =  leantime.dateHelper.getFormatFromSettings("dateformat", "jquery");
        var date;

        try {
            date = jQuery.datepicker.parseDate(dateFormat, element.value);
        } catch ( error ) {
            date = null;
            console.log(error);
        }

        return date;
    }

    var initDateRangePicker = function (fromElement, toElement, minDistance) {

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

    var initDatePicker = function (element, callback) {
        jQuery(element).datepicker(
            getBaseDatePickerConfig(callback)
        );
    }

    // Make public what you want to have public, everything else is private
    return {
        initDateRangePicker:initDateRangePicker,
        initDatePicker:initDatePicker,
    };

})();
