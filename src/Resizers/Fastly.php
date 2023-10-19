<?php

namespace GeneroWP\ImageResizer\Resizers;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Resizer;

/**
 * @see https://developer.fastly.com/reference/io/
 */
class Fastly implements Resizer
{
    public function buildUrl(string $sourceUrl, array $settings = []): string
    {
        $sourceHost = parse_url($sourceUrl, PHP_URL_HOST);
        $targetHost = parse_url(Config::zone(), PHP_URL_HOST);

        // Only works if it's on the same host
        if ($sourceHost !== $targetHost) {
            return $sourceUrl;
        }

        $url = $sourceUrl;
        foreach ($settings as $key => $value) {
            $url = add_query_arg($key, urlencode($value), $url);
        }
        return $url;
    }

    public function defaultSettings(): array
    {
        return [
            'auto' => 'webp',
            'fit' => 'cover',
            'optimize' => 'high',
        ];
    }

    public function focalPointParam(float $left, float $top): array
    {
        return [];
    }
}
