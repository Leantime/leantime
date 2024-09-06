import { appUrl } from './instance-info.module.js';
import jQuery from 'jquery';
import tippy from 'tippy.js';

var setCustomModalCallback = function (callback) {
    if (typeof callback === 'function') {
        window.globalModalCallback = callback;
    }
}

var checksumhUrl = function (s) {
    return s.split("").reduce(function (a, b) {
        a = ((a << 5) - a) + b.charCodeAt(0);
        return a & a;
    }, 0);
}

var removeHash = function () {
    history.pushState("", document.title, window.location.pathname
        + window.location.search);
}

var getModalUrl = function () {

    var url = window.location.hash.substring(1);
    var urlParts = url.split("/");

    if (urlParts.length > 2 && urlParts[1] !== "tab") {

        return url;
    }

    return false;

}

var openPageModal = function (url) {

    jQuery("#modal-wrapper #main-page-modal .modal-box-content").html("");
    htmx.find("#modal-wrapper #main-page-modal").showModal();
    jQuery("#modal-wrapper #main-page-modal .modal-loader").show();

    var baseUrl = appUrl.replace(/\/$/, '');

    htmx.ajax('GET', baseUrl+url, {
        event: "trigger-modal",
        target:'#modal-wrapper #main-page-modal .modal-box-content',
        swap:'innerHTML',
        headers: {
            "Is-Modal": true,
        }
    }).then(() => {

        history.pushState(null, '', "#"+url);

        jQuery("#modal-wrapper #main-page-modal .modal-loader").hide();

        htmx.find("#modal-wrapper #main-page-modal").addEventListener("close", (event) => {
            removeHash();
        })

    });

}

var openHashUrlModal = function () {

    var modalUrl = getModalUrl();
    if (modalUrl !== false) {
        openPageModal(modalUrl);
    }
}


/**
 * Closes a dialog.
 *
 * @function closeModal
 * @description Closes a dialog using jQuery.
 * @returns {void}
 */
var closeModal = function () {
    jQuery("dialog").close();
}


//Open page url modal on page load and hash change
jQuery(document).ready(openHashUrlModal);
window.addEventListener("hashchange", openHashUrlModal);

window.addEventListener("closeModal", closeModal);

export default {
    openPageModal: openPageModal,
    setCustomModalCallback: setCustomModalCallback,
    closeModal: closeModal
};
