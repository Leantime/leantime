//Lets get this party started.
// Use window.leantime explicitly so Rollup doesn't constant-fold `var leantime = leantime || {}`
// into a new empty object, losing properties when other modules reassign the variable.
window.leantime = window.leantime || {};

window.leantime.companyColor = (document.querySelector('meta[name=theme-color]') || {}).content || null;
window.leantime.colorScheme = (document.querySelector('meta[name=color-scheme]') || {}).content || null;
window.leantime.theme = (document.querySelector('meta[name=theme]') || {}).content || null;
window.leantime.appUrl = (document.querySelector('meta[name=identifier-URL]') || {}).content || null;
window.leantime.version = (document.querySelector('meta[name=leantime-version]') || {}).content || null;

// Local alias — points to the same object, safe for Rollup bundling
var leantime = window.leantime;

leantime.replaceSVGColors = function () {

    if (leantime.companyColor != "#1b75bb") {
        document.querySelectorAll("svg > *").forEach(function (child) {
            if (child.getAttribute("fill") == "#1b75bb") {
                child.setAttribute("fill", leantime.companyColor);
            }
        });
    }

};

leantime.handleAsyncResponse = function (response) {

    if (response !== undefined) {
        if (response.result !== undefined && response.result.html !== undefined) {
            document.body.insertAdjacentHTML('beforeend', response.result.html);
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {

    leantime.replaceSVGColors();

    document.querySelectorAll(".confetti").forEach(function (el) {
        el.addEventListener("click", function () {
            confetti.start();
        });
    });

    tippy('[data-tippy-content]');

    var loginAlertText = document.querySelector('.login-alert .alert');
    if (loginAlertText && loginAlertText.textContent !== '') {
        var loginAlert = document.querySelector('.login-alert');
        if (loginAlert) {
            loginAlert.style.display = '';
        }
    }

    document.addEventListener('scroll', function () {
        document.documentElement.dataset.scroll = window.scrollY;
    });

});

htmx.onLoad(function(element){
    tippy('[data-tippy-content]');
    leantime.replaceSVGColors();
});

window.addEventListener("HTMX.ShowNotification", function(evt) {
    fetch(leantime.appUrl + "/notifications/getLatestGrowl", {
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) { return response.text(); })
    .then(function(data) {
        var notification = JSON.parse(data);

        if(notification.notification && notification.notification !== "undefined") {
            leantime.toast.show({
                message: notification.notification, style: notification.notificationType
            });
        }
    });
});
