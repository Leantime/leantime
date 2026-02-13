leantime.commentsController = (function () {

    var enableCommenterForms = function () {

        document.querySelectorAll(".commentBox").forEach(function (el) {
            el.style.display = '';
        });

        //Hide reply comment boxes
        document.querySelectorAll("#comments .replies .commentBox").forEach(function (el) {
            el.style.display = 'none';
        });
        document.querySelectorAll(".deleteComment, .replyButton").forEach(function (el) {
            el.style.display = '';
        });

        document.querySelectorAll(".commentReply .tinymceSimple").forEach(function (el) {
            var editor = tinymce.get(el.id);
            if (editor) {
                editor.getBody().setAttribute('contenteditable', "true");
            }
        });
        document.querySelectorAll(".commentReply .tox-editor-header").forEach(function (el) {
            el.style.display = '';
        });

        document.querySelectorAll(".commenterFields input").forEach(function (el) {
            el.readOnly = false;
            el.disabled = false;
        });

        document.querySelectorAll(".commenterFields textarea").forEach(function (el) {
            el.readOnly = false;
            el.disabled = false;
        });

    };

    var toggleCommentBoxes = function (id) {


        if (id == 0) {
            var mainToggler = document.getElementById('mainToggler');
            if (mainToggler) { mainToggler.style.display = 'none'; }
        } else {
            var mainToggler = document.getElementById('mainToggler');
            if (mainToggler) { mainToggler.style.display = ''; }
        }
        document.querySelectorAll('.commentBox textarea').forEach(function (el) {
            el.remove();
        });

        document.querySelectorAll('.commentBox').forEach(function (el) {
            el.style.display = 'none';
        });

        var commentEl = document.getElementById('comment' + id);
        if (commentEl) {
            var replyEl = commentEl.querySelector('.commentReply');
            if (replyEl) {
                replyEl.insertAdjacentHTML('afterbegin', '<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
            }
        }
        leantime.editorController.initSimpleEditor();

        if (commentEl) {
            commentEl.style.display = '';
        }
        var fatherEl = document.getElementById('father');
        if (fatherEl) {
            fatherEl.value = id;
        }

    };

    // Make public what you want to have public, everything else is private
    return {
        enableCommenterForms:enableCommenterForms,
        toggleCommentBoxes:toggleCommentBoxes
    };

})();
