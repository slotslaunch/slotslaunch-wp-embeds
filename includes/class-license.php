<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class License
{
    public static function validate(string $token): object
    {
        $result = ApiClient::get('check-license', ['token' => $token]);

        if ($result === false) {
            return (object) [
                'error' => __('License check failed.', 'slotslaunch-wp-embeds'),
            ];
        }

        if (! isset($result->success)) {
            if (isset($result->code) && (int) $result->code === 401) {
                $result->error = __('License is invalid, or not valid for the current host.', 'slotslaunch-wp-embeds');
            }
        }

        return $result;
    }

    public static function planFromResponse(object $response): string
    {
        if (isset($response->plan) && $response->plan === 'Free') {
            return 'free';
        }

        return 'premium';
    }
}
