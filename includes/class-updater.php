<?php

namespace SlotsLaunch_WP_Embeds;

if (! defined('ABSPATH')) {
    exit;
}

final class Updater
{
    private string $name;

    private string $slug;

    private string $version;

    private string $cacheKey;

    public static function register(): void
    {
        new self();
    }

    private function __construct()
    {
        $this->name = plugin_basename(SLOTSLAUNCH_WP_EMBEDS_FILE);
        $this->slug = basename(SLOTSLAUNCH_WP_EMBEDS_FILE, '.php');
        $this->version = SLOTSLAUNCH_WP_EMBEDS_VERSION;
        $this->cacheKey = 'slotslaunch_wp_embeds_' . md5($this->slug);

        add_filter('pre_set_site_transient_update_plugins', [$this, 'checkUpdate']);
        add_filter('plugins_api', [$this, 'pluginsApiFilter'], 10, 3);
        add_action('init', [$this, 'trackVersion']);
    }

    /**
     * @param object|false $transient
     * @return object
     */
    public function checkUpdate($transient)
    {
        global $pagenow;

        if (! is_object($transient)) {
            $transient = new \stdClass();
        }

        if ($pagenow === 'plugins.php' && is_multisite()) {
            return $transient;
        }

        if (
            ! empty($transient->response)
            && ! empty($transient->response[$this->name])
        ) {
            return $transient;
        }

        $pluginInfo = $this->getCachedVersionInfo();

        if ($pluginInfo === false) {
            $pluginInfo = $this->apiRequest('info');
        }

        if (
            $pluginInfo !== false
            && is_object($pluginInfo)
            && isset($pluginInfo->version)
        ) {
            if (version_compare($this->version, $pluginInfo->version, '<')) {
                $transient->response[$this->name] = (object) [
                    'new_version' => $pluginInfo->version,
                    'package' => $pluginInfo->download_link,
                    'slug' => $this->slug,
                ];
            }

            $transient->last_checked = current_time('timestamp');
            $transient->checked[$this->name] = $this->version;
        }

        return $transient;
    }

    /**
     * @return object|false
     */
    private function getCachedVersionInfo(string $cacheKey = '')
    {
        if ($cacheKey === '') {
            $cacheKey = $this->cacheKey;
        }

        $cache = get_option($cacheKey);

        if (empty($cache['timeout']) || current_time('timestamp') > $cache['timeout']) {
            return false;
        }

        $decoded = json_decode((string) $cache['value']);

        return is_object($decoded) ? $decoded : false;
    }

    /**
     * @return object|false
     */
    private function apiRequest(string $action)
    {
        $cached = $this->getCachedVersionInfo($this->cacheKey);
        if ($cached !== false) {
            return $cached;
        }

        $request = ApiClient::get('plugins/' . $action, ['slug' => $this->slug]);

        if ($request === false || isset($request->error)) {
            return false;
        }

        $data = $this->parseRequest($request);
        if ($data === false) {
            return false;
        }

        $this->setVersionInfoCache($data);

        return $data;
    }

    /**
     * @return object|false
     */
    private function parseRequest(object $request)
    {
        if (! isset($request->id)) {
            return false;
        }

        return (object) [
            'name' => $request->name ?? '',
            'version' => $request->version,
            'slug' => $request->slug,
            'download_link' => $request->download_link,
            'tested' => $request->tested ?? '',
            'requires' => $request->requires ?? '',
            'last_updated' => $request->updated_at ?? '',
            'homepage' => $request->plugin_url ?? '',
            'sections' => [
                'description' => $request->description ?? '',
                'changelog' => $request->changelog ?? '',
            ],
            'banners' => [
                'low' => $request->banner_low ?? '',
                'high' => $request->banner_high ?? '',
            ],
            'external' => true,
        ];
    }

    private function setVersionInfoCache(object $value): void
    {
        update_option(
            $this->cacheKey,
            [
                'timeout' => strtotime('+3 hours', current_time('timestamp')),
                'value' => wp_json_encode($value),
            ]
        );
    }

    /**
     * @param object|false $data
     * @return object|false
     */
    public function pluginsApiFilter($data, string $action = '', $args = null)
    {
        if ($action !== 'plugin_information') {
            return $data;
        }

        if (! isset($args->slug) || $args->slug !== $this->slug) {
            return $data;
        }

        $data = $this->apiRequest('info');

        if (! is_object($data)) {
            return $data;
        }

        if (isset($data->sections) && ! is_array($data->sections)) {
            $sections = [];
            foreach ($data->sections as $key => $section) {
                $sections[$key] = $section;
            }
            $data->sections = $sections;
        }

        if (isset($data->banners) && ! is_array($data->banners)) {
            $banners = [];
            foreach ($data->banners as $key => $banner) {
                $banners[$key] = $banner;
            }
            $data->banners = $banners;
        }

        return $data;
    }

    public function trackVersion(): void
    {
        $stored = get_option('slotslaunch_wp_embeds_version', '0');
        if ($stored !== SLOTSLAUNCH_WP_EMBEDS_VERSION) {
            update_option('slotslaunch_wp_embeds_version', SLOTSLAUNCH_WP_EMBEDS_VERSION);
        }
    }
}
