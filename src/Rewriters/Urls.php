<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Rewriter;
use GeneroWP\ImageResizer\Image;

class Urls implements Rewriter
{
    public function __construct()
    {
        add_filter('wp_content_img_tag', [$this, 'filterImgTag']);
        add_filter('wp_get_attachment_image', [$this, 'filterImgTag']);
        add_filter('wp_calculate_image_srcset', [$this, 'filterSrcset'], 10, 5);
        add_filter('wp_resource_hints', [$this, 'dnsPrefetch'], -100, 2);
    }

    public function filterImgTag(string $html): string
    {
        $src = preg_match('/src="([^"]+)"/', $html, $matchSrc) ? $matchSrc[1] : '';
        if (! $src) {
            return $html;
        }

        $image = new Image($src);
        $html = preg_replace(
            '/ src="([^"]+)"/',
            sprintf(' src="%s"', $image->url()),
            $html
        );

        return $html;
    }

    /**
     * @param array<array{"url": string, "descriptor": string, "value": int}> $sources
     * @param int[] $size
     * @param array<string,mixed> $meta
     * @return array<array{"url": string, "descriptor": string, "value": int}>
     */
    public function filterSrcset(array $sources, array $size, string $src, array $meta, int $attachmentId): array
    {
        $sources = [];
        foreach (Config::breakpoints() as $breakpoint) {
            if ($breakpoint > $meta['width']) {
                continue;
            }
            $image = new Image($src, ['width' => $breakpoint]);
            $sources[$breakpoint] = [
                'url' => $image->url(),
                'descriptor' => 'w',
                'value' => $breakpoint,
            ];
        }

        return $sources;
    }

    /**
     * @param array<int,array<mixed>> $hints
     * @return array<int,array<mixed>>
     */
    public function dnsPrefetch(array $hints, string $type): array
    {
        if ($type === 'preconnect') {
            $hints[] = [
                'href' => '//' . parse_url(Config::zone(), PHP_URL_HOST),
                'crossorigin',
            ];
        }
        return $hints;
    }
}
