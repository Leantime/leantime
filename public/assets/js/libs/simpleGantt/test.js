(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.Gantt = factory());
}(this, (function () { 'use strict';

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
        return typeof obj;
    } : function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };











    var classCallCheck = function (instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    };

    var createClass = function () {
        function defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }

        return function (Constructor, protoProps, staticProps) {
            if (protoProps) defineProperties(Constructor.prototype, protoProps);
            if (staticProps) defineProperties(Constructor, staticProps);
            return Constructor;
        };
    }();





    var defineProperty = function (obj, key, value) {
        if (key in obj) {
            Object.defineProperty(obj, key, {
                value: value,
                enumerable: true,
                configurable: true,
                writable: true
            });
        } else {
            obj[key] = value;
        }

        return obj;
    };



































    var toConsumableArray = function (arr) {
        if (Array.isArray(arr)) {
            for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

            return arr2;
        } else {
            return Array.from(arr);
        }
    };

    var YEAR = 'year';
    var MONTH = 'month';
    var DAY = 'day';
    var HOUR = 'hour';
    var MINUTE = 'minute';
    var SECOND = 'second';
    var MILLISECOND = 'millisecond';

    var month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    var date_utils = {
        parse: function parse(date) {
            var date_separator = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '-';
            var time_separator = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : ':';

            if (date instanceof Date) {
                return date;
            }
            if (typeof date === 'string') {
                var date_parts = void 0,
                    time_parts = void 0;
                var parts = date.split(' ');

                date_parts = parts[0].split(date_separator).map(function (val) {
                    return parseInt(val, 10);
                });
                time_parts = parts[1] && parts[1].split(time_separator);

                // month is 0 indexed
                date_parts[1] = date_parts[1] - 1;

                var vals = date_parts;

                if (time_parts && time_parts.length) {
                    vals = vals.concat(time_parts);
                }

                return new (Function.prototype.bind.apply(Date, [null].concat(toConsumableArray(vals))))();
            }
        },
        to_string: function to_string(date) {
            var with_time = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            if (!(date instanceof Date)) {
                throw new TypeError('Invalid argument type');
            }
            var vals = this.get_date_values(date).map(function (val, i) {
                if (i === 1) {
                    // add 1 for month
                    val = val + 1;
                }

                return padStart(val + '', 2, '0');
            });
            var date_string = vals[0] + '-' + vals[1] + '-' + vals[2];
            var time_string = vals[3] + ':' + vals[4] + ':' + vals[5];

            return date_string + (with_time ? ' ' + time_string : '');
        },
        format: function format(date) {
            var format_string = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'YYYY-MM-DD HH:mm:ss';

            var values = this.get_date_values(date).map(function (d) {
                return padStart(d, 2, 0);
            });
            var format_map = {
                YYYY: values[0],
                MM: padStart(+values[1] + 1, 2, 0),
                DD: values[2],
                HH: values[3],
                mm: values[4],
                ss: values[5],
                D: values[2],
                MMMM: month_names[+values[1]],
                MMM: month_names[+values[1]]
            };

            var str = format_string;
            var formatted_values = [];

            Object.keys(format_map).sort(function (a, b) {
                return b.length - a.length;
            }) // big string first
                .forEach(function (key) {
                    if (str.includes(key)) {
                        str = str.replace(key, '$' + formatted_values.length);
                        formatted_values.push(format_map[key]);
                    }
                });

            formatted_values.forEach(function (value, i) {
                str = str.replace('$' + i, value);
            });

            return str;
        },
        diff: function diff(date_a, date_b) {
            var scale = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : DAY;

            var milliseconds = void 0,
                seconds = void 0,
                hours = void 0,
                minutes = void 0,
                days = void 0,
                months = void 0,
                years = void 0;

            milliseconds = date_a - date_b;
            seconds = milliseconds / 1000;
            minutes = seconds / 60;
            hours = minutes / 60;
            days = hours / 24;
            months = days / 30;
            years = months / 12;

            if (!scale.endsWith('s')) {
                scale += 's';
            }

            return Math.floor({
                milliseconds: milliseconds,
                seconds: seconds,
                minutes: minutes,
                hours: hours,
                days: days,
                months: months,
                years: years
            }[scale]);
        },
        today: function today() {
            var vals = this.get_date_values(new Date()).slice(0, 3);
            return new (Function.prototype.bind.apply(Date, [null].concat(toConsumableArray(vals))))();
        },
        now: function now() {
            return new Date();
        },
        add: function add(date, qty, scale) {
            qty = parseInt(qty, 10);
            var vals = [date.getFullYear() + (scale === YEAR ? qty : 0), date.getMonth() + (scale === MONTH ? qty : 0), date.getDate() + (scale === DAY ? qty : 0), date.getHours() + (scale === HOUR ? qty : 0), date.getMinutes() + (scale === MINUTE ? qty : 0), date.getSeconds() + (scale === SECOND ? qty : 0), date.getMilliseconds() + (scale === MILLISECOND ? qty : 0)];
            return new (Function.prototype.bind.apply(Date, [null].concat(vals)))();
        },
        start_of: function start_of(date, scale) {
            var _scores;

            var scores = (_scores = {}, defineProperty(_scores, YEAR, 6), defineProperty(_scores, MONTH, 5), defineProperty(_scores, DAY, 4), defineProperty(_scores, HOUR, 3), defineProperty(_scores, MINUTE, 2), defineProperty(_scores, SECOND, 1), defineProperty(_scores, MILLISECOND, 0), _scores);

            function should_reset(_scale) {
                var max_score = scores[scale];
                return scores[_scale] <= max_score;
            }

            var vals = [date.getFullYear(), should_reset(YEAR) ? 0 : date.getMonth(), should_reset(MONTH) ? 1 : date.getDate(), should_reset(DAY) ? 0 : date.getHours(), should_reset(HOUR) ? 0 : date.getMinutes(), should_reset(MINUTE) ? 0 : date.getSeconds(), should_reset(SECOND) ? 0 : date.getMilliseconds()];

            return new (Function.prototype.bind.apply(Date, [null].concat(vals)))();
        },
        clone: function clone(date) {
            return new (Function.prototype.bind.apply(Date, [null].concat(toConsumableArray(this.get_date_values(date)))))();
        },
        get_date_values: function get_date_values(date) {
            return [date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()];
        },
        get_days_in_month: function get_days_in_month(date) {
            var no_of_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            var month = date.getMonth();

            if (month !== 1) {
                return no_of_days[month];
            }

            // Feb
            var year = date.getFullYear();
            if (year % 4 == 0 && year % 100 != 0 || year % 400 == 0) {
                return 29;
            }
            return 28;
        }
    };

// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/padStart
    function padStart(str, targetLength, padString) {
        str = str + '';
        targetLength = targetLength >> 0;
        padString = String(typeof padString !== 'undefined' ? padString : ' ');
        if (str.length > targetLength) {
            return String(str);
        } else {
            targetLength = targetLength - str.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength / padString.length);
            }
            return padString.slice(0, targetLength) + String(str);
        }
    }

    function $(expr, con) {
        return typeof expr === 'string' ? (con || document).querySelector(expr) : expr || null;
    }

    function createSVG(tag, attrs) {
        var elem = document.createElementNS('http://www.w3.org/2000/svg', tag);
        for (var attr in attrs) {
            if (attr === 'append_to') {
                var parent = attrs.append_to;
                parent.appendChild(elem);
            } else if (attr === 'innerHTML') {
                elem.innerHTML = attrs.innerHTML;
            } else if (attr === 'clipPath') {
                elem.setAttribute('clip-path', 'url(#' + attrs[attr] + ')');
            } else {
                elem.setAttribute(attr, attrs[attr]);
            }
        }
        return elem;
    }

    function animateSVG(svgElement, attr, from, to) {
        var animatedSvgElement = getAnimationElement(svgElement, attr, from, to);

        if (animatedSvgElement === svgElement) {
            // triggered 2nd time programmatically
            // trigger artificial click event
            var event = document.createEvent('HTMLEvents');
            event.initEvent('click', true, true);
            event.eventName = 'click';
            animatedSvgElement.dispatchEvent(event);
        }
    }

    function getAnimationElement(svgElement, attr, from, to) {
        var dur = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '0.4s';
        var begin = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : '0.1s';

        var animEl = svgElement.querySelector('animate');
        if (animEl) {
            $.attr(animEl, {
                attributeName: attr,
                from: from,
                to: to,
                dur: dur,
                begin: 'click + ' + begin // artificial click
            });
            return svgElement;
        }

        var animateElement = createSVG('animate', {
            attributeName: attr,
            from: from,
            to: to,
            dur: dur,
            begin: begin,
            calcMode: 'spline',
            values: from + ';' + to,
            keyTimes: '0; 1',
            keySplines: cubic_bezier('ease-out')
        });
        svgElement.appendChild(animateElement);

        return svgElement;
    }

    function cubic_bezier(name) {
        return {
            ease: '.25 .1 .25 1',
            linear: '0 0 1 1',
            'ease-in': '.42 0 1 1',
            'ease-out': '0 0 .58 1',
            'ease-in-out': '.42 0 .58 1'
        }[name];
    }

    $.on = function (element, event, selector, callback) {
        if (!callback) {
            callback = selector;
            $.bind(element, event, callback);
        } else {
            $.delegate(element, event, selector, callback);
        }
    };

    $.off = function (element, event, handler) {
        element.removeEventListener(event, handler);
    };

    $.bind = function (element, event, callback) {
        event.split(/\s+/).forEach(function (event) {
            element.addEventListener(event, callback);
        });
    };

    $.delegate = function (element, event, selector, callback) {
        element.addEventListener(event, function (e) {
            var delegatedTarget = e.target.closest(selector);
            if (delegatedTarget) {
                e.delegatedTarget = delegatedTarget;
                callback.call(this, e, delegatedTarget);
            }
        });
    };

    $.closest = function (selector, element) {
        if (!element) return null;

        if (element.matches(selector)) {
            return element;
        }

        return $.closest(selector, element.parentNode);
    };

    $.attr = function (element, attr, value) {
        if (!value && typeof attr === 'string') {
            return element.getAttribute(attr);
        }

        if ((typeof attr === 'undefined' ? 'undefined' : _typeof(attr)) === 'object') {
            for (var key in attr) {
                $.attr(element, key, attr[key]);
            }
            return;
        }

        element.setAttribute(attr, value);
    };

    var Bar = function () {
        function Bar(gantt, task) {
            classCallCheck(this, Bar);

            this.set_defaults(gantt, task);
            this.prepare();
            this.draw();
            this.bind();
        }

        createClass(Bar, [{
            key: 'set_defaults',
            value: function set_defaults(gantt, task) {
                this.action_completed = false;
                this.gantt = gantt;
                this.task = task;
            }
        }, {
            key: 'prepare',
            value: function prepare() {
                this.prepare_values();
                this.prepare_helpers();
            }
        }, {
            key: 'prepare_values',
            value: function prepare_values() {
                this.invalid = this.task.invalid;
                this.height = this.gantt.options.bar_height;
                this.image_size = this.gantt.options.bar_height - 5;
                this.x = this.compute_x();
                this.y = this.compute_y();
                this.corner_radius = this.gantt.options.bar_corner_radius;
                this.duration = date_utils.diff(this.task._end, this.task._start, 'hour') / this.gantt.options.step;
                this.width = this.gantt.options.column_width * this.duration;
                this.progress_width = this.gantt.options.column_width * this.duration * (this.task.progress / 100) || 0;
                this.group = createSVG('g', {
                    class: 'bar-wrapper ' + (this.task.custom_class || ''),
                    'data-id': this.task.id
                });
                this.bar_group = createSVG('g', {
                    class: 'bar-group',
                    append_to: this.group
                });
                this.handle_group = createSVG('g', {
                    class: 'handle-group',
                    append_to: this.group
                });
            }
        }, {
            key: 'prepare_helpers',
            value: function prepare_helpers() {
                SVGElement.prototype.getX = function () {
                    return +this.getAttribute('x');
                };
                SVGElement.prototype.getY = function () {
                    return +this.getAttribute('y');
                };
                SVGElement.prototype.getWidth = function () {
                    return +this.getAttribute('width');
                };
                SVGElement.prototype.getHeight = function () {
                    return +this.getAttribute('height');
                };
                SVGElement.prototype.getEndX = function () {
                    return this.getX() + this.getWidth();
                };
            }
        }, {
            key: 'draw',
            value: function draw() {
                this.draw_bar();
                this.draw_progress_bar();
                this.draw_label();
                if (this.task.img) {
                    this.draw_img();
                }
                this.draw_resize_handles();
            }
        }, {
            key: 'draw_bar',
            value: function draw_bar() {
                this.$bar = createSVG('rect', {
                    x: this.x,
                    y: this.y,
                    width: this.width,
                    height: this.height,
                    rx: this.corner_radius,
                    ry: this.corner_radius,
                    class: 'bar',
                    append_to: this.bar_group
                });

                animateSVG(this.$bar, 'width', 0, this.width);

                if (this.invalid) {
                    this.$bar.classList.add('bar-invalid');
                }
            }
        }, {
            key: 'draw_progress_bar',
            value: function draw_progress_bar() {
                if (this.invalid) return;
                this.$bar_progress = createSVG('rect', {
                    x: this.x,
                    y: this.y,
                    width: this.progress_width,
                    height: this.height,
                    rx: this.corner_radius,
                    ry: this.corner_radius,
                    class: 'bar-progress',
                    append_to: this.bar_group
                });

                animateSVG(this.$bar_progress, 'width', 0, this.progress_width);
            }
        }, {
            key: 'draw_label',
            value: function draw_label() {
                var _this = this;

                var x_coord = void 0;
                var padding = 5;

                if (this.task.img) {
                    x_coord = this.x + this.image_size + padding;
                } else {
                    x_coord = this.x + 5;
                }

                createSVG('text', {
                    x: x_coord,
                    y: this.y + this.height / 2,
                    innerHTML: this.task.name,
                    class: 'bar-label',
                    append_to: this.bar_group
                });
                // labels get BBox in the next tick
                requestAnimationFrame(function () {
                    return _this.update_label_position();
                });
            }
        }, {
            key: 'draw_img',
            value: function draw_img() {
                var x_offset = 10,
                    y_offset = 2;
                var defs = void 0,
                    clipPath = void 0;

                defs = createSVG('defs', {
                    append_to: this.bar_group
                });

                createSVG('rect', {
                    id: 'rect_' + this.task.id,
                    x: this.x + x_offset,
                    y: this.y + y_offset,
                    width: this.image_size,
                    height: this.image_size,
                    rx: '15',
                    class: 'img_mask',
                    append_to: defs
                });

                clipPath = createSVG('clipPath', {
                    id: 'clip_' + this.task.id,
                    append_to: defs
                });

                createSVG('use', {
                    href: '#rect_' + this.task.id,
                    append_to: clipPath
                });

                createSVG('image', {
                    x: this.x + x_offset,
                    y: this.y + y_offset,
                    width: this.image_size,
                    height: this.image_size,
                    class: 'bar-img',
                    href: this.task.img,
                    clipPath: 'clip_' + this.task.id,
                    append_to: this.bar_group
                });
            }
        }, {
            key: 'draw_resize_handles',
            value: function draw_resize_handles() {
                if (this.invalid) return;

                var bar = this.$bar;
                var handle_width = 8;

                createSVG('rect', {
                    x: bar.getX() + bar.getWidth() - 9,
                    y: bar.getY() + 1,
                    width: handle_width,
                    height: this.height - 2,
                    rx: this.corner_radius,
                    ry: this.corner_radius,
                    class: 'handle right',
                    append_to: this.handle_group
                });

                createSVG('rect', {
                    x: bar.getX() + 1,
                    y: bar.getY() + 1,
                    width: handle_width,
                    height: this.height - 2,
                    rx: this.corner_radius,
                    ry: this.corner_radius,
                    class: 'handle left',
                    append_to: this.handle_group
                });

                if (this.task.progress && this.task.progress < 100) {
                    this.$handle_progress = createSVG('polygon', {
                        points: this.get_progress_polygon_points().join(','),
                        class: 'handle progress',
                        append_to: this.handle_group
                    });
                }
            }
        }, {
            key: 'get_progress_polygon_points',
            value: function get_progress_polygon_points() {
                var bar_progress = this.$bar_progress;
                return [bar_progress.getEndX() - 5, bar_progress.getY() + bar_progress.getHeight(), bar_progress.getEndX() + 5, bar_progress.getY() + bar_progress.getHeight(), bar_progress.getEndX(), bar_progress.getY() + bar_progress.getHeight() - 8.66];
            }
        }, {
            key: 'bind',
            value: function bind() {
                if (this.invalid) return;
                this.setup_click_event();
            }
        }, {
            key: 'setup_click_event',
            value: function setup_click_event() {
                var _this2 = this;

                $.on(this.group, 'focus ' + this.gantt.options.popup_trigger, function (e) {
                    if (_this2.action_completed) {
                        // just finished a move action, wait for a few seconds
                        return;
                    }

                    if (e.type === 'click') {
                        _this2.gantt.trigger_event('click', [_this2.task]);
                    }

                    _this2.gantt.unselect_all();
                    _this2.group.classList.toggle('active');

                    _this2.show_popup();
                });
            }
        }, {
            key: 'show_popup',
            value: function show_popup() {
                if (this.gantt.bar_being_dragged) return;

                var start_date = date_utils.format(this.task._start, 'MMM D');
                var end_date = date_utils.format(date_utils.add(this.task._end, -1, 'second'), 'MMM D');
                var subtitle = start_date + ' - ' + end_date;

                this.gantt.show_popup({
                    target_element: this.$bar,
                    title: this.task.name,
                    subtitle: subtitle,
                    task: this.task
                });
            }
        }, {
            key: 'update_bar_position',
            value: function update_bar_position(_ref) {
                var _this3 = this;

                var _ref$x = _ref.x,
                    x = _ref$x === undefined ? null : _ref$x,
                    _ref$width = _ref.width,
                    width = _ref$width === undefined ? null : _ref$width;

                var bar = this.$bar;
                if (x) {
                    // get all x values of parent task
                    var xs = this.task.dependencies.map(function (dep) {
                        return _this3.gantt.get_bar(dep).$bar.getX();
                    });
                    // child task must not go before parent
                    var valid_x = xs.reduce(function (prev, curr) {
                        return x >= curr;
                    }, x);
                    if (!valid_x) {
                        width = null;
                        return;
                    }
                    this.update_attr(bar, 'x', x);
                }
                if (width && width >= this.gantt.options.column_width) {
                    this.update_attr(bar, 'width', width);
                }
                this.update_label_position();
                this.update_handle_position();
                this.update_progressbar_position();
                this.update_arrow_position();
            }
        }, {
            key: 'update_label_position_on_horizontal_scroll',
            value: function update_label_position_on_horizontal_scroll(_ref2) {
                var x = _ref2.x,
                    sx = _ref2.sx;


                var container = document.querySelector('.gantt-container');
                var label = this.group.querySelector('.bar-label');
                var img = this.group.querySelector('.bar-img') || '';
                var img_mask = this.bar_group.querySelector('.img_mask') || '';

                var barWidthLimit = this.$bar.getX() + this.$bar.getWidth();
                var newLabelX = label.getX() + x;
                var newImgX = img && img.getX() + x || 0;
                var imgWidth = img && img.getBBox().width + 7 || 7;
                var labelEndX = newLabelX + label.getBBox().width + 7;
                var viewportCentral = sx + container.clientWidth / 2;

                if (label.classList.contains('big')) return;

                if (labelEndX < barWidthLimit && x > 0 && labelEndX < viewportCentral) {
                    label.setAttribute('x', newLabelX);
                    if (img) {
                        img.setAttribute('x', newImgX);
                        img_mask.setAttribute('x', newImgX);
                    }
                } else if (newLabelX - imgWidth > this.$bar.getX() && x < 0 && labelEndX > viewportCentral) {
                    label.setAttribute('x', newLabelX);
                    if (img) {
                        img.setAttribute('x', newImgX);
                        img_mask.setAttribute('x', newImgX);
                    }
                }
            }
        }, {
            key: 'date_changed',
            value: function date_changed() {
                var changed = false;

                var _compute_start_end_da = this.compute_start_end_date(),
                    new_start_date = _compute_start_end_da.new_start_date,
                    new_end_date = _compute_start_end_da.new_end_date;

                if (Number(this.task._start) !== Number(new_start_date)) {
                    changed = true;
                    this.task._start = new_start_date;
                }

                if (Number(this.task._end) !== Number(new_end_date)) {
                    changed = true;
                    this.task._end = new_end_date;
                }

                if (!changed) return;

                this.gantt.trigger_event('date_change', [this.task, new_start_date, date_utils.add(new_end_date, -1, 'second')]);
            }
        }, {
            key: 'progress_changed',
            value: function progress_changed() {
                var new_progress = this.compute_progress();
                this.task.progress = new_progress;
                this.gantt.trigger_event('progress_change', [this.task, new_progress]);
            }
        }, {
            key: 'set_action_completed',
            value: function set_action_completed() {
                var _this4 = this;

                this.action_completed = true;
                setTimeout(function () {
                    return _this4.action_completed = false;
                }, 1000);
            }
        }, {
            key: 'compute_start_end_date',
            value: function compute_start_end_date() {
                var bar = this.$bar;
                var x_in_units = bar.getX() / this.gantt.options.column_width;
                var new_start_date = date_utils.add(this.gantt.gantt_start, x_in_units * this.gantt.options.step, 'hour');
                var width_in_units = bar.getWidth() / this.gantt.options.column_width;
                var new_end_date = date_utils.add(new_start_date, width_in_units * this.gantt.options.step, 'hour');

                return { new_start_date: new_start_date, new_end_date: new_end_date };
            }
        }, {
            key: 'compute_progress',
            value: function compute_progress() {
                var progress = this.$bar_progress.getWidth() / this.$bar.getWidth() * 100;
                return parseInt(progress, 10);
            }
        }, {
            key: 'compute_x',
            value: function compute_x() {
                var _gantt$options = this.gantt.options,
                    step = _gantt$options.step,
                    column_width = _gantt$options.column_width;

                var task_start = this.task._start;
                var gantt_start = this.gantt.gantt_start;

                var diff = date_utils.diff(task_start, gantt_start, 'hour');
                var x = diff / step * column_width;

                if (this.gantt.view_is('Month')) {
                    var _diff = date_utils.diff(task_start, gantt_start, 'day');
                    x = _diff * column_width / 30;
                }
                return x;
            }
        }, {
            key: 'compute_y',
            value: function compute_y() {
                return this.gantt.options.header_height + this.gantt.options.padding + this.task._index * (this.height + this.gantt.options.padding);
            }
        }, {
            key: 'get_snap_position',
            value: function get_snap_position(dx) {
                var odx = dx,
                    rem = void 0,
                    position = void 0;

                if (this.gantt.view_is('Week')) {
                    rem = dx % (this.gantt.options.column_width / 7);
                    position = odx - rem + (rem < this.gantt.options.column_width / 14 ? 0 : this.gantt.options.column_width / 7);
                } else if (this.gantt.view_is('Month')) {
                    rem = dx % (this.gantt.options.column_width / 30);
                    position = odx - rem + (rem < this.gantt.options.column_width / 60 ? 0 : this.gantt.options.column_width / 30);
                } else {
                    rem = dx % this.gantt.options.column_width;
                    position = odx - rem + (rem < this.gantt.options.column_width / 2 ? 0 : this.gantt.options.column_width);
                }
                return position;
            }
        }, {
            key: 'update_attr',
            value: function update_attr(element, attr, value) {
                value = +value;
                if (!isNaN(value)) {
                    element.setAttribute(attr, value);
                }
                return element;
            }
        }, {
            key: 'update_progressbar_position',
            value: function update_progressbar_position() {
                this.$bar_progress.setAttribute('x', this.$bar.getX());
                this.$bar_progress.setAttribute('width', this.$bar.getWidth() * (this.task.progress / 100));
            }
        }, {
            key: 'update_label_position',
            value: function update_label_position() {
                var img_mask = this.bar_group.querySelector('.img_mask') || '';
                var bar = this.$bar,
                    label = this.group.querySelector('.bar-label'),
                    img = this.group.querySelector('.bar-img');

                var padding = 5;
                var x_offset_label_img = this.image_size + 10;

                if (label.getBBox().width > bar.getWidth()) {
                    label.classList.add('big');
                    if (img) {
                        img.setAttribute('x', bar.getX() + bar.getWidth() + padding);
                        img_mask.setAttribute('x', bar.getX() + bar.getWidth() + padding);
                        label.setAttribute('x', bar.getX() + bar.getWidth() + x_offset_label_img);
                    } else {
                        label.setAttribute('x', bar.getX() + bar.getWidth() + padding);
                    }
                } else {
                    label.classList.remove('big');

                    if (img) {
                        img.setAttribute('x', bar.getX() + padding);
                        img_mask.setAttribute('x', bar.getX() + padding);
                        label.setAttribute('x', bar.getX() + x_offset_label_img);
                    } else {
                        label.setAttribute('x', bar.getX() + padding);
                    }
                }
            }
        }, {
            key: 'update_handle_position',
            value: function update_handle_position() {
                var bar = this.$bar;
                this.handle_group.querySelector('.handle.left').setAttribute('x', bar.getX() + 1);
                this.handle_group.querySelector('.handle.right').setAttribute('x', bar.getEndX() - 9);
                var handle = this.group.querySelector('.handle.progress');
                handle && handle.setAttribute('points', this.get_progress_polygon_points());
            }
        }, {
            key: 'update_arrow_position',
            value: function update_arrow_position() {
                this.arrows = this.arrows || [];
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = this.arrows[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var arrow = _step.value;

                        arrow.update();
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }
            }
        }]);
        return Bar;
    }();

    var Arrow = function () {
        function Arrow(gantt, from_task, to_task) {
            classCallCheck(this, Arrow);

            this.gantt = gantt;
            this.from_task = from_task;
            this.to_task = to_task;

            this.calculate_path();
            this.draw();
        }

        createClass(Arrow, [{
            key: 'calculate_path',
            value: function calculate_path() {
                var _this = this;

                var start_x = this.from_task.$bar.getX() + this.from_task.$bar.getWidth() / 2;

                var condition = function condition() {
                    return _this.to_task.$bar.getX() < start_x + _this.gantt.options.padding && start_x > _this.from_task.$bar.getX() + _this.gantt.options.padding;
                };

                while (condition()) {
                    start_x -= 10;
                }

                var start_y = this.gantt.options.header_height + this.gantt.options.bar_height + (this.gantt.options.padding + this.gantt.options.bar_height) * this.from_task.task._index + this.gantt.options.padding;

                var end_x = this.to_task.$bar.getX() - this.gantt.options.padding / 2;
                var end_y = this.gantt.options.header_height + this.gantt.options.bar_height / 2 + (this.gantt.options.padding + this.gantt.options.bar_height) * this.to_task.task._index + this.gantt.options.padding;

                var from_is_below_to = this.from_task.task._index > this.to_task.task._index;
                var curve = this.gantt.options.arrow_curve;
                var clockwise = from_is_below_to ? 1 : 0;
                var curve_y = from_is_below_to ? -curve : curve;
                var offset = from_is_below_to ? end_y + this.gantt.options.arrow_curve : end_y - this.gantt.options.arrow_curve;

                this.path = '\n            M ' + start_x + ' ' + start_y + '\n            V ' + offset + '\n            a ' + curve + ' ' + curve + ' 0 0 ' + clockwise + ' ' + curve + ' ' + curve_y + '\n            L ' + end_x + ' ' + end_y + '\n            m -5 -5\n            l 5 5\n            l -5 5';

                if (this.to_task.$bar.getX() < this.from_task.$bar.getX() + this.gantt.options.padding) {
                    var down_1 = this.gantt.options.padding / 2 - curve;
                    var down_2 = this.to_task.$bar.getY() + this.to_task.$bar.getHeight() / 2 - curve_y;
                    var left = this.to_task.$bar.getX() - this.gantt.options.padding;

                    this.path = '\n                M ' + start_x + ' ' + start_y + '\n                v ' + down_1 + '\n                a ' + curve + ' ' + curve + ' 0 0 1 -' + curve + ' ' + curve + '\n                H ' + left + '\n                a ' + curve + ' ' + curve + ' 0 0 ' + clockwise + ' -' + curve + ' ' + curve_y + '\n                V ' + down_2 + '\n                a ' + curve + ' ' + curve + ' 0 0 ' + clockwise + ' ' + curve + ' ' + curve_y + '\n                L ' + end_x + ' ' + end_y + '\n                m -5 -5\n                l 5 5\n                l -5 5';
                }
            }
        }, {
            key: 'draw',
            value: function draw() {
                this.element = createSVG('path', {
                    d: this.path,
                    'data-from': this.from_task.task.id,
                    'data-to': this.to_task.task.id
                });
            }
        }, {
            key: 'update',
            value: function update() {
                this.calculate_path();
                this.element.setAttribute('d', this.path);
            }
        }]);
        return Arrow;
    }();

    var Popup = function () {
        function Popup(parent, custom_html) {
            classCallCheck(this, Popup);

            this.parent = parent;
            this.custom_html = custom_html;
            this.make();
        }

        createClass(Popup, [{
            key: 'make',
            value: function make() {
                this.parent.innerHTML = '\n            <div class="title"></div>\n            <div class="subtitle"></div>\n            <div class="pointer"></div>\n        ';

                this.hide();

                this.title = this.parent.querySelector('.title');
                this.subtitle = this.parent.querySelector('.subtitle');
                this.pointer = this.parent.querySelector('.pointer');
            }
        }, {
            key: 'show',
            value: function show(options) {
                if (!options.target_element) {
                    throw new Error('target_element is required to show popup');
                }
                if (!options.position) {
                    options.position = 'left';
                }
                var target_element = options.target_element;

                if (this.custom_html) {
                    var html = this.custom_html(options.task);
                    html += '<div class="pointer"></div>';
                    this.parent.innerHTML = html;
                    this.pointer = this.parent.querySelector('.pointer');
                } else {
                    // set data
                    this.title.innerHTML = options.title;
                    this.subtitle.innerHTML = options.subtitle;
                    this.parent.style.width = this.parent.clientWidth + 'px';
                }

                // set position
                var position_meta = void 0;
                if (target_element instanceof HTMLElement) {
                    position_meta = target_element.getBoundingClientRect();
                } else if (target_element instanceof SVGElement) {
                    position_meta = options.target_element.getBBox();
                }

                if (options.position === 'left') {
                    this.parent.style.left = position_meta.x + (position_meta.width + 10) + 'px';
                    this.parent.style.top = position_meta.y + 'px';

                    this.pointer.style.transform = 'rotateZ(90deg)';
                    this.pointer.style.left = '-7px';
                    this.pointer.style.top = '2px';
                }

                // show
                this.parent.style.opacity = 1;
            }
        }, {
            key: 'hide',
            value: function hide() {
                this.parent.style.opacity = 0;
            }
        }]);
        return Popup;
    }();

    var Gantt = function () {
        function Gantt(wrapper, tasks, options) {
            classCallCheck(this, Gantt);

            this.setup_wrapper(wrapper);
            this.setup_options(options);
            this.setup_tasks(tasks);
            // initialize with default view mode
            this.change_view_mode();
            this.bind_events();
        }

        createClass(Gantt, [{
            key: 'setup_wrapper',
            value: function setup_wrapper(element) {
                if (typeof element === 'string') {
                    element = document.querySelector(element);
                }

                if (!(element instanceof HTMLElement)) {
                    throw new Error('Invalid argument passed for element');
                }

                // parent div element
                this.$container = document.createElement('div');
                this.$container.classList.add('gantt-container');
                element.appendChild(this.$container);

                // parent svg element
                this.$svg = createSVG('svg', {
                    append_to: this.$container,
                    class: 'gantt'
                });

                // popup wrapper
                this.popup_wrapper = document.createElement('div');
                this.popup_wrapper.classList.add('popup-wrapper');
                this.$svg.parentElement.appendChild(this.popup_wrapper);
            }
        }, {
            key: 'setup_options',
            value: function setup_options(options) {
                var default_options = {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                    bar_height: 20,
                    bar_corner_radius: 3,
                    arrow_curve: 5,
                    padding: 18,
                    view_mode: 'Day',
                    date_format: 'YYYY-MM-DD',
                    popup_trigger: 'click',
                    custom_popup_html: null
                };
                this.options = Object.assign({}, default_options, options);
            }
        }, {
            key: 'setup_tasks',
            value: function setup_tasks(tasks) {
                // prepare tasks
                this.tasks = tasks.map(function (task, i) {
                    // convert to Date objects
                    task._start = date_utils.parse(task.start);
                    task._end = date_utils.parse(task.end);

                    // make task invalid if duration too large
                    if (date_utils.diff(task._end, task._start, 'year') > 10) {
                        task.end = null;
                    }

                    // cache index
                    task._index = i;

                    // invalid dates
                    if (!task.start && !task.end) {
                        var today = date_utils.today();
                        task._start = today;
                        task._end = date_utils.add(today, 2, 'day');
                    }

                    if (!task.start && task.end) {
                        task._start = date_utils.add(task._end, -2, 'day');
                    }

                    if (task.start && !task.end) {
                        task._end = date_utils.add(task._start, 2, 'day');
                    }

                    // if hours is not set, assume the last day is full day
                    // e.g: 2018-09-09 becomes 2018-09-09 23:59:59
                    var task_end_values = date_utils.get_date_values(task._end);
                    if (task_end_values.slice(3).every(function (d) {
                            return d === 0;
                        })) {
                        task._end = date_utils.add(task._end, 24, 'hour');
                    }

                    // invalid flag
                    if (!task.start || !task.end) {
                        task.invalid = true;
                    }

                    // dependencies
                    if (typeof task.dependencies === 'string' || !task.dependencies) {
                        var deps = [];
                        if (task.dependencies) {
                            deps = task.dependencies.split(',').map(function (d) {
                                return d.trim();
                            }).filter(function (d) {
                                return d;
                            });
                        }
                        task.dependencies = deps;
                    }

                    // uids
                    if (!task.id) {
                        task.id = generate_id(task);
                    }

                    return task;
                });

                this.setup_dependencies();
            }
        }, {
            key: 'setup_dependencies',
            value: function setup_dependencies() {
                this.dependency_map = {};
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = this.tasks[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var t = _step.value;
                        var _iteratorNormalCompletion2 = true;
                        var _didIteratorError2 = false;
                        var _iteratorError2 = undefined;

                        try {
                            for (var _iterator2 = t.dependencies[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                                var d = _step2.value;

                                this.dependency_map[d] = this.dependency_map[d] || [];
                                this.dependency_map[d].push(t.id);
                            }
                        } catch (err) {
                            _didIteratorError2 = true;
                            _iteratorError2 = err;
                        } finally {
                            try {
                                if (!_iteratorNormalCompletion2 && _iterator2.return) {
                                    _iterator2.return();
                                }
                            } finally {
                                if (_didIteratorError2) {
                                    throw _iteratorError2;
                                }
                            }
                        }
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }
            }
        }, {
            key: 'refresh',
            value: function refresh(tasks) {
                this.setup_tasks(tasks);
                this.change_view_mode();
            }
        }, {
            key: 'change_view_mode',
            value: function change_view_mode() {
                var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.options.view_mode;

                this.update_view_scale(mode);
                this.setup_dates();
                this.render();
                // fire viewmode_change event
                this.trigger_event('view_change', [mode]);
            }
        }, {
            key: 'update_view_scale',
            value: function update_view_scale(view_mode) {
                this.options.view_mode = view_mode;

                if (view_mode === 'Day') {
                    this.options.step = 24;
                    this.options.column_width = 38;
                } else if (view_mode === 'Half Day') {
                    this.options.step = 24 / 2;
                    this.options.column_width = 38;
                } else if (view_mode === 'Quarter Day') {
                    this.options.step = 24 / 4;
                    this.options.column_width = 38;
                } else if (view_mode === 'Week') {
                    this.options.step = 24 * 7;
                    this.options.column_width = 140;
                } else if (view_mode === 'Month') {
                    this.options.step = 24 * 30;
                    this.options.column_width = 120;
                }
            }
        }, {
            key: 'setup_dates',
            value: function setup_dates() {
                this.setup_gantt_dates();
                this.setup_date_values();
            }
        }, {
            key: 'setup_gantt_dates',
            value: function setup_gantt_dates() {
                this.gantt_start = this.gantt_end = null;

                var _iteratorNormalCompletion3 = true;
                var _didIteratorError3 = false;
                var _iteratorError3 = undefined;

                try {
                    for (var _iterator3 = this.tasks[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
                        var task = _step3.value;

                        // set global start and end date
                        if (!this.gantt_start || task._start < this.gantt_start) {
                            this.gantt_start = task._start;
                        }
                        if (!this.gantt_end || task._end > this.gantt_end) {
                            this.gantt_end = task._end;
                        }
                    }
                } catch (err) {
                    _didIteratorError3 = true;
                    _iteratorError3 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion3 && _iterator3.return) {
                            _iterator3.return();
                        }
                    } finally {
                        if (_didIteratorError3) {
                            throw _iteratorError3;
                        }
                    }
                }

                this.gantt_start = date_utils.start_of(this.gantt_start, 'day');
                this.gantt_end = date_utils.start_of(this.gantt_end, 'day');

                // add date padding on both sides
                if (this.view_is(['Quarter Day', 'Half Day'])) {
                    this.gantt_start = date_utils.add(this.gantt_start, -7, 'day');
                    this.gantt_end = date_utils.add(this.gantt_end, 7, 'day');
                } else if (this.view_is('Month')) {
                    this.gantt_start = date_utils.start_of(this.gantt_start, 'year');
                    this.gantt_end = date_utils.add(this.gantt_end, 1, 'year');
                } else {
                    this.gantt_start = date_utils.add(this.gantt_start, -1, 'month');
                    this.gantt_end = date_utils.add(this.gantt_end, 1, 'month');
                }
            }
        }, {
            key: 'setup_date_values',
            value: function setup_date_values() {
                this.dates = [];
                var cur_date = null;

                while (cur_date === null || cur_date < this.gantt_end) {
                    if (!cur_date) {
                        cur_date = date_utils.clone(this.gantt_start);
                    } else {
                        cur_date = this.view_is('Month') ? date_utils.add(cur_date, 1, 'month') : date_utils.add(cur_date, this.options.step, 'hour');
                    }
                    this.dates.push(cur_date);
                }
            }
        }, {
            key: 'bind_events',
            value: function bind_events() {
                this.bind_grid_click();
                this.bind_bar_events();
            }
        }, {
            key: 'render',
            value: function render() {
                this.clear();
                this.setup_layers();
                this.make_grid();
                this.make_dates();
                this.make_bars();
                this.make_arrows();
                this.map_arrows_on_bars();
                this.set_width();
                this.set_scroll_position();
            }
        }, {
            key: 'setup_layers',
            value: function setup_layers() {
                this.layers = {};
                var layers = ['grid', 'date', 'arrow', 'progress', 'bar', 'details'];
                // make group layers
                var _iteratorNormalCompletion4 = true;
                var _didIteratorError4 = false;
                var _iteratorError4 = undefined;

                try {
                    for (var _iterator4 = layers[Symbol.iterator](), _step4; !(_iteratorNormalCompletion4 = (_step4 = _iterator4.next()).done); _iteratorNormalCompletion4 = true) {
                        var layer = _step4.value;

                        this.layers[layer] = createSVG('g', {
                            class: layer,
                            append_to: this.$svg
                        });
                    }
                } catch (err) {
                    _didIteratorError4 = true;
                    _iteratorError4 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion4 && _iterator4.return) {
                            _iterator4.return();
                        }
                    } finally {
                        if (_didIteratorError4) {
                            throw _iteratorError4;
                        }
                    }
                }
            }
        }, {
            key: 'make_grid',
            value: function make_grid() {
                this.make_grid_background();
                this.make_grid_rows();
                this.make_grid_header();
                this.make_grid_ticks();
                this.make_grid_highlights();
            }
        }, {
            key: 'make_grid_background',
            value: function make_grid_background() {
                var grid_width = this.dates.length * this.options.column_width;
                var grid_height = this.options.header_height + this.options.padding + (this.options.bar_height + this.options.padding) * this.tasks.length;

                createSVG('rect', {
                    x: 0,
                    y: 0,
                    width: grid_width,
                    height: grid_height,
                    class: 'grid-background',
                    append_to: this.layers.grid
                });

                $.attr(this.$svg, {
                    height: grid_height + this.options.padding + 100,
                    width: '100%'
                });
            }
        }, {
            key: 'make_grid_rows',
            value: function make_grid_rows() {
                var rows_layer = createSVG('g', { append_to: this.layers.grid });
                var lines_layer = createSVG('g', { append_to: this.layers.grid });

                var row_width = this.dates.length * this.options.column_width;
                var row_height = this.options.bar_height + this.options.padding;

                var row_y = this.options.header_height + this.options.padding / 2;

                var _iteratorNormalCompletion5 = true;
                var _didIteratorError5 = false;
                var _iteratorError5 = undefined;

                try {
                    for (var _iterator5 = this.tasks[Symbol.iterator](), _step5; !(_iteratorNormalCompletion5 = (_step5 = _iterator5.next()).done); _iteratorNormalCompletion5 = true) {
                        var task = _step5.value;

                        createSVG('rect', {
                            x: 0,
                            y: row_y,
                            width: row_width,
                            height: row_height,
                            class: 'grid-row',
                            append_to: rows_layer
                        });

                        createSVG('line', {
                            x1: 0,
                            y1: row_y + row_height,
                            x2: row_width,
                            y2: row_y + row_height,
                            class: 'row-line',
                            append_to: lines_layer
                        });

                        row_y += this.options.bar_height + this.options.padding;
                    }
                } catch (err) {
                    _didIteratorError5 = true;
                    _iteratorError5 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion5 && _iterator5.return) {
                            _iterator5.return();
                        }
                    } finally {
                        if (_didIteratorError5) {
                            throw _iteratorError5;
                        }
                    }
                }
            }
        }, {
            key: 'make_grid_header',
            value: function make_grid_header() {
                var header_width = this.dates.length * this.options.column_width;
                var header_height = this.options.header_height + 10;
                createSVG('rect', {
                    x: 0,
                    y: 0,
                    width: header_width,
                    height: header_height,
                    class: 'grid-header',
                    append_to: this.layers.grid
                });
            }
        }, {
            key: 'make_grid_ticks',
            value: function make_grid_ticks() {
                var tick_x = 0;
                var tick_y = this.options.header_height + this.options.padding / 2;
                var tick_height = (this.options.bar_height + this.options.padding) * this.tasks.length;

                var _iteratorNormalCompletion6 = true;
                var _didIteratorError6 = false;
                var _iteratorError6 = undefined;

                try {
                    for (var _iterator6 = this.dates[Symbol.iterator](), _step6; !(_iteratorNormalCompletion6 = (_step6 = _iterator6.next()).done); _iteratorNormalCompletion6 = true) {
                        var date = _step6.value;

                        var tick_class = 'tick';
                        // thick tick for monday
                        if (this.view_is('Day') && date.getDate() === 1) {
                            tick_class += ' thick';
                        }
                        // thick tick for first week
                        if (this.view_is('Week') && date.getDate() >= 1 && date.getDate() < 8) {
                            tick_class += ' thick';
                        }
                        // thick ticks for quarters
                        if (this.view_is('Month') && (date.getMonth() + 1) % 3 === 0) {
                            tick_class += ' thick';
                        }

                        createSVG('path', {
                            d: 'M ' + tick_x + ' ' + tick_y + ' v ' + tick_height,
                            class: tick_class,
                            append_to: this.layers.grid
                        });

                        if (this.view_is('Month')) {
                            tick_x += date_utils.get_days_in_month(date) * this.options.column_width / 30;
                        } else {
                            tick_x += this.options.column_width;
                        }
                    }
                } catch (err) {
                    _didIteratorError6 = true;
                    _iteratorError6 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion6 && _iterator6.return) {
                            _iterator6.return();
                        }
                    } finally {
                        if (_didIteratorError6) {
                            throw _iteratorError6;
                        }
                    }
                }
            }
        }, {
            key: 'make_grid_highlights',
            value: function make_grid_highlights() {
                // highlight today's date
                if (this.view_is('Day')) {
                    var x = date_utils.diff(date_utils.today(), this.gantt_start, 'hour') / this.options.step * this.options.column_width;
                    var y = 0;

                    var width = this.options.column_width;
                    var height = (this.options.bar_height + this.options.padding) * this.tasks.length + this.options.header_height + this.options.padding / 2;

                    createSVG('rect', {
                        x: x,
                        y: y,
                        width: width,
                        height: height,
                        class: 'today-highlight',
                        append_to: this.layers.grid
                    });
                }
            }
        }, {
            key: 'make_dates',
            value: function make_dates() {
                var _iteratorNormalCompletion7 = true;
                var _didIteratorError7 = false;
                var _iteratorError7 = undefined;

                try {
                    for (var _iterator7 = this.get_dates_to_draw()[Symbol.iterator](), _step7; !(_iteratorNormalCompletion7 = (_step7 = _iterator7.next()).done); _iteratorNormalCompletion7 = true) {
                        var date = _step7.value;

                        createSVG('text', {
                            x: date.lower_x,
                            y: date.lower_y,
                            innerHTML: date.lower_text,
                            class: 'lower-text',
                            append_to: this.layers.date
                        });

                        if (date.upper_text) {
                            var $upper_text = createSVG('text', {
                                x: date.upper_x,
                                y: date.upper_y,
                                innerHTML: date.upper_text,
                                class: 'upper-text',
                                append_to: this.layers.date
                            });

                            // remove out-of-bound dates
                            if ($upper_text.getBBox().x2 > this.layers.grid.getBBox().width) {
                                $upper_text.remove();
                            }
                        }
                    }
                } catch (err) {
                    _didIteratorError7 = true;
                    _iteratorError7 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion7 && _iterator7.return) {
                            _iterator7.return();
                        }
                    } finally {
                        if (_didIteratorError7) {
                            throw _iteratorError7;
                        }
                    }
                }
            }
        }, {
            key: 'get_dates_to_draw',
            value: function get_dates_to_draw() {
                var _this = this;

                var last_date = null;
                var dates = this.dates.map(function (date, i) {
                    var d = _this.get_date_info(date, last_date, i);
                    last_date = date;
                    return d;
                });
                return dates;
            }
        }, {
            key: 'get_date_info',
            value: function get_date_info(date, last_date, i) {
                if (!last_date) {
                    last_date = date_utils.add(date, 1, 'year');
                }
                var date_text = {
                    'Quarter Day_lower': date_utils.format(date, 'HH'),
                    'Half Day_lower': date_utils.format(date, 'HH'),
                    Day_lower: date.getDate() !== last_date.getDate() ? date_utils.format(date, 'D') : '',
                    Week_lower: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'D MMM') : date_utils.format(date, 'D'),
                    Month_lower: date_utils.format(date, 'MMMM'),
                    'Quarter Day_upper': date.getDate() !== last_date.getDate() ? date_utils.format(date, 'D MMM') : '',
                    'Half Day_upper': date.getDate() !== last_date.getDate() ? date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'D MMM') : date_utils.format(date, 'D') : '',
                    Day_upper: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'MMMM') : '',
                    Week_upper: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'MMMM') : '',
                    Month_upper: date.getFullYear() !== last_date.getFullYear() ? date_utils.format(date, 'YYYY') : ''
                };

                var base_pos = {
                    x: i * this.options.column_width,
                    lower_y: this.options.header_height,
                    upper_y: this.options.header_height - 25
                };

                var x_pos = {
                    'Quarter Day_lower': this.options.column_width * 4 / 2,
                    'Quarter Day_upper': 0,
                    'Half Day_lower': this.options.column_width * 2 / 2,
                    'Half Day_upper': 0,
                    Day_lower: this.options.column_width / 2,
                    Day_upper: this.options.column_width * 30 / 2,
                    Week_lower: 0,
                    Week_upper: this.options.column_width * 4 / 2,
                    Month_lower: this.options.column_width / 2,
                    Month_upper: this.options.column_width * 12 / 2
                };

                return {
                    upper_text: date_text[this.options.view_mode + '_upper'],
                    lower_text: date_text[this.options.view_mode + '_lower'],
                    upper_x: base_pos.x + x_pos[this.options.view_mode + '_upper'],
                    upper_y: base_pos.upper_y,
                    lower_x: base_pos.x + x_pos[this.options.view_mode + '_lower'],
                    lower_y: base_pos.lower_y
                };
            }
        }, {
            key: 'make_bars',
            value: function make_bars() {
                var _this2 = this;

                this.bars = this.tasks.map(function (task) {
                    var bar = new Bar(_this2, task);
                    _this2.layers.bar.appendChild(bar.group);
                    return bar;
                });
            }
        }, {
            key: 'make_arrows',
            value: function make_arrows() {
                var _this3 = this;

                this.arrows = [];
                var _iteratorNormalCompletion8 = true;
                var _didIteratorError8 = false;
                var _iteratorError8 = undefined;

                try {
                    var _loop = function _loop() {
                        var task = _step8.value;

                        var arrows = [];
                        arrows = task.dependencies.map(function (task_id) {
                            var dependency = _this3.get_task(task_id);
                            if (!dependency) return;
                            var arrow = new Arrow(_this3, _this3.bars[dependency._index], // from_task
                                _this3.bars[task._index] // to_task
                            );
                            _this3.layers.arrow.appendChild(arrow.element);
                            return arrow;
                        }).filter(Boolean); // filter falsy values
                        _this3.arrows = _this3.arrows.concat(arrows);
                    };

                    for (var _iterator8 = this.tasks[Symbol.iterator](), _step8; !(_iteratorNormalCompletion8 = (_step8 = _iterator8.next()).done); _iteratorNormalCompletion8 = true) {
                        _loop();
                    }
                } catch (err) {
                    _didIteratorError8 = true;
                    _iteratorError8 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion8 && _iterator8.return) {
                            _iterator8.return();
                        }
                    } finally {
                        if (_didIteratorError8) {
                            throw _iteratorError8;
                        }
                    }
                }
            }
        }, {
            key: 'map_arrows_on_bars',
            value: function map_arrows_on_bars() {
                var _this4 = this;

                var _iteratorNormalCompletion9 = true;
                var _didIteratorError9 = false;
                var _iteratorError9 = undefined;

                try {
                    var _loop2 = function _loop2() {
                        var bar = _step9.value;

                        bar.arrows = _this4.arrows.filter(function (arrow) {
                            return arrow.from_task.task.id === bar.task.id || arrow.to_task.task.id === bar.task.id;
                        });
                    };

                    for (var _iterator9 = this.bars[Symbol.iterator](), _step9; !(_iteratorNormalCompletion9 = (_step9 = _iterator9.next()).done); _iteratorNormalCompletion9 = true) {
                        _loop2();
                    }
                } catch (err) {
                    _didIteratorError9 = true;
                    _iteratorError9 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion9 && _iterator9.return) {
                            _iterator9.return();
                        }
                    } finally {
                        if (_didIteratorError9) {
                            throw _iteratorError9;
                        }
                    }
                }
            }
        }, {
            key: 'set_width',
            value: function set_width() {
                var cur_width = this.$svg.getBoundingClientRect().width;
                var actual_width = this.$svg.querySelector('.grid .grid-row').getAttribute('width');
                if (cur_width < actual_width) {
                    this.$svg.setAttribute('width', actual_width);
                }
            }
        }, {
            key: 'set_scroll_position',
            value: function set_scroll_position() {
                var parent_element = this.$svg.parentElement;
                if (!parent_element) return;

                var hours_before_first_task = date_utils.diff(this.get_oldest_starting_date(), this.gantt_start, 'hour');

                var scroll_pos = hours_before_first_task / this.options.step * this.options.column_width - this.options.column_width;

                parent_element.scrollLeft = scroll_pos;
            }
        }, {
            key: 'bind_grid_click',
            value: function bind_grid_click() {
                var _this5 = this;

                $.on(this.$svg, this.options.popup_trigger, '.grid-row, .grid-header', function () {
                    _this5.unselect_all();
                    _this5.hide_popup();
                });
            }
        }, {
            key: 'bind_bar_events',
            value: function bind_bar_events() {
                var _this6 = this;

                var is_dragging = false;
                var x_on_start = 0;
                var x_on_scroll_start = 0;
                var y_on_start = 0;
                var is_resizing_left = false;
                var is_resizing_right = false;
                var parent_bar_id = null;
                var bars = []; // instanceof Bar
                this.bar_being_dragged = null;

                function action_in_progress() {
                    return is_dragging || is_resizing_left || is_resizing_right;
                }

                $.on(this.$svg, 'mousedown', '.bar-wrapper, .handle', function (e, element) {
                    var bar_wrapper = $.closest('.bar-wrapper', element);

                    if (element.classList.contains('left')) {
                        is_resizing_left = true;
                    } else if (element.classList.contains('right')) {
                        is_resizing_right = true;
                    } else if (element.classList.contains('bar-wrapper')) {
                        is_dragging = true;
                    }

                    bar_wrapper.classList.add('active');

                    x_on_start = e.offsetX;
                    y_on_start = e.offsetY;

                    parent_bar_id = bar_wrapper.getAttribute('data-id');
                    var ids = [parent_bar_id].concat(toConsumableArray(_this6.get_all_dependent_tasks(parent_bar_id)));
                    bars = ids.map(function (id) {
                        return _this6.get_bar(id);
                    });

                    _this6.bar_being_dragged = parent_bar_id;

                    bars.forEach(function (bar) {
                        var $bar = bar.$bar;
                        $bar.ox = $bar.getX();
                        $bar.oy = $bar.getY();
                        $bar.owidth = $bar.getWidth();
                        $bar.finaldx = 0;
                    });
                });

                $.on(this.$svg, 'mousemove', function (e) {
                    if (!action_in_progress()) return;
                    var dx = e.offsetX - x_on_start;
                    var dy = e.offsetY - y_on_start;

                    bars.forEach(function (bar) {
                        var $bar = bar.$bar;
                        $bar.finaldx = _this6.get_snap_position(dx);

                        if (is_resizing_left) {
                            if (parent_bar_id === bar.task.id) {
                                bar.update_bar_position({
                                    x: $bar.ox + $bar.finaldx,
                                    width: $bar.owidth - $bar.finaldx
                                });
                            } else {
                                bar.update_bar_position({
                                    x: $bar.ox + $bar.finaldx
                                });
                            }
                        } else if (is_resizing_right) {
                            if (parent_bar_id === bar.task.id) {
                                bar.update_bar_position({
                                    width: $bar.owidth + $bar.finaldx
                                });
                            }
                        } else if (is_dragging) {
                            bar.update_bar_position({ x: $bar.ox + $bar.finaldx });
                        }
                    });
                });

                document.addEventListener('mouseup', function (e) {
                    if (is_dragging || is_resizing_left || is_resizing_right) {
                        bars.forEach(function (bar) {
                            return bar.group.classList.remove('active');
                        });
                    }

                    is_dragging = false;
                    is_resizing_left = false;
                    is_resizing_right = false;
                });

                $.on(this.$container, 'scroll', function (e) {

                    var elements = document.querySelectorAll('.bar-wrapper');
                    var localBars = [];
                    var ids = [];
                    var dx = void 0;

                    if (x_on_scroll_start) {
                        dx = e.currentTarget.scrollLeft - x_on_scroll_start;
                    }

                    Array.prototype.forEach.call(elements, function (el, i) {
                        ids.push(el.getAttribute('data-id'));
                    });

                    if (dx) {
                        localBars = ids.map(function (id) {
                            return _this6.get_bar(id);
                        });

                        localBars.forEach(function (bar) {
                            bar.update_label_position_on_horizontal_scroll({ x: dx, sx: e.currentTarget.scrollLeft });
                        });
                    }

                    x_on_scroll_start = e.currentTarget.scrollLeft;
                });

                $.on(this.$svg, 'mouseup', function (e) {
                    _this6.bar_being_dragged = null;
                    bars.forEach(function (bar) {
                        var $bar = bar.$bar;
                        if (!$bar.finaldx) return;
                        bar.date_changed();
                        bar.set_action_completed();
                    });
                });

                this.bind_bar_progress();
            }
        }, {
            key: 'bind_bar_progress',
            value: function bind_bar_progress() {
                var _this7 = this;

                var x_on_start = 0;
                var y_on_start = 0;
                var is_resizing = null;
                var bar = null;
                var $bar_progress = null;
                var $bar = null;

                $.on(this.$svg, 'mousedown', '.handle.progress', function (e, handle) {
                    is_resizing = true;
                    x_on_start = e.offsetX;
                    y_on_start = e.offsetY;

                    var $bar_wrapper = $.closest('.bar-wrapper', handle);
                    var id = $bar_wrapper.getAttribute('data-id');
                    bar = _this7.get_bar(id);

                    $bar_progress = bar.$bar_progress;
                    $bar = bar.$bar;

                    $bar_progress.finaldx = 0;
                    $bar_progress.owidth = $bar_progress.getWidth();
                    $bar_progress.min_dx = -$bar_progress.getWidth();
                    $bar_progress.max_dx = $bar.getWidth() - $bar_progress.getWidth();
                });

                $.on(this.$svg, 'mousemove', function (e) {
                    if (!is_resizing) return;
                    var dx = e.offsetX - x_on_start;
                    var dy = e.offsetY - y_on_start;

                    if (dx > $bar_progress.max_dx) {
                        dx = $bar_progress.max_dx;
                    }
                    if (dx < $bar_progress.min_dx) {
                        dx = $bar_progress.min_dx;
                    }

                    var $handle = bar.$handle_progress;
                    $.attr($bar_progress, 'width', $bar_progress.owidth + dx);
                    $.attr($handle, 'points', bar.get_progress_polygon_points());
                    $bar_progress.finaldx = dx;
                });

                $.on(this.$svg, 'mouseup', function () {
                    is_resizing = false;
                    if (!($bar_progress && $bar_progress.finaldx)) return;
                    bar.progress_changed();
                    bar.set_action_completed();
                });
            }
        }, {
            key: 'get_all_dependent_tasks',
            value: function get_all_dependent_tasks(task_id) {
                var _this8 = this;

                var out = [];
                var to_process = [task_id];
                while (to_process.length) {
                    var deps = to_process.reduce(function (acc, curr) {
                        acc = acc.concat(_this8.dependency_map[curr]);
                        return acc;
                    }, []);

                    out = out.concat(deps);
                    to_process = deps.filter(function (d) {
                        return !to_process.includes(d);
                    });
                }

                return out.filter(Boolean);
            }
        }, {
            key: 'get_snap_position',
            value: function get_snap_position(dx) {
                var odx = dx,
                    rem = void 0,
                    position = void 0;

                if (this.view_is('Week')) {
                    rem = dx % (this.options.column_width / 7);
                    position = odx - rem + (rem < this.options.column_width / 14 ? 0 : this.options.column_width / 7);
                } else if (this.view_is('Month')) {
                    rem = dx % (this.options.column_width / 30);
                    position = odx - rem + (rem < this.options.column_width / 60 ? 0 : this.options.column_width / 30);
                } else {
                    rem = dx % this.options.column_width;
                    position = odx - rem + (rem < this.options.column_width / 2 ? 0 : this.options.column_width);
                }
                return position;
            }
        }, {
            key: 'unselect_all',
            value: function unselect_all() {
                [].concat(toConsumableArray(this.$svg.querySelectorAll('.bar-wrapper'))).forEach(function (el) {
                    el.classList.remove('active');
                });
            }
        }, {
            key: 'view_is',
            value: function view_is(modes) {
                var _this9 = this;

                if (typeof modes === 'string') {
                    return this.options.view_mode === modes;
                }

                if (Array.isArray(modes)) {
                    return modes.some(function (mode) {
                        return _this9.options.view_mode === mode;
                    });
                }

                return false;
            }
        }, {
            key: 'get_task',
            value: function get_task(id) {
                return this.tasks.find(function (task) {
                    return task.id === id;
                });
            }
        }, {
            key: 'get_bar',
            value: function get_bar(id) {
                return this.bars.find(function (bar) {
                    return bar.task.id === id;
                });
            }
        }, {
            key: 'show_popup',
            value: function show_popup(options) {
                if (!this.popup) {
                    this.popup = new Popup(this.popup_wrapper, this.options.custom_popup_html);
                }
                this.popup.show(options);
            }
        }, {
            key: 'hide_popup',
            value: function hide_popup() {
                this.popup && this.popup.hide();
            }
        }, {
            key: 'trigger_event',
            value: function trigger_event(event, args) {
                if (this.options['on_' + event]) {
                    this.options['on_' + event].apply(null, args);
                }
            }

            /**
             * Gets the oldest starting date from the list of tasks
             *
             * @returns Date
             * @memberof Gantt
             */

        }, {
            key: 'get_oldest_starting_date',
            value: function get_oldest_starting_date() {
                return this.tasks.map(function (task) {
                    return task._start;
                }).reduce(function (prev_date, cur_date) {
                    return cur_date <= prev_date ? cur_date : prev_date;
                });
            }

            /**
             * Clear all elements from the parent svg element
             *
             * @memberof Gantt
             */

        }, {
            key: 'clear',
            value: function clear() {
                this.$svg.innerHTML = '';
            }
        }]);
        return Gantt;
    }();

    function generate_id(task) {
        return task.name + '_' + Math.random().toString(36).slice(2, 12);
    }

    return Gantt;

})));