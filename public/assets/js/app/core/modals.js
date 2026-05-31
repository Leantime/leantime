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
                    try{
                        history.pushState("", document.title, window.location.pathname + window.location.search);

                    }catch(error){
                        //Code to handle error comes here
                        console.log("Issue pushing history");
                    }

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
            // Guard against nyroModal losing its jQuery registration between opens.
            // This can happen when the modal close/reinit cycle runs before the
            // document-ready wrapper in jquery.nyroModal.custom.js has re-fired.
            if (typeof jQuery.nmManual !== 'function') {
                console.warn('[Modal] jQuery.nmManual not available, retrying...');
                setTimeout(function() {
                    if (typeof jQuery.nmManual === 'function') {
                        jQuery.nmManual(baseUrl+""+url, modalOptions);
                    } else {
                        console.error('[Modal] jQuery.nmManual unavailable after retry — nyroModal may not be loaded.');
                    }
                }, 100);
                return;
            }
            jQuery.nmManual(baseUrl+""+url, modalOptions);
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
        closeModal:closeModal

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

