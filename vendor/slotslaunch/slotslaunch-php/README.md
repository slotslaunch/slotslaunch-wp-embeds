# slotslaunch-php

Minimal PHP library for **signed Slots Launch iframe embeds** and **signed API requests**. No dependencies — PHP 7.4+.

Repository: [github.com/slotslaunch/slotslaunch-php](https://github.com/slotslaunch/slotslaunch-php)

## Install

```bash
composer require slotslaunch/slotslaunch-php
```

Or copy `src/Client.php` into your project.

## Credentials

From **Launch Pad → API**:

| Credential | Use |
|------------|-----|
| **API key** | Public site id (`token` query param) |
| **API secret** | Server-side signing only — never in HTML or browser JS |

Sites that share the same API key share the same API secret.

## Signed iframe

```php
use SlotsLaunch\Client;

$sl = new Client(
    apiKey: 'your-api-key',
    apiSecret: 'your-api-secret',
    siteDomain: 'yourdomain.com',
);

$url = $sl->iframeUrl(45958);
```

```html
<iframe src="<?php echo htmlspecialchars($url); ?>" width="100%" height="600" frameborder="0"></iframe>
```

## Signed API request

```php
$req = $sl->apiRequest('GET', '/api/games');

$ch = curl_init($req['url']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Origin: yourdomain.com',
        'X-SL-Timestamp: ' . $req['headers']['X-SL-Timestamp'],
        'X-SL-Signature: ' . $req['headers']['X-SL-Signature'],
    ],
]);
$response = curl_exec($ch);
```

## WordPress

Non-developers can use the lightweight **[Slots Launch Embeds](https://github.com/slotslaunch/slotslaunch-wp-embeds)** plugin instead of wiring this library manually.

## Deadline

Signed embeds and API calls are required from **November 15, 2026**. See [Getting Started](https://docs.slotslaunch.com/article/11-getting-started).
