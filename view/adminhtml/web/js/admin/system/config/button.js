define([
    'jquery'
], function ($) {
    return function (config) {
        $('#run_cron').on('click', function () {
            $.ajax({
                url: config.ajaxUrl,
                type: 'post',
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.success) {
                        alert('Cron run successfully.');
                    } else {
                        alert('An error occurred while changing order status.  Please check logs');
                    }
                },
                error: function (response) {
                    alert('An error occurred while processing the request.' + response.responseText);
                }
            });
        });
    };
});
