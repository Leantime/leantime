leantime.authController = (function () {

    //Variables
    var closeModal = false;

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initializeEventHandlers();
            }
        );
    })();

    //Functions
    function showNewTokenModal() {
        jQuery('#newTokenModal').modal('show');
    }

    function createToken() {
        var name = jQuery('#tokenName').val();

        jQuery.ajax({
            url: leantime.appUrl + '/advancedAuth/personalTokens/create',
            type: 'POST',
            data: {
                name: name
            },
            success: function(response) {
                jQuery('#newTokenModal').modal('hide');
                jQuery('#tokenName').val('');

                leantime.notification.show(leantime.i18n.__('notifications.token_created_copy_now'));

                // Show token in a modal
                leantime.snippets.copyToClipboard(response.token);

                // Reload the tokens table
                window.location.reload();
            },
            error: function(xhr) {
                leantime.notification.show(xhr.responseJSON.error, 'error');
            }
        });
    }

    function deleteToken(id) {
        if (!confirm(leantime.i18n.__('notifications.confirm_token_delete'))) {
            return;
        }

        jQuery.ajax({
            url: leantime.appUrl + '/advancedAuth/personalTokens/delete/' + id,
            type: 'DELETE',
            success: function() {
                leantime.notification.show(leantime.i18n.__('notifications.token_deleted'));
                window.location.reload();
            },
            error: function() {
                leantime.notification.show(leantime.i18n.__('notifications.token_delete_error'), 'error');
            }
        });
    }

    return {
        showNewTokenModal: showNewTokenModal,
        createToken: createToken,
        deleteToken: deleteToken
    };
})();
