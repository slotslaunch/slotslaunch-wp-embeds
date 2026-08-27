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
    public static function render($atts, ?string $content = null): string
    {
        $atts = shortcode_atts(
            [
                'id' => '',
                'game' => '',
                'height' => '600',
                'width' => '100%',
                'autoload' => 'true',
            ],
            is_array($atts) ? $atts : [],
            'slotslaunch_game'
        );

        $gameId = (int) ($atts['id'] !== '' ? $atts['id'] : $atts['game']);
        if ($gameId < 1) {
            return self::message(__('Slots Launch: missing game id.', 'slotslaunch-wp-embeds'));
        }

        if (Settings::client() === null) {
            return self::message(__('Slots Launch: configure API key and API secret in Settings → Slots Launch Embeds.', 'slotslaunch-wp-embeds'));
        }

        Assets::enqueue();

        $height = self::sanitizeCssSize((string) $atts['height'], '600px');
        $width = self::sanitizeCssSize((string) $atts['width'], '100%');
        $title = sprintf(__('Slots Launch game %d', 'slotslaunch-wp-embeds'), $gameId);
        $autoload = self::parseBool((string) $atts['autoload'], true);
        $placeholder = trim((string) $content);

        if ($autoload) {
            return sprintf(
                '<div class="slotslaunch-embed slotslaunch-embed--loading" data-sl-game="%1$d" data-sl-height="%2$s" data-sl-title="%3$s" style="width:%4$s;max-width:100%%;min-height:%2$s;"><div class="slotslaunch-embed-loading" style="display:flex;align-items:center;justify-content:center;min-height:%2$s;color:#666;font:14px/1.4 system-ui,sans-serif;">%5$s</div></div>',
                $gameId,
                esc_attr($height),
                esc_attr($title),
                esc_attr($width),
                esc_html__('Loading game…', 'slotslaunch-wp-embeds')
            );
        }

        $inner = $placeholder !== '' ? wp_kses_post($placeholder) : '';

        return sprintf(
            '<div class="slotslaunch-embed slotslaunch-embed--manual" data-sl-game="%1$d" data-sl-height="%2$s" data-sl-title="%3$s" data-sl-autoload="0" style="width:%4$s;max-width:100%%;min-height:%2$s;">%5$s</div>',
            $gameId,
            esc_attr($height),
            esc_attr($title),
            esc_attr($width),
            $inner
        );
    }

    private static function parseBool(string $value, bool $default): bool
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return $default;
        }

        if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
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
