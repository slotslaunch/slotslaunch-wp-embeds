<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Ajax
{
    private const MAX_GAMES = 50;

    public static function register(): void
    {
        add_action('wp_ajax_slotslaunch_embed_url', [self::class, 'embedUrl']);
        add_action('wp_ajax_nopriv_slotslaunch_embed_url', [self::class, 'embedUrl']);
    }

    public static function embedUrl(): void
    {
        check_ajax_referer('slotslaunch_wp_embeds', 'nonce');

        $gameIds = self::requestedGameIds();
        if ($gameIds === []) {
            wp_send_json_error(['message' => __('Missing game id.', 'slotslaunch-wp-embeds')], 400);
        }

        $client = Settings::client();
        if ($client === null) {
            wp_send_json_error(['message' => __('Plugin is not configured.', 'slotslaunch-wp-embeds')], 403);
        }

        $urls = [];
        $ttl = 3600;
        foreach ($gameIds as $gameId) {
            $ttl = (int) apply_filters('slotslaunch_wp_embeds/url_ttl', 3600, $gameId);
            $urls[(string) $gameId] = $client->iframeUrl($gameId, $ttl);
        }

        $data = [
            'urls' => $urls,
            'expires_in' => $ttl,
        ];

        if (count($gameIds) === 1) {
            $data['url'] = reset($urls);
        }

        wp_send_json_success($data);
    }

    /**
     * @return list<int>
     */
    private static function requestedGameIds(): array
    {
        $ids = [];

        if (isset($_REQUEST['games'])) {
            $raw = wp_unslash($_REQUEST['games']);
            $parts = is_array($raw) ? $raw : preg_split('/[,\s]+/', (string) $raw);
            if (is_array($parts)) {
                foreach ($parts as $part) {
                    $id = absint($part);
                    if ($id > 0) {
                        $ids[$id] = $id;
                    }
                }
            }
        }

        if (isset($_REQUEST['game'])) {
            $id = absint($_REQUEST['game']);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        $ids = array_values($ids);
        if (count($ids) > self::MAX_GAMES) {
            $ids = array_slice($ids, 0, self::MAX_GAMES);
        }

        return $ids;
    }
}
