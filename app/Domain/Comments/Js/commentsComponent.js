leantime.commentsComponent = (function () {

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

    var toggleCommentBoxes = function (parentId, formHash, commentId, editComment = false, isReply = false) {

        this.resetForm(parentId, formHash);

        if (parseInt(parentId, 10) === 0) {
            jQuery('.mainToggler-'+formHash).hide();
        } else {
            jQuery('.mainToggler-'+formHash).show();
        }

        let content = "";

        if (editComment) {
            content = jQuery("#commentText-"+formHash+"-"+commentId).html();

            //Top level comment edit
            if(parentId == commentId) {
                jQuery('#commentReplyBox-'+formHash+'-' + parentId + ' > .commentImage').hide();
                jQuery('#comment-'+formHash+'-' + parentId + ' > .commentMain > .replies > form').insertBefore(jQuery('#comment-'+formHash+'-' + parentId + ' .replies > div:first-child'));
            } else {
                jQuery('#comment-'+formHash+'-' + commentId + ' > .commentImage').hide();
            }
            jQuery('#comment-'+formHash+'-' + commentId + ' > .commentMain > .commentContent').hide();
            jQuery('#comment-'+formHash+'-' + commentId + ' > .commentMain > .commentLinks').hide();
            jQuery('#edit-comment-' + formHash + '-' + parentId +'').val(commentId);
        }

        jQuery('.commentBox-'+formHash+' textarea').remove();

        jQuery('.commentBox-'+formHash+'').hide();

        jQuery('#commentReplyBox-'+formHash+'-' + parentId + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" id="editor-'+formHash+'-' + parentId + '" class="tinymceSimple">'+ content +'</textarea>');
        leantime.editorController.initSimpleEditor();

        jQuery('#commentReplyBox-' + formHash + '-' + parentId + '').show();

        jQuery('#father-'+formHash).val(parentId);

        setTimeout(function () {                         // you may not need the timeout
            jQuery('#commentReplyBox-' + formHash + '-' + parentId + '')[0].scrollIntoView();
            tinyMCE.get('editor-'+formHash+'-' + parentId + '').focus();
        }, 75);



    };

    var resetForm = function(id, formHash) {

        jQuery('.mainToggler-'+formHash).show();
        jQuery('#comments-'+formHash+' .commentImage').show();
        jQuery('#comments-'+formHash+' .commentContent').show();
        jQuery('#comments-'+formHash+' .commentLinks').show();

        jQuery('.commentReplyBox-'+formHash+'').hide();

        jQuery('.commentReplyBox-'+formHash+' textarea').each(function(){
            if(jQuery(this).tinymce()) {
                jQuery(this).tinymce().remove();
            }
        });

        jQuery('.commentReplyBox-'+formHash+' textarea').remove();

    }

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
        toggleCommentBoxes:toggleCommentBoxes,
        resetForm:resetForm
    };

})();
