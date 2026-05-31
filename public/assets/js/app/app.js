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

jQuery(document).ready(function () {

    leantime.replaceSVGColors();

    jQuery(".confetti").click(function () {
        confetti.start();
    });

    tippy('[data-tippy-content]');

    if (jQuery('.login-alert .alert').text() !== '') {
        jQuery('.login-alert').fadeIn();
    }

    document.addEventListener('scroll', () => {
        document.documentElement.dataset.scroll = window.scrollY;
    });

});

htmx.onLoad(function(element){
    tippy('[data-tippy-content]');
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
