import replaceSVGColors from "../support/replaceSVGColors.module.mjs";
import getLatestGrowl from "../core/getLatestGrowl.module.mjs";

function initConfetti() {
    jQuery(".confetti").click(confetti.start);
}

function initToolTips() {
    tippy('[data-tippy-content]');
}

function initScrollTracker() {
    document.addEventListener('scroll', () => {
        document.documentElement.dataset.scroll = window.scrollY;
        if(window.scrollY > 25) {
            jQuery("body").addClass("scrolled");
        }

        if(window.scrollY <= 25) {
            jQuery("body").removeClass("scrolled");
        }
    });
}

function initNotificationListener() {
    window.addEventListener("HTMX.ShowNotification", getLatestGrowl);
}

export default function () {

    replaceSVGColors();

    initConfetti();

    initToolTips();

    initScrollTracker();

    initNotificationListener();

    // if (jQuery('.login-alert .alert').text() !== '') {
    //     jQuery('.login-alert').fadeIn();
    // }


};
