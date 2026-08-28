(function ($) {
    'use strict';

    $(document).ready(function () {
        $('.slotslaunch-wp-embeds-verify-key').on('click', function (e) {
            e.preventDefault();

            var $button = $(this);
            var $message = $('#slotslaunch-wp-embeds-license-response');
            var apiKey = $('input[name="slotslaunch_wp_embeds_settings[api_key]"]').val();

            if (!window.slotslaunchWpEmbedsAdmin || !window.slotslaunchWpEmbedsAdmin.nonce) {
                $message.html('<p style="color:red">' + (window.slotslaunchWpEmbedsAdmin?.missingNonce || 'Missing security token. Refresh the page.') + '</p>').show();
                return;
            }

            if (!apiKey) {
                $message.html('<p style="color:red">' + (window.slotslaunchWpEmbedsAdmin.enterKey || 'Please enter your API key.') + '</p>').show();
                return;
            }

            $button.prop('disabled', true);
            $message.html('').hide();

            $.ajax({
                url: window.slotslaunchWpEmbedsAdmin.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'slotslaunch_wp_embeds_check_license',
                    license: apiKey,
                    nonce: window.slotslaunchWpEmbedsAdmin.nonce
                },
                success: function (response) {
                    $button.prop('disabled', false);

                    if (response.error) {
                        $message.html('<p style="color:red">' + response.error + '</p>').show();
                        return;
                    }

                    if (response.success === false) {
                        var err = (response.data && response.data.message) ? response.data.message : 'License check failed';
                        $message.html('<p style="color:red">' + err + '</p>').show();
                        return;
                    }

                    var ok = response.success === true
                        ? (response.data || window.slotslaunchWpEmbedsAdmin.validMessage || 'License verified.')
                        : response.success;
                    $message.html('<p style="color:green">' + ok + '</p>').show();
                },
                error: function (xhr) {
                    $button.prop('disabled', false);
                    var msg = window.slotslaunchWpEmbedsAdmin.errorMessage || 'License check failed';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    $message.html('<p style="color:red">' + msg + '</p>').show();
                }
            });
        });
    });
})(jQuery);
