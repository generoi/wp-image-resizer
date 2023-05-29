<?php

namespace GeneroWP\ImageResizer\Resizers;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Resizer;

class Cloudflare implements Resizer
{
    public function buildUrl(string $sourceUrl, array $settings = []): string
    {
        return sprintf(
            '%s/%s/%s',
            untrailingslashit(Config::zone()),
            urlencode($this->serializeSettings($settings)),
            $sourceUrl,
        );
    }

    /**
     * @param array<string,int|string> $settings
     */
    protected function serializeSettings(array $settings): string
    {
        $params = [];
        foreach ($settings as $key => $value) {
            $params[] = "$key=$value";
        }
        return implode(',', $params);
    }

    public function defaultSettings(): array
    {
        return [
            'quality' => 82,
            'fit' => 'scale-down',
            'format' => 'auto',
        ];
    }

    public function focalPointParam(float $left, float $top): array
    {
        return [
            'gravity' => sprintf('%sx%s', $left, $top),
        ];
    }
}
