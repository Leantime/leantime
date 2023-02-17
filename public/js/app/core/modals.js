

function openModal() {
    var modalOptions = {
        sizes: {
            minW: 200,
            minH: 200
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function () {
            },
            afterShowCont: function () {
                jQuery(".formModal, .modal").nyroModal(modalOptions);
            },
            beforeClose: function () {
                history.pushState("", document.title, window.location.pathname + window.location.search);
                location.reload();
            }


        },
        titleFromIframe: true
    };

    var url = window.location.hash.substring(1);
    var urlParts = url.split("/");

    if(urlParts.length>2 && urlParts[1] !== "tab") {
        jQuery.nmManual(leantime.appUrl+"/"+url, modalOptions);
    }
}

jQuery(document).ready(function() {
    openModal();
});

window.addEventListener("hashchange", function () {
    openModal();
});
