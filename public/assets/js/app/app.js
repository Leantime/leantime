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

// On touch devices, require a brief long-press + a few px of movement before a
// jQuery-UI sortable/draggable starts dragging. Without this the whole card is a
// drag target, so a tap "grabs" it instead of opening it and a swipe drags it
// instead of scrolling. Set on the widget prototypes so it applies to every
// sortable/draggable (dashboard to-dos, kanban cards, ideas cards) without
// touching each init. Touch-only, so mouse drag on desktop is unaffected.
// Fixes #1357 (can't tap cards), #3350 (scrolling moves tasks), #1465 (tablet drag).
leantime.applyTouchDragGuards = function () {
    if (!(('ontouchstart' in window || navigator.maxTouchPoints > 0) && jQuery.ui)) {
        return;
    }
    if (jQuery.ui.sortable) {
        jQuery.extend(jQuery.ui.sortable.prototype.options, { delay: 200, distance: 8 });
    }
    if (jQuery.ui.draggable) {
        jQuery.extend(jQuery.ui.draggable.prototype.options, { delay: 200, distance: 8 });
    }
};

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

jQuery(document).ready(function () {

    leantime.applyTouchDragGuards();

    leantime.replaceSVGColors();

    jQuery(".confetti").click(function () {
        confetti.start();
    });

    leantime.initTooltips();

    if (jQuery('.login-alert .alert').text() !== '') {
        jQuery('.login-alert').fadeIn();
    }

    document.addEventListener('scroll', () => {
        document.documentElement.dataset.scroll = window.scrollY;
    });

});

htmx.onLoad(function(element){
    leantime.initTooltips(element);
    // Re-assert touch drag guards before HTMX-loaded sortables (e.g. the
    // dashboard to-do list) initialise.
    if (leantime.applyTouchDragGuards) {
        leantime.applyTouchDragGuards();
    }
});

window.addEventListener("HTMX.ShowNotification", function(evt) {
    jQuery.get(leantime.appUrl+"/notifications/getLatestGrowl", function(data){
        let notification = JSON.parse(data);

        if(notification.notification && notification.notification !== "undefined") {
            jQuery.growl({
                message: notification.notification, style: notification.notificationType
            });
        }
    })
});
