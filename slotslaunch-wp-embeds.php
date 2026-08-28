<?php
/**
 * Plugin Name:       Slots Launch Embeds
 * Plugin URI:        https://github.com/slotslaunch/slotslaunch-wp-embeds
 * Description:       Embed Slots Launch demo games with signed shortcodes. Lightweight — no game sync.
 * Version:           1.0.2
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

define('SLOTSLAUNCH_WP_EMBEDS_VERSION', '1.0.2');
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
