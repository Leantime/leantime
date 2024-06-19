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

    var toggleCommentBoxes = function (id, formHash) {

        if (id == 0) {
            jQuery('.mainToggler-'+formHash).hide();
        } else {
            jQuery('.mainToggler-'+formHash).show();
        }
        jQuery('.commentBox-'+formHash+' textarea').remove();

        jQuery('.commentBox-'+formHash+'').hide();

        jQuery('#comment-'+formHash+'-' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" id="editor-'+formHash+'-' + id + '" class="tinymceSimple"></textarea>');
        leantime.editorController.initSimpleEditor();

        jQuery('#comment-'+formHash+'-' + id + '').show();
        jQuery('#father-'+formHash).val(id);

        setTimeout(function () {                         // you may not need the timeout
            tinyMCE.get('editor-'+formHash+'-' + id + '').focus();
        }, 50);

    };

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
        toggleCommentBoxes:toggleCommentBoxes
    };

})();
