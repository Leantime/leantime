leantime.commentsController = (function () {

    var enableCommenterForms = function () {

        jQuery(".commentBox").show();

        //Hide reply comment boxes
        jQuery("#comments .replies .commentBox").hide();
        jQuery(".deleteComment, .replyButton").show();

        jQuery(".commentReply .tinymceSimple").tinymce().getBody().setAttribute('contenteditable', "true");
        jQuery(".commentReply .tox-editor-header").show();

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
        jQuery('.commentBox textarea').remove();

        jQuery('.commentBox').hide('fast', function () {});

        jQuery('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
        leantime.editorController.initSimpleEditor();

        jQuery('#comment' + id + '').show('fast');
        jQuery('#father').val(id);

    };

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
        toggleCommentBoxes:toggleCommentBoxes
    };

})();
