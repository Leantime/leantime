// Defined once here, not inside the date picker inits: a function created inside an init keeps
// that init's scope (and its date inputs) alive, so every opened modal stayed in memory.
if (typeof Date.prototype.addDays !== 'function') {
    Date.prototype.addDays = function (days) {
        this.setDate(this.getDate() + days);
        return this;
    };
}

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
            // Readonly fields never open the calendar (single pickers too, not only ranges).
            beforeShow: function (input) {
                if (jQuery(input).attr('readonly')) {
                    return false;
                }
            },
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

    // forms.date-picker: init on render and after every htmx swap; a from/to pair (same
    // data-lt-date-range group inside the swapped root) becomes a range picker.
    var initComponents = function (root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        var inputs = Array.prototype.slice.call(root.querySelectorAll('input[data-lt-datepicker]:not(.hasDatepicker)'));
        if (root.matches && root.matches('input[data-lt-datepicker]:not(.hasDatepicker)')) {
            inputs.unshift(root);
        }

        var ranges = {};
        inputs.forEach(function (input) {
            var group = input.getAttribute('data-lt-date-range');
            if (!group) {
                initDatePicker(input);
                return;
            }
            ranges[group] = ranges[group] || {};
            ranges[group][input.getAttribute('data-lt-date-role')] = input;
        });

        Object.keys(ranges).forEach(function (group) {
            var pair = ranges[group];
            if (pair.from && pair.to) {
                initDateRangePicker(pair.from, pair.to);
                // The limits otherwise only apply after the first change; seed them from rendered values.
                if (pair.from.value !== '') {
                    jQuery(pair.to).datepicker('option', 'minDate', getDate(pair.from));
                }
                if (pair.to.value !== '') {
                    jQuery(pair.from).datepicker('option', 'maxDate', getDate(pair.to));
                }
                return;
            }
            // Half a pair in this swap: still a working date field.
            initDatePicker(pair.from || pair.to);
        });
    };

    htmx.onLoad(initComponents);
    // Content inserted outside an htmx swap (dialogs …): see leantime.initLoadedContent in app.js.
    document.addEventListener('lt:ui:content.loaded', function (event) { initComponents(event.detail.root); });

    // jQuery UI keeps a reference to every picker; release it when htmx removes the field.
    document.addEventListener('htmx:beforeCleanupElement', function (event) {
        var element = event.target;
        if (element && element.matches && element.matches('input[data-lt-datepicker].hasDatepicker')) {
            jQuery(element).datepicker('destroy');
        }
    });

    // Make public what you want to have public, everything else is private
    return {
        initDateRangePicker:initDateRangePicker,
        initDatePicker:initDatePicker,
        initComponents:initComponents,
    };

})();
