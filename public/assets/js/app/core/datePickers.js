leantime.dateController = (function () {

    function getBaseDatePickerConfig(callback) {
        var dayNames = leantime.i18n.__("language.dayNames").split(",");
        var dayNamesShort = leantime.i18n.__("language.dayNamesShort").split(",");
        var dayNamesMin = leantime.i18n.__("language.dayNamesMin").split(",");
        var monthNames = leantime.i18n.__("language.monthNames").split(",");
        var monthNamesShort = leantime.i18n.__("language.monthNamesShort").split(",");
        var firstDay = parseInt(leantime.i18n.__("language.firstDayOfWeek"), 10) || 0;

        return {
            dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "flatpickr"),
            locale: {
                firstDayOfWeek: firstDay,
                weekdays: {
                    shorthand: dayNamesMin,
                    longhand: dayNames
                },
                months: {
                    shorthand: monthNamesShort,
                    longhand: monthNames
                }
            },
            allowInput: true,
            onChange: callback ? function (selectedDates, dateStr, instance) {
                callback.call(instance.input, dateStr, instance);
            } : undefined
        };
    }

    var initDateRangePicker = function (fromElement, toElement, minDistance) {
        var fromEl = typeof fromElement === 'string' ? document.querySelector(fromElement) : fromElement;
        var toEl = typeof toElement === 'string' ? document.querySelector(toElement) : toElement;

        if (!fromEl || !toEl) return;

        // Check readonly
        if (fromEl.hasAttribute('readonly') || toEl.hasAttribute('readonly')) return;

        var fromConfig = getBaseDatePickerConfig();
        fromConfig.onChange = function (selectedDates) {
            if (selectedDates.length > 0) {
                toFp.set('minDate', selectedDates[0]);
                if (!toEl.value) {
                    toFp.setDate(selectedDates[0], true);
                }
            }
        };

        var toConfig = getBaseDatePickerConfig();
        toConfig.onChange = function (selectedDates) {
            if (selectedDates.length > 0) {
                fromFp.set('maxDate', selectedDates[0]);
            }
        };

        var fromFp = flatpickr(fromEl, fromConfig);
        var toFp = flatpickr(toEl, toConfig);
    };

    var initDatePicker = function (element, callback) {
        var el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;
        if (el.hasAttribute('readonly')) return;

        flatpickr(el, getBaseDatePickerConfig(callback));
    };

    // Make public what you want to have public, everything else is private
    return {
        initDateRangePicker: initDateRangePicker,
        initDatePicker: initDatePicker,
        getBaseDatePickerConfig: getBaseDatePickerConfig,
    };

})();

/**
 * jQuery.datepicker compatibility shim — maps jQuery UI datepicker calls
 * to flatpickr so domain controllers work during the migration period.
 * Will be removed once all controllers are converted to vanilla JS (Step 6).
 */
(function () {
    'use strict';

    if (typeof jQuery === 'undefined') return;

    // Store flatpickr instances on elements
    var fpInstances = new WeakMap();

    // Shim jQuery.datepicker static methods
    jQuery.datepicker = jQuery.datepicker || {};

    jQuery.datepicker.setDefaults = function () {
        // No-op — flatpickr doesn't need global defaults, config is per-instance
    };

    jQuery.datepicker.parseDate = function (format, dateStr) {
        if (!dateStr) return null;
        var fpFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "flatpickr");
        return flatpickr.parseDate(dateStr, fpFormat);
    };

    jQuery.datepicker.formatDate = function (format, dateObj) {
        if (!dateObj) return '';
        var fpFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "flatpickr");
        return flatpickr.formatDate(dateObj, fpFormat);
    };

    // Shim jQuery(el).datepicker(config) as a jQuery plugin
    var origDatepicker = jQuery.fn.datepicker;
    jQuery.fn.datepicker = function (configOrMethod, value) {
        return this.each(function () {
            var el = this;

            if (typeof configOrMethod === 'string') {
                // Method call: .datepicker("option", "minDate", date)
                var instance = fpInstances.get(el);
                if (!instance) return;

                if (configOrMethod === 'option' || configOrMethod === 'setDate') {
                    if (value === 'minDate' || configOrMethod === 'setDate') {
                        var val = (arguments.length > 2) ? arguments[2] : value;
                        if (configOrMethod === 'setDate') {
                            instance.setDate(val, true);
                        } else {
                            instance.set('minDate', val);
                        }
                    } else if (value === 'maxDate') {
                        instance.set('maxDate', arguments[2]);
                    }
                }
                return;
            }

            // Initialization call
            if (el.hasAttribute('readonly')) return;

            var fpConfig = leantime.dateController.getBaseDatePickerConfig();
            if (configOrMethod && configOrMethod.onSelect) {
                fpConfig.onChange = function (selectedDates, dateStr, inst) {
                    configOrMethod.onSelect.call(el, dateStr, inst);
                };
            }
            var fp = flatpickr(el, fpConfig);
            fpInstances.set(el, fp);
        });
    };
})();
