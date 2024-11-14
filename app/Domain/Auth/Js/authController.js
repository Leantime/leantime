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

        if (jQuery(container).find(".complexEditor").length) {
            jQuery(container).find(".complexEditor").each(function (element) {
                if (jQuery(this).tinymce()) {
                    jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
                }
            });
        }

        if (jQuery(container).find(".tinymceSimple").length) {
            jQuery(container).find(".tinymceSimple").each(function (element) {

                if (jQuery(this).tinymce()) {
                    jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
                }
            });
        }

        jQuery(container).find(".tox-editor-header").hide();
        jQuery(container).find(".tox-statusbar").hide();

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
