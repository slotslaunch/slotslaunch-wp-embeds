<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class ApiClient
{
    /**
     * @param array<string, scalar> $args Query args (token added from settings when omitted).
     */
    public static function get(string $endpoint, array $args = []): object|false
    {
        $settings = Settings::get();

        if (empty($args['token'])) {
            $args['token'] = $settings['api_key'];
        }

        $apiPath = '/api/' . ltrim($endpoint, '/');
        $client = Settings::client();

        if ($client !== null) {
            $request = $client->apiRequest('GET', $apiPath);
            $url = $request['url'];
            $extra = $args;
            unset($extra['token']);
            if ($extra !== []) {
                $url = add_query_arg($extra, $url);
            }
            $headers = array_merge(
                [
                    'Accept' => 'application/json',
                    'Origin' => $settings['site_domain'],
                ],
                $request['headers']
            );
        } else {
            $url = add_query_arg(
                $args,
                rtrim(self::apiUrl(), '/') . '/' . ltrim($endpoint, '/')
            );
            $headers = [
                'Accept' => 'application/json',
                'Origin' => $settings['site_domain'],
            ];
        }

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'headers' => $headers,
                'sslverify' => false,
            ]
        );

        if (is_wp_error($response)) {
            return (object) [
                'error' => $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            return (object) [
                'error' => wp_strip_all_tags($body),
                'code' => $code,
            ];
        }

        $decoded = json_decode(wp_strip_all_tags($body));

        return is_object($decoded) ? $decoded : false;
    }

    public static function apiUrl(): string
    {
        return (string) apply_filters('slotslaunch_wp_embeds/api_url', 'https://slotslaunch.com/api/');
    }
}
