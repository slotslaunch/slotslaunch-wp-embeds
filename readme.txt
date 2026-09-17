=== Slots Launch Embeds ===
Contributors: slotslaunch
Tags: slots, casino, iframe, embed, games
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 8
Stable tag: 1.0.3
License: MIT

Embed Slots Launch demo games with signed shortcodes. Lightweight — no game sync.

== Description ==

Slots Launch Embeds is a tiny plugin for sites that only need to display demo games — not sync the full game catalog.

1. Install and activate the plugin.
2. Add your API key and API secret under Settings → Slots Launch Embeds (from Launch Pad → API).
3. Paste a shortcode into any post or page:

`[slotslaunch_game id="45958"]`

To get a cache-safe game URL (signed via AJAX on each page load), put this inside your own HTML:

`[slotslaunch_url id="45958"]`

Example:

`<a href="#" target="_blank" rel="noopener noreferrer">Play demo [slotslaunch_url id="45958"]</a>`

Or mark your own element: `data-sl-game-url="45958"`.

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

= How do I get just the game URL? =

`[slotslaunch_url id="45958"]` is a cache-safe placeholder. On each page load it fetches a fresh signed URL over AJAX and applies it to the surrounding `<a href>` or `<iframe src>`. Example: `<a href="#">Play [slotslaunch_url id="45958"]</a>`. You can also set `data-sl-game-url="45958"` on your own tag. `slotslaunch_game_url()` signs immediately and should not be used on cached pages.

= Where do I get API credentials? =

Launch Pad → API. Register your website domain (without www.) before embedding.

== Changelog ==

= 1.0.3 =
* Added cache-safe `[slotslaunch_url]`: fetches a fresh signed URL via AJAX on each page load.
* Sign all game URLs on a page in one AJAX request.

= 1.0.2 =
* Added autoload attr to the shortcode and placeholders

= 1.0.1 =
* Cache-friendly embeds: sign iframe URLs via AJAX instead of baking exp/sig into cached HTML.

= 1.0.0 =
* Initial release: settings page, signed iframe shortcode.
