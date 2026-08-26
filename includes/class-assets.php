<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Assets
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'registerScript']);
    }

    public static function registerScript(): void
    {
        wp_register_script(
            'slotslaunch-wp-embeds',
            plugins_url('assets/js/embed.js', SLOTSLAUNCH_WP_EMBEDS_FILE),
            [],
            SLOTSLAUNCH_WP_EMBEDS_VERSION,
            true
        );

        wp_localize_script(
            'slotslaunch-wp-embeds',
            'slotslaunchEmbeds',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('slotslaunch_wp_embeds'),
            ]
        );
    }

    public static function enqueue(): void
    {
        wp_enqueue_script('slotslaunch-wp-embeds');
    }
}
