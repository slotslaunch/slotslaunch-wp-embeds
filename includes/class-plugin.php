<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        require_once SLOTSLAUNCH_WP_EMBEDS_PATH . 'includes/class-settings.php';
        require_once SLOTSLAUNCH_WP_EMBEDS_PATH . 'includes/class-shortcode.php';

        Settings::register();
        Shortcode::register();
    }
}
