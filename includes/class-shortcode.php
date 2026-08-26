<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Shortcode
{
    public static function register(): void
    {
        add_shortcode('slotslaunch_game', [self::class, 'render']);
        add_shortcode('slotslaunch', [self::class, 'render']);
    }

    /**
     * @param array<string, string>|string $atts
     */
    public static function render($atts): string
    {
        $atts = shortcode_atts(
            [
                'id' => '',
                'game' => '',
                'height' => '600',
                'width' => '100%',
            ],
            is_array($atts) ? $atts : [],
            'slotslaunch_game'
        );

        $gameId = (int) ($atts['id'] !== '' ? $atts['id'] : $atts['game']);
        if ($gameId < 1) {
            return self::message(__('Slots Launch: missing game id.', 'slotslaunch-wp-embeds'));
        }

        $client = Settings::client();
        if ($client === null) {
            return self::message(__('Slots Launch: configure API key and API secret in Settings → Slots Launch Embeds.', 'slotslaunch-wp-embeds'));
        }

        $height = self::sanitizeCssSize((string) $atts['height'], '600px');
        $width = self::sanitizeCssSize((string) $atts['width'], '100%');

        $url = $client->iframeUrl($gameId);

        return sprintf(
            '<div class="slotslaunch-embed" style="width:%1$s;max-width:100%%;"><iframe src="%2$s" title="%3$s" width="100%%" height="%4$s" style="border:0;display:block;max-width:100%%;" allowfullscreen loading="lazy"></iframe></div>',
            esc_attr($width),
            esc_url($url),
            esc_attr(sprintf(__('Slots Launch game %d', 'slotslaunch-wp-embeds'), $gameId)),
            esc_attr($height)
        );
    }

    private static function sanitizeCssSize(string $value, string $fallback): string
    {
        $value = trim($value);
        if ($value === '') {
            return $fallback;
        }

        if (preg_match('/^\d+$/', $value)) {
            return $value . 'px';
        }

        if (preg_match('/^(\d+(?:\.\d+)?)(px|%|em|rem|vh)$/', $value)) {
            return $value;
        }

        return $fallback;
    }

    private static function message(string $text): string
    {
        if (! current_user_can('edit_posts')) {
            return '';
        }

        return '<p class="slotslaunch-embed-notice" style="padding:12px;border:1px solid #ddd;background:#f9f9f9;">' . esc_html($text) . '</p>';
    }
}
