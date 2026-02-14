leantime.authController = (function () {

    var makeInputReadonly = function (container) {
        if (typeof container === undefined) {
            container = "body";
        }

        jQuery(container).find("input").not(".filterBar input").prop("readonly", true);
        jQuery(container).find("input").not(".filterBar input").prop("disabled", true);

        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("readonly", true);
        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("disabled", true);

        jQuery(container).find("textarea").not(".filterBar textarea").prop("disabled", true);

        jQuery(container).find("a.delete").remove();

        jQuery(container).find(".quickAddLink").hide();

        // Make Tiptap editors readonly
        if (jQuery(container).find(".tiptap-editor").length && window.leantime && window.leantime.tiptapController) {
            jQuery(container).find(".tiptap-editor").each(function () {
                var editor = leantime.tiptapController.registry.get(this);
                if (editor) {
                    editor.setEditable(false);
                }
            });
        }

        // Hide Tiptap toolbar
        jQuery(container).find(".tiptap-toolbar").hide();

        jQuery(container).find(".ticketDropdown a").removeAttr("data-toggle");

        jQuery("#mainToggler").hide();
        jQuery(".commentBox").hide();
        jQuery(".deleteComment, .replyButton").hide();

        jQuery(container).find(".dropdown i").removeClass('fa-caret-down');
    };

    // Make public what you want to have public, everything else is private
    return {
        makeInputReadonly:makeInputReadonly,
    };

})();
