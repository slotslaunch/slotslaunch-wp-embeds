=== Slots Launch Embeds ===
Contributors: slotslaunch
Tags: slots, casino, iframe, embed, games
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.2
License: MIT

Embed Slots Launch demo games with signed shortcodes. Lightweight — no game sync.

== Description ==

Slots Launch Embeds is a tiny plugin for sites that only need to display demo games — not sync the full game catalog.

1. Install and activate the plugin.
2. Add your API key and API secret under Settings → Slots Launch Embeds (from Launch Pad → API).
3. Paste a shortcode into any post or page:

`[slotslaunch_game id="45958"]`

Iframe URLs are signed on your server. Your API secret is never exposed to visitors.

For full API sync and advanced features, use the main Slots Launch WordPress plugin.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/slotslaunch-wp-embeds/`.
2. Activate through the 'Plugins' menu.
3. Configure under Settings → Slots Launch Embeds.

Download a release build from GitHub if you do not run Composer locally (releases include `vendor/`).

== Frequently Asked Questions ==

= Where do I find the game id? =

In Launch Pad, open any game. Use **Copy Shortcode** or note the numeric id in the URL.

= Where do I get API credentials? =

Launch Pad → API. Register your website domain (without www.) before embedding.

== Changelog ==

= 1.0.2 =
* Added autoload attr to the shortcode and placeholders

= 1.0.1 =
* Cache-friendly embeds: sign iframe URLs via AJAX instead of baking exp/sig into cached HTML.

= 1.0.0 =
* Initial release: settings page, signed iframe shortcode.
