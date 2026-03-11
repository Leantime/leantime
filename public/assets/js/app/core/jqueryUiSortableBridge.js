/**
 * jQuery UI Sortable → SortableJS Bridge
 *
 * Provides a jQuery.fn.sortable() shim that maps the jQuery UI sortable API
 * to SortableJS.  This allows legacy code (kanban board, etc.) to keep calling
 * jQuery(el).sortable({...}), .sortable('refresh'), .sortable('serialize'),
 * etc. without requiring the full jQuery UI library.
 *
 * jQuery is loaded as a classic <script> (available immediately).
 * SortableJS is set on window.Sortable by a Vite module — it will be available
 * by the time any user interaction triggers .sortable(), but may NOT be
 * available at import-time.  All references use window.Sortable lazily.
 */
(function () {
    'use strict';

    var $ = window.jQuery;
    if (!$) return;

    var DATA_KEY = '__sortableBridge';

    /**
     * jQuery UI serialize: reads child element IDs in the format "prefix_number"
     * and returns "prefix[]=number&prefix[]=number&..."
     */
    function serialize(container, options) {
        var opts = options || {};
        var items = opts.items || '> *';
        var attr = opts.attribute || 'id';
        var key = opts.key;
        var expression = opts.expression || /(.+)[_=](.*)/;

        var $items = $(container).find(items).not('.sortable-ghost, .sortable-fallback');
        var parts = [];

        $items.each(function () {
            var val = $(this).attr(attr);
            if (!val) return;
            var match = expression.exec(val);
            if (match) {
                var prefix = key || match[1] + '[]';
                parts.push(encodeURIComponent(prefix) + '=' + encodeURIComponent(match[2]));
            }
        });

        return parts.join('&');
    }

    /**
     * Create a jQuery-UI-like "ui" object from a SortableJS event.
     */
    function makeUi(evt) {
        var $item = $(evt.item);
        return {
            item: $item,
            helper: $item,
            placeholder: $(evt.clone || []),
            sender: evt.from ? $(evt.from) : null,
            position: {
                left: evt.originalEvent ? evt.originalEvent.pageX : 0,
                top: evt.originalEvent ? evt.originalEvent.pageY : 0
            }
        };
    }

    /**
     * Initialise SortableJS on one element with jQuery-UI-style options.
     */
    function initSortable(el, jqOptions) {
        var SortableJS = window.Sortable;
        if (!SortableJS) {
            console.warn('[sortableBridge] window.Sortable not available yet');
            return;
        }

        var opts = jqOptions || {};

        // Destroy any existing instance
        var existing = el[DATA_KEY];
        if (existing && existing.instance) {
            existing.instance.destroy();
        }

        // Map jQuery UI options → SortableJS options
        var sortableOpts = {
            animation: 150,
            ghostClass: opts.placeholder || 'ui-state-highlight',
            chosenClass: 'ui-sortable-helper',
            dragClass: 'sortable-drag',
            forceFallback: false,
            scroll: true,
            scrollSensitivity: 40,
            scrollSpeed: 20
        };

        // items → draggable
        if (opts.items) {
            sortableOpts.draggable = opts.items;
        }

        // cancel → filter
        if (opts.cancel) {
            sortableOpts.filter = opts.cancel;
            sortableOpts.preventOnFilter = false;
        }

        // distance
        if (opts.distance) {
            sortableOpts.fallbackTolerance = opts.distance;
        }

        // connectWith → group (allows cross-list dragging)
        if (opts.connectWith) {
            var groupName = 'sortable-bridge-' + opts.connectWith.replace(/[^a-zA-Z0-9]/g, '');
            sortableOpts.group = groupName;
        }

        // Callbacks
        sortableOpts.onStart = function (evt) {
            if (opts.start) {
                opts.start.call(el, evt, makeUi(evt));
            }
        };

        sortableOpts.onEnd = function (evt) {
            if (opts.stop) {
                opts.stop.call(el, evt, makeUi(evt));
            }
        };

        sortableOpts.onChange = function (evt) {
            if (opts.change) {
                opts.change.call(el, evt, makeUi(evt));
            }
        };

        sortableOpts.onUpdate = function (evt) {
            if (opts.update) {
                opts.update.call(el, evt, makeUi(evt));
            }
        };

        sortableOpts.onAdd = function (evt) {
            if (opts.receive) {
                opts.receive.call(el, evt, makeUi(evt));
            }
        };

        sortableOpts.onRemove = function (evt) {
            if (opts.remove) {
                opts.remove.call(el, evt, makeUi(evt));
            }
        };

        var instance = new SortableJS(el, sortableOpts);

        el[DATA_KEY] = {
            instance: instance,
            options: opts
        };
    }

    /**
     * jQuery.fn.sortable — main entry point.
     *
     * Usage mirrors jQuery UI:
     *   $(sel).sortable({ ... })       — initialise
     *   $(sel).sortable('refresh')     — re-scan items
     *   $(sel).sortable('serialize')   — serialise child IDs
     *   $(sel).sortable('destroy')     — tear down
     *   $(sel).sortable('option', k)   — get option (limited)
     *   $(sel).sortable('disable')     — disable sorting
     *   $(sel).sortable('enable')      — enable sorting
     */
    $.fn.sortable = function (method) {
        // String method call
        if (typeof method === 'string') {
            var args = Array.prototype.slice.call(arguments, 1);

            // Methods that return a value (not chainable)
            if (method === 'serialize') {
                var first = this[0];
                if (!first) return '';
                var data = first[DATA_KEY];
                var serializeOpts = args[0] || {};
                if (data && data.options && data.options.items) {
                    serializeOpts.items = serializeOpts.items || data.options.items;
                }
                return serialize(first, serializeOpts);
            }

            // Chainable methods
            return this.each(function () {
                var data = this[DATA_KEY];
                if (!data || !data.instance) return;

                switch (method) {
                    case 'refresh':
                        // SortableJS auto-detects DOM changes; no-op
                        break;

                    case 'destroy':
                        data.instance.destroy();
                        delete this[DATA_KEY];
                        break;

                    case 'disable':
                        data.instance.option('disabled', true);
                        break;

                    case 'enable':
                        data.instance.option('disabled', false);
                        break;

                    case 'option':
                        if (args.length === 2) {
                            data.instance.option(args[0], args[1]);
                        }
                        break;

                    case 'cancel':
                        // Cannot truly cancel mid-drag in SortableJS; no-op
                        break;
                }
            });
        }

        // Object = initialisation
        return this.each(function () {
            initSortable(this, method);
        });
    };

})();
