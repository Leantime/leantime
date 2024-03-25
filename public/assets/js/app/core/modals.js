leantime.modals = (function () {

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

                    if(tinymce.editors.length>0) {

                        jQuery(".simpleEditor").each(function() {
                            jQuery(this).tinymce().save();
                            jQuery(this).tinymce().remove();
                        });

                        jQuery(".complexEditor").each(function() {
                            jQuery(this).tinymce().save();
                            jQuery(this).tinymce().remove();
                        });
                    }
                },
                beforeShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                },
                afterShowCont: function () {
                    window.htmx.process('.nyroModalCont');
                    jQuery(".formModal, .modal").nyroModal(modalOptions);
                    tippy('[data-tippy-content]');
                },
                beforeClose: function () {
                    history.pushState("", document.title, window.location.pathname + window.location.search);
                    console.log(window.globalModalCallback);

                    if(typeof window.globalModalCallback === 'function') {
                        window.globalModalCallback();
                    }else{
                        location.reload();
                    }
                }
            },
            titleFromIframe: true
        };

        var url = window.location.hash.substring(1);
        if(url.includes("showTicket")
            || url.includes("ideaDialog")
            || url.includes("articleDialog")) {
            modalOptions.sizes.minW = 1800;
            modalOptions.sizes.minH = 1800;
        }

        var urlParts = url.split("/");
        if(urlParts.length>2 && urlParts[1] !== "tab") {
            jQuery.nmManual(leantime.appUrl+"/"+url, modalOptions);
        }
    }

    var closeModal = function () {
        jQuery.nmTop().close();
    }

    return {
        openModal:openModal,
        setCustomModalCallback:setCustomModalCallback,
        closeModal:closeModal

    };

})();

jQuery(document).ready(function() {
    leantime.modals.openModal();
});

window.addEventListener("hashchange", function () {
    leantime.modals.openModal();
});

window.addEventListener("closeModal", function(evt) {
    leantime.modals.closeModal();
});

