//Lets get this party started.
var leantime = leantime || {};

var themeColor = jQuery('meta[name=theme-color]').attr("content");
leantime.companyColor = themeColor;

var colorScheme = jQuery('meta[name=color-scheme]').attr("content");
leantime.colorScheme = colorScheme;

var theme = jQuery('meta[name=theme]').attr("content");
leantime.theme = theme;

var appURL = jQuery('meta[name=identifier-URL]').attr("content");
leantime.appUrl = appURL;

var leantimeVersion = jQuery('meta[name=leantime-version]').attr("content");
leantime.version = leantimeVersion;

// LEAN_SOFT_RELOAD: refresh page content in place after a modal changed data (see modals.js)
leantime.softReloadEnabled = jQuery('meta[name=soft-reload]').attr("content") !== 'false';

leantime.replaceSVGColors = function () {

    jQuery(document).ready(function () {

        if (leantime.companyColor != "#1b75bb") {
            jQuery("svg").children().each(function () {
                if (jQuery(this).attr("fill") == "#1b75bb") {
                    jQuery(this).attr("fill", leantime.companyColor);
                }
            });
        }

    });

};

leantime.handleAsyncResponse = function (response) {

    if (response !== undefined) {
        if (response.result !== undefined && response.result.html !== undefined) {
            var content = jQuery(response.result.html);
            jQuery("body").append(content);
        }
    }
};

jQuery.noConflict();

// Initialise Tippy tooltips idempotently. Calling tippy('[data-tippy-content]')
// on every document.ready AND every htmx.onLoad re-instanced ALL tooltips on
// each HTMX swap, piling up dozens of duplicate "Notifications" tooltips (one of
// which stayed visible). Skip elements that already have an instance, and scope
// to the swapped subtree when given one.
leantime.initTooltips = function (root) {
    // Hover tooltips are a desktop affordance — useless on touch, and on narrow
    // screens the header-icon tooltips flashed/stuck during HTMX page loads
    // (a transient pointerover shows them, then the element re-renders so no
    // mouseleave ever fires to hide them). So on mobile/touch we don't run them
    // at all, and tear down any that already exist.
    var isMobile = window.innerWidth < 1200 || ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
    if (isMobile) {
        document.querySelectorAll('[data-tippy-content]').forEach(function (el) {
            if (el._tippy) { el._tippy.destroy(); }
        });
        return;
    }
    // Desktop: a short show-delay debounces transient hovers from layout reflow.
    if (window.tippy && tippy.setDefaultProps) {
        tippy.setDefaultProps({ delay: [300, 0] });
    }
    var scope = (root && root.querySelectorAll) ? root : document;
    scope.querySelectorAll('[data-tippy-content]').forEach(function (el) {
        if (!el._tippy) {
            tippy(el, { delay: [300, 0] });
        }
    });
};

// Content put on the page outside an htmx swap (a dialog, the List view's to-do detail) gets what
// htmx.onLoad gives a swap: htmx attributes, tooltips, enhanced selects, and lt:ui:content.loaded
// for the components that init on htmx.onLoad (date pickers, snippets). Not htmx:load itself:
// some page onLoad handlers aren't idempotent.
leantime.initLoadedContent = function (root) {
    if (!root) {
        return;
    }
    htmx.process(root);
    leantime.initTooltips(root);
    if (leantime.selectController) {
        leantime.selectController.init(root);
    }
    document.dispatchEvent(new CustomEvent('lt:ui:content.loaded', { detail: { root: root } }));
};

jQuery(document).ready(function () {

    leantime.replaceSVGColors();

    jQuery(".confetti").click(function () {
        confetti.start();
    });

    leantime.initTooltips();

    // The datepicker calendar floats on <body>: picking a date inside a dropdown panel (filters)
    // must not count as an outside click that closes the panel.
    if (jQuery.datepicker && jQuery.datepicker.dpDiv) {
        jQuery.datepicker.dpDiv.on('click', function (event) {
            event.stopPropagation();
        });
    }

    // Bootstrap 2 handles Escape only while focus is on a dropdown's toggle. Close an open dropdown
    // from anywhere inside it too (a menu link, a field in a filter panel) and hand focus back to
    // its toggle. An enhanced select inside a panel closes its own list first.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || event.defaultPrevented || !(event.target instanceof Element)) {
            return;
        }
        if (event.target.closest('.ss-main')) {
            return;
        }
        var openDropdown = event.target.closest('.open');
        var toggle = openDropdown ? openDropdown.querySelector(':scope > [data-toggle="dropdown"]') : null;
        if (!toggle) {
            return;
        }
        openDropdown.classList.remove('open');
        toggle.focus();
    });

    if (jQuery('.login-alert .alert').text() !== '') {
        jQuery('.login-alert').fadeIn();
    }

    document.addEventListener('scroll', () => {
        document.documentElement.dataset.scroll = window.scrollY;
    });

});

htmx.onLoad(function(element){
    leantime.initTooltips(element);
});

// --- Singleton HTMX progress bar -------------------------------------------------
// A single fixed top-of-page bar driven by an in-flight request COUNTER. Concurrent
// requests (e.g. the dashboard's parallel widget loads) keep it visible until ALL of
// them settle — it does not serialize requests. It is position:fixed, so it has zero
// layout impact, and replaces page-wide `.htmx-indicator` toggling as the general
// "something is loading" affordance.
leantime.htmxProgress = (function () {
    var inFlight = 0;
    var bar = null;
    var hideTimer = null;

    function ensureBar() {
        if (bar) { return bar; }
        bar = document.getElementById('lt-htmx-progress');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'lt-htmx-progress';
            bar.setAttribute('aria-hidden', 'true');
            bar.innerHTML = '<div class="bar"></div>';
            (document.body || document.documentElement).appendChild(bar);
        }
        return bar;
    }

    return {
        start: function () {
            inFlight++;
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
            ensureBar().classList.add('active');
        },
        done: function () {
            inFlight = Math.max(0, inFlight - 1);
            if (inFlight === 0) {
                // Small delay smooths over back-to-back requests so the bar doesn't strobe.
                hideTimer = setTimeout(function () {
                    if (inFlight === 0 && bar) { bar.classList.remove('active'); }
                }, 150);
            }
        }
    };
})();

// htmx request events bubble to document, so a single document-level listener covers
// every request regardless of which element triggered it.
document.addEventListener('htmx:beforeRequest', function () { leantime.htmxProgress.start(); });
document.addEventListener('htmx:afterRequest', function () { leantime.htmxProgress.done(); });

// Data events (lt:{domain}:{entity}.{verb}, optionally scoped with #{id}) arrive on the
// HX-Trigger header of the response and are fired on the element that made the request. A
// component listening for them must not refetch itself when that element sits inside it: its
// own request already rendered the change (the to-do widget's inline title edit would otherwise
// reload the whole widget). Components elsewhere on the page still refresh. Only that exact
// format counts: UI commands (lt:ui:notify) and client-only events (lt:chip:changed, which
// chips.js fires after a JSON-RPC save) have no entity.verb part and are never suppressed.
// A control whose response does NOT render everything it affects opts out with
// data-lt-refresh-container (the to-do widget's due-date picker: the task has to move to its
// new date group, so the widget reloads).
var leantimeDataEventPattern = /^lt:[a-z]+:[a-z]+\.[a-z]+(#.+)?$/;

document.addEventListener('htmx:confirm', function (evt) {
    var triggeringEvent = evt.detail.triggeringEvent;
    if (!triggeringEvent || typeof triggeringEvent.type !== 'string') {
        return;
    }

    var isDataEvent = leantimeDataEventPattern.test(triggeringEvent.type) && triggeringEvent.type.indexOf('lt:ui:') !== 0;
    var cameFromInside = triggeringEvent.target instanceof Element && evt.detail.elt.contains(triggeringEvent.target);
    var wantsContainerRefresh = cameFromInside && triggeringEvent.target.closest('[data-lt-refresh-container]') !== null;

    if (isDataEvent && cameFromInside && !wantsContainerRefresh) {
        evt.preventDefault();
    }
});

leantime.showNotification = function (evt) {
    jQuery.get(leantime.appUrl+"/notifications/getLatestGrowl", function(data){
        let notification = JSON.parse(data);

        if(notification.notification && notification.notification !== "undefined") {
            jQuery.growl({
                message: notification.notification, style: notification.notificationType
            });
        }
    })
};

window.addEventListener("lt:ui:notify", leantime.showNotification);
window.addEventListener("HTMX.ShowNotification", leantime.showNotification);

// Links that change data (delete a comment, remove a file, enable a plugin…) must be sent as
// POST: a GET can be fired by any other site through a plain link or image. Such links carry
// data-post-field / data-post-value instead of a query string, and the form that gets posted is:
//   - data-post-url: a one-off form posted to that same-origin URL (full page submit);
//   - data-post-form: the form with that id (for links rendered outside their form);
//   - otherwise the surrounding <form>, so forms that live in a modal keep posting through it.
// Optional data-post-confirm asks before submitting.
jQuery(document).on('click', '[data-post-field]', function (event) {
    event.preventDefault();

    var link = jQuery(this);
    var fieldName = link.attr('data-post-field');
    var fieldValue = link.attr('data-post-value') || '';
    var postUrl = link.attr('data-post-url');
    var postFormId = link.attr('data-post-form');
    var confirmText = link.attr('data-post-confirm');

    if (confirmText && !window.confirm(confirmText)) {
        return;
    }

    var formElement = null;
    if (postUrl) {
        // Only ever post to this app: resolve the URL and refuse other origins / schemes.
        var target;
        try {
            target = new URL(postUrl, window.location.href);
        } catch (e) {
            return;
        }
        if (target.origin !== window.location.origin) {
            return;
        }

        formElement = document.createElement('form');
        formElement.method = 'post';
        formElement.action = target.href;
        formElement.style.display = 'none';

        var csrfToken = jQuery('meta[name=csrf-token]').attr('content');
        if (csrfToken) {
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            formElement.appendChild(tokenInput);
        }
        document.body.appendChild(formElement);
    } else {
        formElement = postFormId ? document.getElementById(postFormId) : this.closest('form');
        if (!formElement || formElement.tagName !== 'FORM') {
            return;
        }
        jQuery(formElement).find('input[type=hidden]').filter(function () {
            return this.name === fieldName;
        }).remove();
    }

    var fieldInput = document.createElement('input');
    fieldInput.type = 'hidden';
    fieldInput.name = fieldName;
    fieldInput.value = fieldValue;
    formElement.appendChild(fieldInput);

    // Fire a real submit event so modal (nyroModal) and htmx handlers can take over; fall back
    // to a native submit when nobody intercepted it. No field validation — this is not a save.
    var submitEvent = new Event('submit', { bubbles: true, cancelable: true });
    if (formElement.dispatchEvent(submitEvent)) {
        HTMLFormElement.prototype.submit.call(formElement);
    }
});
