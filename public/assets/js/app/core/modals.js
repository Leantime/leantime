leantime.modals = (function () {

    // The URL of the modal currently open. Used to make the hashchange->openModal
    // path idempotent: a repeated hashchange pointing at the already-open modal
    // must NOT rebuild it. Rebuilding re-fetches and re-inserts the whole
    // .nyroModalCont, destroying any field the user is typing into (focus loss).
    var currentModalUrl = null;

    // Closing a modal used to always do location.reload() (#1809, #2969): slow, and it lost scroll
    // and board state. Now a modal that changed nothing closes without any refresh, and one that
    // did change data refreshes the page content in place (softReload). Anything unexpected falls
    // back to the full reload, as does LEAN_SOFT_RELOAD=false or a page marked data-full-reload.
    var modalChangedData = false;
    var softReloadInFlight = false;

    var isWriteMethod = function (method) {
        var verb = String(method || 'GET').toUpperCase();
        return verb !== 'GET' && verb !== 'HEAD' && verb !== 'OPTIONS';
    };

    var isModalOpen = function () {
        return typeof jQuery.nmTop === 'function' && !!jQuery.nmTop();
    };

    var noteWriteWhileModalOpen = function (method) {
        if (isWriteMethod(method) && isModalOpen()) {
            modalChangedData = true;
        }
    };

    // Every way the app writes data from inside a modal: htmx requests, jQuery ajax (which also
    // carries nyroModal's form posts), fetch (leantime.rpc / JSON-RPC) and plain form submits.
    document.addEventListener('htmx:beforeRequest', function (event) {
        noteWriteWhileModalOpen(event.detail && event.detail.requestConfig ? event.detail.requestConfig.verb : 'GET');
    });
    jQuery(document).ajaxSend(function (event, xhr, settings) {
        noteWriteWhileModalOpen(settings && settings.type);
    });
    if (typeof window.fetch === 'function') {
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var method = (init && init.method) || (input && typeof input === 'object' && input.method) || 'GET';
            noteWriteWhileModalOpen(method);
            return originalFetch.apply(this, arguments);
        };
    }
    document.addEventListener('submit', function (event) {
        if (event.target && event.target.closest && event.target.closest('.nyroModalCont')) {
            noteWriteWhileModalOpen(event.target.getAttribute('method') || 'GET');
        }
    }, true);

    // Listeners a page registers on document/window outlive its content. Record the ones page
    // scripts add (from inline scripts in the page content on the first load, and everything added
    // while an in-place refresh runs the page scripts) and remove them before the next refresh,
    // so refreshing does not stack up handlers (SlimSelect, Uppy, page inits, ...).
    var pageScopedListeners = [];
    var pageScriptRunDepth = 0;

    var isRunningPageScript = function () {
        if (pageScriptRunDepth > 0) {
            return true;
        }
        var script = document.currentScript;
        return !!(script && !script.src && script.closest
            && (script.closest('.primaryContent') || script.closest('#lt-page-scripts')));
    };

    [document, window].forEach(function (target) {
        var originalAdd = target.addEventListener;
        target.addEventListener = function (type, listener, options) {
            if (listener && isRunningPageScript()) {
                pageScopedListeners.push({ kind: 'dom', target: target, type: type, listener: listener, options: options });
            }
            return originalAdd.call(this, type, listener, options);
        };
    });

    if (jQuery && jQuery.event && typeof jQuery.event.add === 'function') {
        var originalJqueryAdd = jQuery.event.add;
        jQuery.event.add = function (elem, types, handler, data, selector) {
            if ((elem === document || elem === window) && isRunningPageScript()) {
                pageScopedListeners.push({ kind: 'jquery', target: elem, types: types, handler: handler, selector: selector });
            }
            return originalJqueryAdd.apply(this, arguments);
        };
    }

    var removePageScopedListeners = function () {
        pageScopedListeners.forEach(function (entry) {
            if (entry.kind === 'dom') {
                entry.target.removeEventListener(entry.type, entry.listener, entry.options);
            } else if (typeof entry.types === 'string' || (entry.types && typeof entry.types === 'object')) {
                jQuery(entry.target).off(entry.types, entry.selector || null, entry.handler);
            }
        });
        pageScopedListeners = [];
    };

    // Uppy instances register window listeners (online/offline) that only close() removes. The
    // to-do modal's file tab and the file pages create one each time they render, so close the
    // ones a modal created when it closes, and the page's ones before an in-place refresh.
    var uppyInstances = [];

    if (window.Uppy && typeof window.Uppy.Uppy === 'function') {
        var uppyNamespace = window.Uppy;
        var OriginalUppy = uppyNamespace.Uppy;
        var trackedNamespace = {};
        Object.keys(uppyNamespace).forEach(function (name) {
            trackedNamespace[name] = uppyNamespace[name];
        });
        trackedNamespace.Uppy = class extends OriginalUppy {
            constructor(options) {
                super(options);
                uppyInstances.push({ instance: this, scope: isModalOpen() ? 'modal' : 'page' });
            }
        };
        window.Uppy = trackedNamespace;
    }

    var closeUppyInstances = function (scope) {
        uppyInstances = uppyInstances.filter(function (entry) {
            if (entry.scope !== scope) {
                return true;
            }
            try {
                if (typeof entry.instance.close === 'function') {
                    entry.instance.close();
                } else if (typeof entry.instance.destroy === 'function') {
                    entry.instance.destroy();
                }
            } catch (error) {
                console.warn('[Modal] Could not close an uploader', error);
            }
            return false;
        });
    };

    // htmx registers "from:body"/"from:document" triggers on body/document; their closures keep the
    // element (and its whole removed tree) alive until the event fires again. htmx cleans these up
    // when it swaps content itself, but not when content is removed otherwise (modal close, our
    // refresh), so do the same cleanup htmx does (deInitNode) before removing content.
    var cleanUpHtmx = function (root) {
        if (!root) {
            return;
        }
        var elements = [root].concat(Array.prototype.slice.call(root.querySelectorAll('*')));
        elements.forEach(function (element) {
            var internalData = element['htmx-internal-data'];
            if (!internalData) {
                return;
            }
            if (typeof htmx !== 'undefined') {
                htmx.trigger(element, 'htmx:beforeCleanupElement');
            }
            if (internalData.timeout) {
                clearTimeout(internalData.timeout);
            }
            if (internalData.webSocket) {
                internalData.webSocket.close();
            }
            if (internalData.sseEventSource) {
                internalData.sseEventSource.close();
            }
            (internalData.listenerInfos || []).forEach(function (info) {
                if (info.on) {
                    info.on.removeEventListener(info.trigger, info.listener);
                }
            });
            (internalData.onHandlers || []).forEach(function (handlerInfo) {
                element.removeEventListener(handlerInfo.event, handlerInfo.listener);
            });
            Object.keys(internalData).forEach(function (key) {
                delete internalData[key];
            });
        });
    };

    // Release widgets that register outside the content they belong to, so removed content can be
    // garbage collected: htmx triggers, Chosen (document handlers), tippy. For page content we own
    // the whole subtree, so jQuery.cleanData also runs jQuery UI's remove hooks (sortable, datepicker).
    var releaseContent = function (root, withJqueryData) {
        if (!root) {
            return;
        }
        cleanUpHtmx(root);
        try {
            jQuery(root).find('select').each(function () {
                var select = jQuery(this);
                if (select.data('chosen')) {
                    select.chosen('destroy');
                }
            });
            root.querySelectorAll('*').forEach(function (element) {
                if (element._tippy) {
                    element._tippy.destroy();
                }
            });
            if (withJqueryData) {
                jQuery.cleanData([root].concat(Array.prototype.slice.call(root.querySelectorAll('*'))));
            }
        } catch (error) {
            console.warn('[Modal] Could not release removed content', error);
        }
    };

    // Chart.js keeps every chart in Chart.instances until destroy(); a chart whose canvas left the
    // page would keep that whole removed content alive.
    var destroyDetachedCharts = function () {
        if (!window.Chart || !window.Chart.instances) {
            return;
        }
        Object.keys(window.Chart.instances).forEach(function (key) {
            var chart = window.Chart.instances[key];
            if (chart && (!chart.canvas || !chart.canvas.isConnected)) {
                try {
                    chart.destroy();
                } catch (error) {
                    console.warn('[Modal] Could not destroy a chart', error);
                }
            }
        });
    };

    var scrollSnapshot = function (root) {
        var positions = [];
        root.querySelectorAll('*').forEach(function (element) {
            if (element.scrollTop > 0 || element.scrollLeft > 0) {
                var key = element.id
                    ? '#' + CSS.escape(element.id)
                    : (typeof element.className === 'string' && element.className.trim() !== ''
                        ? '.' + element.className.trim().split(/\s+/).map(function (name) { return CSS.escape(name); }).join('.')
                        : null);
                if (key) {
                    positions.push({ key: key, top: element.scrollTop, left: element.scrollLeft });
                }
            }
        });
        return positions;
    };

    var restoreScroll = function (root, positions) {
        positions.forEach(function (position) {
            var element = root.querySelector(position.key);
            if (element) {
                element.scrollTop = position.top;
                element.scrollLeft = position.left;
            }
        });
    };

    var runInlineScripts = function (container) {
        if (!container) {
            return;
        }
        container.querySelectorAll('script:not([src])').forEach(function (original) {
            var type = original.getAttribute('type');
            if (type && type !== 'text/javascript' && type !== 'module') {
                return; // templates, JSON data blocks, ...
            }
            var script = document.createElement('script');
            if (type) {
                script.type = type;
            }
            script.text = original.textContent;
            document.body.appendChild(script);
            script.remove();
        });
    };

    /**
     * Re-render the page content in place: fetch the current page, swap .primaryContent and the
     * page's init scripts (#lt-page-scripts), let htmx process the new content and re-run the
     * page's inline init scripts. Header, menu and the loaded bundles stay untouched.
     */
    var softReload = function () {
        var content = document.querySelector('.primaryContent');
        var canSoftReload = leantime.softReloadEnabled !== false
            && content
            && !document.querySelector('[data-full-reload]')
            && typeof window.fetch === 'function'
            && typeof window.DOMParser === 'function'
            && typeof htmx !== 'undefined';

        if (!canSoftReload) {
            location.reload();
            return;
        }
        if (softReloadInFlight) {
            return;
        }
        softReloadInFlight = true;

        var windowScroll = { x: window.scrollX, y: window.scrollY };
        var innerScroll = scrollSnapshot(content);
        content.classList.add('lt-soft-reloading');

        fetch(window.location.pathname + window.location.search, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok || response.redirected) {
                    throw new Error('HTTP ' + response.status + (response.redirected ? ' (redirected)' : ''));
                }
                return response.text();
            })
            .then(function (html) {
                var fetched = new DOMParser().parseFromString(html, 'text/html');
                var freshContent = fetched.querySelector('.primaryContent');
                if (!freshContent || fetched.querySelector('[data-full-reload]')) {
                    throw new Error('page cannot be refreshed in place');
                }

                var newContent = document.importNode(freshContent, true);
                var oldContent = document.querySelector('.primaryContent');
                releaseContent(oldContent, true);
                oldContent.replaceWith(newContent);

                var currentScripts = document.getElementById('lt-page-scripts');
                var freshScripts = fetched.getElementById('lt-page-scripts');
                var newScripts = freshScripts ? document.importNode(freshScripts, true) : null;
                if (currentScripts && newScripts) {
                    releaseContent(currentScripts, true);
                    currentScripts.replaceWith(newScripts);
                }
                destroyDetachedCharts();

                removePageScopedListeners();
                closeUppyInstances('page');
                htmx.process(newContent);

                // Keep tracking until jQuery's (asynchronous) ready callbacks of these scripts ran.
                pageScriptRunDepth++;
                var listenerCountBefore = pageScopedListeners.length;
                try {
                    runInlineScripts(newContent);
                    runInlineScripts(newScripts);
                    // Page inits that wait for DOMContentLoaded (e.g. calendars) run again. Only the
                    // handlers these page scripts registered: re-dispatching the event globally would
                    // re-run every bundle's DOMContentLoaded init and stack their listeners.
                    pageScopedListeners.slice(listenerCountBefore).forEach(function (entry) {
                        if (entry.kind === 'dom' && entry.target === document && entry.type === 'DOMContentLoaded') {
                            try {
                                var handler = entry.listener;
                                (typeof handler === 'function' ? handler : handler.handleEvent).call(document, new Event('DOMContentLoaded'));
                            } catch (initError) {
                                console.error('[Modal] Page init failed after in-place refresh', initError);
                            }
                        }
                    });
                } finally {
                    setTimeout(function () {
                        setTimeout(function () { pageScriptRunDepth--; }, 0);
                    }, 0);
                }

                window.scrollTo(windowScroll.x, windowScroll.y);
                restoreScroll(newContent, innerScroll);
                // Boards that size/scroll themselves after init get restored once more.
                setTimeout(function () { restoreScroll(newContent, innerScroll); }, 300);

                document.dispatchEvent(new CustomEvent('lt:ui:page.refreshed'));
            })
            .catch(function (error) {
                console.warn('[Modal] In-place refresh failed, reloading the page', error);
                location.reload();
            })
            .finally(function () {
                softReloadInFlight = false;
                var current = document.querySelector('.primaryContent');
                if (current) {
                    current.classList.remove('lt-soft-reloading');
                }
            });
    };

    // After a modal closed: nothing changed -> no refresh; data changed -> refresh in place.
    var refreshAfterModalClose = function () {
        if (!modalChangedData) {
            return;
        }
        modalChangedData = false;
        softReload();
    };

    var setCustomModalCallback = function(callback) {
        if(typeof callback === 'function') {
            window.globalModalCallback = callback;
        }
    }
    var openModal = function () {

        var modalOptions = {
            sizes: {
                minW: 500,
                minH: 200
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforePostSubmit: function () {

                    jQuery(".showDialogOnLoad").show();

                    // Destroy Tiptap editors
                    if(window.leantime?.tiptapController?.registry) {
                        var count = window.leantime.tiptapController.registry.destroyAll();
                        if(count > 0) {
                            console.log('[Modal] Destroyed', count, 'Tiptap editor(s)');
                        }
                    }

                },
                beforeShowCont: function () {
                    jQuery(".showDialogOnLoad").show();

                    // Destroy Tiptap editors
                    if(window.leantime?.tiptapController?.registry) {
                        window.leantime.tiptapController.registry.destroyAll();
                    }

                },
                afterShowCont: function () {
                    window.htmx.process('.nyroModalCont');
                    jQuery(".formModal, .modal").nyroModal(modalOptions);
                    // Idempotent + scoped to the modal so it doesn't re-instance
                    // page tooltips (see app.js initTooltips).
                    window.leantime?.initTooltips?.(document.querySelector('.nyroModalCont'));

                    // Initialize Tiptap editors in modal (after small delay for DOM settlement)
                    setTimeout(function() {
                        if(window.leantime?.tiptapController?.initEditors) {
                            var modalContent = document.querySelector('.nyroModalCont');
                            if(modalContent) {
                                window.leantime.tiptapController.initEditors(modalContent);
                            }
                        }
                    }, 100);
                },
                beforeClose: function () {
                    currentModalUrl = null;
                    closeUppyInstances('modal');
                    releaseContent(document.querySelector('.nyroModalCont'), false);
                    // The modal content is removed after the close animation.
                    setTimeout(destroyDetachedCharts, 1000);
                    try{
                        history.pushState("", document.title, window.location.pathname + window.location.search);

                    }catch(error){
                        //Code to handle error comes here
                        console.log("Issue pushing history");
                    }

                    if(typeof window.globalModalCallback === 'function') {
                        modalChangedData = false;
                        window.globalModalCallback();
                    }else{
                        refreshAfterModalClose();
                    }
                }
            },
            titleFromIframe: true
        };

        var url = window.location.hash.substring(1);
        if(url.includes("showTicket")
            || url.includes("ideaDialog")
            || url.includes("articleDialog")) {
            // These detail modals are intentionally large on desktop. On
            // mobile/tablet (<1200px) the 1800px minimum makes them unusable,
            // so only apply it on desktop. CSS caps the container to ~95vw. #3088
            if (window.innerWidth >= 1200) {
                modalOptions.sizes.minW = 1800;
                modalOptions.sizes.minH = 1800;
            }
        }

        // Never let any modal's minimum width exceed the viewport on small
        // screens, otherwise it forces horizontal overflow. #3088
        if (window.innerWidth < 1200) {
            modalOptions.sizes.minW = Math.min(modalOptions.sizes.minW, window.innerWidth - 20);
        }

        //Ensure we have no trailing slash at the end.
        var baseUrl = leantime.appUrl.replace(/\/$/, '');

        var urlParts = url.split("/");
        if(urlParts.length>2 && urlParts[1] !== "tab") {
            var targetUrl = baseUrl+""+url;

            // Idempotency guard: if the modal for this exact URL is already open, a
            // repeated hashchange must NOT rebuild it — rebuilding destroys the DOM
            // (and any input the user is typing into), stealing focus.
            if (targetUrl === currentModalUrl && jQuery.nmTop()) {
                return;
            }
            currentModalUrl = targetUrl;

            // Guard against nyroModal losing its jQuery registration between opens.
            // This can happen when the modal close/reinit cycle runs before the
            // document-ready wrapper in jquery.nyroModal.custom.js has re-fired.
            if (typeof jQuery.nmManual !== 'function') {
                console.warn('[Modal] jQuery.nmManual not available, retrying...');
                setTimeout(function() {
                    if (typeof jQuery.nmManual === 'function') {
                        jQuery.nmManual(targetUrl, modalOptions);
                    } else {
                        console.error('[Modal] jQuery.nmManual unavailable after retry — nyroModal may not be loaded.');
                    }
                }, 100);
                return;
            }
            jQuery.nmManual(targetUrl, modalOptions);
        }
    }

    var closeModal = function () {
        if( jQuery.nmTop()) {
            jQuery.nmTop().close();
        }
    }

    return {
        openModal:openModal,
        setCustomModalCallback:setCustomModalCallback,
        closeModal:closeModal,
        softReload:softReload

    };

})();

jQuery(document).ready(function() {
    leantime.modals.openModal();
});

window.addEventListener("hashchange", function () {
    leantime.modals.openModal();
});

// 'lt:ui:modal.close' is the canonical client event. The legacy names ('closeModal',
// 'HTMX.closemodal', 'Htmx.CloseModal') are kept for the migration window and also close a
// pre-existing gap: emitters used three different casings but only 'closeModal' had a listener.
var onCloseModalEvent = function (evt) {
    leantime.modals.closeModal();
};

window.addEventListener("lt:ui:modal.close", onCloseModalEvent);
window.addEventListener("closeModal", onCloseModalEvent);
window.addEventListener("HTMX.closemodal", onCloseModalEvent);
window.addEventListener("Htmx.CloseModal", onCloseModalEvent);

