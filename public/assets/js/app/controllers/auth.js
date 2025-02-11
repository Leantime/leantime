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

                leantime.notification.show('Token created successfully. Make sure to copy your token now, you won\'t be able to see it again!');

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
        if (!confirm('Are you sure you want to delete this token?')) {
            return;
        }

        jQuery.ajax({
            url: leantime.appUrl + '/advancedAuth/personalTokens/delete/' + id,
            type: 'DELETE',
            success: function() {
                leantime.notification.show('Token deleted successfully');
                window.location.reload();
            },
            error: function() {
                leantime.notification.show('Error deleting token', 'error');
            }
        });
    }

    return {
        showNewTokenModal: showNewTokenModal,
        createToken: createToken,
        deleteToken: deleteToken
    };
})();
