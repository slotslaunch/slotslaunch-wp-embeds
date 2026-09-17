<?php
/**
 * Plugin Name:       Slots Launch Embeds
 * Plugin URI:        https://github.com/slotslaunch/slotslaunch-wp-embeds
 * Description:       Embed Slots Launch demo games with signed shortcodes. Lightweight — no game sync.
 * Version:           1.0.3
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Slots Launch
 * Author URI:        https://slotslaunch.com
 * License:           MIT
 * Text Domain:       slotslaunch-wp-embeds
 * Update URI:        https://slotslaunch.com
 */

if (! defined('ABSPATH')) {
    exit;
}

define('SLOTSLAUNCH_WP_EMBEDS_VERSION', '1.0.3');
define('SLOTSLAUNCH_WP_EMBEDS_FILE', __FILE__);
define('SLOTSLAUNCH_WP_EMBEDS_PATH', plugin_dir_path(__FILE__));

$autoload = SLOTSLAUNCH_WP_EMBEDS_PATH . 'vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    add_action('admin_notices', static function (): void {
        if (! current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p>';
        esc_html_e('Slots Launch Embeds: run composer install in the plugin directory, or download a release build that includes vendor/.', 'slotslaunch-wp-embeds');
        echo '</p></div>';
    });

    return;
}

require_once SLOTSLAUNCH_WP_EMBEDS_PATH . 'includes/class-plugin.php';

SlotsLaunch_WP_Embeds\Plugin::instance();

if (! function_exists('slotslaunch_game_url')) {
    /**
     * Signed demo-game URL with no markup. Expires immediately in the HTML — do not use on cached pages.
     * For cached pages use [slotslaunch_url] or data-sl-game-url.
     *
     * @param int $game_id
     */
    function slotslaunch_game_url($game_id): string
    {
        $game_id = (int) $game_id;
        $client = SlotsLaunch_WP_Embeds\Settings::client();
        if ($client === null || $game_id < 1) {
            return '';
        }

        $ttl = (int) apply_filters('slotslaunch_wp_embeds/url_ttl', 3600, $game_id);

        return $client->iframeUrl($game_id, $ttl);
    }
}
