<?php

namespace SlotsLaunch_WP_Embeds;

use SlotsLaunch\Client;

if (! defined('ABSPATH')) {
    exit;
}

final class Settings
{
    public const OPTION_KEY = 'slotslaunch_wp_embeds_settings';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);
        add_action('admin_notices', [self::class, 'maybeShowMissingSecretNotice']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        add_action('wp_ajax_slotslaunch_wp_embeds_check_license', [self::class, 'ajaxCheckLicense']);
    }

    public static function addMenu(): void
    {
        add_options_page(
            __('Slots Launch Embeds', 'slotslaunch-wp-embeds'),
            __('Slots Launch Embeds', 'slotslaunch-wp-embeds'),
            'manage_options',
            'slotslaunch-wp-embeds',
            [self::class, 'renderPage']
        );
    }

    public static function registerSettings(): void
    {
        register_setting(
            'slotslaunch_wp_embeds',
            self::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitize'],
                'default' => [],
            ]
        );

        add_settings_section(
            'slotslaunch_wp_embeds_main',
            __('API credentials', 'slotslaunch-wp-embeds'),
            static function (): void {
                echo '<p>';
                esc_html_e('Enter your API key and API secret from Launch Pad → API. The secret is stored server-side only and used to sign iframe URLs.', 'slotslaunch-wp-embeds');
                echo '</p>';
            },
            'slotslaunch-wp-embeds'
        );

        add_settings_field(
            'api_key',
            __('API key', 'slotslaunch-wp-embeds'),
            [self::class, 'renderApiKeyField'],
            'slotslaunch-wp-embeds',
            'slotslaunch_wp_embeds_main'
        );

        add_settings_field(
            'api_secret',
            __('API secret', 'slotslaunch-wp-embeds'),
            [self::class, 'renderApiSecretField'],
            'slotslaunch-wp-embeds',
            'slotslaunch_wp_embeds_main'
        );

        add_settings_field(
            'site_domain',
            __('Site domain', 'slotslaunch-wp-embeds'),
            [self::class, 'renderSiteDomainField'],
            'slotslaunch-wp-embeds',
            'slotslaunch_wp_embeds_main'
        );
    }

    /**
     * @param array<string, mixed>|mixed $input
     * @return array<string, string>
     */
    public static function sanitize($input): array
    {
        if (! is_array($input)) {
            $input = [];
        }

        $existing = self::getStored();

        $apiSecret = isset($input['api_secret']) ? trim((string) $input['api_secret']) : '';
        if ($apiSecret === '') {
            $apiSecret = $existing['api_secret'] ?? '';
        }

        $storedPlan = $existing['plan'] ?? '';

        return [
            'api_key' => sanitize_text_field($input['api_key'] ?? ''),
            'api_secret' => $apiSecret,
            'site_domain' => self::normalizeDomain((string) ($input['site_domain'] ?? '')),
            'plan' => is_string($storedPlan) ? $storedPlan : '',
        ];
    }

    public static function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $settings = self::get();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Slots Launch Embeds', 'slotslaunch-wp-embeds'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('slotslaunch_wp_embeds');
                do_settings_sections('slotslaunch-wp-embeds');
                submit_button();
                ?>
            </form>
            <hr>
            <h2><?php esc_html_e('Shortcode', 'slotslaunch-wp-embeds'); ?></h2>
            <p><?php esc_html_e('Paste a shortcode into any post or page. Find game IDs in Launch Pad.', 'slotslaunch-wp-embeds'); ?></p>
            <code>[slotslaunch_game id="45958"]</code>
            <p class="description"><?php esc_html_e('Optional attributes: height="600" width="100%"', 'slotslaunch-wp-embeds'); ?></p>
            <p class="description"><?php esc_html_e('Games load via AJAX so full-page cache plugins do not serve expired signed URLs.', 'slotslaunch-wp-embeds'); ?></p>
            <?php if ($settings['api_key'] === '' || $settings['api_secret'] === '') : ?>
                <div class="notice notice-warning inline">
                    <p><?php esc_html_e('Add your API key and API secret before November 15, 2026 to keep embeds working after the signed-embed upgrade.', 'slotslaunch-wp-embeds'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function renderApiKeyField(): void
    {
        $settings = self::get();
        $value = esc_attr($settings['api_key']);
        echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[api_key]" value="' . $value . '" autocomplete="off">';
        echo ' <button type="button" class="button slotslaunch-wp-embeds-verify-key">' . esc_html__('Verify Key', 'slotslaunch-wp-embeds') . '</button>';
        echo '<div id="slotslaunch-wp-embeds-license-response" style="margin-top:8px;"></div>';
        if ($settings['plan'] !== '') {
            echo '<p class="description">' . esc_html(sprintf(
                /* translators: %s: plan name (free or premium) */
                __('Verified plan: %s', 'slotslaunch-wp-embeds'),
                $settings['plan']
            )) . '</p>';
        }
        echo '<p class="description">' . esc_html__('From Launch Pad → API → API key. Also used for plugin updates.', 'slotslaunch-wp-embeds') . '</p>';
    }

    public static function renderApiSecretField(): void
    {
        $hasSecret = self::get()['api_secret'] !== '';
        echo '<input type="password" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[api_secret]" value="" autocomplete="new-password" placeholder="' . esc_attr($hasSecret ? '••••••••' : '') . '">';
        echo '<p class="description">' . esc_html__('Leave blank to keep the current secret. Never share this value.', 'slotslaunch-wp-embeds') . '</p>';
    }

    public static function renderSiteDomainField(): void
    {
        $value = esc_attr(self::get()['site_domain']);
        echo '<input type="text" class="regular-text" name="' . esc_attr(self::OPTION_KEY) . '[site_domain]" value="' . $value . '">';
        echo '<p class="description">' . esc_html__('Registered website domain without www. Defaults to this WordPress site.', 'slotslaunch-wp-embeds') . '</p>';
    }

    public static function maybeShowMissingSecretNotice(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && $screen->id === 'settings_page_slotslaunch-wp-embeds') {
            return;
        }

        $settings = self::get();
        if ($settings['api_key'] !== '' && $settings['api_secret'] !== '') {
            return;
        }

        $url = admin_url('options-general.php?page=slotslaunch-wp-embeds');
        echo '<div class="notice notice-warning"><p>';
        printf(
            wp_kses(
                __('Slots Launch Embeds: add your <a href="%s">API key and API secret</a> before November 15, 2026.', 'slotslaunch-wp-embeds'),
                ['a' => ['href' => []]]
            ),
            esc_url($url)
        );
        echo '</p></div>';
    }

    /**
     * @return array{api_key: string, api_secret: string, site_domain: string, plan: string}
     */
    public static function get(): array
    {
        $stored = self::getStored();

        $apiKey = self::constantOrValue('SLOTSLAUNCH_API_KEY', $stored['api_key'] ?? '');
        $apiSecret = self::constantOrValue('SLOTSLAUNCH_API_SECRET', $stored['api_secret'] ?? '');

        $siteDomain = self::constantOrValue('SLOTSLAUNCH_SITE_DOMAIN', $stored['site_domain'] ?? '');
        if ($siteDomain === '') {
            $siteDomain = self::defaultSiteDomain();
        }

        return [
            'api_key' => trim($apiKey),
            'api_secret' => trim($apiSecret),
            'site_domain' => self::normalizeDomain($siteDomain),
            'plan' => trim((string) ($stored['plan'] ?? '')),
        ];
    }

    public static function enqueueAdminAssets(string $hook): void
    {
        if ($hook !== 'settings_page_slotslaunch-wp-embeds') {
            return;
        }

        wp_enqueue_script(
            'slotslaunch-wp-embeds-admin',
            plugins_url('assets/js/admin.js', SLOTSLAUNCH_WP_EMBEDS_FILE),
            ['jquery'],
            SLOTSLAUNCH_WP_EMBEDS_VERSION,
            true
        );

        wp_localize_script(
            'slotslaunch-wp-embeds-admin',
            'slotslaunchWpEmbedsAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('slotslaunch_wp_embeds_admin'),
                'validMessage' => __('License is valid, and your subscription is active.', 'slotslaunch-wp-embeds'),
                'enterKey' => __('Please enter your API key.', 'slotslaunch-wp-embeds'),
                'errorMessage' => __('License check failed.', 'slotslaunch-wp-embeds'),
                'missingNonce' => __('Missing security token. Refresh the page.', 'slotslaunch-wp-embeds'),
            ]
        );
    }

    public static function ajaxCheckLicense(): void
    {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Forbidden', 'slotslaunch-wp-embeds')], 403);
        }

        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash((string) $_REQUEST['nonce'])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, 'slotslaunch_wp_embeds_admin')) {
            wp_send_json_error(['message' => __('Invalid security token. Please refresh the page and try again.', 'slotslaunch-wp-embeds')], 403);
        }

        if (empty($_POST['license']) || ! is_scalar($_POST['license'])) {
            wp_send_json(['error' => __('Please enter your API key.', 'slotslaunch-wp-embeds')]);
        }

        $license = sanitize_text_field(wp_unslash((string) $_POST['license']));
        $response = License::validate($license);

        if (empty($response->success)) {
            wp_send_json($response);
        }

        $stored = self::getStored();
        $stored['api_key'] = $license;
        $stored['plan'] = License::planFromResponse($response);
        update_option(self::OPTION_KEY, $stored);

        wp_send_json($response);
    }

    public static function client(): ?Client
    {
        $settings = self::get();
        if ($settings['api_key'] === '' || $settings['api_secret'] === '' || $settings['site_domain'] === '') {
            return null;
        }

        return new Client(
            $settings['api_key'],
            $settings['api_secret'],
            $settings['site_domain']
        );
    }

    /**
     * @return array<string, string>
     */
    private static function getStored(): array
    {
        $stored = get_option(self::OPTION_KEY, []);

        return is_array($stored) ? $stored : [];
    }

    private static function constantOrValue(string $constant, string $fallback): string
    {
        return defined($constant) ? (string) constant($constant) : $fallback;
    }

    private static function defaultSiteDomain(): string
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);

        return is_string($host) ? self::normalizeDomain($host) : '';
    }

    private static function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        return str_replace('www.', '', $domain);
    }
}
