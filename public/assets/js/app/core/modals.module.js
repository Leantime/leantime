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

    jQuery("#modal-wrapper #main-page-modal .modal-loader").show();
    jQuery("#modal-wrapper #main-page-modal .modal-content-loader").removeClass("htmx-request");
    jQuery("#modal-wrapper #main-page-modal .modal-box-content").html("");
    htmx.find("#modal-wrapper #main-page-modal").showModal();

    var baseUrl = appUrl.replace(/\/$/, '');

    htmx.ajax('GET', baseUrl+url, {
        event: "trigger-modal",
        target:'#modal-wrapper #main-page-modal .modal-box-content',
        swap:'innerHTML',
        headers: {
            "Is-Modal": true,
        }
    }).then((e) => {

        history.pushState(null, '', "#"+url);

        jQuery("#modal-wrapper #main-page-modal .modal-loader").hide();
        jQuery("#modal-wrapper #main-page-modal .modal-loader").removeClass("htmx-request");

        htmx.find("#modal-wrapper #main-page-modal").addEventListener("close", function() {
            removeHash();
        });
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
    removeHash();
    htmx.find("#modal-wrapper #main-page-modal").close();
}

window.addEventListener("HTMX.closemodal", closeModal);

//Open page url modal on page load and hash change

jQuery(document).ready(function() {
    window.addEventListener("hashchange", openHashUrlModal);
    openHashUrlModal();
});


export default {
    openPageModal: openPageModal,
    setCustomModalCallback: setCustomModalCallback,
    closeModal: closeModal
};
