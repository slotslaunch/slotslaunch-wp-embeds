<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Ajax
{
    public static function register(): void
    {
        add_action('wp_ajax_slotslaunch_embed_url', [self::class, 'embedUrl']);
        add_action('wp_ajax_nopriv_slotslaunch_embed_url', [self::class, 'embedUrl']);
    }

    public static function embedUrl(): void
    {
        check_ajax_referer('slotslaunch_wp_embeds', 'nonce');

        $gameId = isset($_REQUEST['game']) ? absint($_REQUEST['game']) : 0;
        if ($gameId < 1) {
            wp_send_json_error(['message' => __('Missing game id.', 'slotslaunch-wp-embeds')], 400);
        }

        $client = Settings::client();
        if ($client === null) {
            wp_send_json_error(['message' => __('Plugin is not configured.', 'slotslaunch-wp-embeds')], 403);
        }

        wp_send_json_success([
            'url' => $client->iframeUrl($gameId),
            'expires_in' => (int) apply_filters('slotslaunch_wp_embeds/url_ttl', 3600, $gameId),
        ]);
    }
}
