# Slots Launch Embeds (WordPress)

Lightweight WordPress plugin for **signed Slots Launch game embeds**. Install, add your API credentials, paste a shortcode — no game sync, no custom code.

Repository: [github.com/slotslaunch/slotslaunch-wp-embeds](https://github.com/slotslaunch/slotslaunch-wp-embeds)

For full game sync, rankings, and advanced features, use the main Slots Launch WordPress plugin.

## Install

1. Download a [release](https://github.com/slotslaunch/slotslaunch-wp-embeds/releases) (includes `vendor/`), or clone and run `composer install --no-dev`.
2. Upload to `wp-content/plugins/slotslaunch-wp-embeds/` and activate.
3. Go to **Settings → Slots Launch Embeds**.
4. Enter your **API key** and **API secret** from Launch Pad → API.

## Shortcode

```
[slotslaunch_game id="45958"]
```

Optional attributes:

```
[slotslaunch_game id="45958" height="600" width="100%"]
```

Alias: `[slotslaunch id="45958"]`

Game IDs are shown in Launch Pad. You can also use **Copy Shortcode** on any game page.

## wp-config.php (optional)

```php
define('SLOTSLAUNCH_API_KEY', 'your-api-key');
define('SLOTSLAUNCH_API_SECRET', 'your-api-secret');
define('SLOTSLAUNCH_SITE_DOMAIN', 'yourdomain.com');
```

Constants override saved settings. The API secret must never appear in theme files or front-end JavaScript.

## How it works

The plugin uses [slotslaunch-php](https://github.com/slotslaunch/slotslaunch-php) to sign iframe URLs **on the server** when WordPress renders the shortcode. Your API secret never leaves PHP.

## Deadline

Signed embeds are required from **November 15, 2026**. This plugin signs URLs automatically once configured.
