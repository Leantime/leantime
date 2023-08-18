leantime.commentsController = (function () {

    var enableCommenterForms = function () {

        jQuery(".commentBox").show();

        //Hide reply comment boxes
        jQuery("#comments .replies .commentBox").hide();
        jQuery(".deleteComment, .replyButton").show();

        jQuery(".commentReply .tinymceSimple").tinymce().getBody().setAttribute('contenteditable', "true");
        jQuery(".commentReply .tox-editor-header").show();
        jQuery(".commentBox input").prop("readonly", false);
        jQuery(".commentBox input").prop("disabled", false);

        jQuery(".commentBox textarea").prop("readonly", false);
        jQuery(".commentBox textarea").prop("disabled", false);

    };

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
    };

})();
