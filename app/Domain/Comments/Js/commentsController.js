leantime.commentsController = (function () {

    var enableCommenterForms = function () {

        jQuery(".commentBox").show();

        //Hide reply comment boxes
        jQuery("#comments .replies .commentBox").hide();
        jQuery(".deleteComment, .replyButton").show();

        // Enable Tiptap editors
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

        jQuery(".commenterFields input").prop("readonly", false);
        jQuery(".commenterFields input").prop("disabled", false);

        jQuery(".commenterFields textarea").prop("readonly", false);
        jQuery(".commenterFields textarea").prop("disabled", false);

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

        jQuery('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="simpleEditor"></textarea>');
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
