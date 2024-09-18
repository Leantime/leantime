import jQuery from 'jquery';

const handleAsyncResponse = function (response) {
    if (
        response === undefined
        || response.result === undefined
        || response.result.html === undefined
    ) {
        return;
    }

    var content = jQuery(response.result.html);
    jQuery('body').append(content);
};

export default handleAsyncResponse;
