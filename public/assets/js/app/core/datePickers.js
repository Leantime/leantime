leantime.dates = (function () {

    function getBaseDatePickerConfig()
    {

        return {
            numberOfMonths: 1,
            dateFormat:  leantime.i18n.__("language.jsdateformat"),
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

        };
    }


    function getDate( element )
    {

        var dateFormat = leantime.i18n.__("language.jsdateformat");
        var date;

        try {
            date = jQuery.datepicker.parseDate(dateFormat, element.value);
        } catch ( error ) {
            date = null;
            console.log(error);
        }

        return date;
    }

    var formattedDateString = function (dateString) {
        return DateFormat.format (date, leantime.i18n.__("language.jsdateformat"));
    }

    var formattedTimeString = function(date) {
        return DateFormat.format (date, leantime.i18n.__("language.jsdateformat"));
    }

    var formatToISO = function (date) {
        return DateFormat.format (date, "yy-mm-dd hh:mm:ss");
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

        var from = jQuery(fromElement).datepicker(getBaseConfig())
                   .on(
                        "change",
                        function (date) {
                            to.datepicker("option", "minDate", getDate(this));

                            if (jQuery(toElement).val() == '') {
                                jQuery(toElement).val(jQuery(fromElement).val());
                            }
                        }
                   );

        var to = jQuery(toElement).datepicker(getBaseConfig())
                 .on(
                     "change",
                     function () {
                         from.datepicker("option", "maxDate", getDate(this));
                     }
                 );
    };

    // Make public what you want to have public, everything else is private
    return {
        initDateRange:initDateRange
    };

})();
