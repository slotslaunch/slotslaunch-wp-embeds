<?php

namespace SlotsLaunch;

class Client
{
    private string $apiKey;

    private string $apiSecret;

    private string $siteDomain;

    private string $baseUrl;

    private int $iframeTtl;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $siteDomain,
        string $baseUrl = 'https://slotslaunch.com',
        int $iframeTtl = 3600
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->siteDomain = str_replace('www.', '', $siteDomain);
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->iframeTtl = $iframeTtl;
    }

    public function iframeUrl(int $gameId, ?int $ttl = null): string
    {
        $ttl = $ttl ?? $this->iframeTtl;
        $exp = time() + $ttl;
        $sig = $this->signEmbed($gameId, $exp);

        return $this->baseUrl . '/iframe/' . $gameId
            . '?token=' . rawurlencode($this->apiKey)
            . '&exp=' . $exp
            . '&sig=' . $sig;
    }

    /**
     * @return array<string, string>
     */
    public function apiHeaders(string $method, string $path, ?int $timestamp = null): array
    {
        $timestamp = $timestamp ?? time();
        $path = '/' . ltrim($path, '/');
        $payload = $timestamp . "\n" . strtoupper($method) . "\n" . $path;
        $signature = hash_hmac('sha256', $payload, $this->apiSecret);

        return [
            'X-SL-Timestamp' => (string) $timestamp,
            'X-SL-Signature' => $signature,
        ];
    }

    public function signEmbed(int $gameId, int $exp): string
    {
        $payload = $gameId . "\n" . $exp . "\n" . $this->siteDomain;

        return hash_hmac('sha256', $payload, $this->apiSecret);
    }

    /**
     * @return array{url: string, headers: array<string, string>}
     */
    public function apiRequest(string $method, string $path): array
    {
        $path = '/' . ltrim($path, '/');
        $separator = str_contains($path, '?') ? '&' : '?';

        return [
            'url' => $this->baseUrl . $path . $separator . 'token=' . rawurlencode($this->apiKey),
            'headers' => $this->apiHeaders($method, $path),
        ];
    }
}
