/**
 * Chips: the colored value pickers rendered by <x-global::forms.chip> (status, priority, milestone…).
 *
 * One delegated click handler for every chip on the page — no per-page init call, so chips rendered
 * later by htmx or a modal work too. A pick saves through the chip's adapter (JSON-RPC), then updates
 * the chip in place and fires `lt:chip:changed` (bubbles) with
 * { adapter, type, field, entityId, value, label, element } for page-specific follow-ups (the kanban
 * moves the card, for example).
 *
 * Markup contract (written by the component, read here):
 *   wrapper [data-lt-chip=adapter][data-chip-type][data-entity-id][data-field][data-canvas-type]
 *   toggle  .dropdown-toggle with .text (or [data-chip-label] / img[data-chip-image] in a custom toggle)
 *   option  a[data-value][data-label][data-class][data-color]
 */
leantime.chipController = (function () {

    // Each adapter saves one field of one entity and resolves truthy on success.
    var adapters = {
        ticket: function (chip, value) {
            var values = {};
            values[chip.field] = value;
            return leantime.rpc('Tickets.Tickets.patchTicket', { id: chip.entityId, values: values });
        },
        canvas: function (chip, value) {
            var params = {};
            params[chip.field] = value;
            return leantime.rpc('Blueprints.Blueprints.patchCanvasItem', { id: chip.entityId, params: params, canvasType: chip.canvasType });
        },
        goal: function (chip, value) {
            var params = {};
            params[chip.field] = value;
            return leantime.rpc('Goalcanvas.Goalcanvas.patchGoalItem', { id: chip.entityId, params: params });
        },
        idea: function (chip, value) {
            var params = {};
            params[chip.field] = value;
            return leantime.rpc('Ideas.Ideas.patchIdeaItem', { id: chip.entityId, params: params });
        }
    };

    var registerAdapter = function (name, saveFunction) {
        adapters[name] = saveFunction;
    };

    var chipFrom = function (wrapper) {
        return {
            adapter: wrapper.getAttribute('data-lt-chip'),
            type: wrapper.getAttribute('data-chip-type'),
            field: wrapper.getAttribute('data-field'),
            entityId: wrapper.getAttribute('data-entity-id'),
            canvasType: wrapper.getAttribute('data-canvas-type')
        };
    };

    // Show the picked option on the chip: text, color class, background color, avatar.
    var showPick = function (wrapper, option) {
        var toggle = wrapper.querySelector('.dropdown-toggle');
        var label = option.getAttribute('data-label');

        var labelElement = toggle.querySelector('[data-chip-label]') || toggle.querySelector('.text');
        if (labelElement && label !== null) {
            labelElement.textContent = label;
        }

        // Remove every color class this chip can show (not just the last saved one: the kanban
        // also recolors chips directly), then add the picked one.
        var colorClasses = Array.prototype.map.call(
            wrapper.querySelectorAll('.dropdown-menu a[data-class]'),
            function (item) { return item.getAttribute('data-class'); }
        );
        colorClasses.forEach(function (colorClass) {
            if (colorClass) {
                toggle.classList.remove(colorClass);
            }
        });
        if (option.getAttribute('data-class')) {
            toggle.classList.add(option.getAttribute('data-class'));
        }

        if (wrapper.querySelector('.dropdown-menu a[data-color]')) {
            toggle.style.backgroundColor = option.getAttribute('data-color') || '';
        }

        // Avatar chips: the picked value is a user id (0 = nobody, which serves the default avatar). The URL is
        // built here from the encoded id rather than read from the DOM (CodeQL js/xss-through-dom, see #3582).
        var image = toggle.querySelector('img[data-chip-image]');
        if (image) {
            image.src = leantime.appUrl + '/users/profileImage/' + encodeURIComponent(option.getAttribute('data-value'));
        }

        wrapper.setAttribute('data-current-value', option.getAttribute('data-value'));
    };

    var notify = function (message, style) {
        jQuery.growl({ message: message, style: style });
    };

    var pick = function (wrapper, option) {
        var chip = chipFrom(wrapper);
        var value = option.getAttribute('data-value');
        var save = adapters[chip.adapter];

        if (!save) {
            console.warn('[chips] No adapter "' + chip.adapter + '"', wrapper);
            return;
        }

        var toggle = wrapper.querySelector('.dropdown-toggle');
        toggle.classList.add('lt-chip-saving');

        Promise.resolve(save(chip, value))
            .then(function (result) {
                if (result === false) {
                    throw new Error(leantime.i18n.__('short_notifications.not_saved'));
                }

                showPick(wrapper, option);
                notify(leantime.i18n.__('short_notifications.' + chip.type + '_updated'), 'success');

                wrapper.dispatchEvent(new CustomEvent('lt:chip:changed', {
                    bubbles: true,
                    detail: {
                        adapter: chip.adapter,
                        type: chip.type,
                        field: chip.field,
                        entityId: chip.entityId,
                        value: value,
                        label: option.getAttribute('data-label'),
                        element: wrapper
                    }
                }));
            })
            .catch(function (error) {
                notify((error && error.message) ? error.message : leantime.i18n.__('short_notifications.not_saved'), 'error');
                console.error('[chips] Could not save ' + chip.field + ' of ' + chip.entityId, error);
            })
            .finally(function () {
                toggle.classList.remove('lt-chip-saving');
            });
    };

    document.addEventListener('click', function (event) {
        var option = event.target.closest('[data-lt-chip] .dropdown-menu a[data-value]');
        if (!option) {
            return;
        }
        event.preventDefault();
        pick(option.closest('[data-lt-chip]'), option);
    });

    return {
        registerAdapter: registerAdapter
    };
})();
