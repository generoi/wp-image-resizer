<?php

namespace GeneroWP\ImageResizer\Resizers;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Resizer;

/**
 * @see https://www.keycdn.com/support/image-processing
 */
class KeyCdn implements Resizer
{
    public function buildUrl(string $sourceUrl, array $settings = []): string
    {
        $sourceHost = parse_url($sourceUrl, PHP_URL_HOST);
        $targetHost = parse_url(Config::zone(), PHP_URL_HOST);
        $url = str_replace($sourceHost, $targetHost, $sourceUrl);

        foreach ($settings as $key => $value) {
            $url = add_query_arg($key, $value, $url);
        }
        return $url;
    }

    public function defaultSettings(): array
    {
        return [
            'format' => 'webp',
        ];
    }

    public function focalPointParam(float $left, float $top): array
    {
        return [
            'crop' => sprintf('fp,%s,%s', $left, $top),
        ];
    }
}
