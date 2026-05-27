leantime.commentsController = (function () {

    var enableCommenterForms = function () {

        // Show the "Add new comment" toggler that makeInputReadonly may have hidden
        jQuery("[class^='mainToggler']").show();

        // Show reply-level comment boxes (legacy .commentBox class only).
        // Do NOT show commentBox-{hash} containers — those are the "new comment"
        // forms that start hidden and open on-demand via toggleCommentBoxes().
        jQuery(".commentBox").show();
        jQuery(".replies .commentBox").hide();
        jQuery(".deleteComment, .replyButton").show();

        // Enable Tiptap editors in comment areas
        jQuery(".commentReply .tiptap-wrapper").each(function() {
            var editorEl = jQuery(this).find('.tiptap-editor')[0];
            if (editorEl && window.leantime && window.leantime.tiptapController) {
                var editor = leantime.tiptapController.registry.get(editorEl);
                if (editor) {
                    editor.setEditable(true);
                }
            }
        });
        jQuery(".commentReply .tiptap-toolbar").show();

        // Re-enable form controls in all comment areas (both legacy .commentBox
        // and hashed commentBox-{hash} containers) without changing visibility (#3194)
        jQuery(".commenterFields, .commentBox, [class*='commentBox-']")
            .find("input, textarea, button, select")
            .prop("readonly", false)
            .prop("disabled", false);

    };

    var toggleCommentBoxes = function (id) {


        if (id == 0) {
            jQuery('#mainToggler').hide();
        } else {
            jQuery('#mainToggler').show();
        }

        // Destroy existing Tiptap editors in comment boxes
        if (window.leantime && window.leantime.tiptapController && window.leantime.tiptapController.registry) {
            jQuery('.commentBox .tiptap-editor').each(function() {
                leantime.tiptapController.registry.destroy(this);
            });
        }
        jQuery('.commentBox .tiptap-wrapper').remove();
        jQuery('.commentBox textarea').remove();

        jQuery('.commentBox').hide('fast', function () {});

        jQuery('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="tiptapSimple"></textarea>');
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }

        jQuery('#comment' + id + '').show('fast');
        jQuery('#father').val(id);

    };

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
        toggleCommentBoxes:toggleCommentBoxes
    };

})();
