leantime.authController = (function () {

    var makeInputReadonly = function (container) {
        if (typeof container === "undefined" || !container) {
            container = "body";
        }

        var containerEl = document.querySelector(container);
        if (!containerEl) { return; }

        containerEl.querySelectorAll("input").forEach(function (el) {
            if (!el.closest(".filterBar")) {
                el.readOnly = true;
                el.disabled = true;
            }
        });

        containerEl.querySelectorAll("select").forEach(function (el) {
            if (!el.closest(".filterBar") && !el.classList.contains("mainSprintSelector")) {
                el.readOnly = true;
                el.disabled = true;
            }
        });

        containerEl.querySelectorAll("textarea").forEach(function (el) {
            if (!el.closest(".filterBar")) {
                el.disabled = true;
            }
        });

        containerEl.querySelectorAll("a.delete").forEach(function (el) {
            el.remove();
        });

        containerEl.querySelectorAll(".quickAddLink").forEach(function (el) {
            el.style.display = 'none';
        });

        containerEl.querySelectorAll(".complexEditor").forEach(function (el) {
            if (typeof tinymce !== 'undefined' && tinymce.get(el.id)) {
                tinymce.get(el.id).getBody().setAttribute('contenteditable', "false");
            }
        });

        containerEl.querySelectorAll(".tinymceSimple").forEach(function (el) {
            if (typeof tinymce !== 'undefined' && tinymce.get(el.id)) {
                tinymce.get(el.id).getBody().setAttribute('contenteditable', "false");
            }
        });

        containerEl.querySelectorAll(".tox-editor-header").forEach(function (el) {
            el.style.display = 'none';
        });
        containerEl.querySelectorAll(".tox-statusbar").forEach(function (el) {
            el.style.display = 'none';
        });

        containerEl.querySelectorAll(".ticketDropdown a").forEach(function (el) {
            el.removeAttribute("data-toggle");
        });

        var mainToggler = document.getElementById("mainToggler");
        if (mainToggler) { mainToggler.style.display = 'none'; }

        document.querySelectorAll(".commentBox").forEach(function (el) {
            el.style.display = 'none';
        });
        document.querySelectorAll(".deleteComment, .replyButton").forEach(function (el) {
            el.style.display = 'none';
        });

        containerEl.querySelectorAll(".dropdown i").forEach(function (el) {
            el.classList.remove('fa-caret-down');
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        makeInputReadonly:makeInputReadonly,
    };

})();
