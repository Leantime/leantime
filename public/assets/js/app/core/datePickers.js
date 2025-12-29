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
var initDateRangePickerTest = function (fromElement, toElement, minDistance) {
    var fromValue = jQuery(fromElement).val();
    var toValue = jQuery(toElement).val();
    
    var startDate, endDate;
    
    if (fromValue) {
        startDate = moment(fromValue, ['YYYY-MM-DD', 'DD.MM.YYYY', 'MM/DD/YYYY', 'DD/MM/YYYY'], true);
        if (!startDate.isValid()) {
            console.warn('Invalid start date format:', fromValue);
            startDate = moment();
        }
    } else {
        startDate = moment();
    }
    
    if (toValue) {
        endDate = moment(toValue, ['YYYY-MM-DD', 'DD.MM.YYYY', 'MM/DD/YYYY', 'DD/MM/YYYY'], true);
        if (!endDate.isValid()) {
            console.warn('Invalid end date format:', toValue);
            endDate = moment();
        }
    } else {
        endDate = moment();
    }

    var picker = jQuery(fromElement).daterangepicker({
        autoUpdateInput: false, 
        opens: 'left',
        linkedCalendars: true,
        startDate: startDate,
        endDate: endDate,
        minDate: moment().subtract(1, 'years'),
        maxDate: moment().add(1, 'years'),
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            firstDay: 1
        },
        ranges: {
            'Today': [moment(), moment()],
            'This Week': [moment().startOf('week'), moment().endOf('week')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()]
        }
    });

    jQuery(fromElement).on('apply.daterangepicker', function(ev, picker) {
        var start = picker.startDate;
        var end = picker.endDate;
        
        jQuery(fromElement).val(start.format('YYYY-MM-DD'));
        jQuery(toElement).val(end.format('YYYY-MM-DD'));

        if (minDistance) {
            var diff = end.diff(start, 'days') + 1;
            if (diff < minDistance) {
                alert('Minimum range is ' + minDistance + ' days.');
                end = start.clone().add(minDistance - 1, 'days');
                jQuery(fromElement).val(start.format('YYYY-MM-DD'));
                jQuery(toElement).val(end.format('YYYY-MM-DD'));
            }
        }
    });

    jQuery(fromElement).on('cancel.daterangepicker', function(ev, picker) {
    });

    jQuery(fromElement).val(startDate.format('YYYY-MM-DD'));
    jQuery(toElement).val(endDate.format('YYYY-MM-DD'));

    jQuery(toElement).on('focus click', function(e) {
        e.preventDefault();
        jQuery(fromElement).data('daterangepicker').show();
    });
};


    // Make public what you want to have public, everything else is private
    return {
        initDateRangePicker:initDateRangePicker,
        initDateRangePickerTest:initDateRangePickerTest,
        initDatePicker:initDatePicker,
    };

})();
