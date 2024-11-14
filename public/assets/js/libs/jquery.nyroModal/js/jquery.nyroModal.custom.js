/*
 * Leantime Modals
 *
 * This is a heavily modified fork of nyroModal v2.0.0
 * Including a variety of filters needed for Leantime specific use cases
 *
 *
 * Included parts:
 * - anims.fade
 * - filters.title
 * - filters.gallery
 * - filters.link
 * - filters.dom
 * - filters.data
 * - filters.image
 * - filters.form
 * - filters.formFile
 * - filters.iframe
 * - filters.iframeForm
 * - filters.embedly
 */

jQuery(function($, undefined) {

    var $w, $d, $b, baseHref, _nmObj, _internal, _animations, _filters;
    $.curCSS = function (element, attrib, val) {
        if(val === true) {
            $(element).css(attrib);
        }else{
            $(element).css(attrib, val);
        }
    };

    $w = $(window),
        $d = $(document),
        $b = $('body'),
        baseHref = $('base').attr('href'),
        // nyroModal Object
        _nmObj = {
            filters: [],	// List of filters used
            callbacks: {},	// Sepcific callbacks
            loadFilter: undefined,	// Name of the filter used for loading

            modal: false,	// Indicates if it's a modal window or not
            closeOnEscape: true,	// Indicates if the modal should close on Escape key
            closeOnClick: false,	// Indicates if a click on the background should close the modal
            useKeyHandler: false,	// Indicates if the modal has to handle key down event

            showCloseButton: true,	// Indicates if the closeButonn should be added
            closeButton: '<a href="#" class="nyroModalClose nyroModalCloseButton nmReposition" title="close"><i class="fa-solid fa-xmark"></i></a>',	// Close button HTML

            stack: false,	// Indicates if links automatically binded inside the modal should stack or not
            nonStackable: 'form',	// Filter to not stack DOM element

            header: undefined,	// header include in every modal
            footer: undefined,	// footer include in every modal

            // Specific confirguation for gallery filter
            galleryLoop: true,	// Indicates if the gallery should loop
            galleryCounts: true,	// Indicates if the gallery counts should be shown
            ltr: true, // Left to Right by default. Put to false for Hebrew or Right to Left language. Used in gallery filter

            // Specific confirguation for image filter
            imageRegex: '[^\.]\.(jpg|jpeg|png|tiff|gif|bmp)\s*$',	// Regex used to detect image link

            selIndicator: 'nyroModalSel', // Value added when a form or Ajax is sent with a filter content

            swfObjectId: undefined, // Object id for swf object
            swf:  {	// Default SWF attributes
                allowFullScreen: 'true',
                allowscriptaccess: 'always',
                wmode: 'transparent'
            },

            store: {},	// Storage object for filters.
            errorMsg: 'An error occured',	// Error message
            elts: {	// HTML elements for the modal
                all: undefined,
                bg: undefined,
                load: undefined,
                cont: undefined,
                hidden: undefined
            },
            sizes: {	// Size information
                mode:"custom",
                initW: undefined,	// Initial width
                initH: undefined,	// Initial height
                w: undefined,		// width
                h: undefined,		// height
                minW: 370,	// minimum Width
                minH: 200,	// minimum height
                wMargin: undefined,	// Horizontal margin
                hMargin: undefined	// Vertical margin
            },
            anim: {	// Animation names to use
                def: undefined,			// Default animation set to use if sspecific are not defined or doesn't exist
                showBg: undefined,		// Set to use for showBg animation
                hideBg: undefined,		// Set to use for hideBg animation
                showLoad: undefined,	// Set to use for showLoad animation
                hideLoad: undefined,	// Set to use for hideLoad animation
                showCont: undefined,	// Set to use for showCont animation
                hideCont: undefined,	// Set to use for hideCont animation
                showTrans: undefined,	// Set to use for showTrans animation
                hideTrans: undefined,	// Set to use for hideTrans animation
                resize: undefined		// Set to use for resize animation
            },

            _open: false,	// Indicates if the modal is open
            _bgReady: false,	// Indicates if the background is ready
            _opened: false,	// Indicates if the modal was opened (useful for stacking)
            _loading: false,	// Indicates if the loading is shown
            _animated: false,	// Indicates if the modal is currently animated
            _transition: false,	//Indicates if the modal is in transition
            _nmOpener: undefined,	// nmObj of the modal that opened the current one in non stacking mode
            _nbContentLoading: 0,	// Counter for contentLoading call
            _scripts: '',	// Scripts tags to be included
            _scriptsShown: '',	//Scripts tags to be included once the modal is swhon

            // save the object in data
            saveObj: function() {
                this.opener.data('nmObj', this);
            },
            // Open the modal
            open: function() {
                if (this._nmOpener)
                    this._nmOpener._close();
                this.getInternal()._pushStack(this.opener);
                this._opened = false;
                this._bgReady = false;
                this._open = true;
                this._initElts();
                this._load();
                this._nbContentLoading = 0;
                this._callAnim('showBg', $.proxy(function() {
                    this._bgReady = true;
                    if (this._nmOpener) {
                        // fake closing of the opener nyroModal
                        this._nmOpener._bgReady = false;
                        this._nmOpener._loading = false;
                        this._nmOpener._animated = false;
                        this._nmOpener._opened = false;
                        this._nmOpener._open = false;
                        this._nmOpener.elts.cont = this._nmOpener.elts.hidden = this._nmOpener.elts.load = this._nmOpener.elts.bg = this._nmOpener.elts.all = undefined;
                        this._nmOpener.saveObj();
                        this._nmOpener = undefined;
                    }
                    this._contentLoading();
                }, this));
            },

            // Resize the modal according to sizes.initW and sizes.initH
            // Will call size function
            // @param recalc boolean: Indicate if the size should be recalaculated (useful when content has changed)
            resize: function(recalc) {

                if (recalc) {
                    this.elts.hidden.append(this.elts.cont.children().first().clone());

                    this.sizes.initW = this.sizes.w = this.elts.hidden.width();
                    this.sizes.initH = this.sizes.h = this.elts.hidden.height();

                    this.elts.hidden.empty();
                } else {
                    this.sizes.w = this.sizes.initW;
                    this.sizes.h = this.sizes.initH;
                }


                this.elts.hidden.empty();

                this._unreposition();
                this.size();
                this._callAnim('resize', $.proxy(function() {
                    this._reposition();
                }, this));
            },

            // Update sizes element to not go outsize the viewport.
            // Will call 'size' callback filter
            size: function() {

                var maxHeight = this.getInternal().fullSize.viewH - this.sizes.hMargin,
                    maxWidth = this.getInternal().fullSize.viewW - this.sizes.wMargin;


                if(this.sizes.mode == "rightHalf") {

                    this.sizes.h = maxHeight;
                    this.sizes.w = (maxWidth/3)*2;

                }else{

                    if (this.sizes.minW && this.sizes.minW > this.sizes.w) {
                        this.sizes.w = this.sizes.minW;
                    }

                    if (this.sizes.minH && this.sizes.minH > this.sizes.h) {
                        this.sizes.h = this.sizes.minH;

                    }

                    if (this.sizes.h > maxHeight || this.sizes.w > maxWidth) {
                        // We're gonna resize the modal as it will goes outside the view port
                        this.sizes.h = maxHeight;
                        this.sizes.w = Math.min(this.sizes.w, (maxWidth-30));
                    }


                }

                this._callFilters('size');

            },

            // Get the nmObject for a new nyroModal
            getForNewLinks: function(elt) {
                var ret;
                if (this.stack && (!elt || this.isStackable(elt))) {
                    ret = $.extend(true, {}, this);
                    ret._nmOpener = undefined;
                    ret.elts.all = undefined;
                } else {
                    ret = $.extend({}, this);
                    ret._nmOpener = this;
                }
                ret.filters = [];
                ret.opener = undefined;
                ret._open = false;
                return ret;
            },

            // Indicate if an element can be stackable or not, regarding the nonStackable setting
            isStackable: function(elt) {
                return !elt.is(this.nonStackable);
            },

            // key handle function.
            // Will call 'keyHandle' callback filter
            keyHandle: function(e) {
                this.keyEvent = e;
                this._callFilters('keyHandle');
                this.keyEvent = undefined;
                delete(this.keyEvent);
            },

            // Get the internal object
            getInternal: function() {
                return _internal;
            },

            // Internal function for closing a nyroModal
            // Will call 'close' callback filter
            _close: function() {
                this.getInternal()._removeStack(this.opener);
                this._opened = false;
                this._open = false;
                this._callFilters('close');
            },
            // Public function for closing a nyroModal
            close: function() {
                this._close();
                this._callFilters('beforeClose');
                var self = this;
                this._unreposition();
                $('body').css("overflow", "auto");
                self._callAnim('hideCont', function() {
                    self._callAnim('hideLoad', function() {
                        self._callAnim('hideBg', function() {
                            self._callFilters('afterClose');
                            self.elts.cont.remove();
                            self.elts.hidden.remove();
                            self.elts.load.remove();
                            self.elts.bg.remove();
                            self.elts.all.remove();
                            self.elts.cont = self.elts.hidden = self.elts.load = self.elts.bg = self.elts.all = undefined;
                        });
                    });
                });
            },

            // Init HTML elements
            _initElts: function() {
                if (!this.stack && this.getInternal().stack.length > 1)
                    this.elts = this.getInternal().stack[this.getInternal().stack.length-2]['nmObj'].elts;
                if (!this.elts.all || this.elts.all.closest('body').length == 0)
                    this.elts.all = this.elts.bg = this.elts.cont = this.elts.hidden = this.elts.load = undefined;
                if (!this.elts.all)
                    this.elts.all = $('<div />').appendTo(this.getInternal()._container);
                if (!this.elts.bg)
                    this.elts.bg = $('<div />').hide().appendTo(this.elts.all);
                if (!this.elts.cont)
                    this.elts.cont = $('<div />').hide().appendTo(this.elts.bg);
                if (!this.elts.hidden)
                    this.elts.hidden = $('<div />').hide().appendTo(this.elts.bg);
                this.elts.hidden.empty();
                if (!this.elts.load)
                    this.elts.load = $('<div />').hide().appendTo(this.elts.all);
                this._callFilters('initElts');
            },

            // Trigger the error
            // Will call 'error' callback filter
            _error: function() {
                this._callFilters('error');
            },

            // Set the HTML content to show.
            // - html: HTML content
            // - selector: selector to filter the content
            // Will init the size and call the 'size' function.
            // Will call 'filledContent' callback filter
            _setCont: function(html, selector) {
                if (selector && 1==2) {
                    var tmp = [],
                        i = 0;
                    // Looking for script to store them
                    html = html
                        .replace(/\r\n/gi, 'nyroModalLN')
                        .replace(/<script(.|\s)*?\/script>/gi, function(x) {
                            tmp[i] = x;
                            return '<pre class=nyroModalScript rel="'+(i++)+'"></pre>';
                        });
                    var cur = $('<div>'+html+'</div>').find(selector);
                    if (cur.length) {
                        html = cur.html()
                            .replace(/<pre class="?nyroModalScript"? rel="?(.?)"?><\/pre>/gi, function(x, y, z) { return tmp[y]; })
                            .replace(/nyroModalLN/gi, "\r\n");
                    } else {
                        // selector not found
                        this._error();
                        return;
                    }
                }

                this.elts.hidden
                    .append(this._filterScripts(html))
                    .prepend(this.header)
                    .append(this.footer)
                    .wrapInner('<div class="nyroModal'+ucfirst(this.loadFilter)+'" />');


                // Store the size of the element
                //75 is the margin around the content
                this.sizes.initW = this.sizes.w = this.elts.hidden.width()+75;
                this.sizes.initH = this.sizes.h = this.elts.hidden.height();
                var outer = this.getInternal()._getOuter(this.elts.cont);
                this.sizes.hMargin = outer.h.total;
                this.sizes.wMargin = outer.w.total;

                this.size();

                this.loading = false;
                this._callFilters('filledContent');
                this._contentLoading();
            },

            // Filter an html content to remove the script[src] and store them appropriately if needed
            // - data: Data to filter
            _filterScripts: function(data) {
                if (typeof data != 'string')
                    return data;

                this._scripts = [];
                this._scriptsShown = [];
                var start = 0,
                    stStart = '<script',
                    stEnd = '</script>',
                    endLn = stEnd.length,
                    pos,
                    pos2,
                    tmp;
                while ((pos = data.indexOf(stStart, start)) > -1) {
                    pos2 = data.indexOf(stEnd)+endLn;
                    tmp = $(data.substring(pos, pos2));
                    if (!tmp.attr('src') || tmp.attr('rel') == 'forceLoad') {
                        if (tmp.attr('rev') == 'shown')
                            this._scriptsShown.push(tmp.get(0));
                        else
                            this._scripts.push(tmp.get(0));
                    }
                    data = data.substring(0, pos)+data.substr(pos2);
                    start = pos;
                }
                return data;
            },

            // Check if the nmObject has a specific filter
            // - filter: Filter name
            _hasFilter: function(filter) {
                var ret = false;
                $.each(this.filters, function(i, f) {
                    ret = ret || f == filter;
                });
                return ret;
            },

            // Remove a specific filter
            // - filter: Filter name
            _delFilter: function(filter) {
                this.filters = $.map(this.filters, function(v) {
                    if (v != filter)
                        return v;
                });
            },

            // Call a function against all active filters
            // - fct: Function name
            // return an array of all return of callbacks; keys are filters name
            _callFilters: function(fct) {
                this.getInternal()._debug(fct);
                var ret = [],
                    self = this;
                $.each(this.filters, function(i, f) {
                    ret[f] = self._callFilter(f, fct);
                });
                if (this.callbacks[fct] && $.isFunction(this.callbacks[fct]))
                    this.callbacks[fct](this);
                return ret;
            },

            // Call a filter function for a specific filter
            // - f: Filter name
            // - fct: Function name
            // return the return of the callback
            _callFilter: function(f, fct) {
                if (_filters[f] && _filters[f][fct] && $.isFunction(_filters[f][fct]))
                    return _filters[f][fct](this);
                return undefined;
            },

            // Call animation callback.
            // Will also call beforeNNN and afterNNN filter callbacks
            // - fct: Animation function name
            // - clb: Callback once the animation is done
            _callAnim: function(fct, clb) {
                this.getInternal()._debug(fct);
                this._callFilters('before'+ucfirst(fct));
                if (!this._animated) {
                    this._animated = true;
                    if (!$.isFunction(clb)) clb = $.noop;
                    var set = this.anim[fct] || this.anim.def || 'basic';
                    if (!_animations[set] || !_animations[set][fct] || !$.isFunction(_animations[set][fct]))
                        set = 'basic';
                    _animations[set][fct](this, $.proxy(function() {
                        this._animated = false;
                        this._callFilters('after'+ucfirst(fct));
                        clb();
                    }, this));
                }
            },

            // Load the content
            // Will call the 'load' function of the filter specified in the loadFilter parameter
            _load: function() {
                this.getInternal()._debug('_load');
                if (!this.loading && this.loadFilter) {
                    this.loading = true;
                    this._callFilter(this.loadFilter, 'load');
                }
            },

            // Show the content or the loading according to the current state of the modal
            _contentLoading: function() {
                if (!this._animated && this._bgReady) {
                    if (!this._transition && this.elts.cont.html().length > 0)
                        this._transition = true;
                    this._nbContentLoading++;
                    if (!this.loading) {
                        if (!this._opened) {
                            this._opened = true;
                            if (this._transition) {
                                var fct = $.proxy(function() {
                                    this._writeContent();
                                    this._callFilters('beforeShowCont');
                                    this._callAnim('hideTrans', $.proxy(function() {
                                        this._transition = false;

                                        this.elts.cont.append(this._scriptsShown);
                                        this._reposition();
                                        this._callFilters('afterShowCont');
                                    }, this));
                                }, this);
                                if (this._nbContentLoading == 1) {
                                    this._unreposition();
                                    this._callAnim('showTrans', fct);
                                } else {
                                    fct();
                                }
                            } else {
                                this._callAnim('hideLoad', $.proxy(function() {
                                    this._writeContent();
                                    this._callAnim('showCont', $.proxy(function() {
                                        this.elts.cont.append(this._scriptsShown);
                                        this._reposition();
                                    }, this));
                                }, this));
                            }
                        }
                    } else if (this._nbContentLoading == 1) {
                        var outer = this.getInternal()._getOuter(this.elts.load);

                        this.elts.load
                            .css({
                                position: 'fixed',
                                top: (this.getInternal().fullSize.viewH - this.elts.load.height() - outer.h.margin)/2,
                                left: (this.getInternal().fullSize.viewW - this.elts.load.width() - outer.w.margin)/2
                            });
                        if (this._transition) {
                            this._unreposition();
                            this._callAnim('showTrans', $.proxy(function() {
                                this._contentLoading();
                            }, this));
                        } else {
                            this._callAnim('showLoad', $.proxy(function() {
                                this._contentLoading();
                            }, this));
                        }
                    }
                }
            },

            // Write the content in the modal.
            // Content comes from the hidden div, scripts and eventually close button.
            _writeContent: function() {
                if(this.sizes.mode == "rightHalf") {
                    var topValue = 0;
                    var leftValue = "auto";
                    var rightValue = 0;
                }else{
                    var topValue = ((this.getInternal().fullSize.viewH - this.sizes.h - this.sizes.hMargin)/2) - 50;
                    if(topValue <=10) {
                        topValue = 50;
                    }
                    var leftValue = (this.getInternal().fullSize.viewW - this.sizes.w - this.sizes.wMargin)/2;
                    var rightValue = "auto";
                }

                this.elts.cont
                    .empty()
                    .append(this.elts.hidden.contents())
                    .append(this._scripts)
                    .append(this.showCloseButton ? this.closeButton : '')
                    .css({
                        position: 'absolute',
                        width: this.sizes.w,
                        height: 'auto',
                        minHeight: (this.sizes.h-topValue),
                        top: topValue,
                        left: leftValue,
                        right: rightValue
                    });
            },

            // Reposition elements with a class nmReposition
            _reposition: function() {
                var elts = this.elts.cont.find('.nmReposition');
                if (elts.length) {
                    var space = this.getInternal()._getSpaceReposition();
                    var nmThis = this;

                    elts.each(function() {

                        let topValue = 0;
                        let leftValue = 0;
                        let rightValue = 0;
                        let me = $(this);
                        let	offset = me.offset();

                        if(nmThis.sizes.mode == "rightHalf") {
                            leftValue = "auto";
                            rightValue = 0;

                        }else{

                            topValue = offset.top - space.top;
                            if(topValue <=50){
                                topValue=50;
                            }

                            leftValue = offset.left - space.left;
                            rightValue = "auto";
                        }

                        me.css({
                            position: 'absolute',
                            top: topValue,
                            left: leftValue,
                            right: rightValue,
                            visibility: 'visible'
                        });
                    });
                    this.elts.cont.after(elts);
                }
                //this.elts.cont.css('overflow', 'auto');
                this._callFilters('afterReposition');
            },

            // Unreposition elements with a class nmReposition
            // Exaclty the reverse of the _reposition function
            _unreposition: function() {
                this.elts.cont.css('overflow', '');
                var elts = this.elts.all.find('.nmReposition');
                if (elts.length)
                    this.elts.cont.append(elts.removeAttr('style'));
                this._callFilters('afterUnreposition');
            }
        },
        _internal = {
            firstInit: true,
            debug: false,
            stack: [],
            fullSize: {
                w: 0,
                h: 0,
                wW: 0,
                wH: 0,
                viewW: 0,
                viewH: 0
            },
            nyroModal: function(opts, fullObj) {
                if (_internal.firstInit) {
                    _internal._container = $('<div />').appendTo($b);
                    $w.smartresize($.proxy(_internal._resize, _internal));
                    $d.bind('keydown.nyroModal', $.proxy(_internal._keyHandler, _internal));
                    _internal._calculateFullSize();
                    _internal.firstInit = false;
                }
                return this.nmInit(opts, fullObj).each(function() {
                    _internal._init($(this).data('nmObj'));
                });
            },
            nmInit: function(opts, fullObj) {
                return this.each(function() {
                    var me = $(this);
                    if (fullObj)
                        me.data('nmObj', $.extend(true, {opener: me}, opts));
                    else
                        me.data('nmObj',
                            me.data('nmObj')
                                ? $.extend(true, me.data('nmObj'), opts)
                                : $.extend(true, {opener: me}, _nmObj, opts));
                });
            },
            nmCall: function() {
                return this.trigger('nyroModal');
            },

            nmManual: function(url, opts) {
                $('<a href="'+url+'"></a>').nyroModal(opts).trigger('nyroModal');
            },
            nmData: function(data, opts) {
                this.nmManual('#', $.extend({data: data}, opts));
            },
            nmObj: function(opts) {
                $.extend(true, _nmObj, opts);
            },
            nmInternal: function(opts) {
                $.extend(true, _internal, opts);
            },
            nmAnims: function(opts) {
                $.extend(true, _animations, opts);
            },
            nmFilters: function(opts) {
                $.extend(true, _filters, opts);
            },
            nmTop: function() {
                if (_internal.stack.length)
                    return _internal.stack[_internal.stack.length-1]['nmObj'];
                return undefined;
            },

            _debug: function(msg) {
                if (this.debug && window.console && window.console.log)
                    window.console.log(msg);
            },

            _container: undefined,

            _init: function(nm) {
                nm.filters = [];
                $.each(_filters, function(f, obj) {
                    if (obj.is && $.isFunction(obj.is) && obj.is(nm)) {
                        nm.filters.push(f);
                    }
                });
                nm._callFilters('initFilters');
                nm._callFilters('init');
                nm.opener
                    .unbind('nyroModal.nyroModal nmClose.nyroModal nmResize.nyroModal')
                    .bind({
                        'nyroModal.nyroModal': 	function(e) { nm.open(); return false;},
                        'nmClose.nyroModal': 	function() { nm.close(); return false;},
                        'nmResize.nyroModal': 	function() { nm.resize(); return false;}
                    });
            },

            _scrollWidth: (function() {
                var scrollbarWidth;
                var $div = $('<div />')
                    .css({ width: 100, height: 100, overflow: 'auto', position: 'absolute', top: -1000, left: -1000 })
                    .prependTo($b).append('<div />').find('div')
                    .css({ width: '100%', height: 200 });
                scrollbarWidth = 100 - $div.width();
                $div.parent().remove();
                return scrollbarWidth;
            })(),

            _selNyroModal: function(obj) {
                return $(obj).data('nmObj') ? true : false;
            },

            _selNyroModalOpen: function(obj) {
                var me = $(obj);
                return me.data('nmObj') ? me.data('nmObj')._open : false;
            },

            _keyHandler: function(e) {
                var nmTop = $.nmTop();
                if (nmTop && nmTop.useKeyHandler) {
                    return nmTop.keyHandle(e);
                }
            },
            _pushStack: function(obj) {
                this.stack = $.map(this.stack, function(elA) {
                    if (elA['nmOpener'] != obj.get(0))
                        return elA;
                });
                this.stack.push({
                    nmOpener: obj.get(0),
                    nmObj: $(obj).data('nmObj')
                });
            },
            _removeStack: function(obj) {
                this.stack = $.map(this.stack, function(elA) {
                    if (elA['nmOpener'] != obj.get(0))
                        return elA;
                });
            },
            _resize: function() {
                // noinspection CssInvalidPseudoSelector
                var opens = $(':nmOpen').each(function() {
                    $(this).data('nmObj')._unreposition();
                });
                this._calculateFullSize();
                opens.trigger('nmResize');
            },
            _calculateFullSize: function() {


                this.fullSize = {
                    w: $d.width(),
                    h: $d.height(),
                    wW: $w.width(),
                    wH: $w.height()
                };

                this.fullSize.viewW = Math.min(this.fullSize.w, this.fullSize.wW);
                this.fullSize.viewH = Math.min(this.fullSize.h, this.fullSize.wH);
            },
            _getCurCSS: function(elm, name) {
                var ret = parseInt($.curCSS(elm, name, true));

                return isNaN(ret) ? 0 : ret;
            },
            _getOuter: function(elm) {
                elm = elm.get(0);
                var ret = {
                    h: {
                        margin: this._getCurCSS(elm, 'margin-top') + this._getCurCSS(elm, 'margin-bottom'),
                        border: this._getCurCSS(elm, 'border-top-width') + this._getCurCSS(elm, 'border-bottom-width'),
                        padding: this._getCurCSS(elm, 'padding-top') + this._getCurCSS(elm, 'padding-bottom')
                    },
                    w: {
                        margin: this._getCurCSS(elm, 'margin-left') + this._getCurCSS(elm, 'margin-right'),
                        border: this._getCurCSS(elm, 'border-left-width') + this._getCurCSS(elm, 'border-right-width'),
                        padding: this._getCurCSS(elm, 'padding-left') + this._getCurCSS(elm, 'padding-right')
                    }
                };

                ret.h.outer = ret.h.margin + ret.h.border;
                ret.w.outer = ret.w.margin + ret.w.border;

                ret.h.inner = ret.h.padding + ret.h.border;
                ret.w.inner = ret.w.padding + ret.w.border;

                ret.h.total = ret.h.outer + ret.h.padding;
                ret.w.total = ret.w.outer + ret.w.padding;

                return ret;
            },
            _getSpaceReposition: function() {
                var	outer = this._getOuter($b);

                var topValue = $w.scrollTop() - (outer.h.border / 2);
                if(topValue <=50){
                    topValue = 50;
                }
                return {
                    top: $w.scrollTop() - (outer.h.border / 2),
                    left: $w.scrollLeft() - (outer.h.border / 2)
                };
            },

            _getHash: function(url) {
                if (typeof url == 'string') {
                    var hashPos = url.indexOf('#');
                    if (hashPos > -1)
                        return url.substring(hashPos);
                }
                return '';
            },
            _extractUrl: function(url) {

                var ret = {
                    url: undefined,
                    sel: undefined
                };

                if (url) {
                    var hash = this._getHash(url),
                        hashLoc = this._getHash(window.location.href),
                        curLoc = window.location.href.substring(0, window.location.href.length - hashLoc.length),
                        req = url.substring(0, url.length - hash.length);
                    ret.sel = hash;
                    if (req != curLoc && req != baseHref) {
                        ret.url = url;
                    }

                }
                return ret;
            }
        },
        _animations = {
            basic: {
                showBg: function(nm, clb) {
                    nm.elts.bg.show();
                    clb();
                },
                hideBg: function(nm, clb) {
                    nm.elts.bg.hide();
                    clb();
                },
                showLoad: function(nm, clb) {
                    nm.elts.load.show();
                    clb();
                },
                hideLoad: function(nm, clb) {
                    nm.elts.load.hide();
                    clb();
                },
                showCont: function(nm, clb) {
                    if(nm.elts.cont != null) {
                        nm.elts.cont.show();
                        clb();
                    }
                },
                hideCont: function(nm, clb) {
                    if(nm.elts.cont != null) {
                        nm.elts.cont.hide();
                        clb();
                    }
                },
                showTrans: function(nm, clb) {
                    nm.elts.cont.hide();
                    nm.elts.load.show();
                    clb();
                },
                hideTrans: function(nm, clb) {

                    if(nm.elts.cont != null && nm.elts.load != null) {
                        nm.elts.cont.show();
                        nm.elts.load.hide();
                        clb();
                    }

                },
                resize: function(nm, clb) {

                    var topValue = (nm.getInternal().fullSize.viewH - nm.sizes.h - nm.sizes.hMargin)/2;
                    if(topValue <=50) {
                        topValue = 50;
                    }

                    nm.elts.cont.css({
                        width: nm.sizes.w,
                        height: "auto",
                        minHeight: (nm.sizes.h-topValue),
                        top: topValue,
                        left: (nm.getInternal().fullSize.viewW - nm.sizes.w - nm.sizes.wMargin)/2
                    });
                    clb();
                }
            }
        },
        _filters = {
            basic: {
                is: function(nm) {
                    return true;
                },
                init: function(nm) {
                    if (nm.opener.attr('rev') == 'modal')
                        nm.modal = true;
                    if (nm.modal)
                        nm.closeOnEscape = nm.closeOnClick = nm.showCloseButton = false;
                    if (nm.closeOnEscape)
                        nm.useKeyHandler = true;
                },
                initElts: function(nm) {
                    nm.elts.bg.addClass('nyroModalBg');
                    if (nm.closeOnClick)
                        nm.elts.bg.unbind('click.nyroModal').bind('click.nyroModal', function(e) {

                            //Only close if user clicked on background and not on content (child element)
                            if(e.target == this) {
                                nm.close();
                            }
                        });
                    nm.elts.cont.addClass('nyroModalCont');
                    nm.elts.hidden.addClass('nyroModalCont nyroModalHidden');
                    nm.elts.load.addClass('nyroModalCont nyroModalLoad');
                },
                error: function(nm) {
                    nm.elts.hidden.addClass('nyroModalError');
                    nm.elts.cont.addClass('nyroModalError');
                    nm._setCont(nm.errorMsg);
                },
                beforeShowCont: function(nm) {
                    $('body').css('overflow', "hidden");

                    if(nm.elts.cont != null) {
                        nm.elts.cont
                            .find('.nyroModal').each(function () {
                            var cur = $(this);
                            cur.nyroModal(nm.getForNewLinks(cur), true);
                        }).end()
                            .find('.nyroModalClose').bind('click.nyroModal', function (e) {
                            e.preventDefault();
                            nm.close();
                        });
                    }
                },
                keyHandle: function(nm) {
                    // used for escape key
                    if (nm.keyEvent.keyCode == 27 && nm.closeOnEscape) {
                        nm.keyEvent.preventDefault();
                        nm.close();
                    }
                }
            },

            custom: {
                is: function(nm) {
                    return true;
                }
            }
        };

    // Add jQuery call fucntions
    $.fn.extend({
        nm: _internal.nyroModal,
        nyroModal: _internal.nyroModal,
        nmInit: _internal.nmInit,
        nmCall: _internal.nmCall
    });

    // Add global jQuery functions
    $.extend({
        nmManual: _internal.nmManual,
        nmData: _internal.nmData,
        nmObj: _internal.nmObj,
        nmInternal: _internal.nmInternal,
        nmAnims: _internal.nmAnims,
        nmFilters: _internal.nmFilters,
        nmTop: _internal.nmTop
    });

    // Add jQuery selectors
    $.expr[':'].nyroModal = $.expr[':'].nm = _internal._selNyroModal;
    $.expr[':'].nmOpen = _internal._selNyroModalOpen;
});

// Smartresize plugin
(function($,sr){

    // debouncing function from John Hann
    // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
    var debounce = function (func, threshold, execAsap) {
        var timeout;

        return function debounced () {
            var obj = this, args = arguments;
            function delayed () {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            }
            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    };
    // smartresize
    jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');
// ucFirst
function ucfirst(str) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: ucfirst('kevin van zonneveld');
    // *     returns 1: 'Kevin van zonneveld'
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substring(1);
}
/*
 * nyroModal v2.0.0
 *
 * Fade animations
 *
 * Depends:
 *
 */

/*
jQuery(function($, undefined) {
    $.nmAnims({
        fade: {
            showBg: function(nm, clb) {
                nm.elts.bg.show(clb);
            },
            hideBg: function(nm, clb) {
                nm.elts.bg.hide(clb);
            },
            showLoad: function(nm, clb) {
                nm.elts.load.show(clb);
            },
            hideLoad: function(nm, clb) {
                nm.elts.load.hide(10, clb);
            },
            showCont: function(nm, clb) {
                nm.elts.cont.fadeIn(10, clb);
            },
            hideCont: function(nm, clb) {
                nm.elts.cont.css('overflow', 'hidden').fadeOut(10, clb);
            },
            showTrans: function(nm, clb) {
                nm.elts.load
                    .css({
                        position: nm.elts.cont.css('position'),
                        top: nm.elts.cont.css('top'),
                        left: nm.elts.cont.css('left'),
                        width: nm.elts.cont.css('width'),
                        height: nm.elts.cont.css('height'),
                        marginTop: nm.elts.cont.css('marginTop'),
                        marginLeft: nm.elts.cont.css('marginLeft')
                    })
                    .fadeIn(function() {
                        nm.elts.cont.hide();
                        clb();
                    });
            },
            hideTrans: function(nm, clb) {
                nm.elts.cont.css('visibility', 'hidden').show();
                nm.elts.load
                    .css('position', nm.elts.cont.css('position'))
                    .animate({
                        top: nm.elts.cont.css('top'),
                        left: nm.elts.cont.css('left'),
                        width: nm.elts.cont.css('width'),
                        height: nm.elts.cont.css('height'),
                        marginTop: nm.elts.cont.css('marginTop'),
                        marginLeft: nm.elts.cont.css('marginLeft')
                    }, function() {
                        nm.elts.cont.css('visibility', '');
                        nm.elts.load.fadeOut(clb);
                    });
            },
            resize: function(nm, clb) {

                var topValue = (nm.getInternal().fullSize.viewH - nm.sizes.h - nm.sizes.hMargin)/2;
                if(topValue <=50){
                    topValue = 50;
                }

                nm.elts.cont.animate({
                    width: nm.sizes.w,
                    height: "auto",
                    top: topValue,
                    left: (nm.getInternal().fullSize.viewW - nm.sizes.w - nm.sizes.wMargin)/2
                }, clb);
            }
        }
    });
    // Define fade aniamtions as default
    $.nmObj({anim: {def: 'fade'}});
});
*/

/*
 * nyroModal v2.0.0
 *
 * Title filter
 *
 * Depends:
 *
 * Before:
 */
jQuery(function($, undefined) {
    $.nmFilters({
        title: {
            is: function(nm) {
                return nm.opener.is('[title]');
            },
            beforeShowCont: function(nm) {
                var offset = nm.elts.cont.offset();
                nm.store.title = $('<h1 />', {
                    text: nm.opener.attr('title')
                }).addClass('nyroModalTitle nmReposition');
                nm.elts.cont.prepend(nm.store.title);
            },
            close: function(nm) {
                if (nm.store.title) {
                    nm.store.title.remove();
                    nm.store.title = undefined;
                    delete(nm.store.title);
                }
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Gallery filter
 *
 * Depends:
 * - filters.title
 *
 * Before: filters.title
 */
jQuery(function($, undefined) {
    $.nmFilters({
        gallery: {
            is: function(nm) {
                var ret = nm.opener.is('[rel]:not([rel=external], [rel=nofollow])');
                if (ret) {
                    var rel = nm.opener.attr('rel'),
                        indexSpace = rel.indexOf(' '),
                        gal = indexSpace > 0 ? rel.substr(0, indexSpace) : rel,
                        links = $('[href][rel="'+gal+'"], [href][rel^="'+gal+' "]');
                    if (links.length < 2)
                        ret = false;
                    if (ret && nm.galleryCounts && !nm._hasFilter('title'))
                        nm.filters.push('title');
                }
                return ret;
            },
            init: function(nm) {
                nm.useKeyHandler = true;
            },
            keyHandle: function(nm) {
                // used for arrows key
                if (!nm._animated && nm._opened) {
                    if (nm.keyEvent.keyCode == 39 || nm.keyEvent.keyCode == 40) {
                        nm.keyEvent.preventDefault();
                        nm._callFilters('galleryNext');
                    } else if (nm.keyEvent.keyCode == 37 || nm.keyEvent.keyCode == 38) {
                        nm.keyEvent.preventDefault();
                        nm._callFilters('galleryPrev');
                    }
                }
            },
            initElts: function(nm) {
                var rel = nm.opener.attr('rel'),
                    indexSpace = rel.indexOf(' ');
                nm.store.gallery = indexSpace > 0 ? rel.substr(0, indexSpace) : rel;
                nm.store.galleryLinks = $('[href][rel="'+nm.store.gallery+'"], [href][rel^="'+nm.store.gallery+' "]');
                nm.store.galleryIndex = nm.store.galleryLinks.index(nm.opener);
            },
            beforeShowCont: function(nm) {
                if (nm.galleryCounts && nm.store.title && nm.store.galleryLinks && nm.store.galleryLinks.length > 1) {
                    var curTitle = nm.store.title.html();
                    nm.store.title.html((curTitle.length ? curTitle+' - ' : '')+(nm.store.galleryIndex+1)+'/'+nm.store.galleryLinks.length);
                }
            },
            filledContent: function(nm) {
                var link = this._getGalleryLink(nm, -1),
                    append = nm.elts.hidden.find(' > div');
                if (link) {
                    $('<a />', {
                        text: 'previous',
                        href: '#'
                    })
                        .addClass('nyroModalPrev')
                        .bind('click', function(e) {
                            e.preventDefault();
                            nm._callFilters('galleryPrev');
                        })
                        .appendTo(append);
                }
                link = this._getGalleryLink(nm, 1);
                if (link) {
                    $('<a />', {
                        text: 'next',
                        href: '#'
                    })
                        .addClass('nyroModalNext')
                        .bind('click', function(e) {
                            e.preventDefault();
                            nm._callFilters('galleryNext');
                        })
                        .appendTo(append);
                }
            },
            close: function(nm) {
                nm.store.gallery = undefined;
                nm.store.galleryLinks = undefined;
                nm.store.galleryIndex = undefined;
                delete(nm.store.gallery);
                delete(nm.store.galleryLinks);
                delete(nm.store.galleryIndex);
                if (nm.elts.cont)
                    nm.elts.cont.find('.nyroModalNext, .nyroModalPrev').remove();
            },
            galleryNext: function(nm) {
                this._getGalleryLink(nm, 1).nyroModal(nm.getForNewLinks(), true).click();
            },
            galleryPrev: function(nm) {
                this._getGalleryLink(nm, -1).nyroModal(nm.getForNewLinks(), true).click();
            },
            _getGalleryLink: function(nm, dir) {
                if (nm.store.gallery) {
                    if (!nm.ltr)
                        dir *= -1;
                    var index = nm.store.galleryIndex + dir;
                    if (nm.store.galleryLinks && index >= 0 && index < nm.store.galleryLinks.length)
                        return nm.store.galleryLinks.eq(index);
                    else if (nm.galleryLoop && nm.store.galleryLinks)
                        return nm.store.galleryLinks.eq(index<0 ? nm.store.galleryLinks.length-1 : 0);
                }
                return undefined;
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Link filter
 *
 * Depends:
 *
 * Before: filters.gallery
 */
jQuery(function($, undefined) {
    $.nmFilters({
        link: {
            is: function(nm) {
                var ret = nm.opener.is('[href]');
                if (ret)
                    nm.store.link = nm.getInternal()._extractUrl(nm.opener.attr('href'));
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'link';
                nm.opener.unbind('click.nyroModal').bind('click.nyroModal', function(e) {
                    e.preventDefault();
                    nm.opener.trigger('nyroModal');
                });
            },
            load: function(nm) {
                $.ajax({
                    url: nm.store.link.url,
                    data: nm.store.link.sel ? [{name: nm.selIndicator, value: nm.store.link.sel.substring(1)}] : undefined,
                    success: function(data) {
                        nm._setCont(data, nm.store.link.sel);
                    },
                    error: function(content) {
                        console.log(content);
                        nm._setCont(content.responseText);
                    }
                });
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Dom filter
 *
 * Depends:
 * - filters.link
 *
 * Before: filters.link

jQuery(function($, undefined) {
	$.nmFilters({
		dom: {
			is: function(nm) {
				return nm._hasFilter('link') && !nm.store.link.url && nm.store.link.sel;
			},
			init: function(nm) {
				nm.loadFilter = 'dom';
			},
			load: function(nm) {
				nm.store.domEl = $(nm.store.link.sel);
				if (nm.store.domEl.length)
					nm._setCont(nm.store.domEl.contents());
				else
					nm._error();
			},
			close: function(nm) {
				if (nm.store.domEl && nm.elts.cont)
					nm.store.domEl.append(nm.elts.cont.find('.nyroModalDom').contents());
			}
		}
	});
});  */
/*
 * nyroModal v2.0.0
 *
 * Data filter
 *
 * Depends:
 * - filters.link
 *
 * Before: filters.dom
 */
jQuery(function($, undefined) {
    $.nmFilters({
        data: {
            is: function(nm) {

                var ret = nm.data ? true : false;
                if (ret) {
                    nm._delFilter('dom');
                }
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'data';
            },
            load: function(nm) {
                nm._setCont(nm.data);
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Image filter
 *
 * Depends:
 * - filters.link
 *
 * Before: filters.data
 */
jQuery(function($, undefined) {
    $.nmFilters({
        image: {
            is: function(nm) {

                if(  ((new RegExp(nm.imageRegex, 'i')).test(nm.opener.attr('href')))
                    ||
                    ( nm.opener.attr('href') != undefined && nm.opener.attr('href').indexOf("files/get") > 0)   ){

                    return true;
                }else{
                    return false;
                }

            },
            init: function(nm) {
                nm.loadFilter = 'image';
            },
            load: function(nm) {
                var url = nm.opener.attr('href');

                var element = $('<img />').attr({
                    'src': url,
                    'width':"400px"
                });
                console.log(element[0]);
                nm._setCont(element[0]);

            },
            size: function(nm) {

            },
            close: function(nm) {
                if (nm.elts.cont) {
                    nm.elts.cont.removeClass('nyroModalImg');
                    nm.elts.hidden.removeClass('nyroModalImg');
                }
            }
        }
    });
});

/*
 * nyroModal v2.0.0
 *
 * Form filter
 *
 * Depends:
 *
 * Before: filters.swf
 */
jQuery(function($, undefined) {
    $.nmFilters({
        form: {
            is: function(nm) {
                var ret = nm.opener.is('form');
                if (ret)
                    nm.store.form = nm.getInternal()._extractUrl(nm.opener.attr('action'));
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'form';
                nm.opener.unbind('submit.nyroModal').bind('submit.nyroModal', function(e) {

                    e.preventDefault();
                    let submitAction = "";
                    if(e && e.originalEvent &&
                        e.originalEvent.submitter && e.originalEvent.submitter.value) {
                        submitAction = e.originalEvent.submitter.value;
                    }

                    nm.opener.append("<input type='hidden' name='submitAction' value='" + submitAction + "' />")
                    nm.opener.trigger('nyroModal');
                });
            },
            load: function(nm) {
                var data = nm.opener.serializeArray();

                let obj = data.find(o => o.name === 'submitAction');
                let action = obj.value;

                if (nm.store.form.sel)
                    data.push({name: nm.selIndicator, value: nm.store.form.sel.substring(1)});

                if (typeof nm.callbacks.beforePostSubmit === "function") {
                    nm.callbacks.beforePostSubmit();
                }

                $.ajax({
                    url: nm.store.form.url,
                    data: data,
                    type: nm.opener.attr('method') ? nm.opener.attr('method') : 'get',
                    success: function(data) {

                        if(action == "closeModal") {
                            nm.close();
                        }else{
                            nm._setCont(data, nm.store.form.sel);
                        }

                    },
                    error: function(content) {

                        nm._setCont(content.responseText);
                    }
                });
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Form file filter
 *
 * Depends:
 *
 * Before: filters.form
*/
jQuery(function($, undefined) {
    $.nmFilters({
        formFile: {
            is: function(nm) {
                var ret = nm.opener.is('form[enctype="multipart/form-data"]');
                if (ret) {
                    nm._delFilter('form');
                    if (!nm.store.form)
                        nm.store.form = nm.getInternal()._extractUrl(nm.opener.attr('action'));
                }
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'formFile';
                nm.store.formFileLoading = false;
                nm.opener.unbind('submit.nyroModal').bind('submit.nyroModal', function(e) {
                    if (!nm.store.formFileIframe) {
                        e.preventDefault();
                        nm.opener.trigger('nyroModal');
                    } else {
                        nm.store.formFileLoading = true;
                    }
                });
            },
            initElts: function(nm) {
                var inputSel;
                if (nm.store.form.sel)
                    inputSel = $('<input />', {
                        'type': 'hidden',
                        name: nm.selIndicator,
                        value: nm.store.form.sel.substring(1)
                    }).appendTo(nm.opener);
                function rmFormFileElts() {
                    if (inputSel) {
                        inputSel.remove();
                        inputSel = undefined;
                        delete(inputSel);
                    }
                    nm.store.formFileIframe.attr('src', 'about:blank').remove();
                    nm.store.formFileIframe = undefined;
                    delete(nm.store.formFileIframe);
                }
                console.log("here");
                console.log(nm.store);
                nm.store.formFileIframe = $('<iframe name="nyroModalFormFile" src="javascript:\'\';" id="nyromodal-iframe-'+(new Date().getTime())+'"></iframe>')
                    .on( "load", function() {
                        if (nm.store.formFileLoading) {
                            nm.store.formFileLoading = false;
                            var content = nm.store.formFileIframe
                                .unbind('load error')
                                .contents().find('body').not('script[src]');
                            if (content && content.html() && content.html().length) {
                                rmFormFileElts();
                                nm._setCont(content.html(), nm.store.form.sel);
                            } else {
                                // Not totally ready, try it in a few secs
                                var nbTry = 0;
                                fct = function() {
                                    nbTry++;
                                    var content = nm.store.formFileIframe
                                        .unbind('load error')
                                        .contents().find('body').not('script[src]');
                                    if (content && content.html() && content.html().length) {
                                        nm._setCont(content.html(), nm.store.form.sel);
                                        rmFormFileElts();
                                    } else if (nbTry < 5) {
                                        setTimeout(fct, 25);
                                    } else {
                                        rmFormFileElts();
                                        nm._error();
                                    }
                                };
                                setTimeout(fct, 25);
                            }
                        }
                    });

                    /*
                    .error(function() {
                        rmFormFileElts();
                        nm._error();
                    }).hide();*/
                nm.elts.all.append(nm.store.formFileIframe);
                nm.opener
                    .attr('target', 'nyroModalFormFile')
                    .submit();
            },
            close: function(nm) {
                nm.store.formFileLoading = false;
                if (nm.store.formFileIframe) {
                    nm.store.formFileIframe.remove();
                    nm.store.formFileIframe = undefined;
                    delete(nm.store.formFileIframe);
                }
            }
        }
    });
});




/*
 * nyroModal v2.0.0
 *
 * Iframe filter
 *
 * Depends:
 * - filters.link
 *
 * Before: filters.formFile
 */
jQuery(function($, undefined) {
    $.nmFilters({
        iframe: {
            is: function(nm) {
                var	target = nm.opener.attr('target') || '',
                    rel = nm.opener.attr('rel') || '',
                    opener = nm.opener.get(0);
                return !nm._hasFilter('image') && (target.toLowerCase() == '_blank'
                    || rel.toLowerCase().indexOf('external') > -1
                    || (opener.hostname && opener.hostname.replace(/:\d*$/,'') != window.location.hostname.replace(/:\d*$/,'')));
            },
            init: function(nm) {
                nm.loadFilter = 'iframe';
            },
            load: function(nm) {
                nm.store.iframe = $('<iframe src="javascript:\'\';" id="nyromodal-iframe-'+(new Date().getTime())+'"></iframe>');
                nm._setCont(nm.store.iframe);
            },
            afterShowCont: function(nm) {
                nm.store.iframe.attr('src', nm.opener.attr('href'));
            },
            close: function(nm) {
                if (nm.store.iframe) {
                    nm.store.iframe.remove();
                    nm.store.iframe = undefined;
                    delete(nm.store.iframe);
                }
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Iframe form filter
 *
 * Depends:
 * - filters.iframe
 *
 * Before: filters.iframe
 */
jQuery(function($, undefined) {
    $.nmFilters({
        iframeForm: {
            is: function(nm) {
                var ret = nm._hasFilter('iframe') && nm.opener.is('form');
                if (ret) {
                    nm._delFilter('iframe');
                    nm._delFilter('form');
                }
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'iframeForm';
                nm.store.iframeFormLoading = false;
                nm.store.iframeFormOrgTarget = nm.opener.attr('target');
                nm.opener.unbind('submit.nyroModal').bind('submit.nyroModal', function(e) {
                    if (!nm.store.iframeFormIframe) {
                        e.preventDefault();
                        nm.opener.trigger('nyroModal');
                    } else {
                        nm.store.iframeFormLoading = true;
                    }
                });
            },
            load: function(nm) {
                nm.store.iframeFormIframe = $('<iframe name="nyroModalIframeForm" src="javascript:\'\';" id="nyromodal-iframe-'+(new Date().getTime())+'"></iframe>');
                nm._setCont(nm.store.iframeFormIframe);
            },
            afterShowCont: function(nm) {
                nm.opener
                    .attr('target', 'nyroModalIframeForm')
                    .submit();
            },
            close: function(nm) {
                nm.store.iframeFormOrgTarget ? nm.opener.attr('target', nm.store.iframeFormOrgTarget) : nm.opener.removeAttr('target');
                delete(nm.store.formFileLoading);
                delete(nm.store.iframeFormOrgTarget);
                if (nm.store.iframeFormIframe) {
                    nm.store.iframeFormIframe.remove();
                    nm.store.iframeFormIframe = undefined;
                    delete(nm.store.iframeFormIframe);
                }
            }
        }
    });
});
/*
 * nyroModal v2.0.0
 *
 * Embedly filter
 *
 * Depends:
 * - filters.link
 *
 * Before: filters.iframeForm
 */
jQuery(function($, undefined) {
    $.nmFilters({
        embedly: {
            is: function(nm) {
                // Regex from https://github.com/embedly/embedly-jquery
                var embedlyReg = /http:\/\/(.*youtube\.com\/watch.*|.*\.youtube\.com\/v\/.*|youtu\.be\/.*|.*\.youtube\.com\/user\/.*|.*\.youtube\.com\/.*#.*\/.*|m\.youtube\.com\/watch.*|m\.youtube\.com\/index.*|.*\.youtube\.com\/profile.*|.*justin\.tv\/.*|.*justin\.tv\/.*\/b\/.*|.*justin\.tv\/.*\/w\/.*|www\.ustream\.tv\/recorded\/.*|www\.ustream\.tv\/channel\/.*|www\.ustream\.tv\/.*|qik\.com\/video\/.*|qik\.com\/.*|qik\.ly\/.*|.*revision3\.com\/.*|.*\.dailymotion\.com\/video\/.*|.*\.dailymotion\.com\/.*\/video\/.*|www\.collegehumor\.com\/video:.*|.*twitvid\.com\/.*|www\.break\.com\/.*\/.*|vids\.myspace\.com\/index\.cfm\?fuseaction=vids\.individual&videoid.*|www\.myspace\.com\/index\.cfm\?fuseaction=.*&videoid.*|www\.metacafe\.com\/watch\/.*|www\.metacafe\.com\/w\/.*|blip\.tv\/file\/.*|.*\.blip\.tv\/file\/.*|video\.google\.com\/videoplay\?.*|.*revver\.com\/video\/.*|video\.yahoo\.com\/watch\/.*\/.*|video\.yahoo\.com\/network\/.*|.*viddler\.com\/explore\/.*\/videos\/.*|liveleak\.com\/view\?.*|www\.liveleak\.com\/view\?.*|animoto\.com\/play\/.*|dotsub\.com\/view\/.*|www\.overstream\.net\/view\.php\?oid=.*|www\.livestream\.com\/.*|www\.worldstarhiphop\.com\/videos\/video.*\.php\?v=.*|worldstarhiphop\.com\/videos\/video.*\.php\?v=.*|teachertube\.com\/viewVideo\.php.*|www\.teachertube\.com\/viewVideo\.php.*|www1\.teachertube\.com\/viewVideo\.php.*|www2\.teachertube\.com\/viewVideo\.php.*|bambuser\.com\/v\/.*|bambuser\.com\/channel\/.*|bambuser\.com\/channel\/.*\/broadcast\/.*|www\.schooltube\.com\/video\/.*\/.*|bigthink\.com\/ideas\/.*|bigthink\.com\/series\/.*|sendables\.jibjab\.com\/view\/.*|sendables\.jibjab\.com\/originals\/.*|www\.xtranormal\.com\/watch\/.*|socialcam\.com\/v\/.*|www\.socialcam\.com\/v\/.*|dipdive\.com\/media\/.*|dipdive\.com\/member\/.*\/media\/.*|dipdive\.com\/v\/.*|.*\.dipdive\.com\/media\/.*|.*\.dipdive\.com\/v\/.*|v\.youku\.com\/v_show\/.*\.html|v\.youku\.com\/v_playlist\/.*\.html|www\.snotr\.com\/video\/.*|snotr\.com\/video\/.*|video\.jardenberg\.se\/.*|.*yfrog\..*\/.*|tweetphoto\.com\/.*|www\.flickr\.com\/photos\/.*|flic\.kr\/.*|twitpic\.com\/.*|www\.twitpic\.com\/.*|twitpic\.com\/photos\/.*|www\.twitpic\.com\/photos\/.*|.*imgur\.com\/.*|.*\.posterous\.com\/.*|post\.ly\/.*|twitgoo\.com\/.*|i.*\.photobucket\.com\/albums\/.*|s.*\.photobucket\.com\/albums\/.*|phodroid\.com\/.*\/.*\/.*|www\.mobypicture\.com\/user\/.*\/view\/.*|moby\.to\/.*|xkcd\.com\/.*|www\.xkcd\.com\/.*|imgs\.xkcd\.com\/.*|www\.asofterworld\.com\/index\.php\?id=.*|www\.asofterworld\.com\/.*\.jpg|asofterworld\.com\/.*\.jpg|www\.qwantz\.com\/index\.php\?comic=.*|23hq\.com\/.*\/photo\/.*|www\.23hq\.com\/.*\/photo\/.*|.*dribbble\.com\/shots\/.*|drbl\.in\/.*|.*\.smugmug\.com\/.*|.*\.smugmug\.com\/.*#.*|emberapp\.com\/.*\/images\/.*|emberapp\.com\/.*\/images\/.*\/sizes\/.*|emberapp\.com\/.*\/collections\/.*\/.*|emberapp\.com\/.*\/categories\/.*\/.*\/.*|embr\.it\/.*|picasaweb\.google\.com.*\/.*\/.*#.*|picasaweb\.google\.com.*\/lh\/photo\/.*|picasaweb\.google\.com.*\/.*\/.*|dailybooth\.com\/.*\/.*|brizzly\.com\/pic\/.*|pics\.brizzly\.com\/.*\.jpg|img\.ly\/.*|www\.tinypic\.com\/view\.php.*|tinypic\.com\/view\.php.*|www\.tinypic\.com\/player\.php.*|tinypic\.com\/player\.php.*|www\.tinypic\.com\/r\/.*\/.*|tinypic\.com\/r\/.*\/.*|.*\.tinypic\.com\/.*\.jpg|.*\.tinypic\.com\/.*\.png|meadd\.com\/.*\/.*|meadd\.com\/.*|.*\.deviantart\.com\/art\/.*|.*\.deviantart\.com\/gallery\/.*|.*\.deviantart\.com\/#\/.*|fav\.me\/.*|.*\.deviantart\.com|.*\.deviantart\.com\/gallery|.*\.deviantart\.com\/.*\/.*\.jpg|.*\.deviantart\.com\/.*\/.*\.gif|.*\.deviantart\.net\/.*\/.*\.jpg|.*\.deviantart\.net\/.*\/.*\.gif|plixi\.com\/p\/.*|plixi\.com\/profile\/home\/.*|plixi\.com\/.*|www\.fotopedia\.com\/.*\/.*|fotopedia\.com\/.*\/.*|photozou\.jp\/photo\/show\/.*\/.*|photozou\.jp\/photo\/photo_only\/.*\/.*|instagr\.am\/p\/.*|instagram\.com\/p\/.*|skitch\.com\/.*\/.*\/.*|img\.skitch\.com\/.*|https:\/\/skitch\.com\/.*\/.*\/.*|https:\/\/img\.skitch\.com\/.*|share\.ovi\.com\/media\/.*\/.*|www\.questionablecontent\.net\/|questionablecontent\.net\/|www\.questionablecontent\.net\/view\.php.*|questionablecontent\.net\/view\.php.*|questionablecontent\.net\/comics\/.*\.png|www\.questionablecontent\.net\/comics\/.*\.png|picplz\.com\/user\/.*\/pic\/.*\/|twitrpix\.com\/.*|.*\.twitrpix\.com\/.*|www\.someecards\.com\/.*\/.*|someecards\.com\/.*\/.*|some\.ly\/.*|www\.some\.ly\/.*|pikchur\.com\/.*|achewood\.com\/.*|www\.achewood\.com\/.*|achewood\.com\/index\.php.*|www\.achewood\.com\/index\.php.*|www\.whosay\.com\/content\/.*|www\.whosay\.com\/photos\/.*|www\.whosay\.com\/videos\/.*|say\.ly\/.*|www\.whitehouse\.gov\/photos-and-video\/video\/.*|www\.whitehouse\.gov\/video\/.*|wh\.gov\/photos-and-video\/video\/.*|wh\.gov\/video\/.*|www\.hulu\.com\/watch.*|www\.hulu\.com\/w\/.*|hulu\.com\/watch.*|hulu\.com\/w\/.*|.*crackle\.com\/c\/.*|www\.fancast\.com\/.*\/videos|www\.funnyordie\.com\/videos\/.*|www\.funnyordie\.com\/m\/.*|funnyordie\.com\/videos\/.*|funnyordie\.com\/m\/.*|www\.vimeo\.com\/groups\/.*\/videos\/.*|www\.vimeo\.com\/.*|vimeo\.com\/groups\/.*\/videos\/.*|vimeo\.com\/.*|vimeo\.com\/m\/#\/.*|www\.ted\.com\/talks\/.*\.html.*|www\.ted\.com\/talks\/lang\/.*\/.*\.html.*|www\.ted\.com\/index\.php\/talks\/.*\.html.*|www\.ted\.com\/index\.php\/talks\/lang\/.*\/.*\.html.*|.*nfb\.ca\/film\/.*|www\.thedailyshow\.com\/watch\/.*|www\.thedailyshow\.com\/full-episodes\/.*|www\.thedailyshow\.com\/collection\/.*\/.*\/.*|movies\.yahoo\.com\/movie\/.*\/video\/.*|movies\.yahoo\.com\/movie\/.*\/trailer|movies\.yahoo\.com\/movie\/.*\/video|www\.colbertnation\.com\/the-colbert-report-collections\/.*|www\.colbertnation\.com\/full-episodes\/.*|www\.colbertnation\.com\/the-colbert-report-videos\/.*|www\.comedycentral\.com\/videos\/index\.jhtml\?.*|www\.theonion\.com\/video\/.*|theonion\.com\/video\/.*|wordpress\.tv\/.*\/.*\/.*\/.*\/|www\.traileraddict\.com\/trailer\/.*|www\.traileraddict\.com\/clip\/.*|www\.traileraddict\.com\/poster\/.*|www\.escapistmagazine\.com\/videos\/.*|www\.trailerspy\.com\/trailer\/.*\/.*|www\.trailerspy\.com\/trailer\/.*|www\.trailerspy\.com\/view_video\.php.*|www\.atom\.com\/.*\/.*\/|fora\.tv\/.*\/.*\/.*\/.*|www\.spike\.com\/video\/.*|www\.gametrailers\.com\/video\/.*|gametrailers\.com\/video\/.*|www\.koldcast\.tv\/video\/.*|www\.koldcast\.tv\/#video:.*|techcrunch\.tv\/watch.*|techcrunch\.tv\/.*\/watch.*|mixergy\.com\/.*|video\.pbs\.org\/video\/.*|www\.zapiks\.com\/.*|tv\.digg\.com\/diggnation\/.*|tv\.digg\.com\/diggreel\/.*|tv\.digg\.com\/diggdialogg\/.*|www\.trutv\.com\/video\/.*|www\.nzonscreen\.com\/title\/.*|nzonscreen\.com\/title\/.*|app\.wistia\.com\/embed\/medias\/.*|https:\/\/app\.wistia\.com\/embed\/medias\/.*|hungrynation\.tv\/.*\/episode\/.*|www\.hungrynation\.tv\/.*\/episode\/.*|hungrynation\.tv\/episode\/.*|www\.hungrynation\.tv\/episode\/.*|indymogul\.com\/.*\/episode\/.*|www\.indymogul\.com\/.*\/episode\/.*|indymogul\.com\/episode\/.*|www\.indymogul\.com\/episode\/.*|channelfrederator\.com\/.*\/episode\/.*|www\.channelfrederator\.com\/.*\/episode\/.*|channelfrederator\.com\/episode\/.*|www\.channelfrederator\.com\/episode\/.*|tmiweekly\.com\/.*\/episode\/.*|www\.tmiweekly\.com\/.*\/episode\/.*|tmiweekly\.com\/episode\/.*|www\.tmiweekly\.com\/episode\/.*|99dollarmusicvideos\.com\/.*\/episode\/.*|www\.99dollarmusicvideos\.com\/.*\/episode\/.*|99dollarmusicvideos\.com\/episode\/.*|www\.99dollarmusicvideos\.com\/episode\/.*|ultrakawaii\.com\/.*\/episode\/.*|www\.ultrakawaii\.com\/.*\/episode\/.*|ultrakawaii\.com\/episode\/.*|www\.ultrakawaii\.com\/episode\/.*|barelypolitical\.com\/.*\/episode\/.*|www\.barelypolitical\.com\/.*\/episode\/.*|barelypolitical\.com\/episode\/.*|www\.barelypolitical\.com\/episode\/.*|barelydigital\.com\/.*\/episode\/.*|www\.barelydigital\.com\/.*\/episode\/.*|barelydigital\.com\/episode\/.*|www\.barelydigital\.com\/episode\/.*|threadbanger\.com\/.*\/episode\/.*|www\.threadbanger\.com\/.*\/episode\/.*|threadbanger\.com\/episode\/.*|www\.threadbanger\.com\/episode\/.*|vodcars\.com\/.*\/episode\/.*|www\.vodcars\.com\/.*\/episode\/.*|vodcars\.com\/episode\/.*|www\.vodcars\.com\/episode\/.*|confreaks\.net\/videos\/.*|www\.confreaks\.net\/videos\/.*|video\.allthingsd\.com\/video\/.*|aniboom\.com\/animation-video\/.*|www\.aniboom\.com\/animation-video\/.*|clipshack\.com\/Clip\.aspx\?.*|www\.clipshack\.com\/Clip\.aspx\?.*|grindtv\.com\/.*\/video\/.*|www\.grindtv\.com\/.*\/video\/.*|ifood\.tv\/recipe\/.*|ifood\.tv\/video\/.*|ifood\.tv\/channel\/user\/.*|www\.ifood\.tv\/recipe\/.*|www\.ifood\.tv\/video\/.*|www\.ifood\.tv\/channel\/user\/.*|logotv\.com\/video\/.*|www\.logotv\.com\/video\/.*|lonelyplanet\.com\/Clip\.aspx\?.*|www\.lonelyplanet\.com\/Clip\.aspx\?.*|streetfire\.net\/video\/.*\.htm.*|www\.streetfire\.net\/video\/.*\.htm.*|trooptube\.tv\/videos\/.*|www\.trooptube\.tv\/videos\/.*|www\.godtube\.com\/featured\/video\/.*|godtube\.com\/featured\/video\/.*|www\.godtube\.com\/watch\/.*|godtube\.com\/watch\/.*|www\.tangle\.com\/view_video.*|mediamatters\.org\/mmtv\/.*|www\.clikthrough\.com\/theater\/video\/.*|soundcloud\.com\/.*|soundcloud\.com\/.*\/.*|soundcloud\.com\/.*\/sets\/.*|soundcloud\.com\/groups\/.*|snd\.sc\/.*|www\.last\.fm\/music\/.*|www\.last\.fm\/music\/+videos\/.*|www\.last\.fm\/music\/+images\/.*|www\.last\.fm\/music\/.*\/_\/.*|www\.last\.fm\/music\/.*\/.*|www\.mixcloud\.com\/.*\/.*\/|www\.radionomy\.com\/.*\/radio\/.*|radionomy\.com\/.*\/radio\/.*|www\.entertonement\.com\/clips\/.*|www\.rdio\.com\/#\/artist\/.*\/album\/.*|www\.rdio\.com\/artist\/.*\/album\/.*|www\.zero-inch\.com\/.*|.*\.bandcamp\.com\/|.*\.bandcamp\.com\/track\/.*|.*\.bandcamp\.com\/album\/.*|freemusicarchive\.org\/music\/.*|www\.freemusicarchive\.org\/music\/.*|freemusicarchive\.org\/curator\/.*|www\.freemusicarchive\.org\/curator\/.*|www\.npr\.org\/.*\/.*\/.*\/.*\/.*|www\.npr\.org\/.*\/.*\/.*\/.*\/.*\/.*|www\.npr\.org\/.*\/.*\/.*\/.*\/.*\/.*\/.*|www\.npr\.org\/templates\/story\/story\.php.*|huffduffer\.com\/.*\/.*|www\.audioboo\.fm\/boos\/.*|audioboo\.fm\/boos\/.*|boo\.fm\/b.*|www\.xiami\.com\/song\/.*|xiami\.com\/song\/.*|www\.saynow\.com\/playMsg\.html.*|www\.saynow\.com\/playMsg\.html.*|listen\.grooveshark\.com\/s\/.*|radioreddit\.com\/songs.*|www\.radioreddit\.com\/songs.*|radioreddit\.com\/\?q=songs.*|www\.radioreddit\.com\/\?q=songs.*|espn\.go\.com\/video\/clip.*|espn\.go\.com\/.*\/story.*|abcnews\.com\/.*\/video\/.*|abcnews\.com\/video\/playerIndex.*|washingtonpost\.com\/wp-dyn\/.*\/video\/.*\/.*\/.*\/.*|www\.washingtonpost\.com\/wp-dyn\/.*\/video\/.*\/.*\/.*\/.*|www\.boston\.com\/video.*|boston\.com\/video.*|www\.facebook\.com\/photo\.php.*|www\.facebook\.com\/video\/video\.php.*|www\.facebook\.com\/v\/.*|cnbc\.com\/id\/.*\?.*video.*|www\.cnbc\.com\/id\/.*\?.*video.*|cnbc\.com\/id\/.*\/play\/1\/video\/.*|www\.cnbc\.com\/id\/.*\/play\/1\/video\/.*|cbsnews\.com\/video\/watch\/.*|www\.google\.com\/buzz\/.*\/.*\/.*|www\.google\.com\/buzz\/.*|www\.google\.com\/profiles\/.*|google\.com\/buzz\/.*\/.*\/.*|google\.com\/buzz\/.*|google\.com\/profiles\/.*|www\.cnn\.com\/video\/.*|edition\.cnn\.com\/video\/.*|money\.cnn\.com\/video\/.*|today\.msnbc\.msn\.com\/id\/.*\/vp\/.*|www\.msnbc\.msn\.com\/id\/.*\/vp\/.*|www\.msnbc\.msn\.com\/id\/.*\/ns\/.*|today\.msnbc\.msn\.com\/id\/.*\/ns\/.*|multimedia\.foxsports\.com\/m\/video\/.*\/.*|msn\.foxsports\.com\/video.*|www\.globalpost\.com\/video\/.*|www\.globalpost\.com\/dispatch\/.*|guardian\.co\.uk\/.*\/video\/.*\/.*\/.*\/.*|www\.guardian\.co\.uk\/.*\/video\/.*\/.*\/.*\/.*|bravotv\.com\/.*\/.*\/videos\/.*|www\.bravotv\.com\/.*\/.*\/videos\/.*|video\.nationalgeographic\.com\/.*\/.*\/.*\.html|dsc\.discovery\.com\/videos\/.*|animal\.discovery\.com\/videos\/.*|health\.discovery\.com\/videos\/.*|investigation\.discovery\.com\/videos\/.*|military\.discovery\.com\/videos\/.*|planetgreen\.discovery\.com\/videos\/.*|science\.discovery\.com\/videos\/.*|tlc\.discovery\.com\/videos\/.*|.*amazon\..*\/gp\/product\/.*|.*amazon\..*\/.*\/dp\/.*|.*amazon\..*\/dp\/.*|.*amazon\..*\/o\/ASIN\/.*|.*amazon\..*\/gp\/offer-listing\/.*|.*amazon\..*\/.*\/ASIN\/.*|.*amazon\..*\/gp\/product\/images\/.*|.*amazon\..*\/gp\/aw\/d\/.*|www\.amzn\.com\/.*|amzn\.com\/.*|www\.shopstyle\.com\/browse.*|www\.shopstyle\.com\/action\/apiVisitRetailer.*|api\.shopstyle\.com\/action\/apiVisitRetailer.*|www\.shopstyle\.com\/action\/viewLook.*|gist\.github\.com\/.*|twitter\.com\/.*\/status\/.*|twitter\.com\/.*\/statuses\/.*|www\.twitter\.com\/.*\/status\/.*|www\.twitter\.com\/.*\/statuses\/.*|mobile\.twitter\.com\/.*\/status\/.*|mobile\.twitter\.com\/.*\/statuses\/.*|https:\/\/twitter\.com\/.*\/status\/.*|https:\/\/twitter\.com\/.*\/statuses\/.*|https:\/\/www\.twitter\.com\/.*\/status\/.*|https:\/\/www\.twitter\.com\/.*\/statuses\/.*|https:\/\/mobile\.twitter\.com\/.*\/status\/.*|https:\/\/mobile\.twitter\.com\/.*\/statuses\/.*|www\.crunchbase\.com\/.*\/.*|crunchbase\.com\/.*\/.*|www\.slideshare\.net\/.*\/.*|www\.slideshare\.net\/mobile\/.*\/.*|slidesha\.re\/.*|scribd\.com\/doc\/.*|www\.scribd\.com\/doc\/.*|scribd\.com\/mobile\/documents\/.*|www\.scribd\.com\/mobile\/documents\/.*|screenr\.com\/.*|polldaddy\.com\/community\/poll\/.*|polldaddy\.com\/poll\/.*|answers\.polldaddy\.com\/poll\/.*|www\.5min\.com\/Video\/.*|www\.howcast\.com\/videos\/.*|www\.screencast\.com\/.*\/media\/.*|screencast\.com\/.*\/media\/.*|www\.screencast\.com\/t\/.*|screencast\.com\/t\/.*|issuu\.com\/.*\/docs\/.*|www\.kickstarter\.com\/projects\/.*\/.*|www\.scrapblog\.com\/viewer\/viewer\.aspx.*|ping\.fm\/p\/.*|chart\.ly\/symbols\/.*|chart\.ly\/.*|maps\.google\.com\/maps\?.*|maps\.google\.com\/\?.*|maps\.google\.com\/maps\/ms\?.*|.*\.craigslist\.org\/.*\/.*|my\.opera\.com\/.*\/albums\/show\.dml\?id=.*|my\.opera\.com\/.*\/albums\/showpic\.dml\?album=.*&picture=.*|tumblr\.com\/.*|.*\.tumblr\.com\/post\/.*|www\.polleverywhere\.com\/polls\/.*|www\.polleverywhere\.com\/multiple_choice_polls\/.*|www\.polleverywhere\.com\/free_text_polls\/.*|www\.quantcast\.com\/wd:.*|www\.quantcast\.com\/.*|siteanalytics\.compete\.com\/.*|statsheet\.com\/statplot\/charts\/.*\/.*\/.*\/.*|statsheet\.com\/statplot\/charts\/e\/.*|statsheet\.com\/.*\/teams\/.*\/.*|statsheet\.com\/tools\/chartlets\?chart=.*|.*\.status\.net\/notice\/.*|identi\.ca\/notice\/.*|brainbird\.net\/notice\/.*|shitmydadsays\.com\/notice\/.*|www\.studivz\.net\/Profile\/.*|www\.studivz\.net\/l\/.*|www\.studivz\.net\/Groups\/Overview\/.*|www\.studivz\.net\/Gadgets\/Info\/.*|www\.studivz\.net\/Gadgets\/Install\/.*|www\.studivz\.net\/.*|www\.meinvz\.net\/Profile\/.*|www\.meinvz\.net\/l\/.*|www\.meinvz\.net\/Groups\/Overview\/.*|www\.meinvz\.net\/Gadgets\/Info\/.*|www\.meinvz\.net\/Gadgets\/Install\/.*|www\.meinvz\.net\/.*|www\.schuelervz\.net\/Profile\/.*|www\.schuelervz\.net\/l\/.*|www\.schuelervz\.net\/Groups\/Overview\/.*|www\.schuelervz\.net\/Gadgets\/Info\/.*|www\.schuelervz\.net\/Gadgets\/Install\/.*|www\.schuelervz\.net\/.*|myloc\.me\/.*|pastebin\.com\/.*|pastie\.org\/.*|www\.pastie\.org\/.*|redux\.com\/stream\/item\/.*\/.*|redux\.com\/f\/.*\/.*|www\.redux\.com\/stream\/item\/.*\/.*|www\.redux\.com\/f\/.*\/.*|cl\.ly\/.*|cl\.ly\/.*\/content|speakerdeck\.com\/u\/.*\/p\/.*|www\.kiva\.org\/lend\/.*|www\.timetoast\.com\/timelines\/.*|storify\.com\/.*\/.*|.*meetup\.com\/.*|meetu\.ps\/.*|www\.dailymile\.com\/people\/.*\/entries\/.*|.*\.kinomap\.com\/.*|www\.metacdn\.com\/api\/users\/.*\/content\/.*|www\.metacdn\.com\/api\/users\/.*\/media\/.*|prezi\.com\/.*\/.*|.*\.uservoice\.com\/.*\/suggestions\/.*|formspring\.me\/.*|www\.formspring\.me\/.*|formspring\.me\/.*\/q\/.*|www\.formspring\.me\/.*\/q\/.*|twitlonger\.com\/show\/.*|www\.twitlonger\.com\/show\/.*|tl\.gd\/.*|www\.qwiki\.com\/q\/.*|crocodoc\.com\/.*|.*\.crocodoc\.com\/.*|https:\/\/crocodoc\.com\/.*|https:\/\/.*\.crocodoc\.com\/.*)/i;
                var ret = nm._hasFilter('link') && nm.opener.attr('href') && nm.opener.attr('href').match(embedlyReg) !== null;
                if (ret)
                    nm._delFilter('iframe');
                return ret;
            },
            init: function(nm) {
                nm.loadFilter = 'embedly';
                nm.store.embedly = {};
            },
            load: function(nm) {
                $.ajax({
                    url: 'http://api.embed.ly/1/oembed',
                    dataType: 'jsonp',
                    data: 'wmode=transparent&url='+nm.opener.attr('href'),
                    success: function(data) {
                        if (data.type == 'error')
                            nm._error();
                        else if (data.type == 'photo') {
                            nm.filters.push('image');
                            $('<img />')
                                .load(function() {
                                    nm.elts.cont.addClass('nyroModalImg');
                                    nm.elts.hidden.addClass('nyroModalImg');
                                    nm._setCont(this);
                                }).error(function() {
                                nm._error();
                            })
                                .attr('src', data.url);
                        } else {
                            nm.store.embedly.w = data.width;
                            nm.store.embedly.h = data.height;
                            nm._setCont('<div>'+data.html+'</div>');
                        }
                    }
                });
            },
            size: function(nm) {
                if (nm.store.embedly.w && !nm.sizes.h) {
                    nm.sizes.w = nm.store.embedly.w;
                    nm.sizes.h = nm.store.embedly.h;
                }
            }
        }
    });
});
