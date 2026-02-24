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
        var elements = typeof element === 'string'
            ? document.querySelectorAll(element)
            : (element ? [element] : []);

        elements.forEach(function (el) {
            if (!el) return;
            if (el.hasAttribute('readonly')) return;

            // Sentinel values like "Anytime" are not valid dates — clear them
            // before flatpickr init to avoid "Invalid date provided" warnings.
            // Store the placeholder so it can be restored if the user clears the date.
            var val = (el.value || '').trim();
            var sentinel = val && !/\d/.test(val);
            if (sentinel) {
                el.setAttribute('placeholder', val);
                el.value = '';
            }

            flatpickr(el, getBaseDatePickerConfig(callback));
        });
    };

    /**
     * Decorates .date-inline-picker wrappers with icon/text toggle behavior.
     * Call AFTER initDateRangePicker / initDatePicker so linked flatpickr
     * instances already exist on the inputs.
     */
    var initInlineDatePickers = function (scope) {
        var root = scope
            ? (typeof scope === 'string' ? document.querySelector(scope) : scope)
            : document;
        if (!root) return;

        var wrappers = root.querySelectorAll('.date-inline-picker');
        wrappers.forEach(function (wrapper) {
            var input = wrapper.querySelector('input.dates');
            var trigger = wrapper.querySelector('.date-inline-trigger');
            if (!input || !trigger) return;

            // Ensure flatpickr is initialised on this input
            var fp = input._flatpickr;
            if (!fp) {
                fp = flatpickr(input, getBaseDatePickerConfig());
            }

            // Position calendar relative to the wrapper (always visible)
            // instead of the input which may be display:none
            fp._positionElement = wrapper;

            function toggleVisibility() {
                var hasDate = input.value !== '';
                input.style.display = hasDate ? '' : 'none';
                trigger.style.display = hasDate ? 'none' : '';
            }

            // Push onto flatpickr's internal hook arrays (never replace them)
            fp.config.onChange.push(function () { toggleVisibility(); });
            fp.config.onClose.push(function () { toggleVisibility(); });

            // Wire trigger click to open flatpickr
            trigger.addEventListener('click', function () {
                fp.open();
            });
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        initDateRangePicker: initDateRangePicker,
        initDatePicker: initDatePicker,
        initInlineDatePickers: initInlineDatePickers,
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
