leantime.authController = (function () {

    var makeInputReadonly = function (container) {
        if (typeof container === undefined) {
            container = "body";
        }

        // A control the server rendered because the user holds its own permission (marked
        // data-lt-permitted, on the element or a wrapper) stays usable: read-only mode only
        // takes away what editing grants.
        var editable = function (selector) {
            return jQuery(container).find(selector).not("[data-lt-permitted], [data-lt-permitted] *");
        };

        editable("input").not(".filterBar input").prop("readonly", true);
        editable("input").not(".filterBar input").prop("disabled", true);

        editable("select").not(".filterBar select, .mainSprintSelector").prop("readonly", true);
        editable("select").not(".filterBar select, .mainSprintSelector").prop("disabled", true);

        editable("textarea").not(".filterBar textarea").prop("disabled", true);

        editable("a.delete").remove();

        editable(".quickAddLink").hide();

        // Make Tiptap editors readonly
        if (editable(".tiptap-editor").length && window.leantime && window.leantime.tiptapController) {
            editable(".tiptap-editor").each(function () {
                var editor = leantime.tiptapController.registry.get(this);
                if (editor) {
                    editor.setEditable(false);
                }
            });
        }

        // Hide Tiptap toolbar
        editable(".tiptap-toolbar").hide();

        jQuery(container).find(".ticketDropdown a").removeAttr("data-toggle");

        // Comment controls inside the container only: a fragment (e.g. subtask rows) going
        // read-only must not hide the rest of the page's comments.
        editable("#mainToggler").hide();
        editable(".commentBox").hide();
        editable(".deleteComment, .replyButton").hide();

        jQuery(container).find(".dropdown i").removeClass('fa-caret-down');
    };

    // Make public what you want to have public, everything else is private
    return {
        makeInputReadonly:makeInputReadonly,
    };

})();
