import { appUrl } from './instance-info.module.js';
import jQuery from 'jquery';
import tippy from 'tippy.js';

var setCustomModalCallback = function(callback) {
    if (typeof callback === 'function') {
        window.globalModalCallback = callback;
    }
}

var windowManager = [];

var checksumhUrl = function(s) {
    return s.split("").reduce(function(a, b) {
        a = ((a << 5) - a) + b.charCodeAt(0);
        return a & a;
    }, 0);
}

var removeHash = function  () {
    history.pushState("", document.title, window.location.pathname
        + window.location.search);
}

var openModal = function () {


    var baseUrl = appUrl.replace(/\/$/, '');

    var url = window.location.hash.substring(1);
    var urlParts = url.split("/");

    if(urlParts.length>2 && urlParts[1] !== "tab") {

        htmx.ajax('GET', baseUrl+""+url, {
            event: "trigger-modal",
            target:'#modal-wrapper',
            swap:'afterbegin',
            headers: {
                "Is-Modal": true,
            }
        }).then(() => {

            let urlHash = "modal"+checksumhUrl(url);
            if(windowManager[urlHash] == undefined) {
                windowManager[urlHash] = urlHash;
            }else{
                htmx.find("#modal-wrapper dialog."+urlHash);
            }

            console.log(htmx.find("#modal-wrapper dialog"));

            htmx.find("#modal-wrapper dialog").classList.add(urlHash);
            htmx.find("#modal-wrapper dialog."+urlHash).showModal();

            htmx.find("#modal-wrapper dialog."+urlHash).addEventListener("close", (event) => {
                removeHash();
            });

        });

    }

}


var closeModal = function () {
    jQuery.nmTop().close();
}

jQuery(document).ready(function() {
    openModal();
});

window.addEventListener("hashchange", openModal);
window.addEventListener("closeModal", closeModal);

export default {
    openModal: openModal,
    setCustomModalCallback: setCustomModalCallback,
    closeModal: closeModal
};
